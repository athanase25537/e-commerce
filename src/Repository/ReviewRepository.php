<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function findOneByProductAndUser(int $productId, int $userId): ?Review
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.product = :product')
            ->andWhere('r.user = :user')
            ->setParameter('product', $productId)
            ->setParameter('user', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
