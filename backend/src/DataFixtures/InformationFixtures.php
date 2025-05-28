<?php

namespace App\DataFixtures;

use App\Entity\Information;
use App\Entity\InformationSection;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class InformationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $informations = [
            [
                'titre' => 'Dates et Lieu',
                'description' => '<p>Le festival se déroulera sur trois jours : Vendredi 1er Août au 3 Août 2025.<br>Le festival aura lieu à la base 217 à Brétigny-sur-oise, un espace vaste et accueillant, offrant des scènes de qualité et des installations confortables pour tous les festivaliers.</p>',
                'dateModification' => '2024-11-28 17:48:29',
                'userModification' => 'Maël Lemaitre',
                'publish' => true,
                'position' => 0,
                'typeSectionId' => 1
            ],
            [
                'titre' => 'Scène Electro',
                'description' => '<p>Dansez au rythme des beats électroniques de DJs et artistes électro de renommée.</p>',
                'dateModification' => '2024-11-28 17:48:46',
                'userModification' => 'Maël Lemaitre',
                'publish' => true,
                'position' => 1,
                'typeSectionId' => 2
            ],
        ];

        foreach ($informations as $infoData) {
            $information = new Information();
            $information->setTitleInformation($infoData['titre']);
            $information->setContentInformation($infoData['description']);
            $information->setDateModificationInformation(new \DateTime($infoData['dateModification']));
            $information->setUserModificationInformation($infoData['userModification']);
            $information->setPublishInformation($infoData['publish']);
            $information->setPositionInformation($infoData['position']);
            
            // Récupérer la section d'information correspondante
            $section = $manager->getRepository(InformationSection::class)->find($infoData['typeSectionId']);
            if ($section) {
                $information->setSectionInformation($section);
            }
            
            $manager->persist($information);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            InformationSectionFixtures::class,
        ];
    }
}
