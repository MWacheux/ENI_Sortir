<?php

namespace App\Repository;

use App\Entity\Filtre\FiltreSortie;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Enum\EtatEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function getSorties(FiltreSortie $filtre, Participant|UserInterface $participant){
        $builder = $this->createQueryBuilder('sortie')
            ->leftJoin('sortie.participants', 'participant')
            ->leftJoin('sortie.organisateur', 'organisateur')
            ->leftJoin('sortie.etat', 'etat');
        if ($filtre->nom){
            $builder->andWhere('sortie.nom LIKE :nom')
                ->setParameter('nom', '%'.$filtre->nom.'%');
        }
        if ($filtre->dateDebut && $filtre->dateFin){
            $builder->andWhere('sortie.dateHeureDebut >= :dateDebut AND sortie.dateHeureDebut <= :dateFin')
                ->setParameter('dateDebut', $filtre->dateDebut)
                ->setParameter('dateFin', $filtre->dateFin);
        }
        if (!$filtre->dateDebut && $filtre->dateFin){
            $builder->andWhere('sortie.dateHeureDebut <= :dateFin')
                ->setParameter('dateFin', $filtre->dateFin);
        }
        if ($filtre->dateDebut && !$filtre->dateFin){
            $builder->andWhere('sortie.dateHeureDebut >= :dateDebut')
                ->setParameter('dateDebut', $filtre->dateDebut);
        }
        if ($filtre->site){
            $builder->andWhere('sortie.site = :site')
                ->setParameter('site', $filtre->site->getId());
        }
        if ($filtre->isInscrit){
            $builder->andWhere('participant.id = :participant')
                ->setParameter('participant', $participant->getId());
        }
        if ($filtre->isOrganisateur){
            $builder->andWhere('organisateur.id = :organisateur')
                ->setParameter('organisateur', $participant->getId());
        }
        if ($filtre->isPassee){
            $builder->andWhere('etat.libelle = :etatLibelle')
                ->setParameter('etatLibelle', 'passée');
        }
        if (!$filtre->isOrganisateurAndCreee){
            $builder->andWhere('etat.libelle <> :libelle')
                ->setParameter('libelle', EtatEnum::CREEE->value);
        }else{
            $builder->andWhere('etat.libelle <> :libelle')
                ->setParameter('libelle', EtatEnum::CREEE->value)
                ->orWhere('etat.libelle = :libelle and sortie.organisateur = :organisateur')
                ->setParameter('libelle', EtatEnum::CREEE->value)
                ->setParameter('organisateur', $participant->getId());
        }
        return $builder->getQuery()->getResult();
    }

//    /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
