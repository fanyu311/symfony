<?php

namespace App\Repository;

use App\Entity\Article;
use App\Search\SearchArticle;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
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
    public function __construct(
        ManagerRegistry $registry,
        private PaginatorInterface $paginator
    ) {
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

    /**
     * function for filter article in db
     *
     * @param SearchArticle $search object of the client search
     * @return PaginationInterface of article
     */
    public function findSearch(SearchArticle $search): PaginationInterface
    {
        $query = $this->createQueryBuilder('a')
            ->select('a', 'u', 'c', 'i', 'co')
            ->innerJoin('a.user', 'u')
            ->leftJoin('a.categories', 'c')
            ->leftJoin('a.images', 'i')
            ->leftJoin('a.commentaires', 'co')
            ->andWhere('a.enable = true');

        // si le title n'est pas vide -> filter les titles
        if (!empty($search->getTitle())) {
            $query->andWhere('a.title LIKE :title')
                // dynamiquemant le title -> %title%
                ->setParameter('title', "%{$search->getTitle()}%");
        }

        if (!empty($search->getTags())) {
            // in->filter title de categories -> comme boucle for en sql->passe un tableau 
            $query->andWhere('c.id IN (:tags)')
                ->setParameter('tags', $search->getTags());
        }

        if (!empty($search->getAuthors())) {
            $query->andWhere('u.id IN (:users)')
                ->setParameter('users', $search->getAuthors());
        }

        $query->getQuery();
        return $this->paginator->paginate(
            // passe tout les bdd
            $query,
            // numero le page -> 1页
            // 1,
            // recuperer dans le page -> 在searcharticle->创建getpage
            $search->getPage(),
            // totalement 6 produit-> 6个；产品
            6
        );
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
