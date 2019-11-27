<?php

namespace App\Repository;

use App\Entity\Picture;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;
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

    public function findLatest(): array
    {
        $posts = $this->findVisibleQuery()
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();
        $this->hydratePicture($posts);
        return $posts;
        
    }

    public function findAdminPostQuery(): QueryBuilder {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.tags', 't')
            ->select('p', 't')
            ;
    }
    
    /**
     * paginateAllVisible
     *
     * @param  mixed $search
     * @param  mixed $page
     *
     * @return PaginationInterface
     */
    public function paginateAllVisible(int $page, ?string $tag = null): PaginationInterface
    {
        // $query = $this->findAdminPostQuery();
        if ($tag) {
            $query = $this->findAdminPostQuery()
                ->where('t.name = :name')
                ->setParameter('name', $tag);
        } else {
            $query = $this->findAdminPostQuery();
            // $query = $this->findVisibleQuery();
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

            // ->where('p.sold = false')
            ;
    }

    private function hydratePicture($posts)
    {
        if (method_exists($posts, 'getItems')) {
            $posts = $posts->getItems();
        }
        $pictures = $this->getEntityManager()->getRepository(Picture::class)->findForPosts($posts);
        foreach($posts as $post) {
            /** @var $post Post */
            if ($pictures->containsKey($post->getId())) {
                $post->setPicture($pictures->get($post->getId()));
            }
        }
    }
}
