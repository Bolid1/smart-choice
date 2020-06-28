<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ImportTransactionsTask;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportTransactionsTaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'mimeType',
                ChoiceType::class,
                [
                    'choices' => \array_reduce(
                        ImportTransactionsTask::MIME_TYPES_VARIANTS,
                        static function ($choices, $value) {
                            return $choices + [$value => $value];
                        },
                        []
                    ),
                ]
            )
            ->add('data')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ImportTransactionsTask::class,
            ]
        );
    }
}
