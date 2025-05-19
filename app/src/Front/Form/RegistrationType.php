<?php

namespace App\Front\Form;

use App\API\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo:',
                'attr' => [
                    'placeholder' => 'Votre pseudo',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email:',
                'attr' => [
                    'placeholder' => 'Votre email',
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password:',
                'attr' => [
                    'placeholder' => 'Votre mot de passe',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'registration_form',
        ]);
    }
}
