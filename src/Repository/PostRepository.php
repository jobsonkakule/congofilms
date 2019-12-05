<?php

namespace App\Repository;

use App\Entity\Comment;
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
        $posts =  $this->createQueryBuilder('p')
            ->where('p.created_at <= :now')
            ->addOrderBy('p.created_at', 'DESC')
            ->addOrderBy('p.score', 'DESC')
            ->setParameter('now', new \DateTime())
            ->setMaxResults(6)
            ->getQuery()
            ->getResult()
            ;
        $this->hydratePicture($posts);
        return $posts;
    }

    public function findLatest(): array
    {
        $posts = $this->findVisibleQuery()
            ->addSelect('c.id', 'c.title', 'a.username', 'a.pseudo', 'COUNT(com.id) AS commentsNb')
            ->join('p.author', 'a')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.comments', 'com')
            ->addOrderBy('p.score', 'DESC')
            ->addOrderBy('p.created_at', 'DESC')
            ->groupBy('p.id')
            ->getQuery()
            ->getResult();
        $hydratePosts = [];
        foreach ($posts as $p) {
            $hydratePosts[] = $p[0];
        }
        $this->hydratePicture($hydratePosts);
        return $posts;
    }
    public function findTopPosts(): array
    {
        $qb = $this->findVisibleQuery();
        $topPosts = $qb
            ->addSelect('c.id', 'c.title', 'a.username', 'a.pseudo', 'COUNT(com.id) AS commentsNb')
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
            ->setMaxResults(16)
            ->getQuery()
            ->getResult();
        $hydratePosts = [];
        foreach ($posts as $p) {
            $hydratePosts[] = $p[0];
        }
        $this->hydratePicture($hydratePosts);
        return $posts;
    }

    public function findLastestPosts(): array
    {
        $posts = $this->findVisibleQuery()
            ->addOrderBy('p.created_at', 'DESC')
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();
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
    public function paginatePostForTag(int $page, ?string $tag = null): PaginationInterface
    {
        if ($tag) {
            $query = $this->findAdminPostQuery()
                ->where('t.name = :name')
                ->setParameter('name', $tag)
                ->orderBy('p.id', 'DESC');
        } else {
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
    
    
    
    /**
     * findPost
     *
     * @param  mixed $search
     * @param  mixed $page
     *
     * @return PaginationInterface
     */
    public function findPost(int $page, ?string $key = null): PaginationInterface
    {
        if ($key) {
            $query = $this->createQueryBuilder('p')
                ->orWhere('p.title LIKE :key')
                ->orWhere('p.content LIKE :key')
                ->setParameter('key', '%' . $key . '%');
        } else {
            $query = $this->createQueryBuilder('p');
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
            ->andWhere('p.category  = :cat')
            ->setParameter('cat', $categoryId);
        $posts = $this->paginator->paginate(
            $query->getQuery(),
            $page,
            12
        );
        $this->hydratePicture($posts);
        return $posts;
    }
    /**
     * findVisibleQuery
     *
     * @return QueryBuilder
     */
    private function findVisibleQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->where('p.online = 1')

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
}
