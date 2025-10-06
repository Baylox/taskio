<?php

namespace App\Form;

use App\Entity\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints as Assert;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['autocomplete' => 'given-name'],
            ])
            ->add('lastname', TextType::class, [
                'attr' => ['autocomplete' => 'family-name'],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => false,
                'first_options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                    'label' => 'New Password',
                    'help' => 'At least 8 characters with uppercase, lowercase, number and special character',
                    'constraints' => [
                        new Assert\Length([
                            'min' => 8,
                            'minMessage' => 'Password must be at least {{ limit }} characters long',
                        ]),
                        new Assert\Regex([
                            'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                            'message' => 'Password must contain at least one uppercase letter, one lowercase letter, one number and one special character (@$!%*?&)',
                        ]),
                        new Assert\NotCompromisedPassword([
                            'message' => 'This password has been compromised in a data breach. Please choose a different one.',
                        ]),
                    ],
                ],
                'second_options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                    'label' => 'Confirm New Password',
                ],
                'invalid_message' => 'The password fields must match.',
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Account::class,
        ]);
    }
}
