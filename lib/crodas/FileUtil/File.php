<?php
/*
  +---------------------------------------------------------------------------------+
  | Copyright (c) 2014 César Rodas                                                  |
  +---------------------------------------------------------------------------------+
  | Redistribution and use in source and binary forms, with or without              |
  | modification, are permitted provided that the following conditions are met:     |
  | 1. Redistributions of source code must retain the above copyright               |
  |    notice, this list of conditions and the following disclaimer.                |
  |                                                                                 |
  | 2. Redistributions in binary form must reproduce the above copyright            |
  |    notice, this list of conditions and the following disclaimer in the          |
  |    documentation and/or other materials provided with the distribution.         |
  |                                                                                 |
  | 3. All advertising materials mentioning features or use of this software        |
  |    must display the following acknowledgement:                                  |
  |    This product includes software developed by César D. Rodas.                  |
  |                                                                                 |
  | 4. Neither the name of the César D. Rodas nor the                               |
  |    names of its contributors may be used to endorse or promote products         |
  |    derived from this software without specific prior written permission.        |
  |                                                                                 |
  | THIS SOFTWARE IS PROVIDED BY CÉSAR D. RODAS ''AS IS'' AND ANY                   |
  | EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED       |
  | WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE          |
  | DISCLAIMED. IN NO EVENT SHALL CÉSAR D. RODAS BE LIABLE FOR ANY                  |
  | DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES      |
  | (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;    |
  | LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND     |
  | ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT      |
  | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS   |
  | SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE                     |
  +---------------------------------------------------------------------------------+
  | Authors: César Rodas <crodas@php.net>                                           |
  +---------------------------------------------------------------------------------+
*/

namespace crodas\FileUtil;

function dump_array(Array $data, $short = null)
{
    $short = $short === null ? version_compare(File::$minPHP ?: PHP_VERSION, '5.4.0', '>=') : $short;
    $php   = $short ? "[" : "array(";
    foreach ($data as $key => $value) {
        $php .= var_export($key, true) . "=>";
        if (is_array($value)) {
            $php .= dump_array($value, $short);
        } else if(is_float($value)) {
            $php .= number_format($value, 2);
        } else {
            $php .= var_export($value, true);
        }
        $php .= ",";
    }
    if (substr($php,-1) == ',') {
        $php = substr($php, 0, -1);
    } 
    $php .= $short ? "]": ")";
    return $php;
}

class File
{
    public static $minPHP;
    protected static $gen;

    /**
     *  Dump array into a file. Similar to *var_dump* but the result
     *  is not human readable (reduces space by a third in large arrays)
     */
    public static function dumpArray($path, Array $data, $perm = 0644)
    {
        self::write($path, "<?php return " . dump_array($data) . ';', $perm);
    }

    public static function write($path, $content, $perm = 0644)
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                throw new \RuntimeException("Cannot create directory {$dir}");
            }
        }

        $tmp = tempnam($dir, "crodas_file_");
        if (file_put_contents($tmp, $content) === false) {
            throw new \RuntimeException("Failed to write temporary file ({$tmp})");
        }

        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);

        if (is_file($path)) {
            if (!unlink($path)) {
                throw new \RuntimeException("Failed to remove old file");
            }
        }

        if (!rename($tmp, $path)) {
            throw new \RuntimeException("Failed to move temporary file");
        }

        if (is_int($perm)) {
            chmod($path, $perm);
        }

        if (is_callable('opcache_invalidate')) {
            opcache_invalidate($path, true);
        } else if (is_callable('apc_clear_cache')) {
            apc_clear_cache();
        }

        return true;
    }

    /**
     *  Given a few identifier (1 or more) it would return 
     *  a filepath that can be used consistently among requets
     *  and it is safely stored in the temp directory of the OS 
     *
     *  @return string
     */
    public static function generateFilepath()
    {
        $args = func_get_args();

        if (empty($args)) {
            throw new \RuntimeException("You would need to give us at least one identifier");
        }

        $gen = self::$gen;
        $dir = $gen($args[0]);

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                throw new \RuntimeException("cannot create directory $dir");
            }
        }

        return $dir . sha1(implode("\0", $args));
    }

    public static function overrideFilepathGenerator($fnc)
    {
        if (!is_callable($fnc)) {
            throw new \InvalidArgumentException("Expecting a callable");
        }

        self::$gen = $fnc;
    }
}

File::overrideFilepathGenerator(function($prefix) {
    return sys_get_temp_dir() . DIRECTORY_SEPARATOR . $prefix . DIRECTORY_SEPARATOR;
});
