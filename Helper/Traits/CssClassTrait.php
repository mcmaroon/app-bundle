<?php

namespace App\AppBundle\Helper\Traits;

use Doctrine\ORM\Mapping as ORM;

trait CssClassTrait {

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $cssClass;

    /**
     * Set cssClass
     *
     * @param string $cssClass
     */
    public function setCssClass($cssClass) {
        $this->cssClass = strip_tags(trim($cssClass));

        return $this;
    }

    /**
     * Get cssClass
     *
     * @return string
     */
    public function getCssClass() {
        return $this->cssClass;
    }

}
