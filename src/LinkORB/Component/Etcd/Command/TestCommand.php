<?php

namespace LinkORB\Component\Etcd\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('server:test')
            ->setDescription(
                'Do a server test'
            )
            ->addArgument(
                'server',
                InputArgument::REQUIRED,
                'Base url of etcd server'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $server = $input->getArgument('server');
        echo "Testing server '$server'\n";
    }
}
