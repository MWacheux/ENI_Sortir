<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lieu')]
final class LieuController extends AbstractController
{

    public function __construct(
        private LieuRepository $lieuRepository,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('/ajouter')]
    public function ajouter(Request $request): Response
    {
        $lieu = new Lieu();
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $lieu = $form->getData();
            $this->entityManager->persist($lieu);
            $this->entityManager->flush();
            $this->addFlash('success', 'Le lieu "'.$lieu->getNom().'" a bien été ajouté');
            return $this->redirectToRoute('app_sortie_ajouter');
        }
        return $this->render('lieu/ajouter.html.twig', [
            'form' => $form,
        ]);
    }
}
