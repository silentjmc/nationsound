<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Controller responsible for handling security-related actions such as login and logout.
 *
 * It provides the `login` action to display the login form and handle authentication errors,
 * including a specific case for users who are not yet verified. The `logout` action
 * is a placeholder, as the actual logout process is intercepted by Symfony's security firewall.
 */

class SecurityController extends AbstractController
{
    /**
     * Displays the login form and handles authentication errors.
     *
     * This action uses Symfony's `AuthenticationUtils` to retrieve the last authentication
     * error (if any) and the last username entered by the user.
     * It renders different views or passes different parameters based on the type of error:
     * - If the error message contains 'not verified', it renders 'security/login_error.html.twig'
     *   with `error_type` set to 'not_verified'.
     * - For other authentication errors, it renders 'security/login_error.html.twig'
     *   with `error_type` set to 'invalid_credentials'.
     * - If there is no error, it renders the standard 'security/login.html.twig' form.
     *
     * @param AuthenticationUtils $authenticationUtils Service to get login error and last username.
     * @return Response The HTTP response rendering the login page or an error page.
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Get the login error if there is one.
        $error = $authenticationUtils->getLastAuthenticationError();
        // Get the last username entered by the user.
        $lastUsername = $authenticationUtils->getLastUsername();

        // Check for a specific error message indicating a non-verified user.
        if ($error && str_contains($error->getMessage(), 'not verified')) {
            return $this->render('security/login_error.html.twig', [
                'error_type' => 'not_verified',
                'last_username' => $lastUsername,
            ]);
        }
        
        // Check for any other authentication error.
        if ($error) {
            return $this->render('security/login_error.html.twig', [
                'error_type' => 'invalid_credentials',
                'last_username' => $lastUsername,
            ]);
        }

        // If there are no errors, render the standard login form.
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * Handles the logout action.
     *
     * This method itself should be left blank as the actual logout process
     * is intercepted and handled by Symfony's security system based on the
     * firewall configuration (e.g., the 'logout' key in `security.yaml`).
     *
     * Accessing the '/logout' route will trigger the security component to
     * invalidate the session and clear the user's authentication token.
     *
     * @throws \LogicException This exception is thrown to indicate that this method
     *                         should not be reached because the firewall should intercept it.
     * @return void This method will not return a Response because it's intercepted.
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
