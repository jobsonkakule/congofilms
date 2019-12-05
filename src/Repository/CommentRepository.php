<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function deleteComments($commentId)
    {
        return $this->createQueryBuilder('c')
            ->delete(Comment::class, 'com')
            ->where('com.parent_id = ' . $commentId)
            ->getQuery()
            ->getResult();
            ;
    }

    /**
     * @param Post[] $posts
     * @return ArrayCollection
     */
    public function findForPosts(array $posts): ArrayCollection{
        $comments = $this->createQueryBuilder('c')
            ->select('c', 'COUNT(c.id) AS commentsNb')
            ->where('c.post IN (:posts)')
            ->groupBy('c.post')
            ->getQuery()
            ->setParameter('posts', $posts)
            ->getResult();
        return new ArrayCollection($comments);
    }
}

