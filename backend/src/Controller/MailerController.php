<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Controller for testing email sending functionality.
 *
 * This controller provides a simple endpoint (`/email`) that attempts to send a test email.
 * It logs detailed information about the DSN, email configuration, and the outcome
 * of the send operation (success or failure) to the PHP error log.
 * This is primarily intended for development and debugging purposes to verify
 * that the mailer component is correctly configured and operational.
 */
class MailerController extends AbstractController
{
    /**
     * Sends a test email and provides feedback via HTTP response and logs.
     *
     * This action attempts to send a simple text email to a hardcoded recipient
     * (should be replaced with a valid test address) using the configured mailer DSN
     * and sender address from environment variables.
     * It logs various stages and details to the PHP error log for debugging.
     *
     * Accessing the `/email` route triggers this test.
     *
     * @param MailerInterface $mailer The Symfony Mailer service.
     * @param TransportInterface $transport The mail transport (injected, though not directly used in this method,
     *                                     its injection might be for ensuring the transport system is loaded or for future use).
     * @return Response An HTTP response indicating whether the email was sent successfully or if an error occurred.
     *                  Includes DSN information for debugging.
     */
    #[Route('/email')]
    public function sendEmail(MailerInterface $mailer, TransportInterface $transport): Response
    {
        try {
            error_log('=== DÉBUT TEST EMAIL ===');
            $dsn = $_ENV['MAILER_DSN'] ?? 'DSN non défini';
            error_log('DSN configuré: ' . $dsn);
            
            // Create a new Email object for the test.
            $email = (new Email())
                ->from($_ENV['ADMIN_EMAIL'])
                ->to('#adresse_mail#')
                ->subject('Test Email ' . date('Y-m-d H:i:s'))
                ->text('Ceci est un email de test envoyé le ' . date('Y-m-d H:i:s'));

            // Log email details before sending.
            error_log('Email configuré avec:');
            error_log('From: ' . $email->getFrom()[0]->getAddress());
            error_log('To: ' . $email->getTo()[0]->getAddress());
            error_log('Subject: ' . $email->getSubject());

            error_log('Tentative d\'envoi...');
            $mailer->send($email);
            error_log('=== Email envoyé avec succès ===');

            // Return a success response to the browser.
            return new Response(
                'Email envoyé avec succès! Vérifiez les logs pour plus de détails.<br>' .
                'DSN utilisé: ' . htmlspecialchars($dsn)
            );
            
        } catch (\Exception $e) {
            error_log('=== ERREUR EMAIL ===');
            error_log('Type: ' . get_class($e));
            error_log('Message: ' . $e->getMessage());
            error_log('Trace: ' . $e->getTraceAsString());
        
        // Return an error response to the browser with a 500 status code.
            return new Response(
                'Erreur lors de l\'envoi:<br>' .
                'Type: ' . get_class($e) . '<br>' .
                'Message: ' . htmlspecialchars($e->getMessage()) . '<br>' .
                'DSN utilisé: ' . htmlspecialchars($dsn),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}