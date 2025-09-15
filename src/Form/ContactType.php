<?php

namespace App\Form;

use App\Dto\ContactData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Honeypot : reject if filled in controller
            ->add('website', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])

            ->add('name', TextType::class, [
                'label' => 'Your name',
                'attr' => [
                    'placeholder' => 'John Doe',
                    'autocomplete' => 'name',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Your email',
                'attr' => [
                    'placeholder' => 'email@example.com',
                    'autocomplete' => 'email',
                    'inputmode' => 'email',
                ],
            ])
            ->add('subject', TextType::class, [
                'label' => 'Subject',
                'attr' => [
                    'placeholder' => 'How can we help ?',
                    'maxlength' => 120,
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => [
                    'placeholder' => 'Write your message…',
                    'rows' => 6,
                    'maxlength' => 2000,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactData::class,
        ]);
    }
}
