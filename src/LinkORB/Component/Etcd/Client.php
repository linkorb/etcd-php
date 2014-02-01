<?php

namespace LinkORB\Component\Etcd;
use Guzzle\Http\Client as GuzzleClient;

class Client
{
    private $guzzleclient;

    public function __construct($server, $version = 'v2')
    {

        $server = rtrim($server, '/');
        echo "Testing server '$server'\n";

        $this->guzzleclient = new GuzzleClient($server);
       
    }

    public function doRequest($uri)
    {
        $request = $this->guzzleclient->get('/v2' . $uri);
        $response = $request->send();
        $data = json_decode($response->getBody());
        return $data;
    }

    public function set($key, $value, $ttl = null, $condition = array())
    {
        $data = array('value' => $value);
        $request = $this->guzzleclient->put('/v2/keys' . $key, null, $data);
        $response = $request->send();
        $data = json_decode($response->getBody());
        return $data;
    }

    public function get($key, $flags = null)
    {
        //$data = array('value' => $value);
        $request = $this->guzzleclient->get('/v2/keys' . $key);
        $response = $request->send();
        $data = json_decode($response->getBody());
        return $data;
    }


    public function mk($key, $value)
    {

    }

    public function update($key, $value)
    {

    }
}