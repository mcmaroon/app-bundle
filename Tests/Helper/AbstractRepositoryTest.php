<?php

namespace App\AppBundle\Tests\Helper;

use App\AppBundle\Helper\AbstractTest;
use App\AppBundle\Helper\AbstractRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

class AbstractRepositoryTest extends AbstractTest {

    private $ar = null;

    function __construct() {
        parent::__construct();
        $this->ar = new AbstractRepositoryExtender($this->em, new ClassMetadata('BasePage'));
        $this->ar->setFilters(array(
            '' => '1',
            null => 'a',
            'a' => null,
            'id' => '1,2,3 ',
            'name' => 'sample'
        ));
    }

    // ~

    public function testGetFilters() {
        $this->assertCount(2, $this->ar->getFilters());
    }

    // ~

    public function testGetFilter() {
        $this->assertEquals('1,2,3', $this->ar->getFilter('id'));
    }

    // ~

    public function testGetFiltersCacheKey() {
        $this->assertEquals('1,2,3-sample', $this->ar->getFiltersCacheKey());
    }

    // ~

    public function testAddWhereFilter() {
        $this->ar->addWhereFilter('int', 'id', 'r.id');
        $this->ar->addWhereFilter('like', 'name', 'r.name');
        $this->assertCount(2, $this->ar->getWhere());
    }

    // ~

    public function testGetList() {
        $this->assertInstanceOf(\Doctrine\ORM\QueryBuilder::class, $this->ar->getList());
    }

    // ~

    public function testfindByIds() {

        $ids = array(
            '1,2' => array(1, 2, 3),
            '11,22,33' => array(11 => '1', 22 => '2', 33 => '3'),
        );

        foreach ($ids as $key => $values) {
            $this->assertEquals($key, implode(',', $this->ar->mapIds($values)));
            $this->assertInstanceOf(\Doctrine\ORM\Query::class, $this->ar->findByIds($values));
        }
    }

}

class AbstractRepositoryExtender extends AbstractRepository {

    protected function findByIdsResults($queryBuilder) {
        return $queryBuilder->getQuery();
    }

}
