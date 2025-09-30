<?php

namespace App\Services;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Psr\Log\LoggerInterface;

class MailjetMailerService
{
    private MailerInterface $mailer;
    private LoggerInterface $logger;


    public function __construct(MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    /**
     * Envoie un email pour la réinitialisation du mot de passe
     *
     * @param string $email L'adresse email du destinataire
     * @param string $nom Le nom ou pseudo du destinataire
     * @param string $templateId L'ID du template (non utilisé ici mais conservé si besoin)
     * @param array $variables Les variables à passer au template Twig
     *
     * @return bool Retourne true si l'email a été envoyé
     */
    public function envoyerEmailMotDePasse(string $email, string $nom, string $lienReset, string $templateId = '', array $variables = []): bool
    {
        // Fusion des variables par défaut avec celles passées en paramètre
        $context = array_merge([
            'pseudo' => $nom,
            'reset_url' => $lienReset,
        ], $variables);

        $emailMessage = (new TemplatedEmail())
            ->from(new Address('plustard@eni.fr', 'ENI Sortir')) // Expéditeur
            ->to($email)                                         // Destinataire
            ->subject('Réinitialisation du mot de passe')        // Sujet du mail
            ->htmlTemplate('reset_password/email.html.twig')    // Template Twig
            ->context($context);                                // Variables pour le template

        // Log avant l’envoi
        $this->logger->info('📧 Envoi d\'un mail de réinitialisation', [
            'to' => $email,
            'reset_url' => $lienReset
        ]);


             $this->logger->info('✅ Mail envoyé (vérifie Papercut sur localhost:1025)');

        // Envoi du mail
        try {
            $this->mailer->send($emailMessage);
            $this->logger->info("✅ Mail envoyé à $email, vérifie Papercut Desktop.");
            return true;
        } catch (\Exception $e) {
            $this->logger->error("❌ Échec de l'envoi du mail : " . $e->getMessage());
            return false;
        }

    }
}
