<?php

namespace LinkORB\Component\Etcd;

use Guzzle\Http\Client as GuzzleClient;
use LinkORB\Component\Etcd\Exception\EtcdException;
use LinkORB\Component\Etcd\Exception\KeyExistsException;
use LinkORB\Component\Etcd\Exception\KeyNotFoundException;
use RecursiveArrayIterator;
use stdClass;

class Client
{
    private $server = 'http://127.0.0.1:4001';

    private $guzzleclient;
    
    private $apiversion;

    private $root = '/';
    
    public function __construct($server = '', $version = 'v2')
    {
        
        $server = rtrim($server, '/');
        
        if ($server) {
            $this->server = $server;
        }
        
        // echo 'Testing server ' . $this->server . PHP_EOL;
         
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
     * Set the default root directory. the default is `/`
     * If the root is others e.g. /linkorb when you set new key,
     * or set dir, all of the key is under the root 
     * e.g.  
     * <code>
     *    $client->setRoot('/linkorb');
     *    $client->set('key1, 'value1'); 
     *    // the new key is /linkorb/key1
     * </code>
     * @param string $root
     * @return Client
     */
    public function setRoot($root)
    {
        if (strpos('/', $root) === false) {
            $root = '/' . $root;
        }
        $this->root = rtrim($root, '/');
        return $this;
    }

    /**
     * Build key space operations
     * @param type $key
     * @return string
     */
    private function buildKeyUri($key)
    {
        if (strpos('/', $key) === false) {
            $key = '/' . $key;
        }
        $uri = '/' . $this->apiversion . '/keys' . $this->root . $key;
        return $uri;
    }


    /**
     * Do a server request
     * @param string $uri
     * @return mixed
     */
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
     * @param array $flags
     * @return stdClass
     */
    public function get($key, $flags = null)
    {
        $request = $this->guzzleclient->get($this->buildKeyUri($key));
        $response = $request->send();
        
        $data = json_decode($response->getBody());
        if (isset($data->errorCode)) {
            throw new KeyNotFoundException($data->message, $data->errorCode);
        }
        return $data->node->value;
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
    
    /**
     * Update directory
     * @param string $key
     * @param int $ttl
     * @return mixe
     * @throws EtcdException
     */
    public function updateDir($key, $ttl)
    {
        if (!$ttl) {
            throw new EtcdException('TTL is required', 204);
        }
        
        $condition = array(
            'dir' => 'true',
            'prevExist' => 'true'
        );
        
        $request = $this->guzzleclient->put(
            $this->buildKeyUri($key),
            null,
            array(
                'ttl' => (int) $ttl
            ),
            array(
                'query' => $condition
            )
        );
        $response = $request->send();
        $body = json_decode($response->getBody());
        if (isset($body->errorCode)) {
            throw new EtcdException($body->message, $body->errorCode);
        }
        return $body;
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
            throw new EtcdException($body->message, $body->errorCode);
        }
        
        return $body;
    }
    
    /**
     * Removes the key if it is directory
     * @param string $key
     * @param boolean $recursive
     * @return mixed
     * @throws EtcdException
     */
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
        if (isset($body->errorCode)) {
            throw new EtcdException($body->message, $body->errorCode);
        }
        return $body;
    }
    
    /**
     * Retrieve a directory
     * @param string $key
     * @param boolean $recursive
     * @return mixed
     * @throws KeyNotFoundException
     */
    public function listDir($key = '/', $recursive = false)
    {
        $query = array();
        if ($recursive === true) {
            $query['recursive'] = 'true';
        }
        $request = $this->guzzleclient->get(
            $this->buildKeyUri($key),
            null,
            array(
                'query' => $query
            )
        );
        $response = $request->send();
        $body = json_decode($response->getBody(true));
        if (isset($body->errorCode)) {
            throw new KeyNotFoundException($body->message, $body->errorCode);
        }

        return $body;
    }

    /**
     * Retrieve a directories key
     * @param string $key
     * @param boolean $recursive
     * @return array
     * @throws EtcdException
     */
    public function ls($key = '/', $recursive = false)
    {
        try {
            $data = $this->listDir($key, $recursive);
        } catch (EtcdException $e) {
            throw $e;
        }
        
        $iterator = new RecursiveArrayIterator($data);
        return $this->traversalDir($iterator);
    }

    private $dirs = array();
    
    /**
     * Traversal the directory to get the keys.
     * @param RecursiveArrayIterator $iterator
     * @return array
     */
    private function traversalDir(RecursiveArrayIterator $iterator)
    {
        while ($iterator->valid()) {
            if ($iterator->hasChildren()) {
                $this->traversalDir($iterator->getChildren());
            } else {
                if ($iterator->key() == 'key' && ($iterator->current() != '/')) {
                    $this->dirs[] = $iterator->current();
                }
            }
            $iterator->next();
        }
        return $this->dirs;
    }
}
