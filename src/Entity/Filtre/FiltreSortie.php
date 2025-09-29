<?php

namespace App\Entity\Filtre;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

class FiltreSortie
{
    public ?string $nom = null;
    public ?\DateTime $dateDebut = null;
    public ?\DateTime $dateFin = null;
    public ?Site $site = null;
    public ?bool $isOrganisateur = null;
    public ?bool $isInscrit = null;
    public ?bool $isPassee = null;
    public ?bool $isOrganisateurAndCreee = null;
}
