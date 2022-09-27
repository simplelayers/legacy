<?php
namespace formats;

use utils\ParamUtil;
use utils\ImportUtil;
use model\reporting\ImportReport;
class Raster extends LayerFormat
{

    public $inputTemplate = 'import/raster1.tpl';

    public $reimportTemplate = 'import/raster1.tpl';

    public function __construct()
    {
        $this->label = 'Raster Format';
    }

    public function Import($args, $world = null, \Person $user = null)
    {
       
        // Step 1 Gather Params
        if (is_null($world))
            $world = \System::Get();
        if (is_null($user))
            $user = \SimpleSession::Get()->GetUser();
        $ini = \System::GetIni();
        
        $specifiedProjection = ParamUtil::Get($args,'projection',"init=epsg:4326");
        $imageParam = "image";
        $worldParam = "world_file";

        $layerId = ParamUtil::Get($args,'layerid');
        if($layerId=="") $layerId = null;
        
        $layerExists = !is_null($layerId);
        
        $name = ParamUtil::Get($args,'name');
         
        $baseName = $_FILES[$imageParam]['name'];
        $baseNameParts = explode('.',$baseName);
        $ext = array_pop($baseNameParts);
        $fileName = implode($baseNameParts);
        
        // Step 2: Create a working temp folder
        $tempDir = ImportUtil::MoveUploaded(null,true,$imageParam,null,false);
        
        // Step 3: Move uploaded files to the working folder
        ImportUtil::MoveUploaded($tempDir,false,$worldParam,null,false);
        //$projFile = ImportUtil::MakeProjectionFile($tempDir, $projection);
        
        //Step 4: Create a projection (proj) file, attempt to use gdalsrsinfo to output the info in proj4 format.
        //        If gdalsrsinfo fails, use specified (or default) projection 
        $projFile = $fileName.'.prj';
        $cmd = "gdalsrsinfo -o proj4 $baseName > ".$projFile;
        
        //Step 5: Convert 
        
        
        ob_start();
            passthru($cmd);
            $result = ob_get_clean();
        ob_end_clean();
        try {
            $projection = file_get_contents($tempDir.'/'.$projFile);
            $projection = substr($projection,1,strlen($projection)-3);
            file_put_contents($projFile, $projection);
        } catch(\Exception $e) {
            ImportUtil::MakeProjectionFile($$tempDir,$specifiedProjection,$projFile);
        }
        
        $cmd = "gdalinfo -stats ".$tempDir.'/'.$baseName;
        ob_start();
        passthru($cmd);
        $metadata = ob_get_clean();
        $metatdata = str_replace($tempDir,'',$metadata);
        ob_end_clean();
        $bbox = $this->GetBBoxFromGDMetadta($metadata);
        
        $metadataXML =  "<metadata src='gdal'><info><![CDATA[".$metadata."]]></info><proj>{$projection}</proj><?metadata>";
        
        $layer = null;
        if($layerExists) {
            $layer = \Layer::GetLayer($layerId);
            unlink($layer->url);
            // the following line refreshes the layer details page
            $layer->name = $layer->name;
        } else {
            $layer = $user->createLayer($name, \LayerTypes::RASTER);
        }
        $layerId = $layer->id;
        $layer->bbox = $bbox;
        $layer->metadata = $metadataXML;
        
        $target = $layer->url;
        

        // call gdalwarp to reproject the image into latlong and GeoTIFF format, in its final resting place
        // ping("<br/>\nReprojecting<br/>\n");
        $command = escapeshellcmd("gdalwarp -r bilinear -s_srs \"{$projection}\" -t_srs \"EPSG:4326\" {$baseName} {$target}");
        ob_start();
        passthru($command);
        ob_end_clean();
        
        // call gdaladdo to add overviews on the image in-place
        // ping("<br/>\nAdding overviews<br/>\n");
        //var_dump($command);
        $command = escapeshellcmd("gdaladdo -r average {$target} 2 4 8 16 32 64 128 256 512 1024");
        ob_start();
        passthru($command);
        ob_end_clean();

        
        $command = escapeshellcmd("zip ".$layer->id.'_orig.zip *');
        //var_dump($command);
        ob_start();
        passthru($command);
        ob_end_clean();

        
        $command = "cp ".$layer->id.'_orig.zip '.dirname($target).'/'.$layer->id.'_orig.zip';
        //var_dump($command);
        ob_start();
        passthru($command);
        ob_end_clean();

        
        //var_dump($target);
        $this->importReport = new ImportReport();
        $report = array();
        $report['status'] = 'ok';
        $report['metadata'] = $metadata;
        $report['projection'] = $projection;
        $info['layer'] = array();
        $info['layer_type'] = 'raster';
        $ifno['stats'] = array();
        $info['layer'] = $layer->name;
        $report['info'] = $info;
        $this->importReport->AddLayerReport($layer->id,$report);
        
        $report = $this->importReport->CreateImportReport();
        return $report;
        //var_dump($report);
        die();
        
        return;
        
        
        $extension = substr($_FILES['source']['name'], strrpos($_FILES['source']['name'], '.') + 1);
        
        // move the four files into a temporary location. This also creates a .prj file if one was not supplied.
        // ping("Handling uploaded file<br/>\n");
        
        $extension = substr($_FILES['source']['name'], strrpos($_FILES['source']['name'], '.') + 1);
        $tempid = md5(microtime() . mt_rand());
        $tempdir = $ini->tempdir;
        $temp_image = "{$tempdir}/{$tempid}.{$extension}";
        $temp_world = "{$tempdir}/{$tempid}.wld";
        $temp_prj = "{$tempdir}/{$tempid}.prj";
        move_uploaded_file($image['tmp_name'], $temp_image);
        move_uploaded_file($world['tmp_name'], $temp_world);
        if (! $_REQUEST['projection'])
            $_REQUEST['projection'] = 'init=epsg:4326';
        file_put_contents($temp_prj, $_REQUEST['projection']);
        
        // ultimately, we'll have a GeoTIFF with no worldfile or prjfile
        $target = $layer->url;
        
        ob_start();
        // call gdalwarp to reproject the image into latlong and GeoTIFF format, in its final resting place
        // ping("<br/>\nReprojecting<br/>\n");
        $command = escapeshellcmd("gdalwarp -r bilinear -s_srs \"ESRI::{$temp_prj}\" -t_srs \"EPSG:4326\" {$temp_image} {$target}");
        passthru($command);
        
        // call gdaladdo to add overviews on the image in-place
        // ping("<br/>\nAdding overviews<br/>\n");
        $command = escapeshellcmd("gdaladdo -r average {$target} 2 4 8 16 32 64 128 256 512 1024");
        passthru($command);
        ob_end_clean();
        $layerno = $layer->id;
        return $layerno;
        // return print redirect("layer.editraster1&id=$layerno");
        // return print redirect('layer.list');
        // print redirect($layer->owner->id == $user->id ? 'layer.list' : 'layer.bookmarks');
    }
    
    private function GetBBoxFromGDMetadta($metadata) {
        $matches = array();
        preg_match_all('/Upper Left[^\(]+\(([^\)]+)/i',$metadata,$matches);
        list($lon1,$lat1) = explode(',',array_pop(array_pop($matches)));
        $matches = array();
        preg_match_all('/Lower Right[^\(]+\(([^\)]+)/i',$metadata,$matches);
        list($lon2,$lat2) = explode(',',array_pop(array_pop($matches)));
        
        $minLon = min(doubleval($lon1),doubleval($lon2));
        $maxLon = max(doubleval($lon1),doubleval($lon2));
        $minLat = min(doubleval($lat1),doubleval($lat2));
        $maxLat = max(doubleval($lat1),doubleval($lat2));
        $bbox = implode(',',array($minLon,$minLat,$maxLon,$maxLat));
        
        return $bbox;
        
    }    
    
}
?>

