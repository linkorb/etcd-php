<?php

namespace LinkORB\Component\Etcd\Command;

use LinkORB\Component\Etcd\Client as EtcdClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EtcdRmdirCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('etcd:rmdir')
            ->setDescription(
                'Removes the key if it is an empty directory or a key-value pair'
            )
            ->addArgument(
                'key',
                InputArgument::REQUIRED,
                'Key to remove'
            )
            ->addArgument(
                'server',
                InputArgument::OPTIONAL,
                'Base url of etcd server and the default is http://127.0.0.1:4001'
            )
            ->addOption(
                'recursive',
                null,
                InputOption::VALUE_NONE,
                'To delete a directory that holds keys'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $server = $input->getArgument('server');
        $key = $input->getArgument('key');
        $recursive = $input->getOption('recursive');
        $output->writeln("<info>Removing key `$key`</info>");
        $client = new EtcdClient($server);
        $data = $client->rmdir($key, $recursive);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        echo $json;
    }
}
