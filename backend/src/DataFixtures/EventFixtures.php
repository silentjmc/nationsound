<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Artist;
use App\Entity\EventDate;
use App\Entity\EventLocation;
use App\Entity\EventType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class EventFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $events = [
            [
                'heureDebut' => '18:00:00',
                'heureFin' => '19:00:00',
                'dateModification' => '2024-11-28 17:45:48',
                'userModification' => 'Maël Lemaitre',
                'publish' => true,
                'typeId' => 1,
                'artistId' => 1,
                'dateId' => 1,
                'eventLocationId' => 6
            ],
        ];

        foreach ($events as $eventData) {
            $event = new Event();
            $event->setHeureDebut(new \DateTime($eventData['heureDebut']));
            $event->setHeureFin(new \DateTime($eventData['heureFin']));
            $event->setDateModificationEvent(new \DateTime($eventData['dateModification']));
            $event->setUserModificationEvent($eventData['userModification']);
            $event->setPublishEvent($eventData['publish']);
            
            // Récupérer le type d'événement
            $type = $manager->getRepository(EventType::class)->find($eventData['typeId']);
            if ($type) {
                $event->setType($type);
            }
            
            // Récupérer l'artiste
            $artist = $manager->getRepository(Artist::class)->find($eventData['artistId']);
            if ($artist) {
                $event->setArtist($artist);
            }
            
            // Récupérer la date
            $date = $manager->getRepository(EventDate::class)->find($eventData['dateId']);
            if ($date) {
                $event->setDate($date);
            }
            
            // Récupérer le lieu
            $location = $manager->getRepository(EventLocation::class)->find($eventData['eventLocationId']);
            if ($location) {
                $event->setEventLocation($location);
            }
            
            $manager->persist($event);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            EventTypeFixtures::class,
            ArtistFixtures::class,
            EventDateFixtures::class,
            EventLocationFixtures::class,
        ];
    }
}
