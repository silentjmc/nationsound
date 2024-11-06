<?php

namespace App\Repository;

use App\Entity\InformationSection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InformationSection>
 */
class InformationSectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InformationSection::class);
    }

    //    /**
    //     * @return InformationSection[] Returns an array of InformationSection objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?InformationSection
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    //      /**
    //     * @return InformationSection[] Returns an array of InformationSection objects
    //     */
    public function findAllSortedByPosition(): array
    {
        return $this->createQueryBuilder('section')
            ->leftJoin('section.information', 'information')
            ->addSelect('information')
            ->where('information.publish = true')
            ->orderBy('section.position', 'ASC')
            ->addOrderBy('information.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

}
