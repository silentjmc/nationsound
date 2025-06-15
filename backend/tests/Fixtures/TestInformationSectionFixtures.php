<?php
namespace App\Tests\Fixtures;

use App\Entity\InformationSection;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TestInformationSectionFixtures extends Fixture
{
    public const SECTION_A_REF = 'test-info-section-a'; // Position 0
    public const SECTION_B_REF = 'test-info-section-b'; // Position 1
    public const SECTION_C_REF = 'test-info-section-c'; // Position 2

    public function load(ObjectManager $manager): void
    {
        $sectionA = new InformationSection();
        $sectionA->setSectionLabel('Label Section A Test');
        $sectionA->setTitleInformationSection('Titre Section A Test');
        $sectionA->setContentInformationSection('Contenu pour la section A de test.');
        $sectionA->setPositionInformationSection(0);
        // userModification and dateModification are managed by EntityListener
        $manager->persist($sectionA);
        $this->addReference(self::SECTION_A_REF, $sectionA);

        $sectionB = new InformationSection();
        $sectionB->setSectionLabel('Label Section B Test');
        $sectionB->setTitleInformationSection('Titre Section B Test');
        $sectionB->setContentInformationSection('Contenu pour la section B de test.');
        $sectionB->setPositionInformationSection(1);
        $manager->persist($sectionB);
        $this->addReference(self::SECTION_B_REF, $sectionB);

        $sectionC = new InformationSection();
        $sectionC->setSectionLabel('Label Section C Test');
        $sectionC->setTitleInformationSection('Titre Section C Test');
        $sectionC->setContentInformationSection('Contenu pour la section C de test.');
        $sectionC->setPositionInformationSection(2);
        $manager->persist($sectionC);
        $this->addReference(self::SECTION_C_REF, $sectionC);

        $manager->flush();
    }
}