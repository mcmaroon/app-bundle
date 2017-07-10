<?php
namespace App\AppBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use App\AppBundle\Model\SimpleActiveEntityMeta;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BaseEntityMeta class
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class BaseActiveEntityMeta extends SimpleActiveEntityMeta
{

    use \App\AppBundle\Helper\Traits\ClearInputTrait;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min = "3")
     */
    protected $name;

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $this->clearString($name);

        return $this;
    }

    /**
     * Get name
     */
    public function getName()
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->getName();
    }
}

?>
