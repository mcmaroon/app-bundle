<?php

namespace App\AppBundle\Helper\Commands;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ratchet\App;
use App\AppBundle\Socket\Chat;

abstract class AbstractChatCommand extends ContainerAwareCommand {

    protected $input;
    protected $output;
    protected $container;
    protected $doctrine;
    protected $defaultManager;
    protected $progress;
    protected $limit;

    protected function configure() {
        $this
                ->setName('app:websocket:chat')
                ->setDescription('websocket chat server run')
        ;
    }

    // ~

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->input = $input;
        $this->output = $output;
        $this->container = $this->getContainer();
        $this->doctrine = $this->container->get('doctrine');
        $this->defaultManager = $this->doctrine->getManager();
        $this->defaultManager->getConnection()->getConfiguration()->setSQLLogger(null);

        $this->output->writeln('Running app:websocket:chat ... ');
        
        $chat = new Chat($this->container, $this->output);
        
        /**
         * http://stackoverflow.com/questions/17529657/how-to-use-properly-websockets-in-symfony2
         * http://socketo.me/docs/hello-world
         */        
        
        //$server = IoServer::factory(new HttpServer(new WsServer($chat)), 8080);
        //$server->run();

        /**
         * http://ourcodeworld.com/articles/read/242/creating-an-agnostic-realtime-chat-with-php-using-sockets-in-symfony-3
         */        
        $app = new App('wedding.lan', 8080, '127.0.0.1');
        $app->route('/chat', $chat);
        $app->run();
    }

}
