<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Category;
use App\Entity\TransactionCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entity = $options['data'] ?? null;
        $entity = $entity instanceof TransactionCategory ? $entity : null;
        $company = $entity && $entity->getCompany() ? $entity->getCompany() : null;
        $categories = $company ? $company->getCategories()->toArray() : [];

        $builder
            ->add(
                'amount',
                NumberType::class,
                [
                    'html5' => true,
                    'scale' => 2,
                    'attr' => [
                        'step' => 0.01,
                        'min' => 0,
                    ],
                ]
            )
            ->add(
                'category',
                EntityType::class,
                [
                    'required' => true,
                    'class' => Category::class,
                    'choices' => $categories,
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => TransactionCategory::class,
            ]
        );
    }
}
