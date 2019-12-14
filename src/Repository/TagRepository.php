<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function findAll()
    {
        return $this->createQueryBuilder('t')
            ->select('t.name')
            ->getQuery()
            ->getResult();
    }

    public function adminFindAll()
    {
        $em = $this->getEntityManager();
        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata(Tag::class, 't');
        return $em->createNativeQuery('
            SELECT * FROM tag 
            LEFT JOIN tag_post ON tag_post.tag_id = tag.id 
            WHERE tag_post.tag_id IS null
        ', $rsm)->getResult();
    }
}
