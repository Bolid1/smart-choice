<?php

declare(strict_types=1);

namespace App\Test;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\DataFixtures\TestsFixtures;
use App\Entity\User;
use RuntimeException;

class ApiTestCase extends \ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase
{
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
                    'email' => TestsFixtures::ADMIN_EMAIL,
                    'password' => TestsFixtures::ADMIN_PASSWORD,
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
                    'email' => TestsFixtures::USER_EMAIL,
                    'password' => TestsFixtures::USER_PASSWORD,
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
                    'email' => TestsFixtures::ANOTHER_ADMIN_EMAIL,
                    'password' => TestsFixtures::ANOTHER_ADMIN_PASSWORD,
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
}
