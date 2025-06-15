<?php

namespace App\Tests\Fixtures;

use App\Entity\EventDate;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TestEventDateFixtures extends Fixture
{
    public const DATE_J1_FESTIVAL_REF = 'test-event-date-j1-festival';
    public const DATE_J2_FESTIVAL_REF = 'test-event-date-j2-festival';
    public const DATE_AUTRE_REF = 'test-event-date-autre';

    public function load(ObjectManager $manager): void
    {
        $eventDate1 = new EventDate();
        $eventDate1->setDate(new \DateTime('2024-07-19')); 
        $manager->persist($eventDate1);
        $this->addReference(self::DATE_J1_FESTIVAL_REF, $eventDate1);

        $eventDate2 = new EventDate();
        $eventDate2->setDate(new \DateTime('2024-07-20')); 
        $manager->persist($eventDate2);
        $this->addReference(self::DATE_J2_FESTIVAL_REF, $eventDate2);

        $eventDateAutre = new EventDate();
        $eventDateAutre->setDate(new \DateTime('2024-10-25'));
        $manager->persist($eventDateAutre);
        $this->addReference(self::DATE_AUTRE_REF, $eventDateAutre);

        $manager->flush();
    }
}