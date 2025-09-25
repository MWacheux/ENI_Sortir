<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class IndexController extends AbstractController
{
    #[Route('/')]
    public function accueil(): Response
    {
        // redirige vers la page des sorties
        return $this->redirectToRoute('app_sortie_lister');
    }
}
