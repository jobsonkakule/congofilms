<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, User::class);
        $this->paginator = $paginator;
    }

    public function countAll()
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id) AS tot')
            ->getQuery()
            ->getResult();
    }
    
    public function findUsers(): array
    {
        $query = $this->createQueryBuilder('u')
            ->getQuery()
            ->getResult();
        return $query;
    }

    public function adminFindAll(int $page, int $limit = 12): PaginationInterface
    {
        $query = $this->createQueryBuilder('u')
            ->getQuery();
        return $this->paginator->paginate(
            $query,
            $page,
            $limit
        );
    }
    
}
