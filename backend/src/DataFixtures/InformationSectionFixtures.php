<?php

namespace App\DataFixtures;

use App\Entity\InformationSection;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class InformationSectionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $sections = [
            [
                'section' => 'Festival',
                'title' => 'Découvrez le Festival Nation Sound',
                'description' => 'Plongez dans l\'univers éclectique du festival Nation Sound où cinq scènes distinctes vous offrent une diversité musicale époustouflante : du métal brut au rock enivrant, du rap percutant à la musique du monde envoûtante, et des rythmes électro enivrants. Notre festival est conçu pour satisfaire tous les amateurs de musique, avec des performances de premier plan et des artistes émergents prêts à vous surprendre.',
                'position' => 0,
                'dateModification' => '2024-11-28 17:47:00',
                'userModification' => 'Maël Lemaitre'
            ],
            [
                'section' => 'Scène',
                'title' => 'Les Scènes',
                'description' => 'Cinq scènes distinctes vous offrent une diversité musicale époustouflante : du métal brut au rock enivrant, du rap percutant à la musique du monde envoûtante, et des rythmes électro enivrants.',
                'position' => 1,
                'dateModification' => '2024-11-28 17:47:10',
                'userModification' => 'Maël Lemaitre'
            ],
        ];

        foreach ($sections as $sectionData) {
            $informationSection = new InformationSection();
            $informationSection->setSectionLabel($sectionData['section']);
            $informationSection->setTitleInformationSection($sectionData['title']);
            $informationSection->setContentInformationSection($sectionData['description']);
            $informationSection->setPositionInformationSection($sectionData['position']);
            $informationSection->setDateModificationInformationSection(new \DateTime($sectionData['dateModification']));
            $informationSection->setUserModificationInformationSection($sectionData['userModification']);
            
            $manager->persist($informationSection);
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
