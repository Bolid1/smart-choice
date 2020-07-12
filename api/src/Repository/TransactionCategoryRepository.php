<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TransactionCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TransactionCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransactionCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransactionCategory[]    findAll()
 * @method TransactionCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransactionCategory::class);
    }
}
