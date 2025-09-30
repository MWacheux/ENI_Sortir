<?php

namespace App\Services;

use App\Entity\Sortie;
use App\Enum\EtatEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Workflow\Registry;

class EtatService
{
    public function __construct(
        private readonly Registry $registry,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function checkWorkflow(Sortie $sortie)
    {
        $workflow = $this->registry->get($sortie, 'sortie');
        $dateNow = new \DateTime();
        // si sortie ouverte
        if ($sortie->getEtat()->getLibelle() == EtatEnum::OUVERTE->value) {
            // test si la date limite d'inscription est passé
            if ($sortie->getDateLimiteInscription() < $dateNow) {
                // cloture la sortie
                $workflow->apply($sortie, 'to_cloturee');
            }
        }
        // si sortie cloturée
        if ($sortie->getEtat()->getLibelle() == EtatEnum::CLOTUREE->value) {
            // test si la date limite d'inscription est passé
            if ($sortie->getDateHeureDebut() < $dateNow) {
                // passe la sortie en "en cours"
                $workflow->apply($sortie, 'to_activitee_en_cours');
            }
        }
        // si sortie activité en cours
        if ($sortie->getEtat()->getLibelle() == EtatEnum::ACTIVITE_EN_COURS->value) {
            // test si la date limite d'inscription est passé
            if ($sortie->getDateHeureDebut()->add(new \DateInterval('PT'.$sortie->getDuree().'M')) < $dateNow) {
                // passe la sortie en "passée"
                $workflow->apply($sortie, 'to_passee');
            }
        }
        // si sortie passée
        if ($sortie->getEtat()->getLibelle() == EtatEnum::PASSEE->value) {
            // test si sortie date depuis plus d'un mois
            if ($sortie->getDateHeureDebut()->add(new \DateInterval('PT'.$sortie->getDuree().'M'))->add(new \DateInterval('P1M')) <= $dateNow) {
                // archive la sortie
                $workflow->apply($sortie, 'to_activitee_en_cours');
            }
        }
        $this->em->persist($sortie);
        $this->em->flush();
    }
}
