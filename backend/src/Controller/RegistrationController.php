<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Role;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Hasher le mot de passe
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );

                // Récupérer le rôle "En attente"
                $roleEnAttente = $entityManager->getRepository(Role::class)->findOneBy(['role' => 'En attente']);
                if (!$roleEnAttente) {
                    throw new \Exception('Le rôle "En attente" n\'existe pas dans la base de données');
                }
                $user->setRole($roleEnAttente);
                $user->setIsVerified(false);
                
                // Génération du token d'inscription
                $user->setRegistrationToken(bin2hex(random_bytes(32)));

                // Sauvegarde de l'utilisateur
                $entityManager->persist($user);
                $entityManager->flush();

                // Envoi des emails
                $this->sendAdminNotificationEmail($user, $mailer);
                $this->sendRegistrationConfirmationEmail($user, $mailer);
                
                // Redirection vers la page de confirmation
                return $this->render('registration/registration_pending.html.twig', [
                    'email' => $user->getEmail()
                ]);

            } catch (\Exception $e) {
                error_log('Erreur d\'inscription: ' . $e->getMessage());
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/registration/validate/{token}', name: 'app_registration_validate')]
    public function validateRegistration(string $token, Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['registrationToken' => $token]);

        if (!$user) {
            return $this->render('registration/invalid_token.html.twig');
        }

        // Récupérer tous les rôles disponibles sauf "Administrateur" et "En attente"
        $roles = $entityManager->getRepository(Role::class)->findAll();
        $availableRoles = array_values(array_filter($roles, function($role) {
            return !in_array($role->getRole(), ['Administrateur', 'En attente']);
        }));

        // Si le formulaire est soumis
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('validate' . $token, $request->request->get('_token'))) {
                return $this->redirectToRoute('app_registration_validate', ['token' => $token]);
            }

            $action = $request->request->get('action');

            if ($action === 'validate') {
                try {
                    // Récupérer le rôle sélectionné
                    $roleId = $request->request->get('role');
                    if (!$roleId) {
                        throw new \Exception('Aucun rôle sélectionné');
                    }

                    $selectedRole = $entityManager->getRepository(Role::class)->find($roleId);
                    if (!$selectedRole) {
                        throw new \Exception('Rôle non trouvé');
                    }

                    // Vérifier que le rôle sélectionné n'est pas Administrateur ou En attente
                    if (in_array($selectedRole->getRole(), ['Administrateur', 'En attente'])) {
                        throw new \Exception('Ce rôle ne peut pas être attribué');
                    }

                    $user->setRole($selectedRole);
                    $user->setIsVerified(true);
                    $user->setRegistrationToken(null);
                    
                    $entityManager->persist($user);
                    $entityManager->flush();
                    
                    // Envoyer l'email de confirmation
                    $this->sendUserNotificationEmail($user, true, $mailer);

                    return $this->redirectToRoute('app_login');
                } catch (\Exception $e) {
                    return $this->redirectToRoute('app_registration_validate', ['token' => $token]);
                }
            } 
            elseif ($action === 'reject') {
                try {
                    // Rejeter l'inscription
                    $entityManager->remove($user);
                    $entityManager->flush();
                    
                    // Envoyer l'email de rejet
                    $this->sendUserNotificationEmail($user, false, $mailer);

                    return $this->redirectToRoute('app_login');
                } catch (\Exception $e) {
                    return $this->redirectToRoute('app_registration_validate', ['token' => $token]);
                }
            }
        }

        return $this->render('registration/validate.html.twig', [
            'user' => $user,
            'token' => $token,
            'roles' => $availableRoles
        ]);
    }

    private function sendAdminNotificationEmail(User $user, MailerInterface $mailer): void
    {
        try {
            // Générer l'URL de validation
            $validationUrl = $this->generateUrl('app_registration_validate', 
                ['token' => $user->getRegistrationToken()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            error_log('Tentative d\'envoi d\'email à l\'administrateur');
            error_log('URL de validation : ' . $validationUrl);
            error_log('Email admin : ' . $_ENV['ADMIN_EMAIL']);

            $email = (new Email())
                ->from($_ENV['ADMIN_EMAIL'])
                ->to($_ENV['ADMIN_EMAIL'])
                ->subject('Nouvelle demande d\'inscription')
                ->html($this->renderView(
                    'registration/admin_notification.html.twig',
                    [
                        'user' => $user,
                        'validationUrl' => $validationUrl
                    ]
                ));

            $mailer->send($email);
            error_log('Email envoyé avec succès à l\'administrateur');
        } catch (\Exception $e) {
            error_log('Erreur détaillée lors de l\'envoi de l\'email: ' . $e->getMessage());
            error_log('Trace: ' . $e->getTraceAsString());
            throw $e; // Remonter l'erreur pour la capturer dans le contrôleur
        }
    }

    private function sendRegistrationConfirmationEmail(User $user, MailerInterface $mailer): void
    {
        try {
            $email = (new Email())
                ->from($_ENV['ADMIN_EMAIL'])
                ->to($user->getEmail())
                ->subject('Confirmation de votre demande d\'inscription - Nation Sound')
                ->html($this->renderView(
                    'registration/registration_confirmation_email.html.twig',
                    ['user' => $user]
                ));

            $mailer->send($email);
        } catch (\Exception $e) {
            error_log('Erreur lors de l\'envoi de l\'email de confirmation: ' . $e->getMessage());
            // Ne pas relancer l'exception pour ne pas bloquer le processus d'inscription
        }
    }

    private function sendUserNotificationEmail(User $user, bool $isApproved, MailerInterface $mailer): void
    {
        try {
            $email = (new Email())
                ->from($_ENV['ADMIN_EMAIL'])
                ->to($user->getEmail())
                ->subject($isApproved ? 'Votre inscription a été validée' : 'Votre inscription a été refusée')
                ->html($this->renderView(
                    'registration/user_notification.html.twig',
                    [
                        'user' => $user,
                        'isApproved' => $isApproved
                    ]
                ));

            $mailer->send($email);
        } catch (\Exception $e) {
            error_log('Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
        }
    }
}