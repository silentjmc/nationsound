<?php
namespace App\Tests\Fixtures;

use App\Entity\EventType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TestEventTypeFixtures extends Fixture
{
    public const TYPE_CONCERT_REF = 'test-event-type-concert';
    public const TYPE_SPECTACLE_REF = 'test-event-type-spectacle';

    public function load(ObjectManager $manager): void
    {
        $concert = new EventType();
        $concert->setNameType('Concert Test');
        // userModificationEventType et dateModificationEventType gérés par EntityListener
        // ou rendre ces champs nullables / définir explicitement si EntityListener ne les gère pas en test.
        $manager->persist($concert);
        $this->addReference(self::TYPE_CONCERT_REF, $concert);

        $spectacle = new EventType();
        $spectacle->setNameType('Spectacle Test');
        $manager->persist($spectacle);
        $this->addReference(self::TYPE_SPECTACLE_REF, $spectacle);

        $manager->flush(); // Peut être flushé ici car c'est une fixture de base sans dépendances complexes
    }
}