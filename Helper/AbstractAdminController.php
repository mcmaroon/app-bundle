<?php

namespace App\AppBundle\Helper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\AppBundle\Helper\AbstractController;
use App\AppBundle\Helper\AbstractControllerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * AbstractAdminController.
 */
abstract class AbstractAdminController extends AbstractController {

    public final function getClassShortName() {
        $reflect = new \ReflectionClass($this);
        return str_replace(array('Admin', 'Controller'), '', $reflect->getShortName());
    }
    
    // ~

    protected function getViewPath() {
        return 'admin/' . strtolower($this->entityName);
    }

}
