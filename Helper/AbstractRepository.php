<?php

namespace App\AppBundle\Helper;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use App\AppBundle\Helper\Filters\IdFilter;
use App\AppBundle\Helper\AbstractRepositoryInterface;

abstract class AbstractRepository extends EntityRepository implements AbstractRepositoryInterface {

    private $filters = array();
    private $whitelistFilters = array();
    protected $where = array();
    protected $having = array();
    private $hasJoined = false;

    // ~

    public final function getWhere() {
        return (array) $this->where;
    }

    // ~

    public final function getHaving() {
        return (array) $this->having;
    }

    // ~

    public final function setFilters(array $filters = array()) {
        $this->where = array();
        $this->having = array();
        foreach ($filters as $key => $filter) {
            if (!is_string($key) || !strlen($key)) {
                unset($filters[$key]);
            }
            if (isset($filters[$key]) && is_array($filters[$key])) {
                $filters[$key] = implode(',', $filters[$key]);
            }
        }
        $this->filters = array_filter((array) $filters, 'strlen');
    }

    // ~

    public final function getFilters() {
        return (array) $this->filters;
    }

    // ~

    public final function getFiltersCacheKey() {
        return str_replace([' '], [''], implode($this->getFilters(), '-'));
    }

    // ~

    public final function getFilter($filtername) {
        if (isset($this->filters[$filtername]) && strlen(trim($this->filters[$filtername]))) {
            return trim($this->filters[$filtername]);
        }
        return false;
    }

    // ~

    public final function setWhitelistFilters(array $filters) {
        $this->whitelistFilters = array_filter((array) $filters, 'strlen');
    }

    // ~

    public final function getWhitelistFilters() {
        return (array) $this->whitelistFilters;
    }

    // ~
    
    public final function hasJoined(){
        return (boolean) $this->hasJoined;
    }
   
    // ~

    public final function getList() {

        $queryBuilder = $this->createQueryBuilder('r');
        $queryBuilder = $this->setSelect($queryBuilder);
        
        // ~

        $this->defaultQueryFilters();

        foreach ($this->getWhere() as $where) {
            $queryBuilder->andWhere($where);
        };

        foreach ($this->getHaving() as $having) {
            $queryBuilder->andHaving($having);
        };

        $this->hasJoined = \count($queryBuilder->getDQLPart('join')) ? true : false;
        
        return $queryBuilder;
    }

    // ~

    public function setSelect($queryBuilder) {
        $queryBuilder->select(array(
                    'obj' => 'r AS obj',
                    'id' => 'r.id AS id',
                    'name' => 'r.name AS name',
                    'created' => 'r.createdAt AS created',
                    'edited' => 'r.editedAt AS edited',
                    'active' => 'r.active AS active'
        ));
        
        return $queryBuilder;
    }

    // ~

    /**
     *
     * @param type $filterType int|eq|gte|lte|gtelte|like|date|boolean
     * @param type $filtername $_GET filters[filtername]
     * @param type $queryField example r.id
     * @throws \Exception
     */
    public final function addWhereFilter($filterType, $filtername, $queryField) {
        $methodName = 'add' . ucfirst($filterType) . 'Filter';
        if (!method_exists($this, $methodName)) {
            throw new \Exception('Invalid filter type. See in ' . get_parent_class($this) . ' if ' . $methodName . ' method exists or implement this method in your EntityRepository');
        }
        $whiteList = $this->getWhitelistFilters();
        if (!count($whiteList) || in_array($filtername, $whiteList)) {
            $this->$methodName('where', $filtername, $queryField);
        }
    }

    // ~

    /**
     *
     * @param type $filterType int|eq|gte|lte|gtelte|like|date|boolean
     * @param type $filtername $_GET filters[filtername]
     * @param type $queryField example r.id
     * @throws \Exception
     */
    public final function addHavingFilter($filterType, $filtername, $queryField) {
        $methodName = 'add' . ucfirst($filterType) . 'Filter';
        if (!method_exists($this, $methodName)) {
            throw new \Exception('Invalid filter type. See in ' . get_parent_class($this) . ' if ' . $methodName . ' method exists or implement this method in your EntityRepository');
        }

        $this->$methodName('having', $filtername, $queryField);
    }

    // ~

