<?php
// tests/Fixtures/TestEventFixtures.php
namespace App\Tests\Fixtures;

use App\Entity\Artist;
use App\Entity\Event;
use App\Entity\EventDate;
use App\Entity\EventLocation;
use App\Entity\EventType;
// Pas besoin d'importer LocationType ici, car EventLocation s'en charge
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TestEventFixtures extends Fixture implements DependentFixtureInterface
{
    // Références pour les événements créés par CETTE fixture
    public const EVENT_PUBLISHED_ARTIST1_J1_SCENE_REF = 'test-event-pub-artist1-j1-scene';
    public const EVENT_PUBLISHED_ARTIST2_J2_CHAPITEAU_REF = 'test-event-pub-artist2-j2-chapiteau';
    public const EVENT_UNPUBLISHED_ARTIST1_J1_SCENE_REF = 'test-event-unpub-artist1-j1-scene';

    public function load(ObjectManager $manager): void
    {
        // --- Récupérer les références des fixtures dépendantes ---

        /** @var Artist $artist1 */
        $artist1 = $this->getReference(TestArtistFixtures::ARTIST_ALPHA_EVENTS_REF);
        /** @var Artist $artist2 */
        $artist2 = $this->getReference(TestArtistFixtures::ARTIST_BETA_EVENTS_REF);

        /** @var EventType $concertType */
        $concertType = $this->getReference(TestEventTypeFixtures::TYPE_CONCERT_REF);
        /** @var EventType $spectacleType */
        $spectacleType = $this->getReference(TestEventTypeFixtures::TYPE_SPECTACLE_REF);

        /** @var EventDate $dateJ1 */
        $dateJ1 = $this->getReference(TestEventDateFixtures::DATE_J1_FESTIVAL_REF);
        /** @var EventDate $dateJ2 */
        $dateJ2 = $this->getReference(TestEventDateFixtures::DATE_J2_FESTIVAL_REF);

        /** @var EventLocation $scenePrincipale */
        $scenePrincipale = $this->getReference(TestEventLocationFixtures::LOC_SCENE_PRINCIPALE_REF);
        /** @var EventLocation $chapiteau */
        $chapiteau = $this->getReference(TestEventLocationFixtures::LOC_CHAPITEAU_REF);

        // --- Assurer que les lieux utilisés pour les événements publiés sont eux-mêmes publiés ---
        // Normalement, TestEventLocationFixtures devrait déjà créer des lieux publiés si c'est le scénario.
        // Mais une vérification/correction ici peut éviter des surprises si les fixtures dépendantes changent.
        if (!$scenePrincipale->isPublishEventLocation()) {
            $scenePrincipale->setPublishEventLocation(true);
            // $manager->persist($scenePrincipale); // Pas besoin de persist si déjà managé et modifié
        }
        if (!$chapiteau->isPublishEventLocation()) {
            $chapiteau->setPublishEventLocation(true);
            // $manager->persist($chapiteau);
        }

        // --- Création des Événements ---

        // Événement 1 (Publié)
        $event1 = new Event();
        $event1->setArtist($artist1);
        $event1->setType($concertType);
        $event1->setDate($dateJ1);
        $event1->setEventLocation($scenePrincipale);
        $event1->setHeureDebut(new \DateTime('20:00:00')); // Utiliser DateTimeImmutable pour éviter modif. par Doctrine
        $event1->setHeureFin(new \DateTime('21:30:00'));
        $event1->setPublishEvent(true);
        // Les champs userModificationEvent et dateModificationEvent sont gérés par EntityListener.
        // Assurez-vous qu'ils sont nullables ou que EntityListener les remplit en mode test.
        $manager->persist($event1);
        $this->addReference(self::EVENT_PUBLISHED_ARTIST1_J1_SCENE_REF, $event1);

        // Événement 2 (Publié)
        $event2 = new Event();
        $event2->setArtist($artist2);
        $event2->setType($spectacleType);
        $event2->setDate($dateJ2); // Autre date
        $event2->setEventLocation($chapiteau); // Autre lieu
        $event2->setHeureDebut(new \DateTime('18:00:00'));
        $event2->setHeureFin(new \DateTime('19:00:00'));
        $event2->setPublishEvent(true);
        $manager->persist($event2);
        $this->addReference(self::EVENT_PUBLISHED_ARTIST2_J2_CHAPITEAU_REF, $event2);

        // Événement 3 (Non Publié)
        $event3 = new Event();
        $event3->setArtist($artist1); // Peut être le même artiste
        $event3->setType($concertType);
        $event3->setDate($dateJ1);
        $event3->setEventLocation($scenePrincipale);
        $event3->setHeureDebut(new \DateTime('22:00:00'));
        $event3->setHeureFin(new \DateTime('23:00:00'));
        $event3->setPublishEvent(false);
        $manager->persist($event3);
        $this->addReference(self::EVENT_UNPUBLISHED_ARTIST1_J1_SCENE_REF, $event3);

        // Événement 4 (Publié mais lieu non publié - pour tester des cas limites si besoin)
        // Pour l'API /api/event actuelle, cet event ne devrait pas apparaître car le EventLocation
        // doit être publié (implicitement, car EventController::getEventList filtre par publishEvent=true,
        // mais la logique de PublishService pourrait avoir un impact si un Event est lié à un EventLocation non publié).
        // On va créer un EventLocation non publié spécifiquement pour ce cas.
        /** @var EventLocation $lieuNonPublie */
        // $lieuNonPublie = $this->getReference(TestEventLocationFixtures::LOC_BAR_UNPUBLISHED_REF); // Assurez-vous que cette référence existe
        // if ($lieuNonPublie) { // Si la fixture pour le lieu non publié existe
        //     $event4 = new Event();
        //     $event4->setArtist($artist2);
        //     $event4->setType($concertType);
        //     $event4->setDate($dateJ2);
        //     $event4->setEventLocation($lieuNonPublie);
        //     $event4->setHeureDebut(new \DateTimeImmutable('15:00:00'));
        //     $event4->setHeureFin(new \DateTimeImmutable('16:00:00'));
        //     $event4->setPublishEvent(true); // L'événement lui-même est marqué comme publié
        //     $manager->persist($event4);
        //     // $this->addReference('EVENT_PUBLISHED_LIEU_NON_PUBLIE_REF', $event4);
        // }


        $manager->flush();
    }

    public function getDependencies(): array
    {
        // Lister toutes les classes de fixtures dont celle-ci dépend DIRECTEMENT
        // Les dépendances des dépendances seront gérées par le Loader.
        return [
            TestArtistFixtures::class,
            TestEventTypeFixtures::class,
            TestEventDateFixtures::class,
            TestEventLocationFixtures::class, // Qui elle-même dépend de TestLocationTypeFixtures
        ];
    }
}