<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\SiteType;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/site')]
#[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les permissions')]
final class SiteController extends AbstractController
{
    public function __construct(
        private readonly SiteRepository $siteRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('/')]
    public function lister(): Response
    {
        // récupère tous les sites depuis la bdd
        $sites = $this->siteRepository->findAll();

        // affiche la page pour lister les sites
        return $this->render('site/lister.html.twig', [
            'sites' => $sites,
        ]);
    }

    #[Route('/ajouter')]
    public function ajouter(Request $request): Response
    {
        // creation de l'instance de site pour le formulaire
        $site = new Site();

        return $this->form($request, $site);
    }

    #[Route('/modifier/{site}')]
    public function modifier(Request $request, ?Site $site): Response
    {
        return $this->form($request, $site);
    }

    private function form(Request $request, ?Site $site)
    {
        // crée le formulaire
        $form = $this->createForm(SiteType::class, $site);
        // gestion des données de la request
        $form->handleRequest($request);
        // test si le formulaire et soumis ET valide
        if ($form->isSubmitted() && $form->isValid()) {
            // sauvegarde le site en base
            $this->em->persist($site);
            $this->em->flush();
            // ajoute un message de success
            $this->addFlash('success', 'Le site "'.$site->getNom().'" a bien été enregistrer');

            // redirige le user sur la pga lister
            return $this->redirectToRoute('app_site_lister');
        }

        // affiche la page pour ajouter un site
        return $this->render('site/ajouter.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/supprimer/{site}')]
    public function supprimer(?Site $site): Response
    {
        if ($site) {
            $this->em->remove($site);
            $this->em->flush();
            $this->addFlash('success', 'Le site "'.$site->getNom().'" a bien été supprimer');

            return $this->redirectToRoute('app_site_lister');
        }
        $this->addFlash('error', 'Le site n\'existe pas');

        return $this->redirectToRoute('app_site_lister');
    }
}
