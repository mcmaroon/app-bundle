<?php

namespace App\AppBundle\Tests\Helper;

use App\AppBundle\Helper\AbstractTest;
use App\AppBundle\Helper\AbstractController;
use Doctrine\ORM\Mapping\ClassMetadata;

class AbstractControllerTest extends AbstractTest {

    private $ac = null;

    function __construct() {
        parent::__construct();
        $this->ac = new AbstractControllerExtender();
    }

    // ~

    public function testInstanceOf() {
        $this->assertInstanceOf(AbstractController::class, $this->ac);
    }

    // ~

    public function testGetClassShortName() {
        $this->assertEquals('AbstractExtender', $this->ac->getClassShortName());
    }

    // ~

    public function testGetViewPath() {
        $this->assertEquals('AbstractTestBundle:AbstractExtender', $this->invokeMethod($this->ac, 'getViewPath'));
    }

    // ~

    public function testGetIndexCacheKey() {
        $this->assertEquals('abstracttestbundle-abstractextender', $this->invokeMethod($this->ac, 'getIndexCacheKey'));
    }

}

class AbstractControllerExtender extends AbstractController {

    public function getControllerBundleName() {
        return 'AbstractTestBundle';
    }

    public function getControllerEntity() {
        return null;
    }

    public function getControllerFormType() {
        return null;
    }

}
