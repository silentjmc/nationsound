<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MailerController extends AbstractController
{
    #[Route('/email')]
    public function sendEmail(MailerInterface $mailer, TransportInterface $transport): Response
    {
        try {
            error_log('=== DÉBUT TEST EMAIL ===');
            
            // Afficher la configuration du transport
            $dsn = $_ENV['MAILER_DSN'] ?? 'DSN non défini';
            error_log('DSN configuré: ' . $dsn);
            
            // Créer et envoyer un email de test
            $email = (new Email())
                ->from($_ENV['ADMIN_EMAIL'])
                ->to('jmj.carre@gmail.com')
                ->subject('Test Email ' . date('Y-m-d H:i:s'))
                ->text('Ceci est un email de test envoyé le ' . date('Y-m-d H:i:s'));

            error_log('Email configuré avec:');
            error_log('From: ' . $email->getFrom()[0]->getAddress());
            error_log('To: ' . $email->getTo()[0]->getAddress());
            error_log('Subject: ' . $email->getSubject());

            error_log('Tentative d\'envoi...');
            $mailer->send($email);
            error_log('=== Email envoyé avec succès ===');

            return new Response(
                'Email envoyé avec succès! Vérifiez les logs pour plus de détails.<br>' .
                'DSN utilisé: ' . htmlspecialchars($dsn)
            );
            
        } catch (\Exception $e) {
            error_log('=== ERREUR EMAIL ===');
            error_log('Type: ' . get_class($e));
            error_log('Message: ' . $e->getMessage());
            error_log('Trace: ' . $e->getTraceAsString());
            
            // Retourner plus de détails sur l'erreur
            return new Response(
                'Erreur lors de l\'envoi:<br>' .
                'Type: ' . get_class($e) . '<br>' .
                'Message: ' . htmlspecialchars($e->getMessage()) . '<br>' .
                'DSN utilisé: ' . htmlspecialchars($dsn),
                500
            );
        }
    }
}