<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

/**
 * LoginFormAuthenticator is responsible for handling the login authentication process.
 * 
 * This class extends AbstractLoginFormAuthenticator and implements the necessary methods
 * to authenticate users via a login form. It checks if the user is verified before allowing
 * them to log in, and it redirects them to the admin dashboard upon successful authentication.
 * It also handles CSRF protection for the login form.
 * 
 */
class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    // Trait to handle target path storage and retrieval
    // This is used to redirect users to their intended destination after login
    use TargetPathTrait;

    /**
     * The route name for the login page.
     * This is used to generate the URL for the login form.
     */
    public const LOGIN_ROUTE = 'app_login';

    /**
     * Constructor for the LoginFormAuthenticator.
     * 
     * @param EntityManagerInterface $entityManager The entity manager to interact with the database.
     * @param UrlGeneratorInterface $urlGenerator The URL generator to create URLs for redirection.
     * @param Security $security The security service to access user information and authentication context.
     * @param LoggerInterface $logger The logger service to log authentication events.
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $urlGenerator,
        private Security $security,
        private LoggerInterface $logger
    ) {
    }

    /**
     * This method is called to authenticate the user.
     * It retrieves the email and password from the request, checks if the user is verified,
     * and returns a Passport object for further authentication processing.
     * 
     * @param Request $request The HTTP request containing the login form data.
     * @return Passport The Passport object containing user credentials and badges.
     * @throws CustomUserMessageAuthenticationException If the user is not verified.
     */
    public function authenticate(Request $request): Passport
    {
        // Get the email from the POST request, defaulting to an empty string if not provided.
        $email = $request->request->get('email', '');

        // Store the submitted email in the session. This is often used to pre-fill the login form
        // if authentication fails, or for display purposes.
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        // Verify if the user is "verified"
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($user && !$user->isVerified()) {
            throw new CustomUserMessageAuthenticationException('not verified');
        }

        // Create and return a Passport.
        // The Passport system is part of Symfony's new authenticator-based security.
        return new Passport(
            // UserBadge is used to load the user by their email.
            new UserBadge($email),
            // PasswordCredentials is used to validate the password provided in the request.
            new PasswordCredentials($request->request->get('password', '')),
            [
                // CsrfTokenBadge is used to protect against CSRF attacks.
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    /**
     * This method is called upon successful authentication.
     * It logs the success event and redirects the user to the admin dashboard.
     * 
     * @param Request $request The HTTP request that was authenticated.
     * @param TokenInterface $token The authenticated token containing user information.
     * @param string $firewallName The name of the firewall used for authentication.
     * @return Response|null A RedirectResponse to the admin dashboard, or null if no redirection is needed.
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $this->logger->info('Authentication success, attempting redirect...');

        return new RedirectResponse($this->urlGenerator->generate('admin'));
    }

    /**
     * This method is called when authentication fails.
     * It logs the failure event and returns null, allowing the request to continue.
     * 
     * @param Request $request The HTTP request that failed authentication.
     * @param \Exception $exception The exception thrown during authentication.
     * @return Response|null Always returns null, indicating no specific response is needed.
     */
    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}