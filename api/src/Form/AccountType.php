<?php

namespace App\Form;

use App\Entity\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $account = $options['data'] ?? null;
        $account = $account instanceof Account ? $account : null;
        $isExist = $account && $account->getId();

        $builder
            ->add('name')
            ->add(
                'currency',
                CurrencyType::class,
                [
                    'disabled' => $isExist,
                ]
            )
            ->add(
                'balance',
                NumberType::class,
                [
                    'html5' => true,
                    'scale' => 2,
                    'attr' => [
                        'step' => 0.01,
                    ],
                    'disabled' => $isExist,
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Account::class,
            ]
        );
    }
}
