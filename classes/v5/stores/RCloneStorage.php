<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace v5\stores;

/**
 * Description of RStoreStorage
 *
 * @author arthu
 */
class RCloneStorage {

    function Exists($path) {
        
    }

    function FromFileInfo($serviceRecord, $info, $toPath) {

        $dir = \utils\ImportUtil::MakeTmpDir(true);
        $fileInfo = \utils\ImportUtil::MakeTmpFile($dir);
        $fh = $fileInfo['fh'];
        if(!$info || !isset($info['data'])) {
            return;
        }
        $fileName = $info['fileName'];

        fwrite($fh, $info['data']);
        fclose($fh);

       exec("base64 -d  {$fileInfo['fileName']} > \"./$fileName\"");

        $cmd = "rclone copy $dir/{$fileName} {$serviceRecord['mount']}:\"{$toPath}\"";
        $results = [];
        exec($cmd,$results);
        if(count($results) > 1) {
            throw new Exception($cmd."\n".implode('\n',$results));
        }
        return;
        
    }

    function FromBlob($serviceRecord, $blob, $fileName, $toPath) {
        $dir = \utils\ImportUtil::MakeTmpDir(true);

        // data:[mediatype];base64,data
        $len = strlen($blob);
        $mime = '';
        $mode = 'mime';
        $format = '';
        $startI = 0;

        $fileInfo = \utils\ImportUtil::MakeTmpFile($dir);
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
        flush();
        disable_ob();
        fclose($fh);
        passthru('base64 -d ' . $fileInfo['fileName'] > "./$fileName.tmp");
        $mount = $this->GetMountPrefix($serviceRecord);
        passthru("rclone copy ./{$fileName} {$mount}:\"{$path}\"");
        enable_ob();
        
    }

    function FromFile($serviceRecord, $fromPath, $toPath) {
        $mount = $this->GetMountPrefix($serviceRecord);
        $cmd = "rclone copy {$fromPath} {$mount}\"$toPath\"";
        ob_start();
        passthru($cmd);
        return ob_end_flush();
    }

    function ToFile($serviceRecord, $remotePath, $toPath) {
        
        $mount = $this->GetMountPrefix($serviceRecord);
        $cmd = "rclone copy {$mount}\"{$remotePath}\" \"$toPath\"";
        
        $results = [];
        exec($cmd,$results);        
          
    } 

    function List($serviceRecord, $path) {
        $mount = $this->GetMountPrefix($serviceRecord);
        $cmd = "rclone lsjson {$mount}{$path}";

        $result = `rclone lsjson {$mount}{$path}`;

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
        $mount = $this->GetMountPrefix($serviceRecord);
        if (is_null($path) || $path === '') {
            return false;
        }
        if (substr($path, -1, 1) !== '/') {
            throw new \Exception('path must end in /');
        }
        $cmd = "rclone mkdir {$mount}:{$path}";
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
        $mount = $this->GetMountPrefix($serviceRecord);
        $cmd = "rclone rmdir {$mount}:{$path}";
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
        $mount = $this->GetMountPrefix($serviceRecord);
        $cmd = "rclone deletefile {$mount}{$filePath}";
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
        $mount = $this->GetMountPrefix($serviceRecord);
        $cmd = "rclone delete {$mount}{$path}";
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

    protected function GetMountPrefix($record) {
        $mount = $record['mount'];
        if ($mount === '') {
            return '';
        } else {
            return "{$mount}:";
        }
    }

}
