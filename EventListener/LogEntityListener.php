<?php

namespace App\AppBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LogEntityListener {

    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    private final function getClassShortName($class) {
        $reflect = new \ReflectionClass($class);
        return str_replace('Repository', '', $reflect->getShortName());
    }

    private function getControllerName($controller) {
        preg_match('/Controller\\\\([a-zA-Z]*)Controller/', $controller, $matches);
        if (isset($matches[1])) {
            return lcfirst($matches[1]);
        }
        return false;
    }

    private function getControllerActionName($controller) {
        preg_match('/::(.*)Action/', $controller, $matches);
        if (isset($matches[1])) {
            return lcfirst($matches[1]);
        }
        return false;
    }

    private function log($args) {
        $entity = $args->getEntity();

        if ($entity !== null) {

            $context = array();
            $excludeMethods = array(
                'getUsername',
                'getUsernameCanonical',
                'getSalt',
                'getPassword',
                'getPlainPassword',
                'getConfirmationToken',
                'getEmail',
                'getEmailCanonical'
            );

            $log = $this->container->get('app.logEntityListener');

            $context['sapi'] = PHP_SAPI;
            $context['class']['name'] = $this->getClassShortName($entity);

            if (PHP_SAPI !== 'cli') {
                $request = $this->container->get('request_stack');
                $context['class']['controller'] = $this->getControllerName($request->getCurrentRequest()->attributes->get('_controller'));
                $context['class']['action'] = $this->getControllerActionName($request->getCurrentRequest()->attributes->get('_controller'));
            }

            foreach (get_class_methods($entity) as $class_method) {
                if (strpos($class_method, 'get') !== false && !in_array($class_method, $excludeMethods)) {
                    $reflection = new \ReflectionMethod($entity, $class_method);
                    if ($reflection->isPublic() && $reflection->getNumberOfRequiredParameters() === 0) {
                        try {
                            $result = $entity->$class_method();
                            if (in_array(gettype($result), array('boolean', 'integer', 'double', 'string', 'NULL'))) {
                                $context['methods'][$class_method] = $result;
                            }
                        } catch (\Exception $e) {
                            
                        }
                    }
                }
            }

            if (PHP_SAPI !== 'cli') {
                $securityContext = $this->container->get('security.authorization_checker');
                if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {                    
                    
                }
            }

            $log->info('Entity Log:', $context);
        }
    }

    public function prePersist(LifecycleEventArgs $args) {
        $this->log($args);
    }

    public function preUpdate(LifecycleEventArgs $args) {
        $this->log($args);
    }

    public function preRemove(LifecycleEventArgs $args) {
        $this->log($args);
    }

}
