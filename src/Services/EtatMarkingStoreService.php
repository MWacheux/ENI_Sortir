<?php

namespace App\Services;

use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\MarkingStore\MarkingStoreInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Sortie;
use App\Entity\Etat;

class EtatMarkingStoreService implements MarkingStoreInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function getMarking(object $subject): Marking
    {
        if (!$subject instanceof Sortie) {
            throw new \LogicException('Le subject doit être une instance de Sortie.');
        }

        $etat = $subject->getEtat();
        if (!$etat) {
            throw new \LogicException('Aucun état défini pour cette sortie.');
        }

        return new Marking([$etat->getLibelle() => 1]);
    }

    public function setMarking(object $subject, Marking $marking, array $context = []): void
    {
        if (!$subject instanceof Sortie) {
            throw new \LogicException('Le subject doit être une instance de Sortie.');
        }

        $place = array_key_first($marking->getPlaces());

        $etat = $this->em->getRepository(Etat::class)->findOneBy(['libelle' => $place]);

        if (!$etat) {
            throw new \LogicException("L'état '$place' n'existe pas dans la base.");
        }

        $subject->setEtat($etat);
    }
}
