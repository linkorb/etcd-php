<?php

namespace LinkORB\Component\Etcd\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use LinkORB\Component\Etcd\Client as EtcdClient;


class EtcdMkCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('etcd:mk')
            ->setDescription(
                'Make a new key with a given value'
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
            )->addArgument(
                'server',
                InputArgument::OPTIONAL,
                'Base url of etcd server and the default is http://127.0.0.1:4001'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $server = $input->getArgument('server');
        $key = $input->getArgument('key');
        $value = $input->getArgument('value');
        $output->writeln("<info>Create `$key` with `$value`</info>");
        $client = new EtcdClient($server);
        $data = $client->mk($key, $value);

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        echo $json;
    }
}
