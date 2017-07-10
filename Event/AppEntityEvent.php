<?php
namespace App\AppBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class AppEntityEvent extends Event
{

    private $entity = null;

    const EVENT_CREATE = 'app.entity.create';
    const EVENT_UPDATE = 'app.entity.update';
    const EVENT_DELETE = 'app.entity.delete';

    public function __construct($entity = null)
    {
        $this->entity = $entity;
    }

    public function getEntity()
    {
        return $this->entity;
    }
}
