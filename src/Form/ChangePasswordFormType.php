<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nouveauMotDePasse', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => [
                    'label' => 'Nouveau mot de passe',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez entrer un mot de passe',
                        ]),
                        new Length([
                            'min' => 12,
                            'minMessage' => 'Votre message doit contenir au moins{{ limit }} caractères',
                            // longueur maximum sécurité alloué par Symfony
                            'max' => 4096,
                        ]),
                        new PasswordStrength([
                            'message' => 'Votre mot de passe est trop faible, ajoutez des majuscules, chiffres et caractères spéciaux.',
                        ]),
                        new NotCompromisedPassword([
                            'message' => 'Ce mot de passe a déjà été divulgué lors d’une fuite de données, merci d’en choisir un autre.',
                        ]),
                    ],
                ],
                'second_options' => [
                    'label' => 'Répétez le mot de passe',
                ],
                'invalid_message' => 'Les deux mots de passe doivent être identiques.',
                'mapped' => false, // ⚠️ Ne sera pas écrit directement en BDD
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
