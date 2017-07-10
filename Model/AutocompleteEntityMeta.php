<?php

namespace App\AppBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use App\AppBundle\Model\SimpleEntityMeta;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AutocompleteEntityMeta class
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class AutocompleteEntityMeta extends SimpleEntityMeta {

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min = "1")
     */
    protected $name;

    /**
     * @ORM\Column(name="searchgroup", type="integer", nullable=false, options={"default" = 0})
     */
    protected $searchGroup = 0;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default" = 0})
     */
    protected $searched = 0;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default" = 0})
     */
    protected $results = 0;

    /**
     * @ORM\Column(type="boolean", options={"default" = 0})
     */
    protected $active = 0;

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name) {
        $name = strtolower($name);
        $name = filter_var($name, FILTER_SANITIZE_STRING);
        $name = preg_replace("/[^a-zA-ZęóąśłżźćńĘÓĄŚŁŻŹĆŃ] /", "", $name);
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set searchgroup
     *
     * @param integer $searchgroup
     */
    public function setSearchGroup($searchgroup) {
        $this->searchGroup = (int) $searchgroup;

        return $this;
    }

    /**
     * Get searchgroup
     *
     * @return integer
     */
    public function getSearchGroup() {
        return (int) $this->searchGroup;
    }

    /**
     * Set searched
     *
     * @param integer $searched
     */
    public function setSearched($searched) {
        $this->searched = (int) $searched;

        return $this;
    }

    /**
     * Get searched
     *
     * @return integer
     */
    public function getSearched() {
        return (int) $this->searched;
    }

    public function searchedUp() {
        $this->searched = $this->getSearched() + 1;

        return $this;
    }

    /**
     * Set results
     *
     * @param integer $results
     */
    public function setResults($results) {
        $this->results = (int) $results;

        return $this;
    }

    /**
     * Get results
     *
     * @return integer
     */
    public function getResults() {
        return (int) $this->results;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return obj
     */
    public function setActive($active) {
        $this->active = (boolean) $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive() {
        return (boolean) $this->active;
    }

    public function __toString() {
        return $this->getName();
    }

}

?>
