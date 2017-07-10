<?php

namespace App\AppBundle\Socket;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {

    protected $container;
    protected $output;
    protected $log;
    protected $clients;

    public function __construct(ContainerInterface $container, OutputInterface $output = null) {
        $this->container = $container;
        $this->output = $output;
        $this->log = $this->container->get('app.log');
        $this->clients = new \SplObjectStorage;
    }

    private final function output($msg) {
        if ($this->output) {
            $this->output->writeln($msg);
        }
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        $this->output('onOpen resourceId: ' . $conn->resourceId);
        $this->log->debug('Chat:onOpen', [
            'conn' => $conn->resourceId
        ]);
    }

    public function onMessage(ConnectionInterface $conn, $msg) {
        $countClients = count($this->clients) - 1;

        $this->output('onMessage resourceId: ' . $conn->resourceId . ' countClients: ' . $countClients . ' message: ' . $msg);
        $this->log->debug('Chat:onMessage', [
            'conn' => $conn->resourceId,
            'msg' => $msg
        ]);

        foreach ($this->clients as $client) {
            if ($conn !== $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        $this->output('onClose resourceId: ' . $conn->resourceId);
        $this->log->debug('Chat:onClose', [
            'conn' => $conn->resourceId
        ]);
    }

    public function onError(ConnectionInterface $conn, \Exception $exc) {
        $this->output('onError exception: ' . $exc->getMessage());
        $this->log->debug('Chat:onError', [
            'conn' => $conn->resourceId,
            'exc' => [
                'code' => $exc->getCode(),
                'message' => $exc->getMessage(),
            ]
        ]);
        $conn->close();
    }

}
