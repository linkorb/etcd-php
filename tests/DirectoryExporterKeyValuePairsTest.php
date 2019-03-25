<?php

namespace LinkORB\Tests\Component\Etcd;

use GuzzleHttp\Exception\ClientException;
use LinkORB\Component\Etcd\DirectoryExporter;

class DirectoryExporterKeyValuePairsTest extends BaseTest
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
        $this->getDirectoryExporter()->exportKeyValuePairs(__FUNCTION__);
    }


    public function testReturnsAllLevelsWithRecursive()
    {
        $res = $this->getDirectoryExporter()->exportKeyValuePairs('/', true);
        $this->assertEquals($this->getExpectedFullTree(), $res);
    }


    public function testReturnsOnlyFilesWithNotRecursive()
    {
        $res = $this->getDirectoryExporter()->exportKeyValuePairs('/', false);
        $this->assertEquals($this->getRootFiles(), $res);
    }


    public function testReturnsOnlyFilesWithNotRecursiveInSubDir()
    {
        $res = $this->getDirectoryExporter()->exportKeyValuePairs('/dir/sub2/', false);
        $this->assertEquals(
            [
                'f1' => 'vs2_1',
                'f2' => 'vs2_2',
            ],
            $res
        );
    }


    public function testReturnsOnlyFilesWithRecursiveInSubDirWithoutSubDir()
    {
        $res = $this->getDirectoryExporter()->exportKeyValuePairs('/dir/sub2/', true);
        $this->assertEquals(
            [
                'f1' => 'vs2_1',
                'f2' => 'vs2_2',
            ],
            $res
        );
    }


    public function testGetProperty()
    {
        $result = $this->getDirectoryExporter()->exportKeyValuePairs('/dir/sub2/f1');
        $this->assertEquals(
            [
                'f1' => 'vs2_1',
            ],
            $result
        );
    }
}
