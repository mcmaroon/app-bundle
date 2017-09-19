<?php
namespace App\AppBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

class AppEntityEvent extends Event
{

    private $entity = null;
    private $entityShortName = null;
    private $request = null;
    private $additionalInfo = [];

    const EVENT_CREATE = 'app.entity.create';
    const EVENT_UPDATE = 'app.entity.update';
    const EVENT_DELETE = 'app.entity.delete';
    const EVENT_HELPER_SORT = 'app.helper.sort';

    public function __construct($entity = null, Request $request = null, array $additionalInfo = [])
    {
        $this->entity = $entity;
        $this->entityShortName = $this->setEntityShortName();
        $this->request = $request;
        $this->additionalInfo = $additionalInfo;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    private function setEntityShortName()
    {
        if ($this->entity) {
            $reflect = new \ReflectionClass($this->entity);
            return $reflect->getShortName();
        }
    }

    public function getEntityShortName()
    {
        return $this->entityShortName;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getAdditionalInfo()
    {
        return $this->additionalInfo;
    }
}
