<?php

declare(strict_types=1);

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\Company;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Overrides default behavior of user changing.
 */
class CompanyDataPersister implements DataPersisterInterface
{
    private EntityManagerInterface $manager;
    private Security $security;

    /**
     * CompanyDataPersister constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $manager
     * @param \Symfony\Component\Security\Core\Security $security
     */
    public function __construct(EntityManagerInterface $manager, Security $security)
    {
        $this->manager = $manager;
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data): bool
    {
        return $data instanceof Company;
    }

    /**
     * If user creates company, he should has access to company as admin.
     *
     * @param Company $company
     *
     * @return Company
     */
    public function persist($company): Company
    {
        $user = $this->security->getUser();
        if ($user instanceof User && !$company->getRights()->count()) {
            $company->addUser($user)->setAdmin(true);
        }

        if (!$this->manager->contains($company)) {
            $this->manager->persist($company);
        }

        $this->manager->flush();
        $this->manager->refresh($company);

        return $company;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($data): void
    {
        $this->manager->remove($data);
        $this->manager->flush();
    }
}
