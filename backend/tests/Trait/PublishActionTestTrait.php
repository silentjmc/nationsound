<?php

namespace App\Tests\Trait;

use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

trait PublishActionTestTrait
{
    /**
     * Simulates a publish action on an entity.
     *
     * @param string $entityType The type of entity to publish (e.g., 'information', 'section').
     * @param int $entityId The ID of the entity to publish.
     * @param string $csrfToken The CSRF token for security.
     * @return array The response from the publish action.
     */
       private function publishAction(string $entity, string $fixtureClass, string $fixtureReference, string $action): void
    {
        $getterPublishMethod = 'isPublish' . $entity;
        $getterIdMethod = 'getId' . $entity;
        $entityFqcn = 'App\\Entity\\' . $entity;
        
        $referenceRepository = $this->loadFixturesAndLoginAdmin([$fixtureClass]);
        $entityToPublish = $referenceRepository->getReference($fixtureReference);
        $this->assertNotNull($entityToPublish);
        if ($action === 'publish') {
            $this->assertFalse($entityToPublish->$getterPublishMethod(), "'$entity' devrait être initialement non publié pour ce test.");
        } elseif ($action === 'unpublish') {
            $this->assertTrue($entityToPublish->$getterPublishMethod(), "'$entity' devrait être initialement publié pour ce test.");
        }

        $entityId = $entityToPublish->$getterIdMethod();

        $adminUrlGenerator = $this->getContainer()->get(AdminUrlGenerator::class);
        $publishUrl = $adminUrlGenerator
            ->setController(static::getControllerFqcn())
            ->setAction($action)
            ->setEntityId($entityId)
            ->generateUrl();

        $this->client->request('GET', $publishUrl);

        $expectedIndexUrl = $this->generateIndexUrl();
        static::assertResponseRedirects($expectedIndexUrl);
        $crawler=$this->client->followRedirect();
        $flashMessageText = $crawler->filter('.alert-success')->text();
        if ($action === 'publish') {
            $this->assertMatchesRegularExpression('/publiée? avec succès/i', $flashMessageText, "Le message flash pour 'publish' devrait contenir 'publié(e) avec succès'. Message trouvé: '$flashMessageText'");
        } elseif ($action === 'unpublish') {
            //Verify that "dépublié" (or "dépubliée") and "succès" are present
            $this->assertMatchesRegularExpression('/dépubliée? avec succès/i', $flashMessageText, "Le message flash pour 'unpublish' devrait contenir 'dépublié(e) avec succès'. Message trouvé: '$flashMessageText'");
        }

        $this->entityManager->clear();
        $publishedEntity = $this->entityManager->getRepository($entityFqcn)->find($entityId);
        $this->assertNotNull($publishedEntity);
        if ($action === 'publish') {
            $this->assertTrue($publishedEntity->$getterPublishMethod(), "'$entity' devrait être publiée après l'action.");
        } elseif ($action === 'unpublish') {
            $this->assertFalse($publishedEntity->$getterPublishMethod(), "'$entity' devrait être dépubliée après l'action.");
        }
    }
}