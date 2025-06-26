<?php

namespace App\Repository;

use App\Entity\EventLocation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for the EventLocation entity.
 * 
 * This class is responsible for providing methods to query and retrieve EventLocation entities
 * from the database. It extends the base ServiceEntityRepository, which provides
 * common repository functionalities.
 * 
 * Standard Doctrine methods like find(), findAll(), findBy(), and findOneBy() are
 * available through the parent class. Custom query methods specific to the EventLocation
 * entity can be added here.
 * 
 * @extends ServiceEntityRepository<EventLocation>
 * @method EventLocation|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventLocation|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventLocation[]    findAll()
 * @method EventLocation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventLocationRepository extends ServiceEntityRepository
{
    /**
     * EventLocationRepository constructor.
     *
     * Initializes the repository with the EventLocation entity class and the Doctrine ManagerRegistry.
     *
     * @param ManagerRegistry $registry The Doctrine ManagerRegistry instance.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventLocation::class);
    }
}