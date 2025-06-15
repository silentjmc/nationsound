<?php
namespace App\Tests\Fixtures;

use App\Entity\PartnerType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TestPartnerTypeFixtures extends Fixture
{
    public const TYPE_SPONSOR_REF = 'test-partner-type-sponsor';
    public const TYPE_MEDIA_REF = 'test-partner-type-media';

    public function load(ObjectManager $manager): void
    {
        $typeSponsor = new PartnerType();
        $typeSponsor->setTitlePartnerType('Sponsor Test');
        $manager->persist($typeSponsor);
        $this->addReference(self::TYPE_SPONSOR_REF, $typeSponsor);

        $typeMedia = new PartnerType();
        $typeMedia->setTitlePartnerType('Partenaire Média Test');
        $manager->persist($typeMedia);
        $this->addReference(self::TYPE_MEDIA_REF, $typeMedia);

        $manager->flush(); // Flush ici car c'est une fixture de base
    }
}