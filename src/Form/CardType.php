<?php

namespace App\Form;

use App\Entity\Card;
use App\Entity\Lane;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Length;

class CardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label'    => 'Card title',
                'attr'     => [
                    'placeholder' => 'Enter a title',
                    'class' => 'input input-bordered w-full text-base-content bg-base-100'
                ],
                'label_attr' => [
                    'class' => 'label-text text-base-content font-medium'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'The title field cannot be empty.'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label'    => 'Description',
                'attr'     => [
                    'placeholder' => 'Enter a description',
                    'class' => 'textarea textarea-bordered w-full text-base-content bg-base-100'
                ],
                'label_attr' => [
                    'class' => 'label-text text-base-content font-medium'
                ],
            ])
            ->add('status', TextType::class, [
                'label'    => 'Status',
                'attr'     => [
                    'placeholder' => 'Enter a status (max 24 characters)',
                    'class' => 'input input-bordered w-full text-base-content bg-base-100'
                ],
                'label_attr' => [
                    'class' => 'label-text text-base-content font-medium'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Card::class,
        ]);
    }
}
