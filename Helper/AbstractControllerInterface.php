<?php

namespace App\AppBundle\Helper;

use Symfony\Component\HttpFoundation\Request;

interface AbstractControllerInterface {

    /**
     * @deprecated dla sf4 do wywalenia
     * 
     * @return mixed
     */
    public function getControllerBundleName();

    public function getControllerEntity();

    public function getControllerFormType();
}
