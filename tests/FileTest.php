<?php

use crodas\FileUtil\File;

class FileTest extends \phpunit_framework_testcase
{
    public function testDumpArray()
    {
        $this->assertFalse(is_file('foobar.php'));
        $data = array('foo' => rand());
        File::dumpArray("foobar.php", $data);
        $this->assertTrue(is_file('foobar.php'));
        $this->assertEquals($data, require 'foobar.php');
        unlink('foobar.php');
    }

    public function testWrite()
    {
        $this->assertFalse(is_file('foobar.php'));
        File::write("foobar.php", "foobar");
        $this->assertTrue(is_file('foobar.php'));
        unlink('foobar.php');
    }

    public function testWriteInclude()
    {
        $this->assertFalse(is_file('foobarxx.php'));
        $this->AssertEquals(2, File::writeAndInclude("foobarxxx.php", "<?php return 2;"));
        $this->AssertEquals(3, File::writeAndInclude("foobarxxx.php", "<?php return 3;"));
        $this->AssertEquals(4, File::writeAndInclude("foobarxxx.php", "<?php return 4;"));
        unlink('foobarxxx.php');
    }



    public function testFilePath()
    {
        $path = File::generateFilepath('activemongo');

        $this->assertTrue(file_Exists($path));
        $this->assertTrue(preg_match("@" . DIRECTORY_SEPARATOR . "activemongo" . DIRECTORY_SEPARATOR . "@", $path) > 0);
        $this->assertTrue(is_writable($path));

        $this->assertEquals($path, File::generateFilepath('activemongo'));
        $this->assertNotEquals($path, File::generateFilepath('activemongo', 'mongo://'));
    }

    /**
     *  @expectedException RuntimeException
     */
    public function testFilePathException()
    {
        File::generateFilepath();
    }
}

