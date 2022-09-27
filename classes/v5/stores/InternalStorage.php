<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace v5\stores;

/**
 * Description of InternalStorage
 *
 * @author arthu
 */
class InternalStorage {

    function FromFileInfo($serviceRecord, $info, $toPath) {
        $dir = \utils\ImportUtil::MakeTmpDir(true);
        var_dump($dir);
        $fileInfo = \utils\ImportUtil::MakeTmpFile($dir);
        $fh = $fileInfo['fh'];
        $fileName = $info['fileName'];
        fwrite($fh, $info['data']);
        fclose($fh);
        passthru("base64 -d  {$fileInfo['fileName']} > \"./$fileName\"");
        passthru("rclone copy ./{$fileName} {$serviceRecord['mount']}:\"{$toPath}\"");
    }

    function FromBlob($serviceRecord, $blob, $fileName, $toPath) {
        $dir = \utils\ImportUtil::MakeTmpDir(true);
        $len = strlen($blob);
        $mime = '';
        $mode = 'mime';
        $format = '';
        $startI = 0;
        $fileInfo = \utils\ImportUtil::MakeTmpFile($path);
        die();
        $fh = $fileInfo['fh'];

        for ($i = 5; $i < $len; $i++) {
            switch ($mode) {
                case 'mime':
                    if ($blob[$i] !== ':') {
                        $mime .= $blob[$i];
                    } else {
                        $mode = 'format';
                    }
                    continue 2;
                case 'format':
                    if ($blob[$i] !== ',') {
                        $format .= $blob[$i];
                    } else {
                        $startI = $i + 1;
                        $mode = 'data';
                    }
                    continue 2;
                case 'data':
                    fwrite($fh, $blob[$i]);
                    continue 2;
            }
        }
        fclose($fh);
        passthru('base64 -d ' . $fileInfo['fileName'] > "./$fileName");
        passthru("rclone copy ./{$fileName} {$serviceRecord['mount']}:\"{$path}\"");
    }

    function FromFile($serviceRecord, $fromPath, $toPath) {

        $cmd = "rclone copy {$fromPath} {$serviceRecord['mount']}:\"$toPath\"";
        ob_start();
        passthru($cmd);
        return ob_end_flush();
    }

    function ToFile($serviceRecord, $remotePath, $toPath) {
        $cmd = "rclone copy {$fromPath} {$serviceRecord['mount']}:\"$toPath\"";
        ob_start();
        $result = passthru($cmd);
        return ob_end_result();
    }

    function List($serviceRecord, $path) {
        $cmd = "rclone lsjson {$serviceRecord['mount']}:{$path}";

        $result = `rclone lsjson {$serviceRecord['mount']}:{$path}`;

        if (!is_string($result)) {
            return false;
        }
        $list = json_decode($result, true);
        if (is_array($list)) {
            return $list;
        } else {
            return false;
        }
    }

    function MkDir($serviceRecord, $path) {
        if (is_null($path) || $path === '') {
            return false;
        }
        if (substr($path, -1, 1) !== '/') {
            throw new \Exception('path must end in /');
        }
        $cmd = "rclone mkdir {$serviceRecord['mount']}:{$path}";
        ob_start();
        passthru($cmd);
        return ob_end_flush();
    }

    function RmDir($serviceRecord, $path) {
        if (is_null($path) || $path === '') {
            return false;
        }
        if (substr($path, -1, 1) !== '/') {
            throw new \Exception('path must end in /');
        }
        $result = $this->RemoveAt($serviceRecord, $path);
        if ($result === false) {
            return $result;
        }
        $cmd = "rclone rmdir {$serviceRecord['mount']}:{$path}";
        ob_start();
        passthru($cmd);
        return ob_end_flush();
    }

    function Remove($serviceRecord, $filePath) {
        if (is_null($filePath) || $filePath === '') {
            return false;
        }
        if (substr($filePath, -1, 1) === '/') {
            throw new \Excpetion('filePath must not be a directory: consider using RemoteAt instead');
        }
        $cmd = "rclone deletefile {$serviceRecord['mount']}:{$filePath}";
        ob_start();
        passthru($cmd);
        return ob_end_flush();
    }

    function RemoveAt($serviceRecord, $path) {
        if (is_null($path) || $path === '') {
            return false;
        }
        if (substr($path, -1, 1) !== '/') {
            throw new Exception('path must end in /');
        }
        $cmd = "rclone delete {$serviceRecord['mount']}:{$path}";
        ob_start();
        passthru($cmd);
        $result = ob_end_flush();
        return $result;
    }

    function Find($startPath, $what) {
        
    }

    function IsDirectory($lsjsonEntry) {
        return ($lsjsonEntry['IsDir'] === 'true');
    }

}
