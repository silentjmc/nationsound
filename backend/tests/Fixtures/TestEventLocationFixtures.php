<?php
namespace App\Tests\Fixtures;

use App\Entity\EventLocation;
use App\Entity\LocationType; // Pour le type hint
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TestEventLocationFixtures extends Fixture implements DependentFixtureInterface
{
    public const LOC_SCENE_PRINCIPALE_REF = 'test-event-location-scene-principale';
    public const LOC_CHAPITEAU_REF = 'test-event-location-chapiteau';
    public const LOC_BAR_UNPUBLISHED_REF = 'test-event-location-bar-unpublished';


    public function load(ObjectManager $manager): void
    {
        /** @var LocationType $typeScene */
        $typeScene = $this->getReference(TestLocationTypeFixtures::LOC_TYPE_SCENE_REF);
        /** @var LocationType $typeChapiteau */
        $typeChapiteau = $this->getReference(TestLocationTypeFixtures::LOC_TYPE_CHAPITEAU_REF);

        $locationScene = new EventLocation();
        $locationScene->setNameEventLocation('Scène Principale Test');
        $locationScene->setLatitude('48.0001');
        $locationScene->setLongitude('2.0001');
        $locationScene->setContentEventLocation('La plus grande scène pour les tests.');
        $locationScene->setPublishEventLocation(true); // Publiée pour pouvoir y associer des événements publiés
        $locationScene->setTypeLocation($typeScene);
        $manager->persist($locationScene);
        $this->addReference(self::LOC_SCENE_PRINCIPALE_REF, $locationScene);

        $locationChapiteau = new EventLocation();
        $locationChapiteau->setNameEventLocation('Chapiteau Test Intime');
        $locationChapiteau->setLatitude('48.0002');
        $locationChapiteau->setLongitude('2.0002');
        $locationChapiteau->setContentEventLocation('Ambiance plus cosy sous le chapiteau de test.');
        $locationChapiteau->setPublishEventLocation(true); // Publiée aussi
        $locationChapiteau->setTypeLocation($typeChapiteau);
        $manager->persist($locationChapiteau);
        $this->addReference(self::LOC_CHAPITEAU_REF, $locationChapiteau);
        
        $locationBarUnpublished = new EventLocation();
        $locationBarUnpublished->setNameEventLocation('Bar VIP (Non Publié)');
        $locationBarUnpublished->setLatitude('48.0003');
        $locationBarUnpublished->setLongitude('2.0003');
        $locationBarUnpublished->setContentEventLocation('Zone non accessible.');
        $locationBarUnpublished->setPublishEventLocation(false); // Non publiée
        $locationBarUnpublished->setTypeLocation($typeScene); // Peut réutiliser un type existant
        $manager->persist($locationBarUnpublished);
        $this->addReference(self::LOC_BAR_UNPUBLISHED_REF, $locationBarUnpublished);


        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TestLocationTypeFixtures::class,
        ];
    }
}