<?php

use crodas\FileUtil\Path;

class PathTest extends \phpunit_framework_testcase
{
    public function testRealPath()
    {
        $this->assertTrue(chdir(__DIR__ . '/../'));
        $this->assertEquals(Path::normalize("/foo/bar/../foo.txt"), "/foo/foo.txt");
        $this->assertEquals(Path::normalize("/foo/bar/../../foo.txt"), "/foo.txt");
        $this->assertEquals(Path::normalize("/foo/bar/./foo.txt"), "/foo/bar/foo.txt");
        $this->assertEquals(Path::normalize("C:\\cesar\\rodas\\..\\foo.txt"), "C:/cesar/foo.txt");
        $this->assertEquals(Path::normalize("../phpunit.xml"), realpath(getcwd()  . "/../phpunit.xml"));
    }
    public function testPaths()
    {
        $path = Path::getRelative(__FILE__, __DIR__);
        $this->assertEquals($path, "/tests/PathTest.php");

        $path = Path::getRelative(__FILE__, __FILE__);
        $this->assertEquals($path, "/PathTest.php");

        $path = Path::getRelative(__FILE__, __DIR__ . "/../lib/crodas/Path.php");
        $this->assertEquals($path, "/../../tests/PathTest.php");
    }
}
