<?php

namespace App\DataFixtures;

use App\Entity\Artist;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ArtistFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $artists = [
            [
                'name' => 'Afrobeat Vibes',
                'description' => 'Afrobeat Vibes fait vibrer la scène avec des rythmes africains contagieux, des chants puissants et des danses exaltantes qui célèbrent la richesse culturelle de l\'Afrique.',
                'image' => 'Afrobeat_Vibes1e902a883c0cc43c1541b3a1584f10267c9a0652.webp',
                'thumbnail' => 'thumb_Afrobeat_Vibes1e902a883c0cc43c1541b3a1584f10267c9a0652.webp',
                'typeMusic' => 'African Music',
                'dateModification' => '2024-11-28 17:24:50',
                'userModification' => 'Maël Lemaitre'
            ],
        ];

        foreach ($artists as $artistData) {
            $artist = new Artist();
            $artist->setName($artistData['name']);
            $artist->setDescription($artistData['description']);
            $artist->setImage($artistData['image']);
            $artist->setThumbnail($artistData['thumbnail']);
            $artist->setTypeMusic($artistData['typeMusic']);
            $artist->setDateModification(new \DateTime($artistData['dateModification']));
            $artist->setUserModification($artistData['userModification']);
            
            $manager->persist($artist);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
