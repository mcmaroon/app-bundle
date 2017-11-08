<?php

namespace App\AppBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use App\AppBundle\Model\BaseActiveEntityMeta;

/**
 * AutocompleteEntityMeta class
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class AutocompleteEntityMeta extends BaseActiveEntityMeta {

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

}

?>
