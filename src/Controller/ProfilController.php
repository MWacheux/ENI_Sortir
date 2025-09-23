<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profil')]
final class ProfilController extends AbstractController
{
    #[Route('/')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $participant = $this->getUser();

        $form = $this->createForm(ProfilType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($participant);
            $entityManager->flush();
            $this->addFlash("success", "Modifications prises en compte");
            return $this->redirectToRoute('/profil');
        }

        return $this->render('profil/profil.html.twig', [
            'form' => $form
        ]);
    }


    #[Route('/consulter/{id}')]
    public function consulter(ParticipantRepository $participantRepository, int $id): Response
    {
        /** @var Participant $participant */
        $participant = $participantRepository->find($id);

        return $this->render('profil/consulter.html.twig', [
            'participant' => $participant
        ]);
    }
}
