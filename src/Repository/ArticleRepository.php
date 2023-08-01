<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function save(Article $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Article $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllWithRelationInfo(): array
    {
        return $this->createQueryBuilder('a')
            ->select('a', 'u', 'c', 'i')
            ->innerJoin('a.user', 'u')
            ->leftJoin('a.categories', 'c')
            ->leftJoin('a.images', 'i')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find the latest article with limit or not and all article or just enable article
     *
     * @param integer|null $limit
     * @param boolean $actif
     * @return array
     */
    public function findLatest(?int $limit = null, bool $actif = true): array
    {
        $query = $this->createQueryBuilder('a')
            ->select('a', 'u', 'c', 'i')
            ->innerJoin('a.user', 'u')
            ->leftJoin('a.categories', 'c')
            ->leftJoin('a.images', 'i');

        if ($actif) {
            $query->where('a.enable = :enable')
                ->setParameter('enable', $actif);
        }

        return $query
            ->orderBy('a.createdAt', 'DESC')
            ->groupBy('a')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findAllEnable(): array
    {
        return $this->createQueryBuilder('a')
            ->select('a', 'u', 'c', 'i')
            ->innerJoin('a.user', 'u')
            ->leftJoin('a.categories', 'c')
            ->leftJoin('a.images', 'i')
            ->where('a.enable = :enable')
            ->setParameter('enable', true)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Article[] Returns an array of Article objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Article
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
