<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\Board;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\NotBlank;


class BoardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label'    => 'Board title',
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Board::class,
        ]);
    }
}
            // ajout message erreur si pas de titre
            // Voir si il faut modifier le "add" de accounts car cela peut créer un nouv account ? (et non le lier a un existant)
            /*->add('accounts', EntityType::class, [
                'class' => Account::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])*/
