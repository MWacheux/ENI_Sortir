<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\CsvImportType;
use App\Form\ProfilType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/participant')]
final class ParticipantController extends AbstractController
{
    public function __construct(
        private readonly ParticipantRepository $participantRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('/profil')]
    public function monProfil(Request $request, SluggerInterface $slugger): Response
    {
        $participant = $this->getUser();
        $baseUrl = $request->getSchemeAndHttpHost();

        $form = $this->createForm(ProfilType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['photo']->getData();

            if ($participant->getPhoto()) {
                $oldPath = __DIR__.'/../../public/photo/'.$participant->getPhoto();
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

            // Déplacement du fichier
            $file->move(
                __DIR__.'/../../public/photo',
                $newFilename
            );

            $participant->setPhoto($newFilename);

            $this->em->persist($participant);
            $this->em->flush();
            $this->addFlash('success', 'Modifications prises en compte');

            return $this->redirectToRoute('app_participant_monprofil');
        }

        return $this->render('profil/profil.html.twig', [
            'form' => $form,
            'photo' => $baseUrl.'/photo/'.$participant->getPhoto(),
        ]);
    }

    #[Route('/consulter/{id}')]
    public function consulter(Request $request, int $id): Response
    {
        /** @var Participant $participant */
        $participant = $this->participantRepository->find($id);
        $baseUrl = $request->getSchemeAndHttpHost();

        return $this->render('profil/consulter.html.twig', [
            'participant' => $participant,
            'photo' => $baseUrl.'/photo/'.$participant->getPhoto(),
        ]);
    }

    #[Route('/ajouter')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les permissions')]
    public function ajouter(Request $request): Response
    {
        $participant = new Participant();
        $form = $this->createForm(ProfilType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participant->setPassword('$2y$13$J/3BoAyb0/O3nGBrf04U6.1vMfrjsl/2Wc0xaAJ9YpS2xNxpKucx2'); // mdp hashé : azerty
            $this->em->persist($participant);
            $this->em->flush();
            $this->addFlash('success', 'L\'utilisateur "'.$participant->getPrenom().' '.$participant->getNom().'" a bien été enregistré');

            return $this->redirectToRoute('app_participant_lister');
        }

        return $this->render('utilisateur/ajouter.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/supprimer/{utilisateur}')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les permissions')]
    public function supprimer(Participant $utilisateur): Response
    {
        if ($utilisateur) {
            $this->em->remove($utilisateur);
            $this->em->flush();
            $this->addFlash('success', 'L\'utilisateur "'.$utilisateur->getPrenom().' '.$utilisateur->getNom().'" a bien été supprimé');

            return $this->redirectToRoute('app_participant_lister');
        }
        $this->addFlash('error', 'L\'utilisateur "'.$utilisateur->getPrenom().' '.$utilisateur->getNom().'" n\'est pas trouvé');

        return $this->redirectToRoute('app_participant_lister');
    }

    #[Route('/')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les permissions')]
    public function lister(Request $request): Response
    {
        $form = $this->createForm(CsvImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
            $participants = $serializer->decode(file_get_contents($form->get('submitFile')->getData()), 'csv');
            foreach ($participants as $row) {
                $participant = new Participant();
                $participant->setEmail($row['email'] ?? null);
                $participant->setNom($row['nom'] ?? null);
                $participant->setPrenom($row['prenom'] ?? null);
                $participant->setTelephone($row['telephone'] ?? null);
                $participant->setRoles([$row['roles'] ?? 'ROLE_USER']);
                $participant->setAdministrateur($row['administrateur'] ?? false);
                $participant->setActif($row['actif'] ?? true);
                $participant->setThemeSombre($row['themeSombre'] ?? false);
                $participant->setPassword('$2y$13$J/3BoAyb0/O3nGBrf04U6.1vMfrjsl/2Wc0xaAJ9YpS2xNxpKucx2'); // hash du mot de passe "azerty"

                try {
                    $this->em->persist($participant);
                    $this->em->flush();
                } catch (\Exception $exception) {
                    $this->addFlash('error', "L'utilisateur '".$participant->getPrenom().' '.$participant->getNom()."' contient une erreur");

                    return $this->redirectToRoute('app_participant_lister');
                }
            }

            $this->addFlash('success', 'Les utilisateurs ont bien été enregistrés');

            return $this->redirectToRoute('app_participant_lister');
        }

        $utilisateurs = $this->participantRepository->findAll();

        return $this->render('utilisateur/lister.html.twig', [
            'utilisateurs' => $utilisateurs,
            'form' => $form,
        ]);
    }

    #[Route('/desactiver/{utilisateur}')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les permissions')]
    public function desactiver(Participant $utilisateur): Response
    {
        $utilisateur->setActif(false);
        $this->em->persist($utilisateur);
        $this->em->flush();
        $this->addFlash('success', 'L\'utilisateur "'.$utilisateur->getPrenom().' '.$utilisateur->getNom().'" a bien été désactivé');

        return $this->redirectToRoute('app_participant_lister');
    }

    #[Route('/activer/{utilisateur}')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les permissions')]
    public function activer(Participant $utilisateur): Response
    {
        $utilisateur->setActif(true);
        $this->em->persist($utilisateur);
        $this->em->flush();
        $this->addFlash('success', 'L\'utilisateur "'.$utilisateur->getPrenom().' '.$utilisateur->getNom().'" a bien été activé');

        return $this->redirectToRoute('app_participant_lister');
    }

    #[Route('/themesombre')]
    public function themeSombre(): Response
    {
        $utilisateur = $this->getUser();
        if ($utilisateur->isThemeSombre()) {
            $utilisateur->setThemeSombre(false);
            $this->addFlash('success', 'Thème sombre désactivé');
        } else {
            $utilisateur->setThemeSombre(true);
            $this->addFlash('success', 'Thème sombre activé');
        }
        $this->em->persist($utilisateur);
        $this->em->flush();

        return $this->redirectToRoute('app_participant_monprofil');
    }
}
