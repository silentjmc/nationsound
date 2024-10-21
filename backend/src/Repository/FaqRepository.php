<?php

namespace App\Repository;

use App\Entity\Faq;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Faq>
 */
class FaqRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Faq::class);
    }

    public function findFaq(): array
    {
        $qb = $this->createQueryBuilder('sponsor')
            // … any other query conditions you need
            ->orderBy('sponsor.position', 'ASC');

        return $qb->getQuery()->execute();
    }

    /**
     * @return Faq[] Returns an array of Faq objects sorted by position
     */
    public function findAllSortedByPosition(): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.publish = true')
            ->orderBy('f.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Faq[] Returns an array of Faq objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Faq
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
