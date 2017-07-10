<?php

namespace App\AppBundle\Helper\Traits;

use Doctrine\ORM\Mapping as ORM;

trait ClearInputTrait {

    public function clearString($string) {
        return trim(filter_var($string, FILTER_SANITIZE_STRING, FILTER_SANITIZE_SPECIAL_CHARS));
    }

    public function clearNumber($string) {
        return filter_var($string, FILTER_SANITIZE_NUMBER_INT);
    }

}
