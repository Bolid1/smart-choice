<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\TransactionCategory;

use App\Entity\TransactionCategory;
use App\Test\ApiTestCase;

class TransactionCategoryPatchApiTest extends ApiTestCase
{
    /**
     * @covers \App\Security\Voter\TransactionCategoryVoter::supports()
     * @covers \App\Security\Voter\TransactionCategoryVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchTransactionCategory(): void
    {
        $client = static::createAuthenticatedClient();
        $transactionCategory = $this->getTransactionCategoryItem();
        $client->request(
            'PATCH',
            $this->getIriFromItem($transactionCategory),
            [
                'json' => [
                    'amount' => $amount = \round($transactionCategory->amount - 100.1, 2),
                ],
            ],
        )
        ;

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/TransactionCategory',
                '@type' => 'TransactionCategory',
                'amount' => $amount,
            ],
            TransactionCategory::class
        );
    }

    /**
     * @covers \App\Security\Voter\TransactionCategoryVoter::supports()
     * @covers \App\Security\Voter\TransactionCategoryVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchTransactionCategoryAnonymous(): void
    {
        static::createClient()->request(
            'PATCH',
            $this->getTransactionCategoryIri(),
            [
                'json' => [
                    'name' => 'new name',
                ],
            ],
        )
        ;

        static::assertResponseIsForbidden();
    }

    /**
     * @return string|null
     */
    private function getTransactionCategoryIri(): ?string
    {
        return $this->findIriByCompany(TransactionCategory::class);
    }

    /**
     * @return TransactionCategory
     */
    private function getTransactionCategoryItem(): TransactionCategory
    {
        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->findByCompany(TransactionCategory::class);
    }
}
