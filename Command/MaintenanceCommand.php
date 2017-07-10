<?php

namespace App\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

// ~

class MaintenanceCommand extends ContainerAwareCommand {

    protected $input;
    protected $output;
    protected $container;
    protected $kernel;

    protected function configure() {
        $this
                ->setName('app:maintenance')
                ->setDescription('maintenance mode on/off')
                ->addArgument('status', InputArgument::REQUIRED, 'on/off')
        ;
    }

    // ~

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->input = $input;
        $this->output = $output;
        $this->container = $this->getContainer();
        $this->kernel = $this->container->get('kernel');

        // ~

        $status = $input->getArgument('status');
        $status = strtolower($status);
        if (!in_array($status, ['on', 'off'])) {
            throw new \InvalidArgumentException(sprintf('Argument status:%s must be "on" or "off"', $status));
        }

        // ~

        $path = realpath($this->kernel->getRootDir() . '/..');
        $finder = new Finder();
        $fs = new Filesystem();
        $directories = \iterator_to_array($finder->depth(0)->directories()->in($path), true);
        foreach ($directories as $directory) {
            $pos = strpos($directory->getFilename(), 'web');
            if ($pos !== false) {
                $file = $directory->getRealpath() . '/maintenance';
                if ($fs->exists($file) && $status == 'off') {
                    $fs->remove($file);
                    $this->output->writeln('Maintenance mode on ' . $directory->getFilename() . ' ' . $status);
                }

                if (!$fs->exists($file) && $status == 'on') {
                    \file_put_contents($file, '');
                    $this->output->writeln('Maintenance mode on ' . $directory->getFilename() . ' ' . $status);
                }
            }
        }
    }

}
