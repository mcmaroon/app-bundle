<?php

namespace App\AppBundle\Helper\Traits;

use Symfony\Component\DependencyInjection\ContainerInterface;

trait EntityContainerTrait {

    /**
     * @var service_container 
     */
    protected $container = null;

    public function setContainer(ContainerInterface $container) {
        $this->container = $container;

        return $this;
    }

    public function getContainer() {
        return $this->container;
    }

    private function getParameter($paramName, $default = null) {
        $value = $default;

        try {
            $value = $this->getContainer()->getParameter($paramName);
        } catch (\Exception $exc) {
            
        }

        return $value;
    }

}
