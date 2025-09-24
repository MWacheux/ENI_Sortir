<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'placeholder' => 'Nom de la sortie',
                ],
            ])
            ->add('dateHeureDebut' , DateType::class, [
                'attr' => [
                    'placeholder' => 'Date et heure de la sortie',
                ],
            ])
            ->add('dateLimiteInscription', DateType::class, [])

            -> add('nbInscriptionsMax', IntegerType::class, [
                'attr' => [
                    'placeholder' => 'Nombre de places',
                ],
            ])
            ->add('duree', IntegerType::class, [])
            ->add('infosSortie' , null, [
                'attr' => [
                    'placeholder' => 'Description et infos',
                ],
             ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'attr' => [
                    'placeholder' => 'ville organisatrice',
                ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
