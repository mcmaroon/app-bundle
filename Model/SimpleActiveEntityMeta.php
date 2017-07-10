<?php
namespace App\AppBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use App\AppBundle\Model\SimpleEntityMeta;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SimpleActiveEntityMeta class
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class SimpleActiveEntityMeta extends SimpleEntityMeta
{

    /**
     * @ORM\Column(type="boolean", options={"default" = 0})
     */
    protected $active = 0;

    /**
     * Set active
     *
     * @param boolean $active
     * @return obj
     */
    public function setActive($active)
    {
        $this->active = (boolean) $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return (boolean) $this->active;
    }
}

?>
