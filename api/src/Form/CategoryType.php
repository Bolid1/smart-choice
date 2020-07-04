<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $category = $options['data'] ?? null;
        $category = $category instanceof Category ? $category : null;
        $company = $category ? $category->company : null;
        $categories = $company
            ? $company
                ->getCategories()
                ->filter(
                    static function (Category $choice) use ($category) {
                        return $choice !== $category;
                    }
                )
            : [];

        $builder
            ->add('name', TextType::class)
            ->add(
                'parent',
                EntityType::class,
                [
                    'required' => false,
                    'class' => Category::class,
                    'choices' => $categories,
                    // See "Cannot set child as parent to node: {$nodeId}" in Gedmo Nested
                    'disabled' => $category && $category->getId(),
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Category::class,
            ]
        );
    }
}
