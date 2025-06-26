<?php

namespace App\Repository;

use App\Entity\PartnerType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for the PartnerType entity.
 * 
 * This class is responsible for providing methods to query and retrieve PartnerType entities
 * from the database. It extends the base ServiceEntityRepository, which provides
 * common repository functionalities.
 * 
 * Standard Doctrine methods like find(), findAll(), findBy(), and findOneBy() are
 * available through the parent class. Custom query methods specific to the PartnerType
 * entity can be added here.
 * 
 * @extends ServiceEntityRepository<PartnerType>
 * @method PartnerType|null find($id, $lockMode = null, $lockVersion = null)
 * @method PartnerType|null findOneBy(array $criteria, array $orderBy = null)
 * @method PartnerType[]    findAll()
 * @method PartnerType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PartnerTypeRepository extends ServiceEntityRepository
{
    /**
     * PartnerTypeRepository constructor.
     *
     * Initializes the repository with the PartnerType entity class and the Doctrine ManagerRegistry.
     *
     * @param ManagerRegistry $registry The Doctrine ManagerRegistry instance.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PartnerType::class);
    }
}