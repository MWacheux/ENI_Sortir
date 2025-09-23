<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SortieController extends AbstractController
{
    #[Route('/sortie', name: 'app_sortie')]
    public function index(): Response
    {

        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);

        return $this->render('sortie/index.html.twig', [
            'sortieform' => $form,
        ]);
    }
}
