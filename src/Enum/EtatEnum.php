<?php

namespace App\Enum;

use phpDocumentor\Reflection\Types\This;

enum EtatEnum: string
{
     case CREEE = 'Créée';
     case OUVERTE = 'Ouverte';
     case ANNULEE = 'Annulée';
     case PASSEE = 'Passée';
     case ACTIVITE_EN_COURS = 'Activité en cours';
     case CLOTUREE = 'Clôturée';
     case ARCHIVEE = 'Archivée';

     public static function getNomWorkflow(string $etat): string
     {
         return match ($etat) {
             EtatEnum::CREEE->value => 'creee',
             EtatEnum::OUVERTE->value => 'ouverte',
             EtatEnum::ANNULEE->value => 'annulee',
             EtatEnum::ACTIVITE_EN_COURS->value => 'activite_en_cours',
             EtatEnum::PASSEE->value => 'passee',
             EtatEnum::CLOTUREE->value => 'cloturee',
             EtatEnum::ARCHIVEE->value => 'archivee',
             default => throw new \Exception('Une erreur est survenue lors de la traduction du workflow !'),
         };
     }

     public static function getTrad(string $etat): string
     {
         return match ($etat) {
             'creee' => EtatEnum::CREEE->value,
             'ouverte' => EtatEnum::OUVERTE->value,
             'annulee' => EtatEnum::ANNULEE->value,
             'activite_en_cours' => EtatEnum::ACTIVITE_EN_COURS->value,
             'passee' => EtatEnum::PASSEE->value,
             'cloturee' => EtatEnum::CLOTUREE->value,
             default => throw new \Exception('Une erreur est survenue lors de la traduction du workflow !'),
         };
     }
}
