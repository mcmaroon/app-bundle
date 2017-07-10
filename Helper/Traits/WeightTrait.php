<?php

namespace App\AppBundle\Helper\Traits;

use Doctrine\ORM\Mapping as ORM;

trait WeightTrait {

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default" = 0})
     */
    protected $weight = 0;

    /**
     * Set weight
     *
     * @param integer $weight
     */
    public function setWeight($weight) {
        $this->weight = (int) $weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return integer
     */
    public function getWeight() {
        return (int) $this->weight;
    }

    public function weightUp() {
        $this->weight = $this->getWeight() + 1;

        return $this;
    }

    public function weightDown() {
        $this->weight = $this->getWeight() - 1;

        return $this;
    }

}
