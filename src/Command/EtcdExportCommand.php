<?php

namespace LinkORB\Component\Etcd\Command;

use LinkORB\Component\Etcd\Client as EtcdClient;
use LinkORB\Component\Etcd\DirectoryExporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class EtcdExportCommand extends Command
{
    const PATH_SEPARATOR = '/';

    const FORMAT_JSON = 'json';
    const FORMAT_YAML = 'yaml';
    const FORMAT_DOTENV = 'dotenv';

    protected function configure()
    {
        $this
            ->setName('etcd:export')
            ->setDescription(
                'Export a directory'
            )
            ->addArgument(
                'key',
                InputArgument::REQUIRED,
                'Dir to export'
            )->addOption(
                'output',
                'o',
                InputOption::VALUE_REQUIRED,
                'json file to save dump'
            )->addOption(
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                'json file to save dump',
                self::FORMAT_JSON
            )->addOption(
                'server',
                's',
                InputOption::VALUE_REQUIRED,
                'Base url of etcd server',
                'http://127.0.0.1:2379'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $key = $input->getArgument('key');
        $server = $input->getOption('server');

        $client = new EtcdClient($server);
        $dirExporter = new DirectoryExporter($client);

        switch ($input->getOption('format')) {
            case self::FORMAT_JSON:
                $result = $this->createJson($dirExporter, $key);
                break;
            case self::FORMAT_YAML:
                $result = $this->createYaml($dirExporter, $key);
                break;
            case self::FORMAT_DOTENV:
                $result = $this->createDotEnv($dirExporter, $key);
                break;
            default:
                throw new \RuntimeException('Unknown format: ' . $input->getOption('format'));
        }

        $file = $input->getOption('output');
        if (!is_null($file)) {
            $fs = new Filesystem();
            $fs->dumpFile($file, $result . PHP_EOL);
        } else {
            $output->writeln($result);
        }
    }


    private function createJson(DirectoryExporter $directoryExporter, $key)
    {
        $data = $directoryExporter->exportArray($key);
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }


    private function createYaml(DirectoryExporter $directoryExporter, $key)
    {
        $data = $directoryExporter->exportArray($key);
        return Yaml::dump($data);
    }


    private function createDotEnv(DirectoryExporter $directoryExporter, $key)
    {
        $data = $directoryExporter->exportKeyValuePairs($key, true);
        $result = [];
        foreach ($data as $k => $v) {
            $result[] = strtoupper(str_replace("/", "_", $k)) . '=' . $v;
        }
        return implode(PHP_EOL, $result);
    }
}
