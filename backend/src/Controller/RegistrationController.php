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

/**
 * Controller responsible for handling the user registration process.
 *
 * This controller manages several stages:
 * 1. User submits the registration form (`register` action).
 * 2. On successful submission:
 *    - The password is hashed.
 *    - The user is assigned an "En attente" (Pending) role.
 *    - A registration token is generated and stored.
 *    - The user is persisted to the database.
 *    - An email notification is sent to the administrator with a validation link.
 *    - A confirmation email is sent to the user about their pending registration.
 *    - The user is redirected to a page indicating their registration is pending approval.
 * 3. Administrator uses the validation link (`validateRegistration` action).
 * 4. On the validation page, the administrator can:
 *    - Assign a role (excluding "Administrateur" and "En attente") and approve the registration.
 *    - Reject the registration.
 * 5. Based on the admin's action, the user's status is updated (verified, role assigned, or user deleted).
 * 6. An email is sent to the user notifying them of the approval or rejection.
 */
class RegistrationController extends AbstractController
{
    /**
     * Handles the user registration form submission.
     *
     * Displays the registration form. Upon valid submission, it hashes the password,
     * assigns a default "En attente" role, generates a registration token,
     * saves the user, and sends notification emails to both the admin and the user.
     * Finally, it redirects the user to a page indicating their registration is pending.
     *
     * @param Request $request The current HTTP request.
     * @param UserPasswordHasherInterface $userPasswordHasher Service for hashing passwords.
     * @param EntityManagerInterface $entityManager Doctrine's entity manager.
     * @param MailerInterface $mailer Service for sending emails.
     * @return Response Renders the registration form or redirects after submission.
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Hash the plain password from the form and set it on the user.
                $user->setPassword
                (
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );

                // Retrieve the "En attente" role
                $roleEnAttente = $entityManager->getRepository(Role::class)->findOneBy(['role' => 'En attente']);
                if (!$roleEnAttente) {
                    // Configuration error if the role doesn't exist.
                    throw new \Exception('Le rôle "En attente" n\'existe pas dans la base de données');
                }
                $user->setRoleUSer($roleEnAttente);
                $user->setIsVerified(false);
                
                // Generate registration token
                $user->setRegistrationToken(bin2hex(random_bytes(32)));

                // Persist the new user to the database.
                $entityManager->persist($user);
                $entityManager->flush();

                // Send notification emails.
                $this->sendAdminNotificationEmail($user, $mailer);
                $this->sendRegistrationConfirmationEmail($user, $mailer);
                
                // Redirection to the confirmation page
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

    /**
     * Handles the validation of a user registration by an administrator.
     *
     * This action is accessed via a tokenized link sent to the admin.
     * It allows the admin to either approve (by assigning a role) or reject the registration.
     *
     * @param string $token The registration validation token from the URL.
     * @param Request $request The current HTTP request.
     * @param EntityManagerInterface $entityManager Doctrine's entity manager.
     * @param MailerInterface $mailer Service for sending notification emails to the user.
     * @return Response Renders the validation page or redirects after action.
     */
    #[Route('/registration/validate/{token}', name: 'app_registration_validate')]
    public function validateRegistration(string $token, Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        // Find the user associated with the provided registration token.
        $user = $entityManager->getRepository(User::class)->findOneBy(['registrationToken' => $token]);

        // If no user is found for the token, render an invalid token page.
        if (!$user) {
            return $this->render('registration/invalid_token.html.twig');
        }

        // Retrieve all available roles except "Administrateur" and "En attente"
        $roles = $entityManager->getRepository(Role::class)->findAll();
        $availableRoles = array_values(array_filter($roles, function($role) {
            return !in_array($role->getRole(), ['Administrateur', 'En attente']);
        }));

        // Handle POST request (when admin submits the validation form).
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('validate' . $token, $request->request->get('_token'))) {
                // If CSRF token is invalid, redirect back to the validation page.
                return $this->redirectToRoute('app_registration_validate', ['token' => $token]);
            }
            // Get the action submitted by the admin ('validate' or 'reject').
            $action = $request->request->get('action');

