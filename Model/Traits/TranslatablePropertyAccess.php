<?php
namespace App\AppBundle\Model\Traits;

use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @link https://github.com/KnpLabs/DoctrineBehaviors#proxy-translations
 */
trait TranslatablePropertyAccess
{

    public function __call($method, $arguments)
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(), $method);
    }

    public function __toString()
    {
        $string = $this->__call('name', []);
        return $string ? $string : '';
    }
}
