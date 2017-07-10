<?php

namespace App\AppBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\AppBundle\Model\BaseActiveEntityMeta;

/**
 * TreeEntityMeta class
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class TreeEntityMeta extends BaseActiveEntityMeta {

    use \App\AppBundle\Helper\Traits\WeightTrait;    

    /**
     * Constructor
     */
    public function __construct() {
        $this->children = new ArrayCollection();
    }    

    /**
     * Add child
     *
     * @param $child
     *
     * @return Object
     */
    public function addChild($child) {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param $child
     */
    public function removeChild($child) {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren() {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param $parent
     *
     * @return Object
     */
    public function setParent($parent = null) {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Object
     */
    public function getParent() {
        return $this->parent;
    }

}

?>