            if ($action === 'validate') {
                try {
                    // Accept registration
                    $roleId = $request->request->get('role');
                    if (!$roleId) {
                        throw new \Exception('Aucun rôle sélectionné');
                    }
                    $selectedRole = $entityManager->getRepository(Role::class)->find($roleId);
                    if (!$selectedRole) {
                        throw new \Exception('Rôle non trouvé');
                    }
                    // Prevent assigning "Administrateur" or "En attente" via this interface.
                    if (in_array($selectedRole->getRole(), ['Administrateur', 'En attente'])) {
                        throw new \Exception('Ce rôle ne peut pas être attribué');
                    }

                    $user->setRoleUser($selectedRole);
                    $user->setIsVerified(true);
                    $user->setRegistrationToken(null);
                    $entityManager->persist($user);
                    $entityManager->flush();
                    
                    // Send confirmation email to the user about their approved registration.
                    $this->sendUserNotificationEmail($user, true, $mailer);
                    // Redirect to the login page after successful validation.
                    return $this->redirectToRoute('app_login');
                } catch (\Exception $e) {
                    return $this->redirectToRoute('app_registration_validate', ['token' => $token]);
                }
            } 
            elseif ($action === 'reject') {
                try {
                    // reject registration by admin
                    $entityManager->remove($user);
                    $entityManager->flush();
                    
                    // Send a notification email to the user about their rejected registration.
                    $this->sendUserNotificationEmail($user, false, $mailer);

                    // Redirect to the login page (or another appropriate page) after rejection.
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

     /**
     * Sends an email notification to the administrator about a new user registration.
     *
     * The email includes a link (with a token) for the administrator to validate or reject
     * the registration.
     *
     * @param User $user The newly registered user.
     * @param MailerInterface $mailer Service for sending emails.
     * @return void
     * @throws \Exception Re-throws exceptions encountered during email sending after logging.
     */
    private function sendAdminNotificationEmail(User $user, MailerInterface $mailer): void
    {
        try {
            // Generate the validation URL
            $validationUrl = $this->generateUrl('app_registration_validate', 
                ['token' => $user->getRegistrationToken()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            error_log('Tentative d\'envoi d\'email à l\'administrateur');
            error_log('URL de validation : ' . $validationUrl);
            error_log('Email admin : ' . $_ENV['ADMIN_EMAIL']);

            // Create the email message.
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
        } catch (\Exception $error) {
            error_log('Erreur détaillée lors de l\'envoi de l\'email: ' . $error->getMessage());
            error_log('Trace: ' . $error->getTraceAsString());
            throw $error;
        }
    }

    /**
     * Sends a confirmation email to the user after their initial registration request.
     *
     * This email informs the user that their registration is pending administrator approval.
     *
     * @param User $user The user who registered.
     * @param MailerInterface $mailer Service for sending emails.
     * @return void
     */
    private function sendRegistrationConfirmationEmail(User $user, MailerInterface $mailer): void
    {
        try {
            // Create the email message.
            $email = (new Email())
                ->from($_ENV['ADMIN_EMAIL'])
                ->to($user->getEmail())
                ->subject('Confirmation de votre demande d\'inscription - Nation Sound')
                ->html($this->renderView(
                    'registration/registration_confirmation_email.html.twig',
                    ['user' => $user]
                ));
            $mailer->send($email);
        } catch (\Exception $error) {
            error_log('Erreur lors de l\'envoi de l\'email de confirmation: ' . $error->getMessage());
        }
    }

     /**
     * Sends an email to the user notifying them of the outcome of their registration (approved or rejected).
     *
     * @param User $user The user whose registration status was determined.
     * @param bool $isApproved True if the registration was approved, false if rejected.
     * @param MailerInterface $mailer Service for sending emails.
     * @return void
     */
    private function sendUserNotificationEmail(User $user, bool $isApproved, MailerInterface $mailer): void
    {
        try {
            // Create the email message.
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
        } catch (\Exception $error) {
            error_log('Erreur lors de l\'envoi de l\'email: ' . $error->getMessage());
        }
    }
}