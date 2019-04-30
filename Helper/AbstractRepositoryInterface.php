<?php

namespace App\AppBundle\Helper;

use Doctrine\ORM\QueryBuilder;

interface AbstractRepositoryInterface
{
    public function getList(): QueryBuilder;

    public function defaultQueryFilters();

    public function findByIds(array $ids);
}
