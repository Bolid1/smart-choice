<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\Transaction;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Test\ApiTestCase;

class TransactionAffectsAccountBalanceApiTest extends ApiTestCase
{
    /**
     * @covers \App\Security\TransactionVoter::supports()
     * @covers \App\Security\TransactionVoter::voteOnAttribute()
     * @covers \App\DataPersister\TransactionDataPersister::__construct()
     * @covers \App\DataPersister\TransactionDataPersister::supports()
     * @covers \App\DataPersister\TransactionDataPersister::persist()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateTransactionAffectsAccount(): void
    {
        $client = static::createAuthenticatedClient();

        $account = $this->getAccount();
        $balance = $account->getBalance();

        $client->request(
            'POST',
            '/api/v0/transactions',
            [
                'json' => [
                    'account' => $this->getIriFromItem($account),
                    'amount' => $amount = 1200.5,
                ],
            ]
        );

        $this->assertEquals(
            $balance + $amount,
            $this->getAccount()->getBalance(),
            'Transaction amount should affects account balance.'
        );
    }

    /**
     * @covers \App\Security\TransactionVoter::supports()
     * @covers \App\Security\TransactionVoter::voteOnAttribute()
     * @covers \App\DataPersister\TransactionDataPersister::__construct()
     * @covers \App\DataPersister\TransactionDataPersister::supports()
     * @covers \App\DataPersister\TransactionDataPersister::persist()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchTransaction(): void
    {
        $client = static::createAuthenticatedClient();

        $fromAccount = $this->getAccount();
        $fromAccountBalance = $fromAccount->getBalance();
        $toAccount = $this->getEmptyAccount();
        $toAccountBalance = $toAccount->getBalance();

        /** @var Transaction $transaction */
        $transaction = $this->findItemBy(
            Transaction::class,
            [
                'account' => $fromAccount->getId(),
            ],
        );

        $client->request(
            'PATCH',
            $this->getIriFromItem($transaction),
            [
                'json' => [
                    'account' => $this->getIriFromItem($toAccount),
                    'amount' => $amount = \round($transaction->getAmount() + 100.1, 2),
                ],
            ],
        );

        $this->assertEquals(
            $fromAccountBalance - $transaction->getAmount(),
            $this->getAccount()->getBalance(),
            'Transaction amount should be removed from account balance.'
        );
        $this->assertEquals(
            $toAccountBalance + $amount,
            $this->getEmptyAccount()->getBalance(),
            'Transaction amount should be added to account balance.'
        );
    }

    /**
     * @return Account
     */
    private function getAccount(): Account
    {
        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->findItemBy(
            Account::class,
            ['name' => 'Salary card']
        );
    }

    /**
     * @return Account
     */
    private function getEmptyAccount(): Account
    {
        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->findItemBy(
            Account::class,
            ['name' => 'Empty account']
        );
    }
}
