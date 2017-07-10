<?php

namespace App\AppBundle\Model;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * http://doctrine-orm.readthedocs.org/en/latest/cookbook/mysql-enums.html
 * http://symfony.com/doc/current/cookbook/doctrine/dbal.html
 */
abstract class EnumType extends Type {

    protected $name;
    protected $values = array();

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform) {
        $values = array_map(function($val) {
            return "'" . $val . "'";
        }, $this->values);

        return "ENUM(" . implode(", ", $values) . ") COMMENT '(DC2Type:" . $this->name . ")'";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform) {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform) {
        if ($value !== null && !in_array($value, $this->values)) {
            throw new \InvalidArgumentException("Invalid '" . $this->name . "' value.");
        }
        return $value;
    }

    public function getName() {
        return $this->name;
    }

}
