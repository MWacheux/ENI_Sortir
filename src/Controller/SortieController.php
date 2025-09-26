<?php

namespace App\Controller;

use App\Entity\Filtre\FiltreSortie;
use App\Entity\Sortie;
use App\Enum\EtatEnum;
use App\Form\Filtre\FiltreSortieType;
use App\Form\SortieType;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\EtatRepository;
use App\Repository\SiteRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Workflow\Registry;

#[Route('/sortie')]
final class SortieController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SortieRepository       $sortieRepository,
        private readonly SiteRepository         $siteRepository,
        private readonly LieuRepository         $lieuRepository,
        private readonly EtatRepository         $etatRepository,
        private readonly ParticipantRepository  $participantRepository,
    )
    {
    }

    #[Route('/')]
    public function lister(Request $request): Response
    {
        $filtre = new FiltreSortie();
        // crée le fomulaire de filtre
        $form = $this->createForm(FiltreSortieType::class, $filtre, ['method' => 'GET']);
        $form->handleRequest($request);
        // récupère toutes les sorties AVEC les fitlres
        $sorties = $this->sortieRepository->getSorties($filtre, $this->getUser());
        $sites = [];
        // rend unique la liste
        foreach ($sorties as $sortie) {
            $site = $sortie->getSite();
            if ($site && !in_array($site, $sites, true)) {
                $sites[] = $site;
            }
        }
        // return la vue
        return $this->render('sortie/lister.html.twig', [
            'sorties' => $sorties,
            'sites' => $sites,
            'form' => $form,
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
            $etat = $this->etatRepository->findOneBy(['libelle' => EtatEnum::CREEE]);
            $sortie->setEtat($etat);
            $sortie->setOrganisateur($this->getUser());
            $sortie->setSite($this->getUser()->getSite());

            if($form->get('enregistrerSortie')->isClicked()){
                $etat = $this->etatRepository->findOneBy(['libelle' => EtatEnum::OUVERTE]);
                $sortie->setEtat($etat);
                if(!$form->get('lieu')->getData()){
                    $this->addFlash('error', 'La sortie doit avoir un lieu');
                    return $this->render('sortie/ajouter.html.twig', [
                        'sortieform' => $form,
                    ]);
                }
            }
            // sauvegarde le site en base de donnée
            $this->entityManager->persist($sortie);
            // maj en base de données
            $this->entityManager->flush();

            // ajoute un message de success
            $this->addFlash('success', 'La sortie "' . $sortie->getNom() . '" a bien été enregistrée');

            if($form->get('ajouterLieu')->isClicked()){
                return $this->redirectToRoute('app_lieu_ajouter', [
                    'sortieId' => $sortie->getId(),
                ]);
            }

            return $this->redirectToRoute('app_sortie_lister');
        }
        return $this->render('sortie/ajouter.html.twig', [
            'sortieform' => $form,
        ]);
    }

    #[Route('/annuler/{sortie}')]
    public function annuler(?Sortie $sortie, Registry $registry): Response
    {
        // test si la sortie existe
        if (!$sortie){
            // revoie un message d'erreur
            $this->addFlash('error', 'La sortie n\'existe pas');
            return $this->redirectToRoute('app_sortie_lister');
        }
        // test si c'est l'organisateur
        if ($this->isGranted('ROLE_USER') && $sortie->getOrganisateur() !== $this->getUser()){
            // revoie un message d'erreur
            $this->addFlash('error', 'Vous n\'avez pas les permissions');
            return $this->redirectToRoute('app_sortie_lister');
        }
        // si la sortie est en etat ouverte
        if ($sortie->getEtat()->getLibelle() === EtatEnum::OUVERTE->value){
            // récupère le workflow sortie
            $workflow = $registry->get($sortie, 'sortie');
            // passe la sortie en annulée
            $workflow->apply($sortie, 'to_annulee');
            // sauvegarde en base de donnée
            $this->entityManager->persist($sortie);
            $this->entityManager->flush();
            $this->addFlash('success', 'La sortie à bien été annulé');
            return $this->redirectToRoute('app_sortie_lister');
        }
        $this->addFlash('error', 'La sortie ne peux pas être annulée');
        return $this->redirectToRoute('app_sortie_lister');
    }

    #[Route('/inscrire/{id}')]
    public function toggleInscription(int $id): Response
    {
        $sortie = $this->sortieRepository->find($id);
        $participant = $this->participantRepository->find($this->getUser()->getId());
        //vérifier si l'utilisateur est déjà inscrit à la sortie :
        if ($sortie->getParticipants()->contains($participant)) {
            //se désinscrire :
            $sortie->removeParticipant($participant);
            $this->addFlash('success', 'La sortie "' . $sortie->getNom() . '" vous êtes bien désinscrit de la sortie');
        } else {
            //inscrire l'utilisateur
            $sortie->addParticipant($participant);
            $this->addFlash('success', 'La sortie "' . $sortie->getNom() . '" vous êtes bien inscrit à la sortie');
        }
        // sauvegarde le site en base de donnée
        $this->entityManager->persist($sortie);
        // maj en base de données
        $this->entityManager->flush();
        return $this->redirectToRoute('app_sortie_lister');
    }


    #[Route(['/modifier/{sortieId}', '/modifier/{sortieId}/{lieuId}'])]
    public function modifier(Request $request, int $sortieId, ?int $lieuId): Response
    {
        $sortie = $this->sortieRepository->find($sortieId);
        if ($sortie->getEtat()->getLibelle() !== EtatEnum::OUVERTE->value){
            $this->addFlash('error', 'La sortie ne peut pas être modifiée');
            return $this->redirectToRoute('app_sortie_lister');
        }
        if ($sortie->getOrganisateur() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')){
            $this->addFlash('error', 'Vous avez pas les permissions');
            return $this->redirectToRoute('app_sortie_lister');
        }
        if ($lieuId){
            $lieu = $this->lieuRepository->find($lieuId);
            $sortie->setLieu($lieu);
        }

        // création du formulaire
        $form = $this->createForm(SortieType::class, $sortie);
        // gestion des données de la request
        $form->handleRequest($request);
        // test si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // sauvegarde le site en base de donnée
            $this->entityManager->persist($sortie);
            // maj en base de données
            $this->entityManager->flush();

            // ajoute un message de success
            $this->addFlash('success', 'La sortie "'.$sortie->getNom().'" a bien été enregistrée');
            return $this->redirectToRoute('app_sortie_lister');
        }
        return $this->render('sortie/ajouter.html.twig', [
            'sortieform' => $form,
        ]);
    }
}
