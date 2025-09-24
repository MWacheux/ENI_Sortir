<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/participant')]
final class ParticipantController extends AbstractController
{

    #[Route('/profil')]
    public function monProfil(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $participant = $this->getUser();
        $baseUrl = $request->getSchemeAndHttpHost();

        $form = $this->createForm(ProfilType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['photo']->getData();

            if ($participant->getPhoto()) {
                $oldPath = __DIR__ . '/../../public/photo/' . $participant->getPhoto();
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

            // Déplacement du fichier
            $file->move(
                __DIR__ . '/../../public/photo',
                $newFilename
            );

            $participant->setPhoto($newFilename);

            $entityManager->persist($participant);
            $entityManager->flush();
            $this->addFlash("success", "Modifications prises en compte");
            return $this->redirectToRoute('app_participant_monprofil');
        }

        return $this->render('profil/profil.html.twig', [
            'form' => $form,
            'photo' => $baseUrl . '/photo/'.$participant->getPhoto(),
        ]);
    }


    #[Route('/consulter/{id}')]
    public function consulter(Request $request, ParticipantRepository $participantRepository, int $id): Response
    {
        /** @var Participant $participant */
        $participant = $participantRepository->find($id);
        $baseUrl = $request->getSchemeAndHttpHost();

        return $this->render('profil/consulter.html.twig', [
            'participant' => $participant,
            'photo' => $baseUrl . '/photo/'.$participant->getPhoto(),
        ]);
    }

    #[Route('/')]
    #[IsGranted("ROLE_ADMIN", message: 'Vous n\'avez pas les permissions')]
    public function lister(ParticipantRepository $participantRepository): Response
    {
        $utilisateurs = $participantRepository->findAll();

        return $this->render('utilisateur/lister.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
    }

    #[Route('/desactiver/{utilisateur}')]
    #[IsGranted("ROLE_ADMIN", message: 'Vous n\'avez pas les permissions')]
    public function desactiver(Participant $utilisateur, EntityManagerInterface $em): Response
    {
        $utilisateur->setActif(false);
        $em->persist($utilisateur);
        $em->flush();
        $this->addFlash('success', 'L\'utilisateur "'.$utilisateur->getNom().'" a bien été désactivé');

        return $this->redirectToRoute('app_participant_lister');
    }

    #[Route('/activer/{utilisateur}')]
    #[IsGranted("ROLE_ADMIN", message: 'Vous n\'avez pas les permissions')]
    public function activer(Participant $utilisateur, EntityManagerInterface $em): Response
    {
        $utilisateur->setActif(true);
        $em->persist($utilisateur);
        $em->flush();
        $this->addFlash('success', 'L\'utilisateur "'.$utilisateur->getNom().'" a bien été activé');

        return $this->redirectToRoute('app_participant_lister');
    }
}
