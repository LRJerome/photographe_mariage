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
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter a password',
                        ]),
                        new Length([
                            'min' => 12,
                            'max' => 255,
                            'minMessage' => "Le mot de passe doit contenir un minimum de {{ limit }} caractères!",
                            'maxMessage' => "Le mot de passe doit contenir un maximum de {{ limit }} caractères!",
                            // max length allowed by Symfony for security reasons
                        ]),
                        new Regex([ 
                            'pattern'=> '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{11,}$/',
                            'match'=> true,
                            'message'=> "Le mot de passe doit contenir au minimum 12 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial."
                        ]),
                        new NotCompromisedPassword(),
                    ],
                    ],
                    'invalid_message' => "Le nouveau mot de passe doit être identique à sa confirmation !",
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
