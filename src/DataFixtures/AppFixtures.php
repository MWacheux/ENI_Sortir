<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Enum\EtatEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // creation des sites
        $nantes = new Site();
        $nantes->setNom('Nantes');
        $manager->persist($nantes);
        $rennes = new Site();
        $rennes->setNom('Rennes');
        $manager->persist($rennes);
        $niort = new Site();
        $niort->setNom('Niort');
        $manager->persist($niort);
        $quimper = new Site();
        $quimper->setNom('Quimper');
        $manager->persist($quimper);
        $enLigne = new Site();
        $enLigne->setNom('En ligne');
        $manager->persist($enLigne);
        // Creation de l'admin
        $admin = new Participant();
        $admin->setNom('ENI')
            ->setPrenom('Admin')
            ->setSite($nantes)
            ->setActif(true)
            ->setEmail('admin@test.fr')
            ->setPassword('$2y$13$J/3BoAyb0/O3nGBrf04U6.1vMfrjsl/2Wc0xaAJ9YpS2xNxpKucx2')
            ->setRoles(['ROLE_ADMIN'])
            ->setTelephone('0123456789');
        $manager->persist($admin);
        // Creation d'un user classique
        $maiwenn = new Participant();
        $maiwenn->setNom('WACHEUX')
            ->setPrenom('Maïwenn')
            ->setSite($nantes)
            ->setActif(true)
            ->setEmail('mwacheux@test.fr')
            ->setPassword('$2y$13$J/3BoAyb0/O3nGBrf04U6.1vMfrjsl/2Wc0xaAJ9YpS2xNxpKucx2')
            ->setRoles(['ROLE_USER'])
            ->setTelephone('0123456789');
        $manager->persist($maiwenn);
        // Creation d'un user classique
        $zohera = new Participant();
        $zohera->setNom('EL Fakhadi')
            ->setPrenom('Zohera')
            ->setSite($nantes)
            ->setActif(true)
            ->setEmail('zozo@test.fr')
            ->setPassword('$2y$13$J/3BoAyb0/O3nGBrf04U6.1vMfrjsl/2Wc0xaAJ9YpS2xNxpKucx2')
            ->setRoles(['ROLE_USER'])
            ->setTelephone('0123456789');
        $manager->persist($zohera);
        $noa = new Participant();
        $noa->setNom('HERVIEU')
            ->setPrenom('Noä')
            ->setSite($nantes)
            ->setActif(true)
            ->setEmail('nhervieu@test.fr')
            ->setPassword('$2y$13$J/3BoAyb0/O3nGBrf04U6.1vMfrjsl/2Wc0xaAJ9YpS2xNxpKucx2')
            ->setRoles(['ROLE_USER'])
            ->setTelephone('0123456789');
        $manager->persist($noa);
        // creation des etats possible
        $creee = new Etat();
        $creee->setLibelle(EtatEnum::CREEE->value);
        $manager->persist($creee);
        $ouverte = new Etat();
        $ouverte->setLibelle(EtatEnum::OUVERTE->value);
        $manager->persist($ouverte);
        $cloturee = new Etat();
        $cloturee->setLibelle(EtatEnum::CLOTUREE->value);
        $manager->persist($cloturee);
        $enCours = new Etat();
        $enCours->setLibelle(EtatEnum::ACTIVITE_EN_COURS->value);
        $manager->persist($enCours);
        $passee = new Etat();
        $passee->setLibelle(EtatEnum::PASSEE->value);
        $manager->persist($passee);
        $annulee = new Etat();
        $annulee->setLibelle(EtatEnum::ANNULEE->value);
        $manager->persist($annulee);
        $archivee = new Etat();
        $archivee->setLibelle(EtatEnum::ARCHIVEE->value);
        $manager->persist($archivee);

        // creation de ville
        $brest = new Ville();
        $brest->setNom('Brest');
        $brest->setCodePostal('29200');
        $manager->persist($brest);
        $caen = new Ville();
        $caen->setNom('Caen');
        $caen->setCodePostal('14000');
        $manager->persist($caen);
        $percy = new Ville();
        $percy->setNom('Percy');
        $percy->setCodePostal('50410');
        $manager->persist($percy);
        $saintHerblain = new Ville();
        $saintHerblain->setNom('Saint Herblain');
        $saintHerblain->setCodePostal('44800');
        $manager->persist($saintHerblain);

        // creation des lieux
        $escapeGame = (new Lieu())
            ->setNom('LockQuest')
            ->setRue('6 Chem. de Lamballard, 14760 Bretteville-sur-Odon')
            ->setVille($caen)
            ->setLatitude(0)
            ->setLongitude(0);
        $manager->persist($escapeGame);
        $paintBall = (new Lieu())
            ->setNom('L\'usine')
            ->setRue('l\'épinière, 2 domaine de Sienne, 50410 Percy-en-Normandie')
            ->setVille($percy)
            ->setLatitude(0)
            ->setLongitude(0);
        $manager->persist($paintBall);
        $cinema = (new Lieu())
            ->setNom('Cinéma Pathé')
            ->setRue('8 All. la Pérouse, 44800 Saint-Herblain')
            ->setVille($saintHerblain)
            ->setLatitude(0)
            ->setLongitude(0);
        $manager->persist($cinema);
        $escalade = (new Lieu())
            ->setNom('Vertical Art')
            ->setRue('1 Rue d\'Athènes, 44300 Nantes')
            ->setVille($saintHerblain)
            ->setLatitude(0)
            ->setLongitude(0);
        $manager->persist($escalade);
        $bowling = (new Lieu())
            ->setNom('Bowling')
            ->setRue('1 Rue d\'Athènes, 44300 Nantes')
            ->setVille($saintHerblain)
            ->setLatitude(0)
            ->setLongitude(0);
        $manager->persist($bowling);

        // Creation des sorties
        $sortieOuverte = (new Sortie())
            ->setNom("Escape game")
            ->setLieu($escapeGame)
            ->setEtat($ouverte)
            ->setSite($nantes)
            ->setInfosSortie('Sortie escape game à LockQuest, pour un niveau intermédiaire !')
            ->setOrganisateur($admin)
            ->setDateHeureDebut((new \DateTime())->add(new \DateInterval('P1D')))
            ->setDateLimiteInscription((new \DateTime())->add(new \DateInterval('P1D'))->sub(new \DateInterval('PT3H')))
            ->setDuree(90)
            ->setNbInscriptionsMax(4);
        $manager->persist($sortieOuverte);
        $sortieOuverte = (new Sortie())
            ->setNom("Bowling")
            ->setLieu($bowling)
            ->setEtat($ouverte)
            ->setSite($nantes)
            ->setInfosSortie('Sortie au bowling venez nombreux !')
            ->setOrganisateur($maiwenn)
            ->setDateHeureDebut((new \DateTime())->add(new \DateInterval('P1D')))
            ->setDateLimiteInscription((new \DateTime())->add(new \DateInterval('P1D'))->sub(new \DateInterval('PT3H')))
            ->setDuree(90)
            ->setNbInscriptionsMax(4);
        $manager->persist($sortieOuverte);
        $sortieCloturee = (new Sortie())
            ->setNom("Paintball")
            ->setLieu($paintBall)
            ->setEtat($ouverte)
            ->setSite($rennes)
            ->setInfosSortie('Sortie paintball urbex dans une ancienne usine')
            ->setOrganisateur($admin)
            ->setDateHeureDebut((new \DateTime())->add(new \DateInterval('P1D'))->add(new \DateInterval('PT1H')))
            ->setDateLimiteInscription((new \DateTime())->sub(new \DateInterval('P1D')))
            ->setDuree(240)
            ->setNbInscriptionsMax(16);
        $manager->persist($sortieCloturee);
        $sortiePassee = (new Sortie())
            ->setNom("Cinéma")
            ->setLieu($cinema)
            ->setEtat($passee)
            ->setSite($nantes)
            ->setInfosSortie('Sortie au cinéma pour voir le film "Une bataille après l\'autres"')
            ->setOrganisateur($maiwenn)
            ->setDateHeureDebut((new \DateTime())->sub(new \DateInterval('P1D')))
            ->setDateLimiteInscription((new \DateTime())->sub(new \DateInterval('P1D'))->sub(new \DateInterval('PT2H')))
            ->setDuree(140)
            ->setNbInscriptionsMax(6);
        $manager->persist($sortiePassee);
        $sortieArchivee = (new Sortie())
            ->setNom("Escalade")
            ->setLieu($escalade)
            ->setEtat($archivee)
            ->setSite($niort)
            ->setInfosSortie('Sortie escalade ouvert à tous, pour tout les niveaux')
            ->setOrganisateur($maiwenn)
            ->setDateHeureDebut((new \DateTime())->sub(new \DateInterval('P31D')))
            ->setDateLimiteInscription((new \DateTime())->sub(new \DateInterval('P31D'))->sub(new \DateInterval('PT6H')))
            ->setDuree(140)
            ->setNbInscriptionsMax(6);
        $manager->persist($sortieArchivee);

        $manager->flush();
    }
}
