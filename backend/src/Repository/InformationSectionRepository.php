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

    public function findAllSortedByPosition(): array
    {
        return $this->createQueryBuilder('section')
            ->leftJoin('section.information', 'information')
            ->addSelect('information')
            ->where('information.publishInformation = true')
            ->orderBy('section.positionInformationSection', 'ASC')
            ->addOrderBy('information.positionInformation', 'ASC')
            ->getQuery()
            ->getResult();
    }

}
