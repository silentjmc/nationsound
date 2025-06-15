<?php
namespace App\Tests\Fixtures;

use App\Entity\Information;
use App\Entity\InformationSection;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
// Pas besoin de FixtureGroupInterface ici sauf si vous voulez grouper pour la console

class TestInformationFixtures extends Fixture
{
    public const SECTION_PRATIQUE_REF = 'test-info-section-pratique';
    public const SECTION_ACCES_REF = 'test-info-section-acces';
    public const INFO_HORAIRES_REF = 'test-info-horaires';
    public const INFO_TARIFS_REF = 'test-info-tarifs';
    public const INFO_PARKING_REF = 'test-info-parking';
    public const INFO_TRANSPORTS_UNPUBLISHED_REF = 'test-info-transports-unpublished';

    public function load(ObjectManager $manager): void
    {
        // Section 1: Infos Pratiques (position 0)
        $sectionPratique = new InformationSection();
        $sectionPratique->setSectionLabel('Pratique Test');
        $sectionPratique->setTitleInformationSection('Informations Pratiques de Test');
        $sectionPratique->setContentInformationSection('Tout ce qu\'il faut savoir pour le festival.');
        $sectionPratique->setPositionInformationSection(0);
        // userModification et dateModification gérés par EntityListener
        // (s'assurer qu'ils sont nullables ou gérés en test)
        $manager->persist($sectionPratique);
        $this->addReference(self::SECTION_PRATIQUE_REF, $sectionPratique);

        // Informations pour la section "Pratique"
        $infoHoraires = new Information();
        $infoHoraires->setTitleInformation('Horaires d\'ouverture Test');
        $infoHoraires->setContentInformation('Le festival ouvre ses portes de 10h à 2h.');
        $infoHoraires->setPublishInformation(true);
        $infoHoraires->setPositionInformation(0); // Position au sein de la section
        $infoHoraires->setSectionInformation($sectionPratique);
        $manager->persist($infoHoraires);
        $this->addReference(self::INFO_HORAIRES_REF, $infoHoraires);

        $infoTarifs = new Information();
        $infoTarifs->setTitleInformation('Tarifs et Billetterie Test');
        $infoTarifs->setContentInformation('Pass 1 jour : 30€, Pass 3 jours : 70€.');
        $infoTarifs->setPublishInformation(true);
        $infoTarifs->setPositionInformation(1); // Position au sein de la section
        $infoTarifs->setSectionInformation($sectionPratique);
        $manager->persist($infoTarifs);
        $this->addReference(self::INFO_TARIFS_REF, $infoTarifs);

        // Section 2: Accès et Transports (position 1)
        $sectionAcces = new InformationSection();
        $sectionAcces->setSectionLabel('Acces Test');
        $sectionAcces->setTitleInformationSection('Accès et Transports de Test');
        $sectionAcces->setContentInformationSection('Comment venir au festival.');
        $sectionAcces->setPositionInformationSection(1);
        $manager->persist($sectionAcces);
        $this->addReference(self::SECTION_ACCES_REF, $sectionAcces);

        // Informations pour la section "Accès"
        $infoParking = new Information();
        $infoParking->setTitleInformation('Parking Test');
        $infoParking->setContentInformation('Un grand parking est disponible à proximité.');
        $infoParking->setPublishInformation(true);
        $infoParking->setPositionInformation(2); // Position au sein de la section
        $infoParking->setSectionInformation($sectionAcces);
        $manager->persist($infoParking);
        $this->addReference(self::INFO_PARKING_REF, $infoParking);

        $infoTransportsUnpublished = new Information();
        $infoTransportsUnpublished->setTitleInformation('Transports en commun (Non Publié)');
        $infoTransportsUnpublished->setContentInformation('Détails des navettes et bus à venir.');
        $infoTransportsUnpublished->setPublishInformation(false); // NON PUBLIÉE
        $infoTransportsUnpublished->setPositionInformation(3); // Position au sein de la section
        $infoTransportsUnpublished->setSectionInformation($sectionAcces);
        $manager->persist($infoTransportsUnpublished);
        $this->addReference(self::INFO_TRANSPORTS_UNPUBLISHED_REF, $infoTransportsUnpublished);

        $manager->flush();
    }
}