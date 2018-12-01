<?php

namespace App\AppBundle\Twig;

/**
 * @example usage {{ 'database_host'|parameter }}
 */
class ParameterExtension extends \Twig_Extension {

    protected $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('parameter', array($this, 'getParameter')),
        );
    }

    function getParameter($name) {
        try {
            return $this->container->getParameter($name);
        } catch (\Exception $exc) {
            /*$log = $this->container->get('app.log');
            $log->error('ParameterExtension', [
                'code' => $exc->getCode(),
                'message' => $exc->getMessage()
            ]);*/
            return null;
        }
    }

}
