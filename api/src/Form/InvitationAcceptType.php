<?php

declare(strict_types=1);

namespace App\Form;

use App\Constraint\IsInvitationSecretValid;
use App\Entity\Invitation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class InvitationAcceptType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'plainSecret',
                TextType::class,
                [
                    'constraints' => [
                        new Assert\NotBlank(['message' => 'Please enter a secret']),
                        new Assert\Length(
                            [
                                'min' => 6,
                                'minMessage' => 'Your secret should be at least {{ limit }} characters',
                                'max' => 4096,
                            ]
                        ),
                        new IsInvitationSecretValid(),
                    ],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Invitation::class,
            ]
        );
    }
}
