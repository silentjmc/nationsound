<?php

namespace App\DataFixtures;

use App\Entity\Partners;
use App\Entity\PartnerType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $roleTypeData=[
            ["id" => 1, "role" => 'Administrateur'],
            ["id" => 2, "role" => 'Commercial'],
            ["id" => 3, "role" => 'Marketing'],
            ["id" => 4, "role" => 'Rédacteur']
        ];
        $roleTypes = [];

        foreach ($roleTypeData as $roleData) {
            $role = new PartnerType();
            $role->setId($roleData['id']); // Assigner l'ID ici
            $role->setType($roleData['role']);
            $manager->persist($role);
            $partnerTypes[$roleData['id']] = $roleTypes; // Stocker l'objet par ID
        }

        $usersData = [
            ["id" => 1, 
            "email" =>'admin@admin.com', 
            "password" =>'$2y$13$9JiOO7W0UTSX.neJnq7VAu7QD5nALSv514nzU7fnilJr4OOAejEvC', 
            "lastname" => 'ad', 
            "firstname" => 'min', 
            "role"=>1]
        ];

        foreach ($usersData as $userData) {
            $user = new Partners();
            $user->setId($userData['id']);
            $user->setName($userData['email']);
            $user->setImage($userData['password']);
            $user->setUrl($userData['lastname']);
            $user->setUrl($userData['firstname']);

            if (isset($roleTypes[$userData['type']])) {
                $user->setType($roleTypes[$userData['role']]); // Ici, on assigne l'objet PartnerType
            }

            $manager->persist($user);
        }

        $eventDatesData=[
            ["id" => 1, "date" => '2025-07-04', "actif" => 1],
            ["id" => 2, "date" => '2025-07-05', "actif" => 1],
            ["id" => 3, "date" => '2025-07-06', "actif" => 1]
        ];

        $eventDates=[];

        foreach ($eventDatesData as $datesData) {
            $date = new Partners();
            $date->setId($datesData['id']);
            $date->setName($datesData['date']);
            $date->setImage($datesData['actif']);

            $manager->persist($date);
        }

        $eventTypesData = [
            ["id" => 1, "type" => 'Concert'],
            ["id" => 2, "type" => 'Rencontre']
        ];

        $eventTypes = [];

        foreach ($eventTypesData as $typesData) {
            $eventType = new Partners();
            $eventType->setId($typesData['id']);
            $eventType->setName($typesData['date']);
            $eventType->setImage($typesData['actif']);

            $manager->persist($eventType);
        }

        $locationTypesData = [
            ["id" => 1, "type" => 'Scènes', "symbol" => 'music5238f0c5618ac670419a5610cc6192740e072e85.png'],
            ["id" => 2, "type" => 'Restaurations', "symbol" => 'food7a5df5c17acc277ca655d91ce12aca28ec473240.png'],
            ["id" => 3, "type" => 'Secours', "symbol" => 'hospital4085de0f8b6362ff263e1658977c492152067e77.png'],
            ["id" => 4, "type" => 'Pavillons', "symbol" => 'housee98bacf1121274746a4d848f20661fe02d4865f1.png'],
            ["id" => 5, "type" => 'Toilettes', "symbol" => 'wc6b3802a4497949a61ad13edcd76d2a314cbea9c9.png'],
            ["id" => 15,"type" => 'test', "symbol" => 'wc5a9401d977593e38b222e0d5484c299cb42cc6da.png'],
            ["id" => 16, "type" => 'testtestecho', "symbol" => 'marker-icon819a6e72aa7385784a3a15ee6efa42dc0891a558.png']
        ];

        $locationTypes = [];

        foreach ($locationTypesData as $typesData) {
            $locationType = new Partners();
            $locationType->setId($typesData['id']);
            $locationType->setName($typesData['type']);
            $locationType->setImage($typesData['symbol']);

            $manager->persist($locationType);
        }



        // Types de partenaires avec IDs
        $partnersTypeData = [
            ["id" => 1, "type" => "Entreprise"],
            ["id" => 2, "type" => "Institution"],
            ["id" => 3, "type" => "Média"]
        ];

        // Tableau pour stocker les objets PartnerType
        $partnerTypes = [];

        // Créer et persister les types de partenaires
        foreach ($partnersTypeData as $typeData) {
            $partnerType = new PartnerType();
            $partnerType->setId($typeData['id']); // Assigner l'ID ici
            $partnerType->setType($typeData['type']);
            $manager->persist($partnerType);
            $partnerTypes[$typeData['id']] = $partnerType; // Stocker l'objet par ID
        }

        // Flusher les PartnerType
        $manager->flush();   

        $partnersList = [
            [
                "type" => 1,
                "name" => "Weezevent",
                "url" => "https://weezevent.com/fr/",
                "logo" => "WEEZEVENT-1.png"
            ],
            [
                "type" => 1,
                "name" => "Spotify",
                "url" => "https://www.spotify.com/fr/premium/",
                "logo" => "SPOTIFY-1.png"
            ],
            [
                "type" => 2,
                "name" => "Sacem",
                "url" => "https://www.sacem.fr",
                "logo" => "SACEM-2022.png"
            ],
            [
                "type" => 3,
                "name" => "Rolling-Stone",
                "url" => "https://www.rollingstone.fr",
                "logo" => "ROLLING-STONE.png"
            ],
            [
                "type" => 1,
                "name" => "Pioneerdj",
                "url" => "https://www.pioneerdj.com/fr-fr/",
                "logo" => "pioneer-1140x570-1.png"
            ],
            [
                "type" => 1,
                "name" => "Orange",
                "url" => "https://www.orange.fr/portail",
                "logo" => "ORANGE-22.png"
            ],
            [
                "type" => 2,
                "name" => "Miniostère de la Culture",
                "url" => "https://www.culture.gouv.fr/",
                "logo" => "MINISTERE-DE-LA-CULTURE.png"
            ],
            [
                "type" => 3,
                "name" => "Europe 2",
                "url" => "https://www.europe2.fr/",
                "logo" => "logos-partenaires-solidays-3-1140x570-1.png"
            ],
            [
                "type" => 2,
                "name" => "CNM",
                "url" => "https://cnm.fr",
                "logo" => "CNM-2022.png"
            ],
            [
                "type" => 1,
                "name" => "Crédit Mutuel",
                "url" => "https://www.creditmutuel.fr/home/index.html",
                "logo" => "CM-1.png"
            ],
            [
                "type" => 3,
                "name" => "Arte Concert",
                "url" => "https://www.arte.tv/fr/arte-concert/",
                "logo" => "ARTE-CONCERT.png"
            ],
            [
                "type" => 2,
                "name" => "Région Ile-de-France",
                "url" => "http://www.iledefrance.fr/",
                "logo" => "idf-1140x570-4.jpg"
            ]
        ];

        foreach ($partnersList as $partnerData) {
            $partners = new Partners();
            $partners->setName($partnerData['name']);
            $partners->setImage($partnerData['logo']);
            $partners->setUrl($partnerData['url']);

            // Associer directement l'objet PartnerType par ID
            if (isset($partnerTypes[$partnerData['type']])) {
                $partners->setType($partnerTypes[$partnerData['type']]); // Ici, on assigne l'objet PartnerType
            }

            $manager->persist($partners);
        }

        // Flusher pour sauvegarder tous les partenaires
        $manager->flush();       
    }
}
