<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ville')]
final class VilleController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/ajouter/{lieuId}/{sortieId}')]
    public function ajouter(Request $request, int $sortieId, int $lieuId): Response
    {
        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $lieu = $form->getData();
            $this->entityManager->persist($lieu);
            $this->entityManager->flush();
            $this->addFlash('success', 'La ville "'.$ville->getNom().'" a bien été ajoutée');
            return $this->redirectToRoute('app_lieu_modifier', [
                'sortieId' => $sortieId,
                'lieuId' => $lieuId,
                'villeId' => $ville->getId(),
            ]);
        }

        return $this->render('ville/ajouter.html.twig', [
            'form' => $form,
        ]);
    }
}
