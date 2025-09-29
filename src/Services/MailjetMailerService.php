<?php

namespace App\Services;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class MailjetMailerService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Envoie un email pour la réinitialisation du mot de passe
     *
     * @param string $email      L'adresse email du destinataire
     * @param string $nom        Le nom ou pseudo du destinataire
     * @param string $templateId L'ID du template (non utilisé ici mais conservé si besoin)
     * @param array  $variables  Les variables à passer au template Twig
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

        $this->mailer->send($emailMessage); // Envoi de l'email

        return true; // Retourne vrai si l'email est envoyé
    }
}
