<?php

namespace App\Entity\Filtre;

use App\Entity\Site;
use Symfony\Component\Validator\Constraints as Assert;

class FiltreSortie
{
    public ?string $nom = null;

    public ?\DateTime $dateDebut = null;
    #[Assert\Expression('this.dateDebut < this.dateFin or this.dateFin == null', message: 'La date doit être supérieur à la date de début')]
    public ?\DateTime $dateFin = null;
    public ?Site $site = null;
    public ?bool $isOrganisateur = null;
    public ?bool $isInscrit = null;
    public ?bool $isPassee = null;
    public ?bool $isOrganisateurAndCreee = true;
}
