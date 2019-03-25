<?php

namespace LinkORB\Tests\Component\Etcd;

use GuzzleHttp\Exception\ClientException;
use LinkORB\Component\Etcd\DirectoryExporter;

class DirectoryExporterArrayTest extends BaseTest
{
    use FixtureTrait;

    protected function setUp()
    {
        parent::setUp();
        $this->prepareFixture($this->client);
    }

    private function getDirectoryExporter()
    {
        return new DirectoryExporter($this->client);
    }


    public function testFailOnNotExistingKey()
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(404);
        $this->getDirectoryExporter()->exportArray(__FUNCTION__);
    }


    public function testRootDir()
    {
        $res = $this->getDirectoryExporter()->exportArray('/');
        $this->assertEquals($this->getExpectedFullTreeAsArray(), $res);
    }


    public function testDir()
    {
        $res = $this->getDirectoryExporter()->exportArray('/dir');
        $this->assertEquals($this->getExpectedDirAsArray(), $res);
    }


    public function testSubDir()
    {
        $res = $this->getDirectoryExporter()->exportArray('/dir/sub2/');
        $this->assertEquals(
            [
                'f1' => 'vs2_1',
                'f2' => 'vs2_2',
            ],
            $res
        );
    }


    public function testProperty()
    {
        $result = $this->getDirectoryExporter()->exportArray('/dir/sub2/f1');
        $this->assertEquals(
            [
                'f1' => 'vs2_1',
            ],
            $result
        );
    }
}
