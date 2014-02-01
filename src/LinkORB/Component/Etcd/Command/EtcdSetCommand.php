<?php

namespace LinkORB\Component\Etcd\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use LinkORB\Component\Etcd\Client as EtcdClient;


class EtcdSetCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('etcd:set')
            ->setDescription(
                'Set a key'
            )
            ->addArgument(
                'server',
                InputArgument::REQUIRED,
                'Base url of etcd server'
            )
            ->addArgument(
                'key',
                InputArgument::REQUIRED,
                'Key to set'
            )
            ->addArgument(
                'value',
                InputArgument::REQUIRED,
                'Value to set'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $server = $input->getArgument('server');
        $key = $input->getArgument('key');
        $value = $input->getArgument('value');
        echo "Setting `$key` to `$value`\n";
        $client = new EtcdClient($server);
        $data = $client->set($key, $value);

        $json = json_encode($data, JSON_PRETTY_PRINT);
        echo $json;



    }
}
