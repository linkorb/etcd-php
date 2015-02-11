<?php

namespace LinkORB\Component\Etcd\Command;

use LinkORB\Component\Etcd\Client as EtcdClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EtcdWatchCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('etcd:watch')
            ->setDescription(
                'Watch a key for changes'
            )
            ->addArgument(
                'key',
                InputArgument::REQUIRED,
                'Key to set'
            )
            ->addArgument(
                'server',
                InputArgument::OPTIONAL,
                'Base url of etcd server and the default is http://127.0.0.1:4001'
            )->addOption(
                'recursive',
                null,
                InputOption::VALUE_NONE
            )->addOption(
                'after-index',
                null,
                InputOption::VALUE_OPTIONAL,
                'watch after the given index',
                0
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $recursive = $input->getOption('recursive');
        $server = $input->getArgument('server');
        $key = $input->getArgument('key');
        $afterIndex = $input->getOption('after-index');
        $output->writeln("<info>Watching key `$key`</info>");
        $client = new EtcdClient($server);
        
        $query = array('wait' => 'true');
        
        if ($recursive) {
            $query['recursive'] = 'true';
        }
        
        if ($afterIndex) {
            $query['waitIndex'] = $afterIndex;
        }
        
        $data = $client->get($key, $query);
        $output->writeln($data);
    }
}