    /**
     * Add special filter transname for table search
     * @param type $objectClass
     * @param type $field
     */
    protected final function addTranslationFilter(&$queryBuilder, $field)
    {
        if ($this->getClassMetadata()->hasAssociation('translations')) {
            $translationEntity = $this->getClassMetadata()->getAssociationTargetClass('translations');
            $this->addHavingFilter('like', 'trans' . $field, 'trans' . $field);
            $queryBuilder->addSelect('GROUP_CONCAT(trans.' . $field . ') AS trans' . $field);
            $queryBuilder->leftJoin($translationEntity, 'trans', \Doctrine\ORM\Query\Expr\Join::WITH, 'r.id = trans.translatable');
        }
    }

    // ~

    private final function addIntFilter($type, $filtername, $queryField) {
        if (false !== $filter = $this->getFilter($filtername)) {
            $id = new IdFilter();
            $idResult = $id->parse($filter)->genSQL($queryField);
            if (strlen($idResult)) {
                $this->{$type}[] = $idResult;
            }
        }
    }

    // ~

    private final function addEqFilter($type, $filtername, $queryField) {
        if (false !== $filter = $this->getFilter($filtername)) {
            $this->{$type}[] = $queryField . " = '" . $filter . "'";
        }
    }

    // ~

    private final function addGteFilter($type, $filtername, $queryField) {
        if (false !== $filter = $this->getFilter($filtername)) {
            $this->{$type}[] = $queryField . " >= '" . $filter . "'";
        }
    }

    // ~

    private final function addLteFilter($type, $filtername, $queryField) {
        if (false !== $filter = $this->getFilter($filtername)) {
            $this->{$type}[] = $queryField . " <= '" . $filter . "'";
        }
    }

    // ~

    private final function addGtelteFilter($type, $filtername, $queryField) {
        $filtername = str_replace(array('_min', '_max'), '', $filtername);
        if (false !== $filter = $this->getFilter($filtername . '_min')) {
            $this->{$type}[] = $queryField . " >= '" . $filter . "'";
        }
        if (false !== $filter = $this->getFilter($filtername . '_max')) {
            $this->{$type}[] = $queryField . " <= '" . $filter . "'";
        }
    }

    // ~

    private final function addLikeFilter($type, $filtername, $queryField) {
        if (false !== $filter = $this->getFilter($filtername)) {
            $this->{$type}[] = $queryField . " LIKE '%" . $filter . "%'";
        }
    }

    // ~

    private final function addDateFilter($type, $filtername, $queryField) {
        $filtername = str_replace(array('_min', '_max'), '', $filtername);
        if (isset($this->filters[$filtername . '_min'])) {
            $this->{$type}[] = "DATE(" . $queryField . ") >= '" . $this->filters[$filtername . '_min'] . "'";
        }
        if (isset($this->filters[$filtername . '_max'])) {
            $this->{$type}[] = "DATE(" . $queryField . ") <= '" . $this->filters[$filtername . '_max'] . "'";
        }
    }

    // ~

    private final function addBooleanFilter($type, $filtername, $queryField) {
        if (false !== $filter = $this->getFilter($filtername)) {
            if (in_array($filter, array(0, 1))) {
                $this->{$type}[] = $queryField . " = " . $filter;
            }
        }
    }

    // ~

    public function defaultQueryFilters() {

        $this->addWhereFilter('int', 'id', 'r.id');

        $this->addWhereFilter('like', 'name', 'r.name');

        $this->addWhereFilter('date', 'created', 'r.createdAt');

        $this->addWhereFilter('date', 'edited', 'r.editedAt');

        $this->addWhereFilter('date', 'deleted', 'r.deletedAt');

        $this->addWhereFilter('boolean', 'active', 'r.active');
    }

    // ~

    public function mapIds(array $ids) {
        return array_values(array_unique(array_filter(array_map("intval", array_keys($ids)))));
    }

    // ~

    public final function findByIds(array $ids) {

        $ids = $this->mapIds($ids);

        if (!$ids) {
            return array();
        }

        $queryBuilder = $this->createQueryBuilder('q');
        $queryBuilder->select('q');
        $queryBuilder->where($queryBuilder->expr()->in('q.id', $ids));

        return $this->findByIdsResults($queryBuilder);
    }

    // ~

    protected function findByIdsResults($queryBuilder) {
        return $queryBuilder->getQuery()->getResult();
    }

}
