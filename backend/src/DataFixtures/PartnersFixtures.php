<?php

namespace App\DataFixtures;

use App\Entity\Partners;
use App\Entity\PartnerType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class PartnersFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $partners = [
            [
                'name' => 'Weezevent',
                'image' => 'WEEZEVENTc64ae704d630e7b2bde1ab4ce6cde3c4e8229966.webp',
                'url' => 'https://weezevent.com/fr/',
                'dateModification' => '2024-11-28 17:18:45',
                'userModification' => 'Maël Lemaitre',
                'publish' => true,
                'typeId' => 2
            ],
            [
                'name' => 'Sacem',
                'image' => 'SACEM6b7c968e8a9cd625315b10480cc72aba7196d93b.webp',
                'url' => 'https://www.sacem.fr',
                'dateModification' => '2024-11-28 17:19:45',
                'userModification' => 'Maël Lemaitre',
                'publish' => true,
                'typeId' => 1
            ],
            [
                'name' => 'Europe 2',
                'image' => 'Europe2b7579b956d2fba6a49be25f5da5c5f259c8d7670.webp',
                'url' => 'https://www.europe2.fr/',
                'dateModification' => '2024-11-28 17:21:21',
                'userModification' => 'Maël Lemaitre',
                'publish' => false,
                'typeId' => 3
            ],
        ];

        foreach ($partners as $partnerData) {
            $partner = new Partners();
            $partner->setName($partnerData['name']);
            $partner->setImage($partnerData['image']);
            $partner->setUrl($partnerData['url']);
            $partner->setDateModification(new \DateTime($partnerData['dateModification']));
            $partner->setUserModification($partnerData['userModification']);
            $partner->setPublish($partnerData['publish']);
            
            // Récupérer le type de partenaire correspondant
            $type = $manager->getRepository(PartnerType::class)->find($partnerData['typeId']);
            if ($type) {
                $partner->setType($type);
            }
            
            $manager->persist($partner);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            PartnerTypeFixtures::class,
        ];
    }
}
