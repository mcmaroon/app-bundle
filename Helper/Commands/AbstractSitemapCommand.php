<?php

namespace App\AppBundle\Helper\Commands;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

abstract class AbstractSitemapCommand extends ContainerAwareCommand {

    protected $input;
    protected $output;
    protected $container;
    protected $kernel;
    protected $doctrine;
    protected $defaultManager;
    protected $baseUrl = '';

    protected function configure() {
        $this
                ->setName('app:sitemap')
                ->setDescription('generate app sitemap')
                ->addArgument('baseUrl', InputArgument::REQUIRED, 'http://example.lan/')
        ;
    }

    // ~

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->input = $input;
        $this->output = $output;
        $this->container = $this->getContainer();
        $this->kernel = $this->container->get('kernel');
        $this->doctrine = $this->container->get('doctrine');
        $this->defaultManager = $this->doctrine->getManager();
        $this->defaultManager->getConnection()->getConfiguration()->setSQLLogger(null);

        $baseUrl = $input->getArgument('baseUrl');
        if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException(sprintf('Invalid Domain Validation baseUrl:%s', $baseUrl));
        }

        $this->baseUrl = $baseUrl;
    }

    // ~

    /**
     * @return [[
     *      'id' => ,
     *      'name' => ,
     *      'slug' => ,
     *      'priority' => default 1,
     *      'prefix' => default ''
     * ]]
     */
    abstract protected function prepareLinks();

    // ~

    protected function execute(InputInterface $input, OutputInterface $output) {
        $log = $this->container->get('app.log');
        $urls = $this->prepareLinks();
        if (count($urls)) {
            $this->generateSitemap($urls);
            $log->debug($this->getName(), ['count' => count($urls)]);
        } else {
            $log->error($this->getName(), ['count' => 0]);
        }
    }

    // ~

    protected final function generateSitemap(array $urls = []) {

        $path = $this->kernel->getRootDir() . '/../web/';

        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = true;

        $urlset = $document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'urlset');
        $document->appendChild($urlset);

        $prefix = '';
        foreach ($urls as $url) {
            if (isset($url['prefix'])) {
                $prefix = $url['prefix'];
            }
            $link = $this->baseUrl . '/' . $prefix . '/' . $url['id'] . '/' . $url['slug'];
            $lastMod = (isset($url['lastmod']) ? $url['lastmod'] : date("Y-m-d"));
            $priority = (isset($url['priority']) ? $url['priority'] : 1);
            $domUrl = $this->prepareUrl($document, $link, $lastMod, $priority);
            $urlset->appendChild($domUrl);
        }

        $document->saveXML();
        $document->save($path . 'sitemap.xml');
    }

    // ~

    protected final function prepareUrl(&$document, $link, $lastMod, $priorityNumber = 1) {
        $url = $document->createElement('url');

        $loc = $document->createElement('loc', $link);
        $url->appendChild($loc);

        $lastmod = $document->createElement('lastmod', $lastMod);
        $url->appendChild($lastmod);

        $changefreq = $document->createElement('changefreq', 'always');
        $url->appendChild($changefreq);

        $priority = $document->createElement('priority', $priorityNumber);
        $url->appendChild($priority);

        return $url;
    }

}
