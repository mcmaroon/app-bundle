<?php

namespace App\AppBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * SimpleEntityMeta class
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class SimpleEntityMeta {   

    /**
     * @ORM\Column(name="created_at", type="datetime")
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="edited_at", type="datetime")
     * @var \DateTime
     */
    protected $editedAt;   

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @ORM\PrePersist()
     */
    public function setCreatedAt($date = null)
    {
        if ($date instanceof \DateTime) {
            $this->createdAt = $date;
        }
        if (is_null($this->createdAt)) {
            $this->createdAt = new \DateTime("now");
        }

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Set editedAt
     *
     * @param \DateTime $editedAt
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function setEditedAt($date = null)
    {
        if ($date instanceof \DateTime) {
            $this->editedAt = $date;
        }
        if (is_null($this->editedAt)) {
            $this->editedAt = new \DateTime("now");
        }

        return $this;
    }

    /**
     * Get editedAt
     *
     * @return \DateTime
     */
    public function getEditedAt() {
        return $this->editedAt;
    }

}

?>
