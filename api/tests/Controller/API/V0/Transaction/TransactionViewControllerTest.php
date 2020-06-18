<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\Transaction;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Test\ApiTestCase;

class TransactionViewControllerTest extends ApiTestCase
{
    /**
     * @covers \App\Security\TransactionVoter::supports()
     * @covers \App\Security\TransactionVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testViewTransaction(): void
    {
        static::createAuthenticatedClient()->request(
            'GET',
            $this->getTransactionIri(),
        );

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Transaction',
                '@type' => 'https://schema.org/MoneyTransfer',
            ],
            Transaction::class
        );
    }

    /**
     * @covers \App\Security\TransactionVoter::supports()
     * @covers \App\Security\TransactionVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testViewTransactionAnonymous(): void
    {
        static::createClient()->request(
            'GET',
            $this->getTransactionIri(),
        );
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
