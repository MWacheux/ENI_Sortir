<?php

// src/Twig/MonExtension.php
namespace App\Twig;

use App\Entity\Etat;
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
            'ouverte' => 'success',
            'annulée' => 'danger',
            'passée' => 'info',
            'activité en cours' => 'warning',
            'cloturée' => 'gray',
            'archivée' => 'black',
            default => 'UNDEFINED_COLORETATTWIG',
        };
    }
}
