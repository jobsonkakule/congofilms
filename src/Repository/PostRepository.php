<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\Picture;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
 use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Post::class);
        $this->paginator = $paginator;
    }

    public function findForSidebar(): array
    {
        $posts =  $this->findVisibleQuery()
            ->addOrderBy('p.score', 'DESC')
            ->addOrderBy('p.created_at', 'DESC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult()
            ;
        $this->hydratePicture($posts);
        return $posts;
    }

    public function findLatest(int $page, ?string $key = null, $tags = [], $limit = 11, $exclude = null): PaginationInterface
    {
        $query = $this->findVisibleQuery()
            ->addSelect('c.id', 'c.title', 'a.id AS authorId', 'a.username', 'a.pseudo', 'COUNT(com.id) AS commentsNb')
            ->join('p.author', 'a')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.comments', 'com');
        if ($key) {

            $searchTerms = $this->extractSearchTerms($key);
        
            $queryBuilder = $this->createQueryBuilder('p')
                ->addSelect('c.id', 'c.title', 'a.id AS authorId', 'a.username', 'a.pseudo', 'COUNT(com.id) AS commentsNb')
                ->join('p.author', 'a')
                ->leftJoin('p.category', 'c')
                ->leftJoin('p.comments', 'com');
        
            foreach ($searchTerms as $key => $term) {
                $queryBuilder
                    ->orWhere('p.title LIKE :t_'.$key)
                    ->setParameter('t_'.$key, '%'.$term.'%')
                ;
            }
            
            $query = $queryBuilder
                ->andWhere('p.online = 1')
                ->andWhere('p.created_at <= :now')
                ->setParameter('now', new \DateTime())
                ->addOrderBy('p.score', 'DESC')
                ->addOrderBy('p.created_at', 'DESC')
                ->groupBy('p.id');

        } elseif ($tags) {
            $query = $query
                ->leftJoin('p.tags', 't')
                ->andWhere('t.name IN (:tags)')
                ->addOrderBy('p.score', 'DESC')
                ->addOrderBy('p.created_at', 'DESC')
                ->setParameter('tags', $tags)
                ->groupBy('p.id');
        }
        else {
            $query = $query
                ->addOrderBy('p.score', 'DESC')
                ->addOrderBy('p.created_at', 'DESC')
                ->groupBy('p.id');
        }
        if ($exclude) {
            $query = $query
                ->andWhere('p.id != ' . $exclude)
                ->addOrderBy('p.score', 'DESC')
                ->addOrderBy('p.created_at', 'DESC')
                ->groupBy('p.id');
        }
        $posts = $this->paginator->paginate(
            $query->getQuery(),
            $page,
            $limit
        );
        $hydratePosts = [];
        foreach ($posts as $p) {
            $hydratePosts[] = $p[0];
        }
        $this->hydratePicture($hydratePosts);
        return $posts;
    }

    

    public function filterPosts(SearchData $search, int $page, $limit = 11): PaginationInterface
    {
        $query = $this->getSearchQuery($search)
            ->groupBy('p.id');
        $posts = $this->paginator->paginate(
            $query->getQuery(),
            $page,
            $limit
        );
        $hydratePosts = [];
        foreach ($posts as $p) {
            $hydratePosts[] = $p[0];
        }
        $this->hydratePicture($hydratePosts);
        return $posts;
    }
    
    public function findTopPosts(int $categoryId = null): array
    {
        $qb = $this->findVisibleQuery();
        if ($categoryId) {
            $qb
                ->andWhere('c.id = :cat')
                ->setParameter('cat', $categoryId);
        }
        $topPosts = $qb
            ->addSelect('c.id', 'c.title', 'a.id AS authorId', 'a.username', 'a.pseudo', 'COUNT(com.id) AS commentsNb')
            ->join('p.author', 'a')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.comments', 'com')
            ->addGroupBy('p.id')
            ->addOrderBy('p.score', 'DESC')
            ->addOrderBy('p.created_at', 'DESC')
            ->setMaxResults(16)
            ->getQuery()
            ->getResult();
        $hydratePosts = [];
        foreach ($topPosts as $p) {
            $hydratePosts[] = $p[0];
        }
        $this->hydratePicture($hydratePosts);
        return $topPosts;
    }

    public function findLastPosts(): array
    {
        $posts = $this->findVisibleQuery()
            ->addSelect('c.id', 'c.title', 'a.username', 'a.pseudo', 'COUNT(com.id) AS commentsNb')
            ->join('p.author', 'a')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.comments', 'com')
            ->groupBy('p.id')
            ->addOrderBy('p.created_at', 'DESC')
            ->addOrderBy('p.score', 'DESC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();
        $hydratePosts = [];
        foreach ($posts as $p) {
            $hydratePosts[] = $p[0];
        }
        $this->hydratePicture($hydratePosts);
        return $posts;
    }

    public function findPostsByField(int $page, $field, $cond, $exclude = null): PaginationInterface
    {
        
        $query = $this->findVisibleQuery();
        if ($exclude) {
            $query = $query->andWhere('p.id != ' . $exclude);
        }
        $query = $query
            ->addSelect('c.id', 'c.title', 'a.username', 'a.pseudo', 'COUNT(com.id) AS commentsNb')
            ->join('p.author', 'a')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.comments', 'com')
            ->andWhere('p.' . $field . ' = :auth')
            ->setParameter('auth', $cond)
            ->groupBy('p.id')
            ->addOrderBy('p.created_at', 'DESC');
        
        $posts = $this->paginator->paginate(
            $query->getQuery(),
            $page,
            3
        );
        $hydratePosts = [];
        foreach ($posts as $p) {
            $hydratePosts[] = $p[0];
        }
        $this->hydratePicture($hydratePosts);
        return $posts;
    }

    public function findPopularPosts(): array
    {
        $posts = $this->findVisibleQuery()
            ->addSelect('c.id', 'c.title')
            ->leftJoin('p.category', 'c')
            ->groupBy('p.id')
            ->addOrderBy('p.views', 'DESC')
            ->setMaxResults(8)
            ->getQuery()
            ->getResult();
        $hydratePosts = [];
        foreach ($posts as $p) {
            $hydratePosts[] = $p[0];
        }
        $this->hydratePicture($hydratePosts);
        return $posts;
    }
    
    /**
     * paginateAllVisible
     *
     * @param  mixed $search
     * @param  mixed $page
     *
     * @return PaginationInterface
     */
    public function paginatePostForTag(int $page, ?string $tag = null, $key = null): PaginationInterface
    {
        if ($tag) {
            $query = $this->findAdminPostQuery()
                ->where('t.name = :name')
                ->setParameter('name', $tag)
                ->orderBy('p.id', 'DESC');
        } elseif ($key) {

            $searchTerms = $this->extractSearchTerms($key);
        
            $queryBuilder = $this->findAdminPostQuery();
        
            foreach ($searchTerms as $key => $term) {
                $queryBuilder
                    ->orWhere('p.title LIKE :t_'.$key)
                    ->setParameter('t_'.$key, '%'.$term.'%')
                ;
            }
            
            $query = $queryBuilder
                ->addOrderBy('p.id', 'DESC');
        }
        else {
            $query = $this->findAdminPostQuery()
                ->orderBy('p.id', 'DESC');
        }
        $posts = $this->paginator->paginate(
            $query->getQuery(),
            $page,
            12
        );
        $this->hydratePicture($posts);
        return $posts;
    }

    public function findWithCategory(int $categoryId, int $page): PaginationInterface
    {
        $query = $this->findVisibleQuery()
            ->addSelect('c.id', 'c.title', 'a.id AS authorId', 'a.username', 'a.pseudo', 'COUNT(com.id) AS commentsNb')
            ->join('p.author', 'a')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.comments', 'com')
            ->andWhere('p.category  = :cat')
            ->addOrderBy('p.score', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->setParameter('cat', $categoryId)
            ->groupBy('p.id');
        $posts = $this->paginator->paginate(
            $query->getQuery(),
            $page,
            9
        );
        $hydratePosts = [];
        foreach ($posts as $p) {
            $hydratePosts[] = $p[0];
        }
        $this->hydratePicture($hydratePosts);
        return $posts;
    }

    /**
     * findMinMax
     *
     * @param  SearchData $search
     *
     * @return integer[]
     */
    public function findMinMax (SearchData $search): array
    {
        $results = $this->getSearchQuery($search, true)
            ->select('MIN(p.score) as min, MAX(p.score) as max')
            ->getQuery()
            ->getScalarResult();
        return [(int)$results[0]['min'], (int)$results[0]['max']];
        // return [0, 10];
    }

    public function countAll()
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id) AS tot')
            ->getQuery()
            ->getResult();
    }

    private function getSearchQuery (SearchData $search, $ignoreScore = false): QueryBuilder
    {
        $query = $this->findVisibleQuery()
            ->addSelect('c.id', 'c.title', 'a.id AS authorId', 'a.username', 'a.pseudo', 'COUNT(com.id) AS commentsNb')
            ->join('p.author', 'a')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.comments', 'com');
        if (!empty($search->q)) {
            $searchTerms = $this->extractSearchTerms($search->q);
        
            $queryBuilder = $this->createQueryBuilder('p')
                ->addSelect('c.id', 'c.title', 'a.id AS authorId', 'a.username', 'a.pseudo', 'COUNT(com.id) AS commentsNb')
                ->join('p.author', 'a')
                ->leftJoin('p.category', 'c')
                ->leftJoin('p.comments', 'com');
        
            foreach ($searchTerms as $key => $term) {
                $queryBuilder
                    ->orWhere('p.title LIKE :t_'.$key)
                    ->setParameter('t_'.$key, '%'.$term.'%')
                ;
            }
            
            $query = $queryBuilder
                ->andWhere('p.online = 1')
                ->andWhere('p.created_at <= :now')
                ->setParameter('now', new \DateTime());

        } 
        if (!empty($search->min && $ignoreScore === false)) {
            $query = $query
                ->andWhere('p.score >= :min')
                ->setParameter('min', $search->min);
        }
        if (!empty($search->max && $ignoreScore === false)) {
            $query = $query
                ->andWhere('p.score <= :max')
                ->setParameter('max', $search->max);
        }
        if(!empty($search->online)) {
            $query = $query
                ->andWhere('p.online = 1');
        }
        if (!empty($search->category)) {
            $query = $query
                ->andWhere('p.category = :cat')
                ->setParameter('cat', $search->category);
        }
        return $query;

    }

    /**
     * findVisibleQuery
     *
     * @return QueryBuilder
     */
    private function findVisibleQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.online = 1')
            ->andWhere('p.created_at <= :now')
            ->setParameter('now', new \DateTime())

            // ->where('p.sold = false')
            ;
    }

    private function findAdminPostQuery(): QueryBuilder {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.tags', 't')
            ->select('p', 't')
            ;
    }

    private function hydratePicture($posts)
    {
        if (method_exists($posts, 'getItems')) {
            $posts = $posts->getItems();
        }
        $pictures = $this->getEntityManager()->getRepository(Picture::class)->findForPosts($posts);
        foreach($posts as $post) {
            /** @var Post $post */
            if ($pictures->containsKey($post->getId())) {
                $post->setPicture($pictures->get($post->getId()));
            }
        }
    }

    private function extractSearchTerms(string $searchQuery): array
    {
        $searchQuery = trim(preg_replace('/[[:space:]]+/', ' ', $searchQuery));
        $terms = array_unique(explode(' ', $searchQuery));

        // ignore the search terms that are too short
        return array_filter($terms, function ($term) {
            return 2 <= mb_strlen($term);
        });
    }
}
