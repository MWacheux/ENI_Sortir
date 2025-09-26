<?php

// src/Twig/MonExtension.php
namespace App\Twig;

use App\Entity\Etat;
use App\Enum\EtatEnum;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ColorEtatTwig extends AbstractExtension
{

    public function getFunctions(): array
    {
        return [
            new TwigFunction('color_etat', [$this, 'colorEtatTwig']),
        ];
    }

    public function colorEtatTwig(Etat $etat) :string
    {
        return match ($etat->getLibelle()) {
            EtatEnum::CREEE->value => 'info',
            EtatEnum::OUVERTE->value => 'success',
            EtatEnum::ANNULEE->value => 'danger',
            EtatEnum::PASSEE->value => 'secondary',
            EtatEnum::ACTIVITE_EN_COURS->value => 'warning',
            EtatEnum::CLOTUREE->value => 'gray',
            EtatEnum::ARCHIVEE->value => 'dark',
            default => 'UNDEFINED_COLORETATTWIG',
        };
    }
}
