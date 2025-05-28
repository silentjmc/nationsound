<?php

namespace App\DataFixtures;

use App\Entity\PartnerType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class PartnerTypeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $types = [
            [
                'type' => 'Institutions',
                'dateModification' => '2024-11-28 17:10:03',
                'userModification' => 'Maël Lemaitre'
            ],
            [
                'type' => 'Entreprises',
                'dateModification' => '2024-11-28 17:10:08',
                'userModification' => 'Maël Lemaitre'
            ],
            [
                'type' => 'Médias',
                'dateModification' => '2024-11-28 17:10:13',
                'userModification' => 'Maël Lemaitre'
            ],
        ];

        foreach ($types as $typeData) {
            $partnerType = new PartnerType();
            $partnerType->setTitlePartnerType($typeData['type']);
            $partnerType->setDateModificationPartnerType(new \DateTime($typeData['dateModification']));
            $partnerType->setUserModificationPartnerType($typeData['userModification']);
            
            $manager->persist($partnerType);
        }

        $manager->flush();
    }
/*
    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }*/
}
