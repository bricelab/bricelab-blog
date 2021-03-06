<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\Tag;
use App\Utils\Pagination\Paginator;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use function Symfony\Component\String\u;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @throws Exception
     */
    public function findLatest(int $page = 1, Category $category = null, Tag $tag = null): Paginator
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('a')
            ->innerJoin('p.author', 'a')
            ->where('p.publishedAt <= :now')
            ->andWhere('p.status = :status')
            ->setParameter('status', Post::POST_STATUS_PUBLISHED)
            ->orderBy('p.publishedAt', 'DESC')
            ->setParameter('now', new DateTimeImmutable())
        ;

        if (null !== $category) {
            $qb
                ->addSelect('c.name')
                ->leftJoin('p.categories', 'c')
                ->andWhere(':category MEMBER OF p.categories')
                ->setParameter('category', $category);
        }

        if (null !== $tag) {
            $qb
                ->addSelect('t.name')
                ->leftJoin('p.tags', 't')
                ->andWhere(':tag MEMBER OF p.tags')
                ->setParameter('tag', $tag);
        }

        return (new Paginator($qb))->paginate($page);
    }

    /**
     * @throws Exception
     */
    public function findPaginatedListe(int $page = 1, Category $category = null, Tag $tag = null): Paginator
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('a')
            ->innerJoin('p.author', 'a')
            ->orderBy('p.updatedAt', 'DESC')
            ->orderBy('p.publishedAt', 'DESC')
        ;

        if (null !== $category) {
            $qb
                ->addSelect('c.name')
                ->leftJoin('p.categories', 'c')
                ->andWhere(':category MEMBER OF p.categories')
                ->setParameter('category', $category);
        }

        if (null !== $tag) {
            $qb
                ->addSelect('t.name')
                ->leftJoin('p.tags', 't')
                ->andWhere(':tag MEMBER OF p.tags')
                ->setParameter('tag', $tag);
        }

        return (new Paginator($qb))->paginate($page);
    }

    /**
     * @return Post[]
     */
    public function findBySearchQuery(string $query, int $limit = Paginator::PAGE_SIZE): array
    {
        $searchTerms = $this->extractSearchTerms($query);

        if (0 === count($searchTerms)) {
            return [];
        }

        $queryBuilder = $this->createQueryBuilder('p');

        foreach ($searchTerms as $key => $term) {
            $queryBuilder
                ->orWhere('p.title LIKE :t_'.$key)
                ->setParameter('t_'.$key, '%'.$term.'%')
            ;
        }

        return $queryBuilder
            ->orderBy('p.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Transforms the search string into an array of search terms.
     */
    private function extractSearchTerms(string $searchQuery): array
    {
        $searchQuery = u($searchQuery)->replaceMatches('/[[:space:]]+/', ' ')->trim();
        $terms = array_unique($searchQuery->split(' '));

        // ignore the search terms that are too short
        return array_filter($terms, function ($term) {
            return 2 <= $term->length();
        });
    }
}
