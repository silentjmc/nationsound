<?php

namespace App\Repository;

use App\Entity\Faq;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for the Faq entity.
 * This class is responsible for providing methods to query and retrieve Faq entities
 * from the database. It extends the base ServiceEntityRepository, which provides
 * common repository functionalities.
 * 
 * Standard Doctrine methods like find(), findAll(), findBy(), and findOneBy() are
 * available through the parent class. Custom query methods specific to the Faq
 * entity can be added here.
 * 
 * @extends ServiceEntityRepository<Faq>
 * @method Faq|null find($id, $lockMode = null, $lockVersion = null)
 * @method Faq|null findOneBy(array $criteria, array $orderBy = null)
 * @method Faq[]    findAll()
 * @method Faq[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FaqRepository extends ServiceEntityRepository
{
    /**
     * FaqRepository constructor.
     *
     * Initializes the repository with the Faq entity class and the Doctrine ManagerRegistry.
     *
     * @param ManagerRegistry $registry The Doctrine ManagerRegistry instance.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Faq::class);
    }

    /**
     * Finds all published FAQ (Frequently Asked Questions) items, ordered by their position.
     *
     * This method retrieves only FAQ items that are marked as published (`publishFaq = true`).
     * The results are then sorted by the `positionFaq` field in ascending order.
     * This is typically used to display FAQs to users in a predefined sequence.
     *
     * @return Faq[] An array of Faq objects, sorted by position.
     *               Returns an empty array if no published FAQs are found.
     */
    public function findAllSortedByPosition(): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.publishFaq = true')
            ->orderBy('f.positionFaq', 'ASC')
            ->getQuery()
            ->getResult();
    }
}