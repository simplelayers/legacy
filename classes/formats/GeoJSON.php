<?php

namespace formats;


use model\reporting\ReportError;
use utils\OGR\OGRGeoJSONUtil;
use utils\ParamUtil;
class GeoJSON extends LayerFormat {
    public $inputTemplate = 'import/kml1.tpl';

    public $reimportTemplate = 'import/kml1.tpl';
    
    public $label = 'GeoJSON';
   
    public function __construct() {
        $this->label = 'GeoJSON';
        $this->ext = 'geojson';
        $this->targetPattern = '*.[gG][eE][oO][jJ][sS][oO][nN]';
        array_push($this->moveExts,'kml');                        
    }
    
    protected function GetOGRImportLayer($file,$layer)
    {
        $params = array(
            'file' => $file,
            'layerId' => $layer->id,
            'basename' => $this->basename,
            'srs' => $this->srs
        );
        $geoJSONUtil = new OGRGeoJSONUtil($params);

        $geoJSONUtil->Import($params);
        $ogrLayer = $geoJSONUtil;
        
        return $ogrLayer;
    }
    /*public function Import($args,$process=true) {
        
        $this->Import($args,false);
        $desiredName = ParamUtil::GetOne($args,'desired_name','desiredName');
        foreach($this->targets as $target) {
            $layerId = ParamUtil::Get($args, 'layerid');
            $layer = \Layer::GetLayer($layerId,true);
            if($layer) $layer->autoGenerateThumbnail=false;
            
            try {                
                $layer->colorscheme->setSchemeToSingle();
                $layer->autoGenerateThumbnail=true;
                $layer->touch();
                array_push($this->import_ok, $layer->name);
            } catch (ReportError $e) {
                chdir('..');
                $layer->autoGenerateThumbnail=true;
                $layer->touch();
                $this->HandleReportError($e, $$layer->name);
                
            }
            
        }
        $this->FinalProcessing();
        
        
    }*/
    
  
}
 ?>
                					                					
