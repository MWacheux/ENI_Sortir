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
            'passée' => 'secondary',
            'activité en cours' => 'warning',
            'clôturée' => 'gray',
            'archivée' => 'dark',
            default => 'UNDEFINED_COLORETATTWIG',
        };
    }
}
