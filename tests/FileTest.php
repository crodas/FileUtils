<?php

use crodas\FileUtil\File;

class FileTest extends \phpunit_framework_testcase
{
    public function testWrite()
    {
        $this->assertFalse(is_file('foobar.php'));
        File::write("foobar.php", "foobar");
        $this->assertTrue(is_file('foobar.php'));
        unlink('foobar.php');
    }
}

