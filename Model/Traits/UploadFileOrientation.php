<?php
namespace App\AppBundle\Model\Traits;

use Doctrine\ORM\Mapping as ORM;

trait UploadFileOrientation
{

    /**
     * @ORM\Column(type="boolean", options={"default" = 0})
     */
    protected $orientation = 0;
    private $orientationMap = [
        0 => 'horizontal',
        1 => 'vertical',
    ];

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

    public function getOrientationName()
    {
        if (\array_key_exists((int) $this->orientation, $this->orientationMap)) {
            return \strtolower($this->orientationMap[(int) $this->orientation]);
        }
        return '';
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
