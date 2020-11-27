<?php

namespace App\Repository;

use App\Entity\Photo;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Photo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Photo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Photo[]    findAll()
 * @method Photo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhotoRepository extends ServiceEntityRepository
{
    private $paginator;
    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Photo::class);
        $this->paginator = $paginator;
    }

    public function countAll()
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id) AS tot')
            ->getQuery()
            ->getResult();
    }
    
    public function findPhotos(): array
    {
        $query = $this->createQueryBuilder('u')
            ->setMaxResults(20)
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
