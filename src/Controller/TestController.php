<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Repository\EtatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\Registry;

final class TestController extends AbstractController
{

    public function __construct(private readonly EtatRepository $etatRepository)
    {
    }

    #[Route('/test', name: 'app_test')]
    public function index(Registry $registry): Response
    {
        $sortie = new Sortie();
        $etat = $this->etatRepository->findOneBy(['libelle' => 'creee']);
        $sortie->setEtat($etat);
        $workflow = $registry->get($sortie, 'sortie');
        $workflow->apply($sortie, 'to_ouverte');
        dd($sortie);
        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }
}
