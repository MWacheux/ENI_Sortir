<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
                    'label' => 'Nom de la sortie',
                    'placeholder' => 'Nom de la sortie',
                    'class' => 'form-control',
                ],
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date et heure de la sortie',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                'label' => 'Date limite pour s\'inscription',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre de places',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée en minutes',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])

            ->add('infosSortie', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'Description et information de la sortie',
                    'class' => 'form-control',
                ],
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'required' => false,
                'attr' => [
                    'placeholder' => 'ville organisatrice',
                    'class' => 'form-control',
                ],
            ])
            ->add('ajouterLieu', SubmitType::class, [
                'label' => 'Ajouter un lieu',
                'attr' => [
                    'class' => 'btn btn-dark',
                ],
            ])
            ->add('enregistrerSortie', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-dark  mt-3',
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
