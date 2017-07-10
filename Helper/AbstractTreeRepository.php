<?php

namespace App\AppBundle\Helper;

use Doctrine\ORM\EntityRepository;
use App\AppBundle\Helper\Filters\IdFilter;
use App\AppBundle\Helper\AbstractRepository;

abstract class AbstractTreeRepository extends AbstractRepository {

    public function getTreeList($repositoryNamespace, $limit = null) {
        $list = array();
        $repository = $this->getEntityManager()->getRepository($repositoryNamespace);        
        $items = $repository->findBy(array(), array('weight' => 'ASC'), $limit);
        
        if(!$repository->getClassMetadata()->getReflectionClass()->hasMethod('getParent')){
            $list[0] = $items;
            return $list;
        }

        foreach ($items as $i) {
            $list[($i->getParent() ? $i->getParent()->getId() : 0)][] = $i;
        }
        unset($items, $i);

        return $list;
    }

}
