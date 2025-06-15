<?php

namespace App\Tests\Trait;

use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

trait MoveActionTestTrait 
{
    /**
     * Simulates a move action on an entity.
     *
     * @param string $entityType The type of entity to move (e.g., 'information', 'section').
     * @param int $entityId The ID of the entity to move.
     * @param int $newPosition The new position for the entity.
     * @param string $csrfToken The CSRF token for security.
     * @return array The response from the move action.
     */
    private function moveAction(string $entity, string $fixtureClass, string $fixtureReference, string $direction, int $initialExpectedPosition, int $finalExpectedPosition): void
    {
        $getterPositionMethod = 'getPosition' . $entity;
        $getterIdMethod = 'getId' . $entity;
        $entityFqcn = 'App\\Entity\\' . $entity;

        $referenceRepository = $this->loadFixturesAndLoginAdmin([$fixtureClass]);
        
        if ($finalExpectedPosition === -1) {
             $entityRepository = $this->entityManager->getRepository($entityFqcn);
            $countEntities = $entityRepository->count([]); // Compte toutes les FAQs
            $expectedLastPosition = $countEntities > 0 ? $countEntities - 1 : 0;
        }  else {
            $expectedLastPosition = $finalExpectedPosition;
        }

        $entityToMove = $referenceRepository->getReference($fixtureReference,); 
        $this->assertNotNull($entityToMove, "Fixture '$fixtureReference' de la classe '$fixtureClass' non trouvée.");
        
        $this->assertEquals($initialExpectedPosition, $entityToMove->{$getterPositionMethod}(), "L'entité '$fixtureReference' devrait être initialement en position $initialExpectedPosition");

        /** @var AdminUrlGenerator $adminUrlGenerator */
        $adminUrlGenerator = $this->getContainer()->get(AdminUrlGenerator::class);
        $urlMove = $adminUrlGenerator
            ->setDashboard(static::getDashboardFqcn())
            ->setController(static::getControllerFqcn())
            ->setAction($direction)
            ->setEntityId($entityToMove->{$getterIdMethod}())
            ->generateUrl();
        $this->client->request('GET', $urlMove);

        $expectedIndexUrl = $this->generateIndexUrl();
        $this->assertResponseRedirects($expectedIndexUrl, null, "Devrait rediriger vers la page d'index après l'action moveDown.");
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success', "Un message flash de succès devrait être affiché pour moveDown.");

        $this->entityManager->clear();
     
        $updatedEntityMoved = $this->entityManager->getRepository($entityFqcn)->find($entityToMove->{$getterIdMethod}());
        $this->assertNotNull($updatedEntityMoved, "Entité '$entity' déplacée non trouvée après mise à jour.");
        $this->assertEquals($expectedLastPosition, $updatedEntityMoved->$getterPositionMethod(), "L'entité '$entity' devrait maintenant être en position $finalExpectedPosition après '$direction'." );
    }
}