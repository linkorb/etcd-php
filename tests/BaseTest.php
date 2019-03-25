<?php

namespace LinkORB\Tests\Component\Etcd;

use LinkORB\Component\Etcd\Client;
use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{
    /** @var Client */
    protected $client;

    protected $dirname = '/phpunit_test';


    protected function setUp()
    {
        $this->client = new Client();
        $this->client->mkdir($this->dirname);
        $this->client->setRoot($this->dirname);
    }

    protected function tearDown()
    {
        $this->client->setRoot('/');
        $this->client->rmdir($this->dirname, true);
    }
}
