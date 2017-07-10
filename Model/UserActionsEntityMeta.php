<?php

namespace App\AppBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use App\AppBundle\Model\BaseActiveEntityMeta;

/**
 * UserActionsEntityMeta class
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class UserActionsEntityMeta extends BaseActiveEntityMeta {

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @var user_id
     */
    protected $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @var user_id
     */
    protected $editedBy;

    /**
     * Set createdBy
     *
     * @param integer $createdBy
     */
    public function setCreatedBy($createdBy = NULL) {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return integer
     */
    public function getCreatedBy() {
        return $this->createdBy;
    }

    /**
     * Set editedBy
     *
     * @param integer $editedBy
     */
    public function setEditedBy($editedBy = NULL) {
        $this->editedBy = $editedBy;

        return $this;
    }

    /**
     * Get editedBy
     *
     * @return integer
     */
    public function getEditedBy() {
        return $this->editedBy;
    }

}

?>
