<?php

namespace LinkORB\Component\Etcd\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use LinkORB\Component\Etcd\Client as EtcdClient;

class EtcdRequestCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('etcd:request')
            ->setDescription(
                'Do a server request'
            )
            ->addArgument(
                'server',
                InputArgument::REQUIRED,
                'Base url of etcd server'
            )
            ->addArgument(
                'uri',
                InputArgument::REQUIRED,
                'Uri to request'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $server = $input->getArgument('server');
        $uri = $input->getArgument('uri');
        $client = new EtcdClient($server);
        $data = $client->doRequest($uri);
        $output->writeln($data);
    }
}
