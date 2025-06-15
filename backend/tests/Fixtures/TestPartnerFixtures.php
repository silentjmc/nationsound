<?php
namespace App\Tests\Fixtures;

use App\Entity\Partner;
use App\Entity\PartnerType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TestPartnerFixtures extends Fixture implements DependentFixtureInterface
{
    public const PARTNER_PUBLISHED_1_REF = 'test-partner-published-1';
    public const PARTNER_PUBLISHED_2_REF = 'test-partner-published-2';
    public const PARTNER_UNPUBLISHED_REF = 'test-partner-unpublished';

    public function load(ObjectManager $manager): void
    {
        /** @var PartnerType $sponsorType */
        $sponsorType = $this->getReference(TestPartnerTypeFixtures::TYPE_SPONSOR_REF);
        /** @var PartnerType $mediaType */
        $mediaType = $this->getReference(TestPartnerTypeFixtures::TYPE_MEDIA_REF);

        // Partenaire 1 (Publié, Sponsor)
        $partner1 = new Partner();
        $partner1->setNamePartner('Super Sponsor Test');
        $partner1->setImagePartner('sponsor_logo.png'); // Nom de fichier factice
        $partner1->setUrl('https://sponsor.test.com');
        $partner1->setTypePartner($sponsorType);
        $partner1->setPublishPartner(true);
        $manager->persist($partner1);
        $this->addReference(self::PARTNER_PUBLISHED_1_REF, $partner1);

        // Partenaire 2 (Publié, Média)
        $partner2 = new Partner();
        $partner2->setNamePartner('Média Partenaire Test');
        $partner2->setImagePartner('media_logo.jpg');
        $partner2->setUrl('https://media.test.org');
        $partner2->setTypePartner($mediaType);
        $partner2->setPublishPartner(true);
        $manager->persist($partner2);
        $this->addReference(self::PARTNER_PUBLISHED_2_REF, $partner2);

        // Partenaire 3 (Non Publié, Sponsor)
        $partner3 = new Partner();
        $partner3->setNamePartner('Sponsor Discret Test');
        $partner3->setImagePartner('discret_logo.gif');
        $partner3->setUrl('https://discret.test.net');
        $partner3->setTypePartner($sponsorType);
        $partner3->setPublishPartner(false);
        $manager->persist($partner3);
        $this->addReference(self::PARTNER_UNPUBLISHED_REF, $partner3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TestPartnerTypeFixtures::class,
        ];
    }
}