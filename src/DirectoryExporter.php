<?php

namespace LinkORB\Component\Etcd;

class DirectoryExporter
{
    const PATH_SEPARATOR = '/';

    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }


    public function exportArray($directory)
    {
        $lsResult = $this->client->listDir($directory, true);
        $rootPath = $lsResult['node']['key'];

        $kvList = $this->createKeyValuePairs($lsResult);

        $result = [];
        if (count($kvList) === 1 && array_keys($kvList)[0] === $rootPath) {
            // $dir is property
            $parts = explode(self::PATH_SEPARATOR, $rootPath);
            $result[$parts[count($parts) - 1]] = $kvList[$rootPath];
        } else {
            foreach ($kvList as $k => $v) {
                $realKey = substr($k, strlen($rootPath));
                $parts = explode(self::PATH_SEPARATOR, $realKey);
                array_shift($parts); // remove first empty element
                $this->addToDepth($result, $parts, $v);
            }
        }
        return $result;
    }


    public function exportKeyValuePairs($dir, $recursive = true)
    {
        $lsResult = $this->client->listDir($dir, $recursive);
        $rootPath = $lsResult['node']['key'];

        $kvList = $this->createKeyValuePairs($lsResult);

        $result = [];
        if (count($kvList) === 1 && array_keys($kvList)[0] === $rootPath) {
            // $dir is property
            $parts = explode(self::PATH_SEPARATOR, $rootPath);
            $result[$parts[count($parts) - 1]] = $kvList[$rootPath];
        } else {
            foreach ($kvList as $key => $value) {
                $result[substr($key, strlen($rootPath) + 1)] = $value;
            }
        }
        return $result;
    }


    private function createKeyValuePairs($lsResult)
    {
        $result = [];
        $iterator = new \RecursiveArrayIterator($lsResult);
        $this->traverse($result, $iterator);
        ksort($result);
        return $result;
    }


    private function traverse(&$values, \RecursiveArrayIterator $iterator)
    {
        while ($iterator->valid()) {
            if ($iterator->hasChildren()) {
                $this->traverse($values, $iterator->getChildren());
            } else {
                $currentLevel = $iterator->getArrayCopy();
                if (array_key_exists('key', $currentLevel) && array_key_exists('value', $currentLevel)) {
                    $values[$currentLevel['key']] = $currentLevel['value'];
                    return;
                }
            }
            $iterator->next();
        }
    }


    private function addToDepth(&$array, $path, $value)
    {
        if (1 === count($path)) {
            $array[current($path)] = $value;
        } else {
            $current = array_shift($path);
            if (!array_key_exists($current, $array)) {
                $array[$current] = [];
            }
            $this->addToDepth($array[$current], $path, $value);
        }
    }
}
