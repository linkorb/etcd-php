<?php

namespace LinkORB\Component\Etcd;

use Guzzle\Http\Client as GuzzleClient;

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
        
        $data = json_decode($response->getBody());
        return $data;
    }

    public function get($key, $flags = null)
    {
        $request = $this->guzzleclient->get($this->buildKeyUri($key));
        $response = $request->send();
        $data = json_decode($response->getBody());
        return $data;
    }

    /**
     * 
     * @param type $key
     * @param type $value
     * @param type $ttl
     * @return type
     * @throws \Exception
     */
    public function mk($key, $value, $ttl = 0)
    {
        $data = $request = $this->set(
            $key,
            $value,
            $ttl,
            array('prevExist' => 'false')
        );
        
        if (isset($data->errorCode)) {
            throw new \Exception($data->message, $data->errorCode);
        }
        
        return $data;
    }

    public function update($key, $value, $ttl = 0)
    {
        $data = $this->set($key, $value, $ttl, array('prevExist' => 'true'));
        if (isset($data->errorCode)) {
            throw new \Exception($data->message, $data->errorCode);
        }
        return $data;
    }
    
    public function rm($key)
    {
        
    }
}
