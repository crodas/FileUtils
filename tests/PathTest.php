<?php

use crodas\FileUtil\Path;

class PathTest extends \phpunit_framework_testcase
{
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
