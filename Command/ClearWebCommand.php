<?php

namespace App\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ClearWebCommand extends ContainerAwareCommand {

    protected $output;
    protected $container;
    protected $log;

    protected function configure() {
        $this
                ->setName('app:clear:web')
                ->setDescription('clear web/uploads web/media directory images etc.')
        ;
    }

    // ~

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->output = $output;
        $this->container = $this->getContainer();
        $this->log = $this->container->get('app.log');

        // ~

        foreach (array('uploads', 'media') as $path) {

            $uploadPath = $this->container->get('kernel')->getRootDir() . '/../web/' . $path;

            $finder = new Finder();
            $files = $finder->files()->in($uploadPath);
            foreach ($files as $file) {
                try {
                    unlink($file->getRealpath());
                } catch (\Exception $exc) {
                    
                }
            }

            $directories = iterator_to_array($finder->directories()->in($uploadPath), true);
            foreach ($directories as $directory) {
                try {
                    $this->output->writeln($this->getName() . ' clear directory ' . $directory->getRealpath());
                    rmdir($directory->getRealpath());
                } catch (\Exception $exc) {
                    
                }
            }
        }
    }

}
