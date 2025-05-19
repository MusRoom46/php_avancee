<?php

namespace App\Front\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email:',
                'attr' => [
                    'placeholder' => 'Votre email',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'L\'email ne peut pas être vide']),
                    new Email(['message' => 'L\'adresse email doit être valide']),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password:',
                'attr' => [
                    'placeholder' => 'Votre mot de passe',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le mot de passe ne peut pas être vide']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'login_form',
        ]);
    }
}