<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('rue', TextType::class)
            ->add('latitude', IntegerType::class)
            ->add('longitude', IntegerType::class)
            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => 'nom',
                'required' => false,
                'placeholder' => '<Nouvelle ville>',
                'attr' => [
                    'data-sortie-target' => 'dropdownVille',
                    'data-action' => 'input->sortie#toggleShow',
                    'class' => 'form-control',
                ],
            ])
            ->add('newville', VilleType::class, [
                'label' => 'Nouvelle ville',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
