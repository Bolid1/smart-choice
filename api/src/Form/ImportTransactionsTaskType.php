<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ImportTransactionsTask;
use DateTimeImmutable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportTransactionsTaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ImportTransactionsTask|null $task */
        $task = (($task = $options['data'] ?? null) instanceof ImportTransactionsTask) ? $task : null;

        $builder
            ->add('scheduledTime', DateTimeType::class, [
                'input' => 'datetime_immutable',
                'data' => $task && $task->getScheduledTime()
                    ? $task->getScheduledTime() : new DateTimeImmutable(),
                'help' => 'The date, when import should started',
            ])
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
