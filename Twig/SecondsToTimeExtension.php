<?php

namespace App\AppBundle\Twig;

class SecondsToTimeExtension extends \Twig_Extension {

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('secondsToTime', function ($seconds) {
                        $dtF = new \DateTime("@0");
                        $dtT = new \DateTime("@$seconds");
                        return $dtF->diff($dtT)->format('%dd %Hh %im %ss');
                    })
        );
    }

    public function getName() {
        return 'seconds_to_time_extension';
    }

}
