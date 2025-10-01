<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\ParticipantRepository;
use App\Services\MailjetMailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/mot-de-passe-oublie')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Affiche le formulaire pour demander la réinitialisation du mot de passe.
     */
    #[Route('', name: 'app_demande_mot_de_passe_oublie')]
    public function request(Request $request, ParticipantRepository $participantRepository, EntityManagerInterface $entityManager, MailjetMailerService $mailjetMailer, TranslatorInterface $translator): Response
    {
        // Création du formulaire pour demander la réinitialisation du mot de passe
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère l'email saisi
            $emailSaisi = $form->get('email')->getData();
            $participant = $participantRepository->findOneBy(['email' => $emailSaisi]);

            if ($participant) {
                // Génère le token sécurisé avec ResetPasswordHelper
                $token = $this->resetPasswordHelper->generateResetToken($participant);

                // On génère l'url vers le lien complet de la réinitialisation
                $lienReset = $this->generateUrl(
                    'app_reset_password',
                    ['token' => $token->getToken()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                // Envoi de l'email via le service Mailjet
                $ok = $mailjetMailer->envoyerEmailMotDePasse(
                    $participant->getEmail(),                           // Email du destinataire
                    $participant->getPrenom() ?? 'Utilisateur',    // Nom
                    $lienReset,                                         // l'URL de réinitialisation
                    '7094841',                                 // Id du template de Mailjet (facultatif)
                    [
                        'pseudo' => $participant->getPrenom() ?? 'cher utilisateur',
                        'reset_url' => $lienReset,
                    ]
                );

                if (!$ok) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l’envoi de l’email.');
                }
            }

            // Message générique pour ne pas révéler si l'utilisateur existe
            $this->addFlash('success', 'Si cet email existe, un lien de réinitialisation a été envoyé.');

            return $this->redirectToRoute('app_verifier_email');
        }

        // Affichage du formulaire
        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    /**
     * Page de confirmation après la demande d'un utilisateur de  réinitialiser le mot de passe.
     */
    #[Route('/check-email', name: 'app_verifier_email')]
    public function checkEmail(): Response
    {
        // Générer un faux jeton si l'utilisateur n'existe pas ou si quelqu'un a accédé directement à cette page.
        // Cela empêche de révéler si un utilisateur a été trouvé avec l'adresse e-mail indiquée.
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return $this->render('reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    /**
     * Valide et traite l'URL de réinitialisation sur laquelle l'utilisateur a cliqué dans son e-mail.
     */
    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator, ?string $token = null): Response
    {
        if ($token) {
            // Nous stockons le jeton dans la session et le supprimons de l'URL,
            // pour éviter que l'URL ne soit chargée dans un navigateur et ne divulgue potentiellement le jeton à un JavaScript tiers.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();

        if (null === $token) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try {
            /** @var Participant $participant */
            $participant = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', sprintf(
                '%s - %s',
                $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE, [], 'ResetPasswordBundle'),
                $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            ));

            return $this->redirectToRoute('app_demande_mot_de_passe_oublie');
        }

        // The token is valid; allow the user to change their password.
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // A password reset token should be used only once, remove it.
            $this->resetPasswordHelper->removeResetRequest($token);

            /** @var string $nouveauMotDePasse */
            $nouveauMotDePasse = $form->get('nouveauMotDePasse')->getData();

            // Encode(hash) the plain password, and set it.
            $participant->setPassword($passwordHasher->hashPassword($participant, $nouveauMotDePasse));
            $this->entityManager->flush();

            // The session is cleaned up after the password has been changed.
            $this->cleanSessionAfterReset();

            return $this->redirectToRoute('app_index_accueil');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form,
        ]);
    }

    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer, TranslatorInterface $translator): RedirectResponse
    {
        $participant = $this->entityManager->getRepository(Participant::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Do not reveal whether a user account was found or not.
        if (!$participant) {
            return $this->redirectToRoute('app_check_email');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($participant);
        } catch (ResetPasswordExceptionInterface $e) {
            // If you want to tell the user why a reset email was not sent, uncomment
            // the lines below and change the redirect to 'app_forgot_password_request'.
            // Caution: This may reveal if a user is registered or not.
            //
            // $this->addFlash('reset_password_error', sprintf(
            //     '%s - %s',
            //     $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_HANDLE, [], 'ResetPasswordBundle'),
            //     $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            // ));

            return $this->redirectToRoute('app_check_email');
        }

        $email = (new TemplatedEmail())
            ->from(new Address('plustard@eni.fr', 'ENI Sortir'))
            ->to((string) $participant->getEmail())
            ->subject('Réinitialisation du mot de passe')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ])
        ;

        $mailer->send($email);

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('app_check_email');
    }
}
