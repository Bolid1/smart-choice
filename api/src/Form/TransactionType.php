<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\Transaction;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class TransactionType extends AbstractType
{
    public const TYPE_EXPENSE = 'expense';
    public const TYPE_INCOMING = 'incoming';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $transaction = $options['data'] ?? null;
        $transaction = $transaction instanceof Transaction ? $transaction : null;
        $company = $transaction ? $transaction->getCompany() : null;

        $builder
            ->add(
                'type',
                ChoiceType::class,
                [
                    'label' => 'Transfer type',
                    'mapped' => false,
                    'empty_data' => static::TYPE_EXPENSE,
                    'choices' => [
                        'Expense' => static::TYPE_EXPENSE,
                        'Incoming' => static::TYPE_INCOMING,
                    ],
                ]
            )
            ->add(
                'amount',
                NumberType::class,
                [
                    'html5' => true,
                    'scale' => 2,
                    'data' => $transaction ? \abs($transaction->getAmount()) : 0.,
                    'attr' => [
                        'step' => 0.01,
                        'min' => 0,
                    ],
                ]
            )
            ->add(
                'date',
                DateTimeType::class,
                [
                    'input' => 'datetime_immutable',
                    'data' => $transaction && $transaction->getDate()
                        ? $transaction->getDate() : new \DateTimeImmutable(),
                    'help' => 'The date, when transaction occurred',
                ]
            )
            ->add(
                'account',
                EntityType::class,
                [
                    'class' => Account::class,
                    'choices' => $company ? $company->getAccounts() : [],
                    'empty_data' => $company ? $company->getAccounts()->first() : null,
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Transaction::class,
            ]
        );
    }
}
