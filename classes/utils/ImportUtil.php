<?php
namespace utils;

use model\MongoCRUD;

class ImportUtil
{

    public static function MakeTmpDir($goto = false)
    {
        $ini = \System::GetIni();
        $directory = $ini->tempdir . md5(microtime() . mt_rand());
        mkdir($directory);
        if ($goto)
            chdir($directory);
        return $directory;
    }

    public static function MoveUploaded($whereTo = null, $goTo = false, $fileParam = 'source',$ext='zip',$urlCheck=true)
    {
        
        $ini = \System::GetIni();
        $exts = is_array($ext) ? $ext : explode(',',$ext);
        
        if(!in_array('zip',$exts)) $exts[] = 'zip';
        foreach($exts as $key=>$val) {
            $exts[$key] = $val;
        }
        
        $hadWhereTo = is_null($whereTo);
      
        if (is_null($whereTo))
            $whereTo = self::MakeTmpDir($goTo) . '/';
        
        $url = $urlCheck? ParamUtil::Get(\WAPI::GetParams(), 'fileURL') : null;
        $hasFileName = isset($_FILES[$fileParam]);
        
        if($hasFileName)$hasFileName = $_FILES[$fileParam]['name'] != '';
        
        if (!$hasFileName && ! is_null($url)) {
            list ($path, $query) = explode('?', $url);
            $path = explode('/', $path);
            $lastItem =array_pop($path);
                        
            $fileMoved = false;
            foreach($exts as $ext) {
                $fileMoved = self::SetFileNameFromURL($url, $fileParam, $ext);
                if($fileMoved) break;
                 
            }
             if(!isset($_FILES[$fileParam])) {
                if(!$hadWhereTo) rmdir($whereTo);
                throw new \Exception('File Import problem: Unable to identify file name');
            }
            
            $tempName = "sldl".MongoCRUD::NewID();
            $filePath = $ini->tempdir . $tempName;
           
             
            $_FILES[$fileParam]['error'] = 0;
            $_FILES[$fileParam]['tmp_name'] = $filePath;
            
            $url = escapeshellarg($url);
            $cmd = "curl -L -o $filePath $url";
            ob_start();
            passthru($cmd);
            
            $_FILES[$fileParam]['size'] = filesize($filePath);
            $targetName =  explode('.',$_FILES[$fileParam]['name']);
            $ext = array_pop($targetName);
            if($ext != 'zip') {
                array_push($targetName,$ext);
                
            } else  {
                array_push($targetName,'zip');
            }
            
            $targetName = implode('.',$targetName);
            
            $_FILES[$fileParam]['name'] = $targetName;
            
            $cmd = 'mv '.escapeshellarg($_FILES[$fileParam]['tmp_name']).' '.$whereTo.$targetName;
            #var_dump($cmd);
            passthru($cmd);
            ob_end_clean();
            //copy($_FILES[$fileParam]['tmp_name'],$whereTo.$targetName);
            //unlink($_FILES[$fileParam]['tmp_name']);
            return $whereTo;
            
            
        }
        
        $file = $_FILES[$fileParam]['name'];//self::SanitizeFileName($_FILES[$fileParam]['name']);
        
        move_uploaded_file($_FILES[$fileParam]['tmp_name'], $whereTo . $file);
        
        return $whereTo;
    }
    
    private static function SanitizeFileName($filename) {
        try {
            list($file,$ext) = explode('.',$filename);
        } catch(\Exception $e) {
            // do nothing
        }
        $replace="_";
        $pattern='/([[:alnum:]_\.-]*)/';
        $file=str_replace(str_split(preg_replace($pattern,$replace,$file)),$replace,$file);
        //$file = \Normalizer::normalize($file);
        return $file.'.'.$ext;
        
        
    }
    
    private static function SetFileNameFromURL($url,$fileParam,$ext) {
        list ($path, $query) = explode('?', $url);
        $path = explode('/', $path);
        $lastItem =array_pop($path);
        $foundMatch = false;
        if (substr($lastItem, - strlen($ext)) == $ext) {
            $_FILES[$fileParam]['name'] = $lastItem;
            $foundMatch = true;
        } else {
            $params = array();
            parse_str($query, $params);
            foreach ($params as $key => $val) {
                if (substr($val, -strlen($ext))==$ext) {
                    $_FILES[$fileParam]['name'] = $val;
                    $foundMatch = true;
                    break;
                }
            }
        }
        return $foundMatch;
        
    }

    public static function UnpackZip($file, $ext = '.zip', &$subDirs)
    {
        
        $directory = dirname($file).'/';
        
        if($directory != getcwd()) chdir($directory);
     
         
        
        if(!substr($file,-strlen($ext))==$ext) $ext = array_pop(explode('.',$file));
        $zip_name = basename($file, $ext);
        $unzipDir = strtolower($zip_name);
        $fullPath = $directory.$unzipDir;
        
        
        if(!file_exists($unzipDir)) {
            mkdir( $unzipDir);
            
            $command = escapeshellcmd("unzip -j -o '{$file}' -d '{$fullPath}'");
            
            shell_exec($command);
            
        }
        
        chdir( $unzipDir);
        
        $command = "rename -v 's/\ /_/g' $unzipDir/*";
        
        shell_exec($command);
        
        $subDirs[] = $unzipDir;
        
        $globPattern = "*.";
        for($i = 1; $i < strlen($ext);$i++) {
            $char = substr($ext,$i,1);
            $globPattern.='['.strtolower($char).strtoupper($char).']';            
        }
       
        // Step 3: If the uploaded zip involves multiple layers there will be zip files for each.
        // Unzip any zip files in the work folder
        foreach (glob($globPattern) as $zip) {
            self::UnpackZip($fullPath . '/' . $zip, $ext, $subDirs);
        }
        
        chdir('..');
        
        if (file_exists($file))
            unlink($file);
    }

    public static function GetTargets($paths, $pattern)
    {
        $dir = getcwd();
        
    
        
        
        $targets = array();
        
        if(!is_array($paths)) $paths = array($paths);
        foreach ($paths as $path) {
            
            if (! file_exists($path))
                continue;
            
            $isRel = stripos($path,'/') != 0;
            
            if($isRel) {
                chdir("./$path");
            } else {
                chdir($path);
            }
           
            foreach (glob($pattern) as $match) {
                
                $match = escapeshellarg($path . "/$match");
                $match = substr($match, 1, (strlen($match) - 2));
                if($isRel) {
                    $targets[] =  dirname(getcwd()).'/'.$match;
                } else {
                    $targets[] =  $match;
                }
                
            }
            
        }
        chdir($dir);
      
        return $targets;
    }

    public static function GoToTarget($target)
    {
        $target = str_replace('//','/',$target);
        
        
        $dir = dirname($target);
        
        chdir($dir);
        
        return getcwd();
    }

    public static function NewExt($file, $ext, $newExt = null)
    {
        $newExt = is_null($newExt) ? "" : ".$newExt";
        return basename($file, ".$ext") . $newExt;
    }

    public static function RenameFile($file, $ext, $newExt = null, $prefix = null, $incDir = true, $preSep = '_', $suffSep = '.')
    {
        if ($prefix == '') $prefix = null;
        $dir = dirname($file);
        $newFile = self::NewExt($file, $ext, $newExt);
        if (! is_null($prefix)) {
            $newFile = $prefix . $preSep . $newFile;
        }
        if ($incDir)
            return $dir . '/' . $newFile;
        return $newFile;
    }
    
    public static function MakeProjectionFile($path,$projection,$fileName='projection.prj') {
        file_put_contents($path.'/'.$fileName, $projection);
    }
}

?>