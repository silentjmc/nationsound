<?php

namespace App\Tests\Fixtures;

use App\Entity\Faq;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class TestFaqFixtures extends Fixture
{
    public const FAQ_1_REF = 'test-faq-1';
    public const FAQ_2_REF = 'test-faq-2';
    public const FAQ_3_REF = 'test-faq-3';

    public function load(ObjectManager $manager): void
    {
        $faq1 = new Faq();
        $faq1->setQuestion('Question Publiée Un (Fixture Complète)');
        $faq1->setReponse('Réponse pour la question publiée un.');
        $faq1->setPublishFaq(true);
        $faq1->setPositionFaq(0);
        $manager->persist($faq1);
        $this->addReference(self::FAQ_1_REF, $faq1);

        $faq2 = new Faq();
        $faq2->setQuestion('Question Publiée Deux (Fixture Complète)');
        $faq2->setReponse('Réponse pour la question publiée deux.');
        $faq2->setPublishFaq(true);
        $faq2->setPositionFaq(1);
        $manager->persist($faq2);
        $this->addReference(self::FAQ_2_REF, $faq2);

        $faq3 = new Faq();
        $faq3->setQuestion('Question Non Publiée (Fixture Complète)');
        $faq3->setReponse('Cette réponse ne devrait pas être visible par défaut via l\'API publique.');
        $faq3->setPublishFaq(false); // Non publiée
        $faq3->setPositionFaq(2);
        $manager->persist($faq3);
        $this->addReference(self::FAQ_3_REF, $faq3);

        $manager->flush();
    }
/*
    public static function getGroups(): array // <= IMPLÉMENTER CETTE MÉTHODE
    {
        return ['test_api_faq', 'test_group']; // Choisissez un ou plusieurs noms de groupe
    }*/
}