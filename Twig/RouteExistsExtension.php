<?php

namespace App\AppBundle\Twig;

class RouteExistsExtension extends \Twig_Extension {

    protected $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('routeExists', array($this, 'routeExistsCheck')),
        );
    }

    function routeExistsCheck($name) {
        $router = $this->container->get('router');

        return (null === $router->getRouteCollection()->get($name)) ? false : true;
    }

    public function getName() {
        return 'route_exists_extension';
    }

}
