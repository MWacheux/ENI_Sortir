<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lieu')]
final class LieuController extends AbstractController
{

    public function __construct(
        private readonly LieuRepository $lieuRepository,
        private readonly SortieRepository $sortieRepository,
        private readonly EntityManagerInterface $entityManager,
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
