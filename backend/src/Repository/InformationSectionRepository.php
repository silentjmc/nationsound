<?php

namespace App\Repository;

use App\Entity\InformationSection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for the InformationSection entity.
 * * This class is responsible for providing methods to query and retrieve InformationSection entities
 * from the database. It extends the base ServiceEntityRepository, which provides
 * common repository functionalities.
 * 
 * Standard Doctrine methods like find(), findAll(), findBy(), and findOneBy() are
 * available through the parent class. Custom query methods specific to the InformationSection
 * entity can be added here.
 * 
 * @extends ServiceEntityRepository<InformationSection>
 * @method InformationSection|null find($id, $lockMode = null, $lockVersion = null)
 * @method InformationSection|null findOneBy(array $criteria, array $orderBy = null)
 * @method InformationSection[]    findAll()
 * @method InformationSection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null) 
 */
class InformationSectionRepository extends ServiceEntityRepository
{
    /**
     * InformationSectionRepository constructor.
     *
     * Initializes the repository with the InformationSection entity class and the Doctrine ManagerRegistry.
     *
     * @param ManagerRegistry $registry The Doctrine ManagerRegistry instance.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InformationSection::class);
    }

    /**
     * Finds all InformationSection entities, along with their associated published Information items,
     * sorted by the position of the sections and then by the position of the information items within each section.
     *
     * This method performs a left join to include all InformationSection entities,
     * but only fetches Information items that are marked as published (`publishInformation = true`).
     * The results are ordered first by `InformationSection.positionInformationSection` (ASC)
     * and then by `Information.positionInformation` (ASC).
     *
     * This is useful for displaying information sections and their content in a predefined order.
     *
     * @return InformationSection[] An array of InformationSection entities, with their published
     *                              Information items eagerly fetched and ordered.
     *                              Returns an empty array if no sections are found.
     */
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