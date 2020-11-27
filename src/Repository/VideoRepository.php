<?php

namespace App\Repository;

use App\Entity\Video;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class VideoRepository extends ServiceEntityRepository
{
    /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Video::class);
        $this->paginator = $paginator;
    }

    public function findVideos(): array
    {
        return $this->createQueryBuilder('v')
            ->getQuery()
            ->getResult();
    }

    /**
     * paginateAllVisible
     *
     * @param  mixed $page
     *
     * @return PaginationInterface
     */
    public function findAdmin(int $page): PaginationInterface
    {
        $query = $this->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC');
        $videos = $this->paginator->paginate(
            $query->getQuery(),
            $page,
            12
        );
        return $videos;
    }

    public function countAll()
    {
        return $this->createQueryBuilder('v')
            ->select('COUNT(v.id) AS tot')
            ->getQuery()
            ->getResult();
    }
    
}
