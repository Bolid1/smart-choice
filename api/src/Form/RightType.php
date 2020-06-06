<?php

namespace App\Form;

use App\Entity\Right;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class RightType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'user',
                EmailType::class,
                [
                    'property_path' => 'user.username',
                    'disabled' => true,
                ]
            )
            ->add('admin')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Right::class,
            ]
        );
    }
}
