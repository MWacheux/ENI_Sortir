<?php

namespace App\Form\Filtre;

use App\Entity\Filtre\FiltreSortie;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreSortieType extends AbstractType
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
                'required' => false,
            ])
            ->add('dateDebut', DateType::class, [
                'attr' => [
                    'class' => 'form-control',
                ],
                'required' => false,
            ])
            ->add('dateFin', DateType::class, [
                'attr' => [
                    'class' => 'form-control',
                ],
                'required' => false,
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
                'placeholder' => 'Tous',
                'attr' => [
                    'class' => 'form-control',
                ],
                'required' => false,
            ])
            ->add('isOrganisateur', CheckboxType::class, [
                'label' => 'Seulement sortie dont je suis l\'organisatrice/teur',
                'attr' => [
                    'class' => 'form-check-input',
                ],
                'required' => false,
            ])
            ->add('isInscrit', CheckboxType::class, [
                'label' => 'Seulement sortie dont je suis inscrit',
                'attr' => [
                    'class' => 'form-check-input',
                ],
                'required' => false,
            ])
            ->add('isPassee', CheckboxType::class, [
                'label' => 'Seulement sortie passées',
                'attr' => [
                    'class' => 'form-check-input',
                ],
                'required' => false,
            ])
            ->add('isOrganisateurAndCreee', CheckboxType::class, [
                'label' => 'Inclure mes sorties en brouillon',
                'attr' => [
                    'class' => 'form-check-input',
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FiltreSortie::class,
            'csrf_protection' => false,
        ]);
    }
}
