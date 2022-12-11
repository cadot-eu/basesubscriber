<?php

namespace App\EventSubscriber\base;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Doctrine\Common\Collections\Criteria;

class DoctrineSubscriber implements EventSubscriberInterface
{
    // this method can only return the event names; you cannot define a
    // custom method name to execute when each event triggers
    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postRemove,
            Events::postUpdate,
        ];
    }

    // callback methods must be called exactly like the events they listen to;
    // they receive an argument of type LifecycleEventArgs, which gives you access
    // to both the entity object of the event and the entity manager itself
    public function postPersist(LifecycleEventArgs $args): void
    {
        //$this->logActivity('persist', $args);
        $class = get_class($args->getObject());
        $reflexion = new \ReflectionClass($class);
        //on vérifie si un slug est donné dans id
        $propertieSlug = $reflexion->getProperty('id');
        foreach (explode("\n", $propertieSlug->getDocComment()) as $doc) {
            $comment = trim(substr(trim($doc), 1));
            if (explode(':', $comment)[0] == 'slug') {
                $slug = trim(explode(':', $comment)[1]);
                break;
            }
        }
        //si on a pas de slug définis dans id
        if (!isset($slug)) {

            foreach ($reflexion->getProperties() as $prop) {
                $propertiesName[] = $prop->getName();
            }
            foreach (['titre',  'title', 'nom', 'name', 'label', 'id'] as $motParImportance) {
                if (
                    in_array($motParImportance, $propertiesName)
                ) {
                    $slug = $motParImportance;
                    break;
                }
            }
        }
        //génération du slug
        $slugger = new AsciiSlugger();
        $method = 'get' . ucfirst($slug);
        $lugGenerated = $slugger->slug($args->getObject()->$method())->lower();
        //on récupère le dernier slug avec le même préfixe
        $repo = $args->getObjectManager()->getRepository($class);
        $entityRepository = $repo->createQueryBuilder('e');
        $entityRepository->where('e.slug LIKE :slug');
        $entityRepository->setParameter('slug', $lugGenerated . '%');
        $entityRepository->orderBy('e.slug', Criteria::DESC);
        $entity = $entityRepository->getQuery()->setMaxResults(1)->getOneOrNullResult();
        //si on a un slug avec ce préfixe
        if ($entity) {
            $inc = (int)array_reverse(explode('-', $entity->getSlug()))[0] + 1;
            //on set le slug    
            $args->getObject()->SetSlug($lugGenerated . '-' . $inc);
        } else {
            $args->getObject()->SetSlug($lugGenerated);
        }
        $args->getObjectManager()->persist($args->getObject());
        $args->getObjectManager()->flush();
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        //$this->logActivity('remove', $args);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        //$this->logActivity('update', $args);
    }

    private function logActivity(string $action, LifecycleEventArgs $args): void
    {
        //$entity = $args->getObject();

        // if this subscriber only applies to certain entity types,
        // add some code to check the entity type as early as possible
        // if (!$entity instanceof Product) {
        //     return;
        // }

        // ... get the entity information and log it somehow
    }
}
