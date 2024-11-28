<?php

namespace App\DataFixtures;

use App\Entity\Faq;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class FaqFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faqs = [
            [
                'question' => 'Quels sont les horaires d\'ouverture du festival ?',
                'reponse' => 'Le festival ouvre ses portes chaque jour à 12h00 et se termine à 00h00. Consultez notre planning pour les horaires spécifiques des scènes.',
                'publish' => true,
                'dateModification' => '2024-11-28 17:49:10',
                'userModification' => 'Maël Lemaitre',
                'position' => 0
            ],
        ];

        foreach ($faqs as $faqData) {
            $faq = new Faq();
            $faq->setQuestion($faqData['question']);
            $faq->setReponse($faqData['reponse']);
            $faq->setPublish($faqData['publish']);
            $faq->setDateModification(new \DateTime($faqData['dateModification']));
            $faq->setUserModification($faqData['userModification']);
            $faq->setPosition($faqData['position']);
            
            $manager->persist($faq);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
