<?php

declare(strict_types=1);

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\Right;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Overrides default behavior of user changing.
 */
class RightDataPersister implements DataPersisterInterface
{
    private EntityManagerInterface $manager;

    /**
     * RightDataPersister constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data): bool
    {
        return $data instanceof Right;
    }

    /**
     * If user creates right, he should has access to right as admin.
     *
     * @param Right $right
     *
     * @return Right
     */
    public function persist($right): Right
    {
        if (!$this->manager->contains($right)) {
            $this->manager->persist($right);
        }

        $this->manager->flush();
        $this->manager->refresh($right);

        return $right;
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
