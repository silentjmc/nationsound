<?php

namespace App\Repository;

use App\Entity\LocationType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for the LocationType entity.
 * 
 * This class is responsible for providing methods to query and retrieve LocationType entities
 * from the database. It extends the base ServiceEntityRepository, which provides
 * common repository functionalities.
 * 
 * Standard Doctrine methods like find(), findAll(), findBy(), and findOneBy() are
 * available through the parent class. Custom query methods specific to the LocationType
 * entity can be added here.
 * 
 * @extends ServiceEntityRepository<LocationType>
 * @method LocationType|null find($id, $lockMode = null, $lockVersion = null)
 * @method LocationType|null findOneBy(array $criteria, array $orderBy = null)
 * @method LocationType[]    findAll()
 * @method LocationType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LocationTypeRepository extends ServiceEntityRepository
{
    /**
     * LocationTypeRepository constructor.
     *
     * Initializes the repository with the LocationType entity class and the Doctrine ManagerRegistry.
     *
     * @param ManagerRegistry $registry The Doctrine ManagerRegistry instance.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocationType::class);
    }
}