<?php

namespace App\Form;

use App\Entity\Board;
use App\Entity\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'autocomplete' => 'email',
                    'inputmode' => 'email', // Mobile-friendly keyboard
                    'placeholder' => 'votre@email.com'
                ]
            ])
            ->add('name', TextType::class, [
                'required' => true,
                'label'    => 'First name',
                'attr'     => ['autocomplete' => 'given-name'],
            ])
            ->add('lastname', TextType::class, [
                'required' => true,
                'label'    => 'Last name',
                'attr'     => ['autocomplete' => 'family-name'],
            ])
            ->add('role')
            ->add('isVerified', CheckboxType::class, [
                'disabled' => true,   // greyed out, cannot be changed
                'label'    => 'Email vérifié',
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
