<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Creation de l'admin
        $participant = new Participant();
        $participant->setNom('Admin')
            ->setPrenom('Admin')
            ->setActif(true)
            ->setEmail('admin@test.fr')
            ->setPassword('$2y$13$jEijnLRVHCo6TltxxzEal.1QGpilma0yZPbiDjbfrqxwWXERIp.Du')
            ->setRoles(['ROLE_ADMIN'])
            ->setTelephone('0123456789');
        // Creation d'un user classique
        $participant = new Participant();
        $participant->setNom('User')
            ->setPrenom('User')
            ->setActif(true)
            ->setEmail('user@test.fr')
            ->setPassword('$2y$13$bmvOngqCVyJ5B7CeduZzH.lzOHxFn79NZaLd3SXa.0psSQDytKMLW')
            ->setRoles(['ROLE_USER'])
            ->setTelephone('0123456789');
        $manager->persist($participant);
        // creation des etats possible
        $etat = new Etat();
        $etat->setLibelle('creee');
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle('ouverte');
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle('cloturee');
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle('activite_en_cours');
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle('passee');
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle('annulee');
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle('archivee');
        $manager->persist($etat);
        // creation des sites
        $site = new Site();
        $site->setNom('Nantes');
        $manager->persist($site);
        $site = new Site();
        $site->setNom('Rennes');
        $manager->persist($site);
        $site = new Site();
        $site->setNom('Niort');
        $manager->persist($site);
        $site = new Site();
        $site->setNom('Quimper');
        $manager->persist($site);
        $site = new Site();
        $site->setNom('En ligne');
        $manager->persist($site);

        $manager->flush();
    }
}
