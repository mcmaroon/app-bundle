<?php

namespace App\AppBundle\Helper;

use Symfony\Component\HttpFoundation\Request;

interface AbstractControllerInterface {
    
    public function getControllerBundleName();

    public function getControllerEntity();

    public function getControllerFormType();
}
