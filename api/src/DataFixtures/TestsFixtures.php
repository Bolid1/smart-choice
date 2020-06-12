<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Invitation;
use App\Entity\User;
use App\Security\InvitationSecretEncoder;
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
    public const ANOTHER_ADMIN_INVITATION_SECRET = 'Super secret';

    public const ANOTHER_ADMIN_EMAIL = 'another.admin@doctrine.fixture';
    public const ANOTHER_ADMIN_PASSWORD = 'password';
    public const ANOTHER_COMPANY_NAME = 'Corporation LTD';
    public const ADMIN_INVITATION_SECRET = 'Another secret';

    private UserPasswordEncoderInterface $passwordEncoder;
    private InvitationSecretEncoder $invitationEncoder;

    /**
     * TestsFixtures constructor.
     *
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
     * @param \App\Security\InvitationSecretEncoder $invitationEncoder
     */
    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        InvitationSecretEncoder $invitationEncoder
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->invitationEncoder = $invitationEncoder;
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

        $invitation = (new Invitation())->setFromUser($user)->setToCompany($company);
        $invitation->setSecret(
            $this->invitationEncoder->encodeSecret($invitation, static::ANOTHER_ADMIN_INVITATION_SECRET)
        );
        $invitation->setEmail(static::ANOTHER_ADMIN_EMAIL);
        $manager->persist($invitation);

        $anotherUser = (new User())->setEmail(static::ANOTHER_ADMIN_EMAIL);
        $anotherUser->setPassword($this->passwordEncoder->encodePassword($anotherUser, static::ANOTHER_ADMIN_PASSWORD));
        $manager->persist($anotherUser);

        $anotherCompany = (new Company())->setName(static::ANOTHER_COMPANY_NAME);
        $anotherCompany->addUser($anotherUser)->setAdmin(true);
        $manager->persist($anotherCompany);

        $anotherInvitation = (new Invitation())->setFromUser($anotherUser)->setToCompany($anotherCompany);
        $anotherInvitation->setSecret(
            $this->invitationEncoder->encodeSecret($anotherInvitation, static::ADMIN_INVITATION_SECRET)
        );
        $anotherInvitation->setEmail(static::ADMIN_EMAIL);
        $manager->persist($anotherInvitation);

        $manager->flush();
    }
}
