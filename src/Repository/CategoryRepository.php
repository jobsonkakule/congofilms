<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findForFooter(): array
    {
        $categories = $this->createQueryBuilder('c')
            ->select('c.title', 'COUNT(p.id) as postsNb')
            ->join('c.posts', 'p')
            ->orderBy('postsNb', 'DESC')
            ->groupBy('p.category')
            ->setMaxResults(8)
            ->getQuery()
            ->getResult()
            ;
        return $categories;
    }

    public function findAll()
    {
        $categories = $this->createQueryBuilder('c')
            ->getQuery()
            ->getResult()
        ;
        return $categories;
    }

    public function findAllAdmin()
    {
        return $this->createQueryBuilder('c')
            ->getQuery()
            ->getResult()
        ;
    }
}
