<?php
namespace App\AppBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use App\AppBundle\Model\Interfaces\WeightListenerInterface;

class WeightListener
{

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity !== null && \method_exists($entity, 'setWeight')) {
            $em = $args->getEntityManager();
            $reflect = new \ReflectionClass($entity);
            try {
                $repository = $em->getRepository($reflect->getName());
                if ($repository instanceof WeightListenerInterface) {
                    $count = (int) $repository->countWeightListener($entity);
                    $entity->setWeight($count);
                }
            } catch (\Exception $exc) {
                
            }
        }
    }
}
