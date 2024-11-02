<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\PasswordStrength;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'first_options'  => [
                    'label' => 'Mot de passe',
                    'attr' => [
                        'class' => 'form-control password',
                        'placeholder' => 'Mot de passe',
                        'autocomplete' => 'new-password',
                        'minlength' => 16,
                        'maxlength' => 255,
                        'pattern' => '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{16,255}$',
                        'title' => 'Le mot de passe doit contenir au moins 16 caractères, dont une lettre majuscule, une lettre minuscule, un chiffre, et un caractère spécial.',
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmation du mot de passe',
                    'attr' => [
                        'class' => 'form-control password',
                        'placeholder' => 'Confirmer le mot de passe',
                        'autocomplete' => 'new-password',
                    ],
                ],
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le mot de passe est obligatoire.',
                    ]),
                    new Length([
                        'min' => 16,
                        'minMessage' => 'Votre mot de passe doit comporter au moins {{ limit }} caractères.',
                        'max' => 255,
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{16,255}$/',
                        'message' => 'Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial.',
                    ]),
                    new PasswordStrength(),
                    new NotCompromisedPassword(),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
