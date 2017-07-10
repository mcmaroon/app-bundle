<?php

namespace App\AppBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerEntityListener {

    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function postLoad(LifecycleEventArgs $args) {
        $entity = $args->getEntity();

        if (($entity !== null) && method_exists($entity, 'setContainer')) {
            $entity->setContainer($this->container);
        }
    }

}
