<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @return Product[]
     */
    public function findForStore(?string $query, ?int $categoryId, ?int $subCategoryId): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.subCategories', 'sc')
            ->leftJoin('sc.category', 'c')
            ->addSelect('sc', 'c');

        if ($query) {
            $qb->andWhere('LOWER(p.name) LIKE :q OR LOWER(p.description) LIKE :q')
                ->setParameter('q', '%' . mb_strtolower($query) . '%');
        }

        if ($categoryId) {
            dd(["here", $categoryId]);
            $qb->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if ($subCategoryId) {
            $qb->andWhere('sc.id = :subCategoryId')
                ->setParameter('subCategoryId', $subCategoryId);
        }

        return $qb
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Product[]
     */
    public function findLatest(int $limit = 8): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
