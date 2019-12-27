<?php

namespace App\Repository;

use App\Entity\Pub;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @method Pub|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pub|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pub[]    findAll()
 * @method Pub[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PubRepository extends ServiceEntityRepository
{
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Pub::class);
        $this->paginator = $paginator;
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

    public function countAll()
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id) AS tot')
            ->getQuery()
            ->getResult();
    }
}
