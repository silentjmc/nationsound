<?php

namespace App\Repository;

use App\Entity\EntityHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for the EntityHistory entity.
 * 
 * This class is responsible for providing methods to query and retrieve EntityHistory entities
 * from the database. It extends the base ServiceEntityRepository, which provides
 * common repository functionalities.
 * 
 * Standard Doctrine methods like find(), findAll(), findBy(), and findOneBy() are
 * available through the parent class. Custom query methods specific to the EntityHistory
 * entity can be added here.
 * 
 * @extends ServiceEntityRepository<EntityHistory>
 * @method EntityHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntityHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntityHistory[]    findAll()
 * @method EntityHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntityHistoryRepository extends ServiceEntityRepository
{
    /**
     * EntityHistoryRepository constructor.
     *
     * Initializes the repository with the EntityHistory entity class and the Doctrine ManagerRegistry.
     *
     * @param ManagerRegistry $registry The Doctrine ManagerRegistry instance.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntityHistory::class);
    }
}