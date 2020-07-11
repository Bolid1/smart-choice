<?php

declare(strict_types=1);

namespace App\Test;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\Category;
use App\Entity\Company;
use App\Entity\Transaction;
use App\Entity\User;
use RuntimeException;

class ApiTestCase extends \ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase
{
    protected const COMPANY_NAME = 'Richards family';
    protected const ANOTHER_COMPANY_NAME = 'Corporation LTD';

    /**
     * @return \ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public static function createCompanyAdminClient(): Client
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/v0/auth/json',
            [
                'json' => [
                    'email' => 'admin@doctrine.fixture',
                    'password' => 'password',
                ],
            ],
        );

        return $client;
    }

    /**
     * @return \ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public static function createAuthenticatedClient(): Client
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/v0/auth/json',
            [
                'json' => [
                    'email' => 'user@doctrine.fixture',
                    'password' => 'password',
                ],
            ],
        );

        return $client;
    }

    /**
     * @return \ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public static function createAnotherAdminClient(): Client
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/v0/auth/json',
            [
                'json' => [
                    'email' => 'another.admin@doctrine.fixture',
                    'password' => 'password',
                ],
            ],
        );

        return $client;
    }

    /**
     * @param array $schema
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public static function assertResponseIsSuccessfulJsonSchema(array $schema): void
    {
        static::assertResponseIsSuccessful();
        static::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        static::assertJsonContains($schema);
    }

    /**
     * @param array $schema
     * @param string $class
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public static function assertResponseIsSuccessfulItemJsonSchema(array $schema, string $class): void
    {
        static::assertResponseIsSuccessfulJsonSchema($schema);
        static::assertMatchesResourceItemJsonSchema($class, 'view');
    }

    /**
     * @param array $schema
     * @param string $class
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public static function assertResponseIsSuccessfulCollectionJsonSchema(array $schema, string $class): void
    {
        static::assertResponseIsSuccessfulJsonSchema($schema);
        static::assertMatchesResourceCollectionJsonSchema($class, 'view');
    }

    public static function assertResponseIsForbidden(): void
    {
        static::assertResponseStatusCodeSame(403);
        static::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
    }

    /**
     * @param array|null $schema
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public static function assertResponseIsInvalidParams(?array $schema = null): void
    {
        static::assertResponseStatusCodeSame(400);
        static::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        if (null !== $schema) {
            static::assertJsonContains($schema);
        }
    }

    /**
     * Finds the IRI of a resource item matching the resource class and the specified criteria.
     *
     * @param string $resourceClass
     * @param array $criteria
     *
     * @return object|null
     */
    protected function findItemBy(string $resourceClass, array $criteria): ?object
    {
        if (null === static::$container) {
            throw new RuntimeException(\sprintf('The container is not available. You must call "bootKernel()" or "createClient()" before calling "%s".', __METHOD__));
        }

        /* @noinspection MissingService */
        if (
            (
                !static::$container->has('doctrine') ||
                null === $objectManager = static::$container->get('doctrine')->getManagerForClass($resourceClass)
            ) &&
            (
                !static::$container->has('doctrine_mongodb') ||
                null === $objectManager = static::$container->get('doctrine_mongodb')->getManagerForClass($resourceClass)
            )
        ) {
            throw new RuntimeException(\sprintf('"%s" only supports classes managed by Doctrine ORM or Doctrine MongoDB ODM. Override this method to implement your own retrieval logic if you don\'t use those libraries.', __METHOD__));
        }

        return $objectManager->getRepository($resourceClass)->findOneBy($criteria);
    }

    protected function getIriFromItem(object $item): ?string
    {
        if (null === static::$container) {
            throw new RuntimeException(\sprintf('The container is not available. You must call "bootKernel()" or "createClient()" before calling "%s".', __METHOD__));
        }

        return static::$container->get('api_platform.iri_converter')->getIriFromItem($item);
    }

    protected function findUserByEmail(string $email): ?User
    {
        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->findItemBy(User::class, \compact('email'));
    }

    protected function findCompanyIriBy(string $name): ?string
    {
        return $this->findIriBy(Company::class, \compact('name'));
    }

    protected function findCompanyIdBy(string $name): ?string
    {
        /** @var Company|null $company */
        $company = $this->findItemBy(Company::class, \compact('name'));

        return $company ? (string)$company->getId() : null;
    }

    protected function findTransactionByCompany(string $name): ?Transaction
    {
        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->findItemBy(Transaction::class, ['company' => $this->findCompanyIdBy($name)]);
    }

    protected function findTransactionIriByCompany(string $name): ?string
    {
        return $this->getIriFromItem($this->findTransactionByCompany($name));
    }

    protected function findCategoryByCompany(string $name): ?Category
    {
        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->findItemBy(Category::class, ['company' => $this->findCompanyIdBy($name)]);
    }

    protected function findCategoryIriByCompany(string $name): ?string
    {
        return $this->getIriFromItem($this->findCategoryByCompany($name));
    }

    protected function findByCompany(string $className, string $name = self::COMPANY_NAME): ?object
    {
        return $this->findItemBy($className, ['company' => $this->findCompanyIdBy($name)]);
    }

    protected function findIriByCompany(string $className, string $name = self::COMPANY_NAME): ?string
    {
        return $this->getIriFromItem($this->findByCompany($className, $name));
    }
}
