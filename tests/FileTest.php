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
}

