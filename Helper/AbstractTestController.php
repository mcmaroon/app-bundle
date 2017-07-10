<?php

namespace App\AppBundle\Helper;

use App\AppBundle\Helper\AbstractTest;
use App\AppBundle\Helper\AbstractControllerInterface;

abstract class AbstractTestController extends AbstractTest implements AbstractControllerInterface {

    const FORM_FIELD_NAME_VALUE = 'phpunittest';

    protected $entityName = null;

    function __construct() {
        parent::__construct();
        $this->entityName = $this->getClassShortName();
    }

    // ~

    public final function getClassShortName() {
        $reflect = new \ReflectionClass($this);
        $name = str_replace('Controller', '', $reflect->getShortName());
        return str_replace(array('Admin', 'Test'), '', $name);
    }

    // ~

    protected function skip() {
        $this->markTestSkipped('Skipped test.');
    }

    // ~

    protected function incomplete() {
        $this->markTestIncomplete('Incomplete test.');
    }

    // ~

    public function testCreateAction() {

        $client = self::createClient();
        $url = $client->getContainer()->get('router')->generate(strtolower($this->entityName) . '_new');

        $crawler = $this->client->request('GET', $this->domain . $url);

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $buttonCrawlerNode = $crawler->selectButton(strtolower($this->entityName) . '[submit]');

        $form = $buttonCrawlerNode->form(array(
            strtolower($this->entityName) . '[name]' => self::FORM_FIELD_NAME_VALUE,
        ));

        $this->client->submit($form);
    }

    // ~

    public function testIndexAction() {

        $client = self::createClient();
        $url = $client->getContainer()->get('router')->generate(strtolower($this->entityName));

        $crawler = $this->client->request('GET', $this->domain . $url);

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $this->assertGreaterThan(0, $crawler->filter('#container form tbody tr')->count());
    }

    // ~

    public function testDeleteAction() {

        $this->em->getFilters()->disable('softdeleteable');
        $repository = $this->em->getRepository($this->getControllerBundleName() . ':' . $this->entityName);

        $entity = $repository->findOneByName(self::FORM_FIELD_NAME_VALUE);

        if ($entity !== null) {

            if (!method_exists($entity, 'setDeletedAt')) {
                $client = self::createClient();
                $url = $client->getContainer()->get('router')->generate(strtolower($this->entityName) . '_edit', array('id' => $entity->getId()));

                $crawler = $this->client->request('GET', $this->domain . $url);

                $this->assertTrue($this->client->getResponse()->isSuccessful());

                $link = $crawler->filter('.form-buttons-wrapper .btn-danger')->link();

                $this->client->click($link);
            } else {
                foreach ($this->em->getEventManager()->getListeners() as $eventName => $listeners) {
                    foreach ($listeners as $listener) {
                        if ($listener instanceof \Gedmo\SoftDeleteable\SoftDeleteableListener) {
                            $this->em->getEventManager()->removeEventListener($eventName, $listener);
                        }
                    }
                }

                $this->em->remove($entity);
                $this->em->flush();
            }

            $entities = $repository->findByName(self::FORM_FIELD_NAME_VALUE);

            $this->assertEquals(0, count($entities));
        } else {
            $this->assertTrue(false);
        }
    }

}
