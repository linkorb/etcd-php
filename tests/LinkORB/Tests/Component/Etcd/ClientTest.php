<?php

namespace LinkORB\Tests\Component\Etcd;

use LinkORB\Component\Etcd\Client;
use PHPUnit_Framework_TestCase;

class ClientTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    protected $client;
   
    private $dirname = '/phpunit_test';

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

    /**
     * @covers LinkORB\Component\Etcd\Client::doRequest
     */
    public function testDoRequest()
    {
        $version = $this->client->doRequest('/version');
        $this->assertArrayHasKey('etcdserver', json_decode($version, true));
    }

    /**
     * @covers LinkORB\Component\Etcd\Client::set
     */
    public function testSet()
    {
        $this->client->set('/testset', 'setvalue');
        $value = $this->client->get('/testset');
        $this->assertEquals('setvalue', $value);
        
        // test ttl
        $ttl = 10;
        $b = $this->client->set('testttl', 'ttlvalue', $ttl);
        $node = $this->client->getNode('testttl');
        $this->assertLessThanOrEqual($ttl, $node['ttl']);
    }

    /**
     * @covers LinkORB\Component\Etcd\Client::get
     */
    public function testGet()
    {
        $this->client->set('/testgetvalue', 'getvalue');
        $value = $this->client->get('/testgetvalue');
        $this->assertEquals('getvalue', $value);
    }

    /**
     * @covers LinkORB\Component\Etcd\Client::mk
     * @expectedException \LinkORB\Component\Etcd\Exception\KeyExistsException
     */
    public function testMk()
    {
        $this->client->mk('testmk', 'mkvalue');
        $this->assertEquals('mkvalue', $this->client->get('testmk'));
        $this->client->mk('testmk', 'mkvalue');
    }

    /**
     * @covers LinkORB\Component\Etcd\Client::mkdir
     * @expectedException \LinkORB\Component\Etcd\Exception\KeyExistsException
     */
    public function testMkdir()
    {
        $this->client->mkdir('testmkdir');
        $this->client->mkdir('testmkdir');
    }

    /**
     * @covers LinkORB\Component\Etcd\Client::update
     * @expectedException \LinkORB\Component\Etcd\Exception\KeyNotFoundException
     */
    public function testUpdate()
    {
        $key = '/testupdate_key';
        $value1 = 'value1';
        $value2 = 'value2';
        $this->client->update($key, $value1);
        
        $this->client->set($key, $value2);
        $value = $this->client->get($key);
        $this->assertEquals('value2', $value);
    }
        

    /**
     * @covers LinkORB\Component\Etcd\Client::updatedir
     */
    public function testUpdatedir()
    {
        $dirname = '/test_updatedir';
        $this->client->mkdir($dirname);
        $this->client->updateDir($dirname, 10);
        $dir = $this->client->listDir($dirname);
        $this->assertLessThanOrEqual(10, $dir['node']['ttl']);
    }

    /**
     * @covers LinkORB\Component\Etcd\Client::rm
     * @expectedException \LinkORB\Component\Etcd\Exception\EtcdException
     */
    public function testRm()
    {
        $this->client->rm('/rmkey');
    }

    /**
     * @covers LinkORB\Component\Etcd\Client::rmdir
     * @expectedException \LinkORB\Component\Etcd\Exception\EtcdException
     */
    public function testRmdir()
    {
        $this->client->mkdir('testrmdir');
        $this->client->rmdir('testrmdir', true);
        $this->client->rmdir('testrmdir');
    }

    /**
     * @covers LinkORB\Component\Etcd\Client::listDir
     */
    public function testListDir()
    {
        $data = $this->client->listDir();
        $this->assertEquals($this->dirname, $data['node']['key']);
        $this->assertTrue($data['node']['dir'] == 1);
    }

    /**
     * @covers LinkORB\Component\Etcd\Client::ls
     */
    public function testLs()
    {
        $dirs = $this->client->ls();
        $this->assertTrue(in_array($this->dirname, $dirs));
    }
    
    /**
     * @covers LinkORB\Component\Etcd\Client::getKeysValue
     */
    
    public function testGetKeysValue()
    {
        $this->client->set('/a/aa', 'a_a');
        $this->client->set('/a/ab', 'a_b');
        $this->client->set('/a/b/ab', 'aa_b');
        
        $values = $this->client->getKeysValue('/', false);
        $this->assertFalse(isset($values[$this->dirname . '/a/aa']));
        
        $values = $this->client->getKeysValue();
        $this->assertTrue(isset($values[$this->dirname . '/a/aa']));
        $this->assertEquals('a_a', $values[$this->dirname . '/a/aa']);
        $this->assertTrue(in_array('aa_b', $values));
    }
    
    public function testGetNode()
    {
        $key = 'node_key';
        $setdata = $this->client->set($key, 'node_value');
        $node = $this->client->getNode($key);
        $this->assertJsonStringEqualsJsonString(json_encode($node), json_encode($setdata['node']));
    }
}
