<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\Board;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AddCollaboratorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $board = $options['board'];

        $builder
            ->add('collaborator', EntityType::class, [
                'class' => Account::class,
                'choice_label' => function (Account $account) {
                    return sprintf('%s (%s %s)', $account->getEmail(), $account->getName(), $account->getLastname());
                },
                'placeholder' => 'Select a user to add...',
                'required' => true,
                'label' => false,
                'attr' => [
                    'class' => 'select select-bordered flex-1'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please select a user.']),
                    new Callback(function (?Account $collaborator, ExecutionContextInterface $context) use ($board) {
                        if (!$collaborator) {
                            return;
                        }

                        // Cannot add owner
                        if ($collaborator === $board->getOwner()) {
                            $context->buildViolation('Cannot add the owner as a collaborator.')
                                ->addViolation();
                        }

                        // Prevent duplicates
                        if ($board->getAccounts()->contains($collaborator)) {
                            $context->buildViolation('This user is already a collaborator.')
                                ->addViolation();
                        }
                    })
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('board');
        $resolver->setAllowedTypes('board', Board::class);
    }
}
