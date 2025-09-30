<?php

namespace App\Controller;

use App\Entity\Filtre\FiltreSortie;
use App\Entity\Sortie;
use App\Enum\EtatEnum;
use App\Form\Filtre\FiltreSortieType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\Registry;

#[Route('/sortie')]
final class SortieController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SortieRepository $sortieRepository,
        private readonly SiteRepository $siteRepository,
        private readonly LieuRepository $lieuRepository,
        private readonly EtatRepository $etatRepository,
        private readonly ParticipantRepository $participantRepository,
    ) {
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

        return $this->form($sortie, $request);
        // TODO A SUP SEULMENET POUR LES TEST
        //        $sortie->setNom('Voiture');
        //        $sortie->setInfosSortie('Sortie en voiture avec une lotus');
        //        $sortie->setDateHeureDebut((new \DateTime('now'))->add(new \DateInterval('P2D')));
        //        $sortie->setDateLimiteInscription((new \DateTime('now'))->add(new \DateInterval('P1D')));
        //        $sortie->setNbInscriptionsMax(4);
        //        $sortie->setDuree(60);
    }

    #[Route('/modifier/{sortieId}')]
    public function modifier(Request $request, int $sortieId): Response
    {
        $sortie = $this->sortieRepository->find($sortieId);
        if (!($sortie->getEtat()->getLibelle() === EtatEnum::OUVERTE->value or $sortie->getEtat()->getLibelle() === EtatEnum::CREEE->value)) {
            $this->addFlash('error', 'La sortie ne peut pas être modifiée');

            return $this->redirectToRoute('app_sortie_lister');
        }
        if ($sortie->getOrganisateur() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous avez pas les permissions');

            return $this->redirectToRoute('app_sortie_lister');
        }

        return $this->form($sortie, $request);
    }

    private function form(Sortie $sortie, Request $request)
    {
        // création du formulaire
        $form = $this->createForm(SortieType::class, $sortie);
        // gestion des données de la request
        $form->handleRequest($request);
        // test si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // definie l'utilisateur de connecter en tant qu'organisateur
            $sortie->setOrganisateur($this->getUser());
            // definie le site de ratachement en fonction de l'organisateur
            $sortie->setSite($this->getUser()->getSite());
            // si bouton enregister cliqué
            if ($form->get('enregistrerSortie')->isClicked()) {
                if (!$form->get('lieu')->getData()) {
                    // récupère l'état créée
                    $etat = $this->etatRepository->findOneBy(['libelle' => EtatEnum::CREEE]);
                    $newlieuData = $form->get('newlieu')->getData();
                    // Vérifie si les champs sont rempli dans newlieu
                    if ($newlieuData && (null !== $newlieuData->getNom() && '' !== trim($newlieuData->getNom()))
                        && (null !== $newlieuData->getRue() && '' !== trim($newlieuData->getRue()))
                        && (null !== $newlieuData->getLatitude() && '' !== trim($newlieuData->getLatitude()))
                        && (null !== $newlieuData->getLongitude() && '' !== trim($newlieuData->getLongitude()))) {
                        $newVilleData = $form->get('newlieu')->get('newville')->getData();
                        $sortie->setLieu($newlieuData);
                        if ($newVilleData && (null !== $newVilleData->getNom() && '' !== trim($newVilleData->getNom()))
                            && (null !== $newVilleData->getCodePostal() && '' !== trim($newVilleData->getCodePostal()))) {
                            $sortie->getLieu()->setVille($newVilleData);
                        }
                    }
                }
            }
            // si le bouton publier cliqué
            if ($form->get('publierSortie')->isClicked()) {
                if (!$form->get('lieu')->getData()) {
                    $newlieuData = $form->get('newlieu')->getData();
                    // Vérifie si les champs sont rempli dans newlieu
                    if ($newlieuData && (null !== $newlieuData->getNom() && '' !== trim($newlieuData->getNom()))
                        && (null !== $newlieuData->getRue() && '' !== trim($newlieuData->getRue()))
                        && (null !== $newlieuData->getLatitude() && '' !== trim($newlieuData->getLatitude()))
                        && (null !== $newlieuData->getLongitude() && '' !== trim($newlieuData->getLongitude()))) {
                        $newVilleData = $form->get('newlieu')->get('newville')->getData();
                        if ($newVilleData && (null !== $newVilleData->getNom() && '' !== trim($newVilleData->getNom()))
                            && (null !== $newVilleData->getCodePostal() && '' !== trim($newVilleData->getCodePostal()))) {
                            $sortie->setLieu($newlieuData);
                            $sortie->getLieu()->setVille($newVilleData);
                        } else {
                            $this->addFlash('error', 'La nouvelle ville dois être entièrement complété');

                            return $this->render('sortie/ajouter.html.twig', [
                                'sortieform' => $form,
                            ]);
                        }
                    } else {
                        $this->addFlash('error', 'Le nouveau lieu dois être entièrement complété');
                        return $this->render('sortie/ajouter.html.twig', [
                            'sortieform' => $form,
                        ]);
                    }
                }

                // récupère l'état ouverte
                $etat = $this->etatRepository->findOneBy(['libelle' => EtatEnum::OUVERTE]);
            }
            // definie l'etat à la sortie
            $sortie->setEtat($etat);
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

    #[Route('/annuler/{sortie}')]
    public function annuler(?Sortie $sortie, Registry $registry): Response
    {
        // test si la sortie existe
        if (!$sortie) {
            // revoie un message d'erreur
            $this->addFlash('error', 'La sortie n\'existe pas');

            return $this->redirectToRoute('app_sortie_lister');
        }
        // test si c'est l'organisateur
        if ($this->isGranted('ROLE_USER') && $sortie->getOrganisateur() !== $this->getUser()) {
            // revoie un message d'erreur
            $this->addFlash('error', 'Vous n\'avez pas les permissions');

            return $this->redirectToRoute('app_sortie_lister');
        }
        // si la sortie est en etat ouverte
        if ($sortie->getEtat()->getLibelle() === EtatEnum::OUVERTE->value) {
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
        // vérifier si l'utilisateur est déjà inscrit à la sortie :
        if ($sortie->getParticipants()->contains($participant)) {
            // se désinscrire :
            $sortie->removeParticipant($participant);
            $this->addFlash('success', 'Vous êtes bien désinscrit de la sortie '.$sortie->getNom());
        } else {
            if ($sortie->getParticipants()->count() >= $sortie->getNbInscriptionsMax()) {
                $this->addFlash('error', 'La sortie "'.$sortie->getNom().'" n\'a plus de place disponible');

                return $this->redirectToRoute('app_sortie_lister');
            }
            // inscrire l'utilisateur
            $sortie->addParticipant($participant);
            $this->addFlash('success', 'Vous êtes bien inscrit à la sortie '.$sortie->getNom());
        }
        // sauvegarde le site en base de donnée
        $this->entityManager->persist($sortie);
        // maj en base de données
        $this->entityManager->flush();

        return $this->redirectToRoute('app_sortie_lister');
    }
}
