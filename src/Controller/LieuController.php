<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lieu')]
final class LieuController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LieuRepository $lieuRepository,
        private readonly VilleRepository $villeRepository,
    ) {
    }

    #[Route('/')]
    public function lister(): Response
    {
        $lieux = $this->lieuRepository->findAll();

        return $this->render('lieu/lister.html.twig', [
            'lieux' => $lieux,
        ]);
    }

    #[Route('/ajouter')]
    public function ajouter(Request $request): Response
    {
        $lieu = new Lieu();
        return $this->form($lieu, $request);
    }

    #[Route('/modifier/{lieuId}')]
    public function modifier(Request $request, int $lieuId): Response
    {
        $lieu = $this->lieuRepository->find($lieuId);
        return $this->form($lieu, $request);
    }

    private function form(Lieu $lieu, Request $request)
    {
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$form->get('ville')->getData()){
                // Vérifie si les champs sont rempli dans newVille
                $newVilleData = $form->get('newville')->getData();
                if ($newVilleData && (null !== $newVilleData->getNom() && '' !== trim($newVilleData->getNom()))
                    && (null !== $newVilleData->getCodePostal() && '' !== trim($newVilleData->getCodePostal()))) {
                    $lieu->setVille($newVilleData);
                }
            }
            $this->entityManager->persist($lieu);
            $this->entityManager->flush();
            $this->addFlash('success', 'Le lieu "'.$lieu->getNom().'" a bien été enregister');
            return $this->redirectToRoute('app_lieu_lister');
        }
        return $this->render('lieu/ajouter.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/supprimer/{lieu}')]
    public function supprimer(?Lieu $lieu): Response
    {
        if ($lieu) {
            $this->entityManager->remove($lieu);
            $this->entityManager->flush();
            $this->addFlash('success', 'Le lieu "'.$lieu->getNom().'" a bien été supprimer');

            return $this->redirectToRoute('app_lieu_lister');
        }
        $this->addFlash('error', 'Le lieu n\'existe pas');

        return $this->redirectToRoute('app_lieu_lister');
    }
}
