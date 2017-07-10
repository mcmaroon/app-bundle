<?php

namespace App\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use App\AppBundle\Helper\CacheTxt;

class CrawlCityCommand extends ContainerAwareCommand {

    const PERFORMANCE_FLUSH_LIMIT = 100;

    protected $output;
    protected $container;
    protected $log;
    protected $doctrine;
    protected $defaultManager;
    protected $items = array();
    private $postalCode = null;

    protected function configure() {
        $this
                ->setName('app:crawl:city')
                ->setDescription('crawl https://pl.wikipedia.org/wiki/Miasta_w_Polsce_%28statystyki%29')
        ;
    }

    // ~

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->output = $output;
        $this->container = $this->getContainer();
        $this->log = $this->container->get('app.log');
        $this->doctrine = $this->container->get('doctrine');
        $this->defaultManager = $this->doctrine->getManager();
        $this->crawl();
    }

    // ~

    /**
     * @link https://github.com/guzzle/guzzle
     */
    private final function crawlUrl($url) {      
        $client = new \GuzzleHttp\Client([
            'verify' => false,
        ]);
        $response = $client->request('GET', $url);         
        $crawler = new Crawler(null, $url);
        $crawler->addContent($response->getBody()->getContents());
        return $crawler;     
    }

    // ~

    private final function crawlCity($url) {
        $this->postalCode = null;
        $crawler = $this->crawlUrl($url);
        $crawler->filter('.infobox tr')->each(function ($node_tr, $key) {
            $children = $node_tr->children();
            if ($children->text() === 'Kod pocztowy') {
                preg_match_all('/[0-9]{2}[-][0-9]{3}/', $children->eq(1)->text(), $postalCodes);
                if (isset($postalCodes[0][0])) {
                    $this->postalCode = $postalCodes[0][0];
                }
            }
        });
        return $this->postalCode;
    }

    // ~

    private function crawl() {

        $ct = new CacheTxt($this->container->get('kernel')->getLogDir() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR, 'city');
        $cache = $ct->getCache($this->getName());

        if ($cache === null) {

            $this->output->writeln($this->getName() . ' - load from crawler');

            $crawler = $this->crawlUrl('https://pl.wikipedia.org/wiki/Miasta_w_Polsce_%28statystyki%29');            
            
            $crawler->filter('.wikitable tr')->each(function ($node_tr, $key) {
                if ($key > 1) {
                    $children = $node_tr->children();
                    $item = array(
                        'city' => $children->first()->text(),
                        'district' => $children->eq(1)->text(),
                        'province' => $children->eq(2)->text(),
                        'amount' => $children->eq(4)->text()
                    );

                    $address = rawurlencode(htmlentities($item['city'] . ',+' . $item['province']));
                    $url = sprintf("https://maps.googleapis.com/maps/api/geocode/json?address=%s", $address);
                    $geoResult = json_decode(file_get_contents($url));
                    if ($geoResult->status === "OK") {
                        $item['lat'] = (float) $geoResult->results[0]->geometry->location->lat;
                        $item['lng'] = (float) $geoResult->results[0]->geometry->location->lng;
                        if (isset($geoResult->results[0]->address_components)) {
                            $address_components_length = count($geoResult->results[0]->address_components) - 1;
                            if (in_array('postal_code', $geoResult->results[0]->address_components[$address_components_length]->types)) {
                                $item['postal'] = $geoResult->results[0]->address_components[$address_components_length]->long_name;
                            }
                        }
                    }

                    if (!isset($item['postal'])) {
                        $url = $children->first()->filter('a')->link()->getUri();
                        if ($url) {
                            $postalCode = $this->crawlCity($url);
                            if ($postalCode) {
                                $item['postal'] = $postalCode;
                            }
                        }
                    }

                    array_push($this->items, $item);
                }
            });
            $ct->setCache($this->getName(), $this->items);
        }

        if ($cache) {
            $this->output->writeln($this->getName() . ' - load from cache');
        }
    }

}
