<?php

namespace App\API\Repository;

use App\API\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Users>
 */
class UsersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    public function findByPseudo(string $pseudo): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.pseudo LIKE :pseudo')
            ->setParameter('pseudo', '%' . $pseudo . '%')
            ->orderBy('u.pseudo', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    public function findOneBySomeField($value): ?Users
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
