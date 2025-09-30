<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ville')]
final class VilleController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly VilleRepository $repository,
    ) {
    }

    #[Route('/')]
    public function lister(): Response
    {
        $villes = $this->repository->findAll();

        return $this->render('ville/lister.html.twig', [
            'villes' => $villes,
        ]);
    }

    #[Route('/ajouter/{lieuId}/{sortieId}')]
    public function ajouter(Request $request, ?int $sortieId, ?int $lieuId): Response
    {
        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lieu = $form->getData();
            $this->entityManager->persist($lieu);
            $this->entityManager->flush();
            $this->addFlash('success', 'La ville "'.$ville->getNom().'" a bien été ajoutée');
            if (0 === $lieuId) {
                return $this->redirectToRoute('app_ville_lister');
            }

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

    #[Route('/modifier/{villeId}/{lieuId}/{sortieId}')]
    public function modifier(Request $request, ?int $villeId, ?int $sortieId, ?int $lieuId): Response
    {
        $ville = $this->repository->find($villeId);
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lieu = $form->getData();
            $this->entityManager->persist($lieu);
            $this->entityManager->flush();
            $this->addFlash('success', 'La ville "'.$ville->getNom().'" a bien été modifiée');
            if (0 === $lieuId) {
                return $this->redirectToRoute('app_ville_lister');
            }

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

    #[Route('/supprimer/{ville}')]
    public function supprimer(?Ville $ville): Response
    {
        if ($ville) {
            $this->entityManager->remove($ville);
            $this->entityManager->flush();
            $this->addFlash('success', 'La ville "'.$ville->getNom().'" a bien été supprimer');

            return $this->redirectToRoute('app_lieu_lister');
        }
        $this->addFlash('error', 'La ville n\'existe pas');

        return $this->redirectToRoute('app_ville_lister');
    }
}
