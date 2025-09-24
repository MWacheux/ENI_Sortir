<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use App\Repository\EtatRepository;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie')]
final class SortieController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly SortieRepository $sortieRepository, private readonly SiteRepository $siteRepository, private readonly EtatRepository $etatRepository)
    {
    }

    #[Route('/')]
    public function lister(): Response
    {
        // récupère toutes les sorties
        $sorties = $this->sortieRepository->findAll();
        // return la vue
        return $this->render('sortie/lister.html.twig', [
            'sorties' => $sorties,
        ]);
    }

    #[Route('/ajouter')]
    public function ajouter(Request $request): Response
    {
        // creation de l'instance de sortie pour le formulaire
        $sortie = new Sortie();
        // création du formulaire
        $form = $this->createForm(SortieType::class, $sortie);
        // gestion des données de la request
        $form->handleRequest($request);
        // test si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            $etat = $this->etatRepository->find(1);
            $sortie->setEtat($etat);
            $sortie->setOrganisateur($this->getUser());

            $site = $this->siteRepository->find(1);
            $sortie->setSite($site);
            // sauvegarde le site en base de donnée
            $this->entityManager->persist($sortie);
            // maj en base de données
            $this->entityManager->flush();

            // ajoute un message de success
            $this->addFlash('success', 'La sortie "'.$sortie->getNom().'" a bien été enregistrer');
            return $this->redirectToRoute('app_sortie');
        }
        return $this->render('sortie/ajouter.html.twig', [
            'sortieform' => $form,
        ]);
    }
}
