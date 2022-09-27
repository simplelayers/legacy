<?php
namespace model\media;

use utils\ParamUtil;
class FeatureImage
{
    
    public static function CreateImage($fileInfo,$layerId,$featureId,$field) {
        
        $ini = \System::GetIni();
        $type = ParamUtil::Get($fileInfo,'type','image/jpg');
        $name = ParamUtil::Get($fileInfo,'name');
        $tmpFile = ParamUtil::Get($fileInfo,'tmp_name');
        if(!file_exists($tmpFile)) return null;
        $path = $ini->media_path.'/features/';
        if(!file_exists($path)) {
            mkdir($path);        
        }
        if(!file_exists($path.$layerId)) { mkdir($path.$layerId); }
        move_uploaded_file($tmpFile, $path.$layerId.'/'.$featureId);
        $layer = \Layer::GetLayer($layerId,true);
        
        $type = str_replace('/','_',$type);
        $layer->updateRecordById($featureId, array($field=>"feature_img://name:$name/type:$type/layerId:$layerId/featureId:$featureId/field:$field"));
       
        return file_exists($path.$layerId.'/'.$featureId);
    }
    
    
    public static function GetImage($layerId,$featureId,$field,$asDownload=false) {
        $ini = \System::GetIni();
        
        $layer = \Layer::GetLayer($layerId);
        $record = $layer->getRecordById($featureId);
        if(!$record) return null;
        $url = $record[$field];
        
        if(!substr($url,0,14)=='feature_img://') {
            header("ContentType: image/png");
            readfile(BASEDIR.'media/images.empty.png');
            die();
        }
        $url = substr($url,14);
        $url = explode('/',$url);
        $path = $ini->media_path."features/$layerId/$featureId";
        
        
        if(!file_exists($path)) {
            header("ContentType: image/png");
            readfile(BASEDIR.'media/images/empty.png');
            die();            
        }
        
        $urlParams = array();
        foreach($url as $keyVal) {
            $keyVals = explode(':',$keyVal);
            if(count($keyVals) == 0) continue;
            if(count($keyVals)==1) continue;
            list($key,$val)=$keyVals;
            $urlParams[$key] = $val;
        }
        
        $urlParams['type'] = str_replace('_','/',$urlParams['type']);
        
        header("Content-Type: ".$urlParams['type']);
        
         if($asDownload) {
             header("Content-Disposition: attachment; filename=\"".basename($urlParams['name'])."\";" );
                                       
         }
         header("Content-Length: ".filesize($path));
        readfile($path);
        
        
        
    }
    
}

?>