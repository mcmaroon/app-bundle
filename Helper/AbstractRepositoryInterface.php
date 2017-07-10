<?php

namespace App\AppBundle\Helper;

interface AbstractRepositoryInterface {

    public function getList();

    public function defaultQueryFilters();

    public function findByIds(array $ids);
}
