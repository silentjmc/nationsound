<?php

namespace App\Tests\Fixtures;

use App\Entity\News;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TestNewsFixtures extends Fixture
{
    public const NEWS_PUBLISHED_1_REF = 'test-news-published-1'; // Récente
    public const NEWS_PUBLISHED_2_REF = 'test-news-published-2'; // Plus ancienne
    public const NEWS_UNPUBLISHED_REF = 'test-news-unpublished';
    public const NEWS_PUSH_ACTIVE_REF = 'test-news-push-active'; // Publiée, push, pas de date de fin ou future
    public const NEWS_PUSH_EXPIRED_REF = 'test-news-push-expired';// Publiée, push, date de fin passée
    public const NEWS_PUSH_NOT_PUBLISHED_REF = 'test-news-push-not-published'; // Non publiée, push (devrait être corrigé par la logique métier)
    public const NEWS_NO_PUSH_REF = 'test-news-no-push'; // Publiée, pas de push

    public function load(ObjectManager $manager): void
    {
        // News 1 (Publiée, Récente, Push Actif)
        $news1 = new News();
        $news1->setTitleNews('Actualité Récente Publiée avec Push');
        $news1->setContentNews('Contenu de l\'actualité récente...');
        $news1->setPublishNews(true);
        $news1->setTypeNews('Important');
        $news1->setPush(true); // Définira notificationDate
        $news1->setNotificationEndDate(new \DateTime('+7 days')); // Notification active pour 7 jours
        // userModificationNews et dateModificationNews gérés par EntityListener (s'assurer qu'ils sont nullables ou gérés)
        $manager->persist($news1);
        $this->addReference(self::NEWS_PUBLISHED_1_REF, $news1);
        $this->addReference(self::NEWS_PUSH_ACTIVE_REF, $news1); // Même news pour ce scénario

        // News 2 (Publiée, Plus Ancienne)
        $news2 = new News();
        $news2->setTitleNews('Ancienne Actualité Publiée');
        $news2->setContentNews('Contenu de l\'ancienne actualité...');
        $news2->setPublishNews(true);
        $news2->setTypeNews('Normal');
        $news2->setPush(false);
        $manager->persist($news2);
        $this->addReference(self::NEWS_PUBLISHED_2_REF, $news2);

        // News 3 (Non Publiée)
        $news3 = new News();
        $news3->setTitleNews('Actualité Non Publiée');
        $news3->setContentNews('Ce contenu ne devrait pas apparaître.');
        $news3->setPublishNews(false);
        $news3->setTypeNews('Normal');
        $news3->setPush(false); // La logique setPublishNews(false) mettra aussi push à false
        $manager->persist($news3);
        $this->addReference(self::NEWS_UNPUBLISHED_REF, $news3);

        // News 4 (Push Expiré)
        $news4 = new News();
        $news4->setTitleNews('Actualité avec Push Expiré');
        $news4->setContentNews('Notification terminée.');
        $news4->setPublishNews(true);
        $news4->setTypeNews('Important');
        $news4->setPush(true);
        $news4->setNotificationEndDate(new \DateTime('-1 day')); // Date de fin passée
        $manager->persist($news4);
        $this->addReference(self::NEWS_PUSH_EXPIRED_REF, $news4);

        // News 5 (Publiée, pas de push)
        $news5 = new News();
        $news5->setTitleNews('Actualité Publiée Sans Push');
        $news5->setContentNews('Juste une news normale.');
        $news5->setPublishNews(true);
        $news5->setTypeNews('Normal');
        $news5->setPush(false);
        $manager->persist($news5);
        $this->addReference(self::NEWS_NO_PUSH_REF, $news5);

        // News 6 (Non publiée mais push tenté - la logique métier devrait corriger)
        // Normalement, setPublishNews(false) devrait mettre push à false.
        // Et la validation Callback devrait empêcher push=true si publishNews=false.
        // On la crée pour tester le callback si on la soumettait via un formulaire,
        // mais ici EntityListener appliquera la logique de l'entité.
        $news6 = new News();
        $news6->setTitleNews('Test Push sur Non Publiée');
        $news6->setContentNews('Devrait avoir push=false après setPublishNews(false).');
        $news6->setPublishNews(false); // Cela devrait mettre push à false
        $news6->setTypeNews('Urgent');
        // $news6->setPush(true); // On laisse setPublishNews(false) gérer ça.
        $manager->persist($news6);
        $this->addReference(self::NEWS_PUSH_NOT_PUBLISHED_REF, $news6);

        $manager->flush();
    }
}