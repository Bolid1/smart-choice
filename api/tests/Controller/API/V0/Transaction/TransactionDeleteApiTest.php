<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\Transaction;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Test\ApiTestCase;

class TransactionDeleteApiTest extends ApiTestCase
{
    /**
     * @covers \App\Security\TransactionVoter::supports()
     * @covers \App\Security\TransactionVoter::voteOnAttribute()
     * @covers \App\DataPersister\TransactionDataPersister::__construct()
     * @covers \App\DataPersister\TransactionDataPersister::supports()
     * @covers \App\DataPersister\TransactionDataPersister::remove()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testDeleteTransaction(): void
    {
        static::createAuthenticatedClient()->request(
            'DELETE',
            $this->getTransactionIri(),
        )
        ;

        static::assertResponseIsSuccessful();
    }

    /**
     * @covers \App\Security\TransactionVoter::supports()
     * @covers \App\Security\TransactionVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testDeleteTransactionAnonymous(): void
    {
        static::createClient()->request(
            'DELETE',
            $this->getTransactionIri(),
        )
        ;

        static::assertResponseIsForbidden();
    }

    /**
     * @return string|null
     */
    private function getTransactionIri(): ?string
    {
        /** @var Account $account */
        $account = $this->findItemBy(
            Account::class,
            ['name' => 'Salary card']
        );

        return $this->findIriBy(
            Transaction::class,
            [
                'account' => $account->getId(),
            ],
        );
    }
}
