<?php
namespace App\Tests\Fixtures;

use App\Entity\Artist;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TestArtistFixtures extends Fixture
{
    public const ARTIST_ALPHA_EVENTS_REF = 'test-artist-alpha-events'; // Renommé pour clarté
    public const ARTIST_BETA_EVENTS_REF = 'test-artist-beta-events';   // Renommé pour clarté

    public function load(ObjectManager $manager): void
    {
        $artist1 = new Artist();
        $artist1->setNameArtist('Artiste Alpha (pour événements)');
        $artist1->setContentArtist('Description Alpha pour tests événements.');
        $artist1->setImageArtist('alpha_event.webp');
        $artist1->setThumbnail('thumb_alpha_event.webp');
        $artist1->setTypeMusic('Rock Test');
        $manager->persist($artist1);
        $this->addReference(self::ARTIST_ALPHA_EVENTS_REF, $artist1);

        $artist2 = new Artist();
        $artist2->setNameArtist('Artiste Beta (pour événements)');
        $artist2->setContentArtist('Description Beta pour tests événements.');
        $artist2->setImageArtist('beta_event.webp');
        $artist2->setThumbnail('thumb_beta_event.webp');
        $artist2->setTypeMusic('Pop Test');
        $manager->persist($artist2);
        $this->addReference(self::ARTIST_BETA_EVENTS_REF, $artist2);

        $manager->flush();
    }
}