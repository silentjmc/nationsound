<?php
namespace App\Tests\Fixtures;

use App\Entity\LocationType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TestLocationTypeFixtures extends Fixture
{
    public const LOC_TYPE_SCENE_REF = 'test-location-type-scene';
    public const LOC_TYPE_CHAPITEAU_REF = 'test-location-type-chapiteau';

    public function load(ObjectManager $manager): void
    {
        $typeScene = new LocationType();
        $typeScene->setNameLocationType('Scène Test');
        $typeScene->setSymbol('musice3afc4c99e4dee432ca4ae6813b7b9bd56c4ade6.png'); 
        $manager->persist($typeScene);
        $this->addReference(self::LOC_TYPE_SCENE_REF, $typeScene);

        $typeChapiteau = new LocationType();
        $typeChapiteau->setNameLocationType('Chapiteau Test');
        $typeChapiteau->setSymbol('house7e746f4331ae34ec989e1f4a6c589b70855946d0.png');
        $manager->persist($typeChapiteau);
        $this->addReference(self::LOC_TYPE_CHAPITEAU_REF, $typeChapiteau);

        $manager->flush();
    }
}