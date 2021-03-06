<?php
/*
  +---------------------------------------------------------------------------------+
  | Copyright (c) 2016 César Rodas                                                  |
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

/**
 *  Cache Class file
 *
 *  This class is a wrapper class that would make
 *  easier the task of making object lives among requets (make
 *  them persistent).
 *  
 *  The class is serialized to disk and constructed properly
 *  the next time.
 */
class Cache
{
    protected $file;
    protected $object;
    protected $content = false;
    protected $is_listening = false;

    protected static $dir = '';
    protected static $includes = array();

    public static function setDirectory($dir)
    {
        self::$dir = $dir;
    }

    public function __construct($object, $file = '')
    {
        if (empty($file)) {
            $file = File::generateFilepath('class_cache', is_string($object) ? $object : get_class($object));
        } else {
            if (self::$dir == '') {
                self::$dir = sys_get_temp_dir() . '/php-cache-';
            }
        }

        $this->file   = $file[0] == '/'  || $file[0] == '.' ? $file : self::$dir . $file;
        $this->object = is_string($object) ? new $object : $object;
    }

    public function __call($method, $args)
    {
        return $this->run($method, $args);
    }

    public function __destruct()
    {
        if ($this->is_listening) {
            File::dumpArray($this->file, $this->content);
        }
    }

    public function run($method, Array $args)
    {
        if ($this->content === false) {
            if (empty(self::$includes[$this->file])) {
                self::$includes[$this->file] = (array)@ include $this->file ;
            }
            $this->content = & self::$includes[$this->file];
        }

        $name = serialize($args);

        if (empty($this->content[$method][$name])) {
            $this->content[$method][$name] = call_user_func_array(array($this->object, $method), $args);
            $this->is_listening = true;
        }

        return $this->content[$method][$name];
    }
}
