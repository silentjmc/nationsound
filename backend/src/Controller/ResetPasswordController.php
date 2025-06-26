<?php

namespace App\Controller;

use App\Form\ResetPasswordRequestType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

/**
 * Controller responsible for handling the password reset process.
 *
 * This controller manages the entire workflow:
 * 1. User requests a password reset by submitting their email (`request` action).
 * 2. If the email corresponds to an existing user, a reset token is generated,
 *    stored, and an email with a reset link containing the token is sent to the user.
 * 3. User clicks the reset link, which leads to a form where they can enter a new password (`reset` action).
 * 4. If the token is valid and the new password meets requirements, the password is updated,
 *    and the reset token is invalidated.
 * 5. Confirmation pages are displayed after the email is sent and after the password is successfully reset.
 */
class ResetPasswordController extends AbstractController
{
    /**
     * Handles the initial password reset request.
     *
     * Displays a form where the user can enter their email address.
     * If the form is submitted and valid, and the email belongs to an existing user:
     * - A unique reset token is generated.
     * - The token is associated with the user and saved.
     * - An email containing a link with this token is sent to the user's email address.
     * - The user is then redirected to a page indicating that the email has been sent.
     *
     * @param Request $request The current HTTP request.
     * @param UserRepository $userRepository Repository for finding User entities.
     * @param MailerInterface $mailer Service for sending emails.
     * @param TokenGeneratorInterface $tokenGenerator Service for generating secure random tokens.
     * @param EntityManagerInterface $entityManager Doctrine's entity manager for persisting changes.
     * @return Response Renders the request form or redirects after submission.
     */
    #[Route('/reset-password', name: 'app_forgot_password_request')]
    public function request(Request $request, UserRepository $userRepository, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator, EntityManagerInterface $entityManager): Response 
    {
        // Create the password reset request form.
        $form = $this->createForm(ResetPasswordRequestType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Get the email submitted by the user.
            $email = $form->get('email')->getData();
            // Find the user by their email address.
            $user = $userRepository->findOneBy(['email' => $email]);
            // If a user is found with that email address.
            if ($user) {
                // Generate a unique, secure token for the password reset.
                $resetToken = $tokenGenerator->generateToken();
                // Associate the token with the user entity.
                $user->setResetPasswordToken($resetToken);
                 // Persist the change (the new token) to the database.
                $entityManager->flush();

                // Create and send the password reset email.
                $email = (new TemplatedEmail())
                    ->from($this->getParameter('app.admin_email'))
                    ->to($user->getEmail())
                    ->subject('Réinitialisation de votre mot de passe')
                    ->htmlTemplate('reset_password/email.html.twig')
                    ->context([
                        'resetToken' => $resetToken,
                    ]);
                $mailer->send($email);
            }
            return $this->redirectToRoute('app_reset_password_email_sent');
        }
        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    /**
     * Handles the actual password reset using a token.
     *
     * This action is typically accessed via a link sent to the user's email.
     * It validates the provided token:
     * - If the token is invalid or no user is found for it, a 404 error is thrown.
     * - If the token is valid, a form is displayed for the user to enter their new password.
     *
     * Upon successful submission of the new password:
     * - The reset token is invalidated (set to null).
     * - The user's password is updated with the new, hashed password.
     * - The user is redirected to a confirmation page.
     *
     * @param string $token The password reset token from the URL.
     * @param Request $request The current HTTP request.
     * @param UserRepository $userRepository Repository for finding User entities.
     * @param UserPasswordHasherInterface $passwordHasher Service for hashing passwords.
     * @param EntityManagerInterface $entityManager Doctrine's entity manager.
     * @return Response Renders the reset form or redirects after successful password change.
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If the token is invalid.
     */
    #[Route('/reset-password/reset/{token}', name: 'app_reset_password')]
    public function reset(string $token, Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response 
    {
        // Find the user associated with the provided reset token.
        $user = $userRepository->findOneBy(['resetPasswordToken' => $token]);

        // If no user is found for the token, or the token is otherwise invalid.
        if (!$user) {
            throw $this->createNotFoundException('Token invalide.');
        }

        // Create the form for entering the new password.
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) 
        {
            // Invalidate the reset token by setting it to null.
            $user->setResetPasswordToken(null);
            // Hash the new password and set it on the user entity.
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $entityManager->flush();

            // Redirect to the password reset confirmation page.
            return $this->redirectToRoute('app_reset_password_confirmation');
        }

        // If the form is not submitted or not valid, render the reset password form page.
        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    /**
     * Displays a page informing the user that a password reset email has been sent.
     *
     * This page is shown after the user submits the reset password request form,
     * regardless of whether an email was actually sent (to prevent email enumeration).
     *
     * @return Response Renders the 'email_sent.html.twig' template.
     */
    #[Route('/reset-password/email-sent', name: 'app_reset_password_email_sent')]
        public function emailSent(): Response
        {
            return $this->render('reset_password/email_sent.html.twig');
        }

    /**
     * Displays a page confirming that the password has been successfully reset.
     *
     * This page is shown after the user successfully submits the new password form.
     *
     * @return Response Renders the 'reset_confirmation.html.twig' template.
     */    
    #[Route('/reset-password/reset_confirmation', name: 'app_reset_password_confirmation')]
        public function resetConfirmation(): Response
        {
            return $this->render('reset_password/reset_confirmation.html.twig');
        }
}
