<?php

namespace LinkORB\Component\Etcd;

use Guzzle\Http\Client as GuzzleClient;
use LinkORB\Component\Etcd\Exception\KeyExistsException;
use LinkORB\Component\Etcd\Exception\KeyNotFoundException;
use stdClass;

class Client
{
    private $server = 'http://127.0.0.1:4001';

    private $guzzleclient;
    
    private $apiversion;

    public function __construct($server = '', $version = 'v2')
    {
        
        $server = rtrim($server, '/');
        
        if ($server) {
            $this->server = $server;
        }
        
        echo 'Testing server ' . $this->server . PHP_EOL;
         
        $this->apiversion = $version;
        $this->guzzleclient = new GuzzleClient(
            $this->server,
            array(
                'request.options' => array(
                    'exceptions' => false
                )
            )
        );
    }

    /**
     * Build key space operations
     * @param type $key
     * @return string
     */
    private function buildKeyUri($key)
    {
        $uri = '/' . $this->apiversion . '/keys' . $key;
        return $uri;
    }


    public function doRequest($uri)
    {
        $request = $this->guzzleclient->get($uri);
        $response = $request->send();
        $data = $response->getBody(true);
        return $data;
    }

    /**
     * Set the value of a key
     * @param string $key
     * @param string $value
     * @param int $ttl
     * @param array $condition
     * @return stdClass
     */
    public function set($key, $value, $ttl = null, $condition = array())
    {
        $data = array('value' => $value);
        
        if ($ttl) {
            $data['ttl'] = $ttl;
        }
        
        $request = $this->guzzleclient->put($this->buildKeyUri($key), null, $data, array(
            'query' => $condition
        ));
        $response = $request->send();
        $body = json_decode($response->getBody());
        return $body;
    }

    /**
     * Retrieve the value of a key
     * @param string $key
     * @param type $flags
     * @return stdClass
     */
    public function get($key, $flags = null)
    {
        $request = $this->guzzleclient->get($this->buildKeyUri($key));
        $response = $request->send();
        $data = json_decode($response->getBody());
        return $data;
    }

    /**
     * make a new key with a given value
     * 
     * @param string $key
     * @param string $value
     * @param int $ttl
     * @return array|stdClass
     * @throws KeyExistsException
     */
    public function mk($key, $value, $ttl = 0)
    {
        $body = $request = $this->set(
            $key,
            $value,
            $ttl,
            array('prevExist' => 'false')
        );
        
        if (isset($body->errorCode)) {
            throw new KeyExistsException($body->message, $body->errorCode);
        }
        
        return $body;
    }

    /**
     * make a new directory
     * 
     * @param string $key
     * @param int $ttl
     * @return stdClass
     * @throws KeyExistsException
     */
    public function mkdir($key, $ttl = 0)
    {
        $data = array('dir' => 'true');
        
        if ($ttl) {
            $data['ttl'] = $ttl;
        }
        $request = $this->guzzleclient->put(
            $this->buildKeyUri($key),
            null,
            $data,
            array(
                'query' => array('prevExist' => 'false')
            )
        );
        
        $response = $request->send();
        $body = json_decode($response->getBody());
        if (isset($body->errorCode)) {
            throw new KeyExistsException($body->message, $body->errorCode);
        }
        return $body;
    }


    /**
     * Update an existing key with a given value.
     * @param strint $key
     * @param string $value
     * @param int $ttl
     * @return stdClass
     * @throws KeyNotFoundException
     */
    public function update($key, $value, $ttl = 0)
    {
        $body = $this->set($key, $value, $ttl, array('prevExist' => 'true'));
        if (isset($body->errorCode)) {
            throw new KeyNotFoundException($body->message, $body->errorCode);
        }
        return $body;
    }
    
    public function updatedir()
    {
        // TODO:
    }


    /**
     * remove a key
     * @param string $key
     * @return array|stdClass
     * @throws KeyNotFoundException
     */
    public function rm($key)
    {
        $request = $this->guzzleclient->delete($this->buildKeyUri($key));
        $response = $request->send();
        $body = json_decode($response->getBody());
        
        if (isset($body->errorCode)) {
            throw new KeyNotFoundException($body->message, $body->errorCode);
        }
        
        return $body;
    }
    
    public function rmdir($key, $recursive = false)
    {
        $query = array('dir' => 'true');
        
        if ($recursive === true) {
            $query['recursive'] = 'true';
        }
        $request = $this->guzzleclient->delete(
            $this->buildKeyUri($key),
            null,
            null,
            array(
                'query' => $query
            )
        );
        $response = $request->send();
        $body = json_decode($response->getBody());
        return $body;
    }
}
