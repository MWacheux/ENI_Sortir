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
    )
    {
    }

    #[Route('/ajouter/{sortieId}')]
    public function ajouter(Request $request, int $sortieId): Response
    {
        $lieu = new Lieu();
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            if($form->get('enregistrerLieu')->isClicked()){
                if(!$form->get('ville')->getData()){
                    $this->addFlash('error', 'Le lieu doit avoir une ville');
                    return $this->render('lieu/ajouter.html.twig', [
                        'form' => $form,
                    ]);
                }
            }

            $lieu = $form->getData();
            $this->entityManager->persist($lieu);
            $this->entityManager->flush();
            $this->addFlash('success', 'Le lieu "'.$lieu->getNom().'" a bien été ajouté');

            if($form->get('ajouterVille')->isClicked()) {
                return $this->redirectToRoute('app_ville_ajouter', [
                    'lieuId' => $lieu->getId(),
                    'sortieId' => $sortieId,
                ]);
            }
            return $this->redirectToRoute('app_sortie_modifier', [
                'sortieId' => $sortieId,
                'lieuId' => $lieu->getId(),
            ]);
        }

        return $this->render('lieu/ajouter.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/modifier/{lieuId}/{villeId}/{sortieId}')]
    public function modifier(Request $request, int $lieuId, int $villeId, int $sortieId): Response
    {
        $lieu = $this->lieuRepository->find($lieuId);
        $ville = $this->villeRepository->find($villeId);
        $lieu->setVille($ville);

        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            if(!$form->get('ville')->getData()){
                $this->addFlash('error', 'Le lieu doit avoir une ville');
                return $this->render('lieu/ajouter.html.twig', [
                    'form' => $form,
                ]);
            }
            $lieu = $form->getData();
            $this->entityManager->persist($lieu);
            $this->entityManager->flush();
            $this->addFlash('success', 'Le lieu "'.$lieu->getNom().'" a bien été ajouté');
            return $this->redirectToRoute('app_sortie_modifier', [
                'sortieId' => $sortieId,
                'lieuId' => $lieu->getId(),
            ]);
        }
        return $this->render('lieu/ajouter.html.twig', [
            'form' => $form,
        ]);
    }
}
