<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/*
 * AccessDeniedHandler is responsible for handling access denied exceptions.
 * It renders a custom access denied page when a user tries to access a resource they are not authorized to view.
 */
class AccessDeniedHandler extends AbstractController implements AccessDeniedHandlerInterface
{
    /**
     * Handles the access denied exception by rendering a custom access denied page.
     * 
     * This method is called by the Symfony Security component when an AccessDeniedException
     * is caught (e.g., when a user fails an `isGranted()` check or tries to access a route
     * protected by security rules they do not meet).
     *
     * It renders the 'security/access_denied.html.twig' template with an
     * HTTP 403 Forbidden status code.
     *
     * @param Request $request The current request object.
     * @param AccessDeniedException $accessDeniedException The exception that was thrown.
     * @return Response A response object containing the rendered access denied page.
     */
    public function handle(Request $request, AccessDeniedException $accessDeniedException): Response
    {
        return $this->render('security/access_denied.html.twig', [], new Response(null, Response::HTTP_FORBIDDEN));
    }
}