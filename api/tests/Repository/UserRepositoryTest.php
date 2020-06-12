<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserRepositoryTest extends KernelTestCase
{
    private ?EntityManager $entityManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @covers \App\Repository\UserRepository::__construct()
     * @covers \App\Repository\UserRepository::upgradePassword()
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testUpgradePassword(): void
    {
        /** @var UserRepository $repository */
        $repository = $this->entityManager->getRepository(User::class);
        $this->assertInstanceOf(UserRepository::class, $repository);

        $user = $repository->findOneBy(['email' => 'admin@doctrine.fixture']);
        if (null === $user) {
            throw new RuntimeException('You should load fixtures with command "bin/console doctrine:fixtures:load".');
        }

        $repository->upgradePassword($user, $newPassword = 'foo-bar');

        $this->assertEquals($newPassword, $user->getPassword());
    }

    /**
     * @covers \App\Repository\UserRepository::__construct()
     * @covers \App\Repository\UserRepository::upgradePassword()
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testUpgradePasswordThrowsUnsupportedUserException(): void
    {
        /** @var UserRepository $repository */
        $repository = $this->entityManager->getRepository(User::class);
        $this->assertInstanceOf(UserRepository::class, $repository);

        $this->expectException(UnsupportedUserException::class);
        $repository->upgradePassword($this->createMock(UserInterface::class), $newPassword = 'foo-bar');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null; // avoid memory leaks
    }
}
