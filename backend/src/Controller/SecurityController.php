<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // Si l'erreur est liée à un compte non vérifié
        if ($error && str_contains($error->getMessage(), 'not verified')) {
            return $this->render('security/login_error.html.twig', [
                'error_type' => 'not_verified',
                'last_username' => $lastUsername,
            ]);
        }
        
        // Si une autre erreur d'authentification s'est produite
        if ($error) {
            return $this->render('security/login_error.html.twig', [
                'error_type' => 'invalid_credentials',
                'last_username' => $lastUsername,
            ]);
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
