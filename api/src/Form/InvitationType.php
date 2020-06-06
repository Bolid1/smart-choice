<?php

namespace App\Form;

use App\Entity\Invitation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class InvitationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $invitation = $options['data'] ?? null;
        $invitation = $invitation instanceof Invitation ? $invitation : null;

        $builder
            ->add(
                'email',
                EmailType::class,
                [
                    'disabled' => $invitation && $invitation->getEmail(),
                ]
            )
            ->add('plainSecret', TextType::class)
            ->add(
                'admin',
                CheckboxType::class,
                [
                    'required' => false,
                ]
            )
        ;
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
