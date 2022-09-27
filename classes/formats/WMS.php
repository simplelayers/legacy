<?php
namespace formats;

use utils\ParamUtil;
class WMS
extends LayerFormat{
	public  $inputTemplate = 'import/wms1.tpl';
	public  $reimportTemplate = 'import/wms1.tpl';
	public function __construct(){
		$this->label = 'WMS Format';
	}
	
	public static function GetSubDataSets($args) {
	    $url = ParamUtil::Get($args,'url');
	    $url = 'WMS:'.$url;
	    
	    $cmd = <<<CMD
gdalinfo "$url" | grep SUBDATASET_    
CMD;
	    
	    ob_start();
	    passthru($cmd);
	    $result = explode("\n",trim(ob_get_clean())); 
	    
	    if(!(count($result) % 2 === 0)) return array();
	    $subdata = array(); 
	    $numResults = count($result);
	    $z = 1;
	    for($ctr=0;$ctr < $numResults;$ctr+=2) {
	        
	        $suburl = explode('=',trim($result[$ctr]));
	        array_shift($suburl);
	        //var_dump($suburl);
	        $suburl = trim(implode('=',$suburl));
	        
	        $name = explode("=",trim($result[$ctr+1]));
	        array_shift($name);
	        $name = trim(implode('=',$name));
	        $subdata[] = array('z'=>$z,'name'=>$name,'url'=>$suburl);
	        $z++;
	    }
	    return $subdata;
	    
	}
	
	public static function WriteXML($url,$layerId) {
	    $ini = \System::GetIni();
	    $fileName = $ini->mapfiledir.'mapfile-'.$ini->name.'-layer-'.$layerId.'-wms.xml';
	    
	    if(file_exists($fileName)) unlink($fileName);
	    $url = escapeshellarg($url);
	    $cmd = <<<CMD
gdal_translate $url $fileName -of WMS
CMD;
	   ob_start();
	   passthru($cmd);
	   ob_end_clean();
	   return $fileName;
	}
    
	public static function WriteMapFile($layerId,$xmlFile,$title) {
	    $ini = \System::GetIni();
	    $xmlFile = explode("/",$xmlFile);
	    $xmlFile = array_pop($xmlFile);
	    $relPath = 'mapfile-'.$ini->name.'-layer-'.$layerId.'-wms.map';
	    $fileName = $ini->mapfiledir.$relPath;
	    if(file_exists($fileName)) unlink($fileName);
	    
	    $mapfile = <<<MAPFILE
MAP
INCLUDE "/mnt/wms/include-mapfile_header.map"
WEB
  INCLUDE "/mnt/wms/include-web_block.map"
END
#######################################

LAYER
METADATA
 "wms_title" "$title"
 "wms_srs" "EPSG:4326"
 "wms_server_version" "1.3"
 "wms_enable_request" "wms"
END
CLASS
STYLE
#ANTIALIAS TRUE
END
END

DATA "user_data/$xmlFile"
NAME "$layerId"
PROJECTION
  "init=epsg:4326"
END
STATUS ON
TYPE RASTER
UNITS METERS
END

END

MAPFILE;
	    file_put_contents($fileName,$mapfile);
	  $url = explode('?',$ini->sl_wms_basepath);
	  $url = array_shift($url);
	  //"http://10.133.103.4/cgi-bin/mapserv?map=/mnt/wms/mapquest_sat.map&Layers=osm&VERSION=1.1.1&SERVICE=WMS&REQUEST=GetMap&SRS=4326"
	  $url.='?map=/mnt/wms/data/user_data/'.$relPath."&Layers=$layerId&VERSION=1.1.1&SERVICE=WMS&REQUEST=GetMap&SRS=4326";
	  return $url;
	}
	
	public function Import($args,$world=null, \Person $user=null )
    {
        if(is_null($world)) $world = \System::Get();
        if(is_null($user)) $user = \SimpleSession::Get()->GetUser();   
		/* @var $user \Person */
		// sfoo: old dispatcher for import shp incl $template var too
		// Process the importwms1 form
		//$user = $args['user'];
		
		// sanitize the name
		$desiredname = $_REQUEST['name'];
		// create the Layer object we'll be populating		
		// creation is easy!
		if ($user->community) {
			print javascriptalert('You cannot create WMS layers with a community account.');
			return print redirect('layer.list');
		}
		$layerexist = isset($_REQUEST['layerid']);
		if($layerexist==true){
			$layer = $user->getLayerById($_REQUEST['layerid']);
			$desiredname = $layer->name;
		}else{
			$layer = $user->createLayer($desiredname,\LayerTypes::WMS,false);
		}

		if(!$layer) {
			print javascriptalert('there was a problem creating your layer');
			return print redirect('layer.list');
		}
		$url = $layer->url;
		// populate metadata with wms capabilities doc
		$getCapabilitiesURL=$_REQUEST['getCapURL'];
		$convert=New \Convert();
		$layer->metadata=$convert->xmlToPhp(file_get_contents($getCapabilitiesURL)); 
		
		//create array and populate the custom data field in the Layers table
		$getMapURL=$_REQUEST['getMapURL'];
		$bboxParam=$_REQUEST['bboxparam'];
		$wmsInfo = array();
		$wmsInfo[get_map]=$getMapURL;
		$wmsInfo[get_capabilities]=$getCapabilitiesURL;
		$wmsInfo[bbox_param]=$bboxParam;
		$layer->custom_data=$wmsInfo;
		
		// send them to the editing view for their new creation
		return print redirect("layer.edit1&id={$layer->id}");	
	}
}?>
