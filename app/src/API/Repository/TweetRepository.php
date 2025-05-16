<?php

namespace App\API\Repository;

use App\API\Entity\Tweet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tweet>
 */
class TweetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tweet::class);
    }

    public function findByContentContaining(string $searchString): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.contenu LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchString . '%')
            ->orderBy('t.date', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
