<?php
/*
  +---------------------------------------------------------------------------------+
  | Copyright (c) 2013 César Rodas                                                  |
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

class Path
{
    public static function normalize($path)
    {
        $rpath  = realpath($path);
        if ($rpath) {
            return $rpath;
        }
        $path  = str_replace("\\", "/", trim($path));
        preg_match("@([A-Z]+:)?/+@i", $path, $scheme);
        $scheme = $scheme[0];
        if (!$scheme) {
            $path = getcwd() . '/' . $path;
        } else {
            $path = substr($path, strlen($scheme));
        }
        $parts = array_values(array_filter(explode("/", $path)));
        $new   = array();
        foreach ($parts as $id => $value) {
            switch ($value) {
            case '..':
                array_pop($new);
            case '.':
                break;
            default:
                $new[] = $value;
            }
        }

        return $scheme . implode("/", $new);
    }

    public static function getRelative($dir1, $dir2=NULL, $win = false)
    {
        if (empty($dir2)) {
            $dir2 = getcwd();
        }

        $slash = DIRECTORY_SEPARATOR;
        if ($win) {
            $slash = "\\";
        }

        $file = basename($dir1);
        $dir1 = trim(self::normalize(dirname($dir1)), $slash);
        $dir2 = trim(self::normalize(dirname($dir2)), $slash);

        if ($slash == '\\') {
            // F*cking windows ;-)
            if (strncasecmp($dir1, $dir2, 2) != 0) {
                // There is no relative path
                return $dir1;
            }
            $dir1 = substr($dir1, 2);
            $dir2 = substr($dir2, 2);
        }

        $to   = explode($slash, $dir1);
        $from = explode($slash, $dir2);

        $realPath = $to;

        foreach ($from as $depth => $dir) {
            if(isset($to[$depth]) && $dir === $to[$depth]) {
                array_shift($realPath);
            } else {
                $remaining = count($from) - $depth;
                if($remaining) {
                    // add traversals up to first matching dir
                    $padLength = (count($realPath) + $remaining) * -1;
                    $realPath  = array_pad($realPath, $padLength, '..');
                    break;
                }
            }
        }

        $rpath = implode($slash, $realPath);
        if ($rpath && $rpath[0] != $slash) {
            $rpath = $slash . $rpath;
        }
        
        if ($file) {
            $rpath .= $slash . $file;
        }

        if (DIRECTORY_SEPARATOR == "\\") {
            $rpath = addslashes($rpath);
        }

        return $rpath;
    }
}
