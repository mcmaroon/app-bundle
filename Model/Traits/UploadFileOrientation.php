<?php
namespace App\AppBundle\Model\Traits;

use Doctrine\ORM\Mapping as ORM;

trait UploadFileOrientation
{

    /**
     * @ORM\Column(type="boolean", options={"default" = 0})
     */
    protected $orientation = 0;

    /**
     * Set orientation
     *
     * @param boolean $orientation
     * @return obj
     */
    public function setOrientation($orientation)
    {
        $this->orientation = (boolean) $orientation;

        return $this;
    }

    /**
     * Get orientation
     *
     * @return boolean
     */
    public function getOrientation()
    {
        return (boolean) $this->orientation;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        parent::preUpload();
        if (null !== $this->getFile()) {
            $size = \getimagesize($this->getFile());
            if ($size) {
                if ($size[0] > $size[1]) {
                    $this->setOrientation(0); // horizontal
                } else {
                    $this->setOrientation(1); //vertical
                }
            }
        }
    }
}
