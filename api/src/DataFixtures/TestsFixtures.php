<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class TestsFixtures extends Fixture
{
    public const ADMIN_EMAIL = 'admin@doctrine.fixture';
    public const ADMIN_PASSWORD = 'password';
    public const USER_EMAIL = 'user@doctrine.fixture';
    public const USER_PASSWORD = 'password';
    public const COMPANY_NAME = 'Richards family';
    public const SECOND_ADMIN_EMAIL = 'second.admin@doctrine.fixture';
    public const SECOND_ADMIN_PASSWORD = 'password';

    public const ANOTHER_ADMIN_EMAIL = 'another.admin@doctrine.fixture';
    public const ANOTHER_ADMIN_PASSWORD = 'password';
    public const ANOTHER_COMPANY_NAME = 'Corporation LTD';

    private UserPasswordEncoderInterface $passwordEncoder;

    /**
     * UserFixtures constructor.
     *
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager): void
    {
        $admin = (new User())->setEmail(static::ADMIN_EMAIL);
        $admin->setPassword($this->passwordEncoder->encodePassword($admin, static::ADMIN_PASSWORD));
        $manager->persist($admin);

        $user = (new User())->setEmail(static::USER_EMAIL);
        $user->setPassword($this->passwordEncoder->encodePassword($user, static::USER_PASSWORD));
        $manager->persist($user);

        $secondAdmin = (new User())->setEmail(static::SECOND_ADMIN_EMAIL);
        $secondAdmin->setPassword($this->passwordEncoder->encodePassword($secondAdmin, static::SECOND_ADMIN_PASSWORD));
        $manager->persist($secondAdmin);

        $company = (new Company())->setName(static::COMPANY_NAME);
        $company->addUser($admin)->setAdmin(true);
        $company->addUser($user);
        $company->addUser($secondAdmin)->setAdmin(true);
        $manager->persist($company);

        $anotherUser = (new User())->setEmail(static::ANOTHER_ADMIN_EMAIL);
        $anotherUser->setPassword($this->passwordEncoder->encodePassword($anotherUser, static::ANOTHER_ADMIN_PASSWORD));
        $manager->persist($anotherUser);

        $anotherCompany = (new Company())->setName(static::ANOTHER_COMPANY_NAME);
        $anotherCompany->addUser($anotherUser)->setAdmin(true);
        $manager->persist($anotherCompany);

        $manager->flush();
    }
}
