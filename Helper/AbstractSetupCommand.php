<?php

namespace App\AppBundle\Helper;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\AppBundle\Helper\CacheTxt;

abstract class AbstractSetupCommand extends ContainerAwareCommand implements InterfaceSetupCommand {

    use \App\AppBundle\Helper\Traits\ClearInputTrait;

    const PERFORMANCE_FLUSH_LIMIT = 100;

    protected $commands = array(
        'cache:clear' => array(),
        'doctrine:schema:drop' => array(
            '--force' => true
        ),
        'doctrine:schema:update' => array(
            '--force' => true
        )
    );
    protected $methods = array();
    private $methodsIterator = 0;
    protected $input;
    protected $output;    
    protected $container;
    protected $log;
    protected $doctrine;
    protected $defaultManager;
    protected $progress;
    protected $limit;
    protected $cache;
    protected $removeCacheKey;

    protected function configure() {
        $this
                ->setName('app:setup')
                ->setDescription('install app examples')
                ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'number', 100)
                ->addOption('removeCacheKey', null, InputOption::VALUE_OPTIONAL, 'cacheKey')
        ;
    }

    // ~

    protected function appendExecute() {
        
    }

    /**
     * @param type $url to crawl
     * @return $crawler
     */
    protected final function crawlUrl($url) {
        $client = new \Goutte\Client();
        $guzzleClient = new \GuzzleHttp\Client(array(
            'verify' => false,
        ));
        $client->setClient($guzzleClient);
        $crawler = $client->request('GET', $url);
        if ($client->getResponse()->getStatus() !== 200) {
            return null;
        }
        return $crawler;
    }

    // ~

    protected function uploadFile($filePath)
    {
        if (\file_exists($filePath) && \is_file($filePath)) {
            $filePathMd = '_copy' . \md5(\rand(1, 1000000));
            $splFile = new \SplFileObject($filePath);
            $splFileCopyPath = \str_replace('.' . $splFile->getExtension(), $filePathMd . '.' . $splFile->getExtension(), $splFile->getPathname());
            \copy($splFile->getPathname(), $splFileCopyPath);
            return new UploadedFile($splFileCopyPath, \basename($filePath), null, null, null, true);
        }
    }
    
    // ~

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->input = $input;
        $this->output = $output;
        $this->container = $this->getContainer();
        $this->log = $this->container->get('app.log');
        $this->doctrine = $this->container->get('doctrine');
        $this->defaultManager = $this->doctrine->getManager();
        $this->defaultManager->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->limit = (int) $input->getOption('limit');
        $this->removeCacheKey = $input->getOption('removeCacheKey');

        $this->cache = new CacheTxt($this->container->get('kernel')->getLogDir() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR, 'setup');

        if ($this->removeCacheKey && $this->cache->removeCache($this->removeCacheKey)) {
            $this->output->writeln('RemoveCacheKey: ' . $this->removeCacheKey);
        }

        if ($this->limit < 10) {
            throw new \InvalidArgumentException("Limit paramter must be a number larger than 10");
        }

        $this->appendExecute();

        $this->output->writeln('Running setup ... ');

        $stopwatch = new Stopwatch();
        $event = $stopwatch->start('fetchMethods');

        /**
         * http://symfony.com/doc/current/components/console/introduction.html#calling-an-existing-command
         */
        foreach ($this->commands as $key => $value) {
            $command = $this->getApplication()->find($key);
            $this->input = new ArrayInput(array_merge(array('command' => $key), $value));
            $command->run($this->input, $this->output);
        }

        $this->output->writeln('');

        foreach (get_class_methods($this) as $class_method) {
            if (strpos($class_method, 'load') !== false && strpos($class_method, 'Data') !== false) {
                $this->methods[] = $class_method;
            }
        }

        //$this->progress = new ProgressBar($this->output, count($this->methods) - 1);
        //$this->progress->setFormat('debug');
        //$this->progress->start();

        $this->fetchMethods();

        //$this->progress->finish();

        $event->stop();
        $this->output->writeln('');
        $this->output->writeln('Setup complete ... ');
        $this->output->writeln(' Time executed: ' . gmdate("H:i:s", ($event->getDuration() / 1000)));
        $this->output->writeln('');
    }

    // ~
    
    protected function iterateMethod($methodName){
        $this->methodsIterator++;
        $this->output->writeln($this->methodsIterator . '/' . count($this->methods) . ' ' . $methodName);
    }


    // ~
    
    
    /**
     * @example
     * public function fetchMethods() {
     *   foreach ($this->methods as $method) {
     *       call_user_func(array(get_class(), $method));
     *       $this->progress->advance();
     *   }
     * }
     */
    abstract public function fetchMethods();
}
