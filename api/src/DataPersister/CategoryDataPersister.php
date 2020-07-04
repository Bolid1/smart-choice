<?php

declare(strict_types=1);

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Overrides default behavior of user changing.
 */
class CategoryDataPersister implements DataPersisterInterface
{
    private EntityManagerInterface $manager;

    /**
     * CategoryDataPersister constructor.
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
        return $data instanceof Category;
    }

    /**
     * If user creates category, he should has access to category as admin.
     *
     * @param Category $category
     *
     * @return Category
     */
    public function persist($category): Category
    {
        if (!$this->manager->contains($category)) {
            $this->manager->persist($category);
        }

        $this->manager->flush();
        $this->manager->refresh($category);

        return $category;
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
