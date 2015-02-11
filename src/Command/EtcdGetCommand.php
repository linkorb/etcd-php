<?php

namespace LinkORB\Component\Etcd\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use LinkORB\Component\Etcd\Client as EtcdClient;

class EtcdGetCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('etcd:get')
            ->setDescription(
                'Get a key'
            )
            ->addArgument(
                'key',
                InputArgument::REQUIRED,
                'Key to set'
            )->addArgument(
                'server',
                InputArgument::OPTIONAL,
                'Base url of etcd server',
                'http://127.0.0.1:4001'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $server = $input->getArgument('server');
        $key = $input->getArgument('key');
        echo "Getting `$key` on `$server`\n";
        $client = new EtcdClient($server);
        $data = $client->get($key);
        
        $output->writeln($data);
        
        /*
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        echo $json;
        */
    }
}
