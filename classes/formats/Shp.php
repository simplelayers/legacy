<?php
namespace formats;

use utils\OGR\OGRShpUtil;
use utils\ImportUtil;


// equire_once(dirname(__FILE__).'/../../lib/PHP-SQL-Parser/php-sql-parser.php');
// equire_once (dirname ( __FILE__ ) . '/../OGRShpUtil.class.php');

// error_reporting(E_ALL);
class Shp extends LayerFormat
{
    public $inputTemplate = 'import/shapefiles1.tpl';

    public $reimportTemplate = 'import/shapefiles1.tpl';

    public function __construct()
    {
        $this->label = 'Shapefile Format';
        $this->targetPattern = '*.[sS][hH][pP]';
        $this->ext = 'shp';
    }

    protected function GetOGRImportLayer($file,$layer)
    {
        $params = array(
            'file' => $file,
            'layerId' => $layer->id,
            'basename' => $this->basename,
            'srs' => $this->srs
        );
        
        $shpUtil = new OGRShpUtil($params);
      
        $shpUtil->Import($params);
        $ogrLayer = $shpUtil;
        
        return $ogrLayer;
    }

     public function SetupLayer($params, $user, $file,$layerType = LayerTypes::Vector) {
         
        $xmlfiles = array();
        $xmlfiles[] = ImportUtil::NewExt($file, 'shp', 'xml');
        $xmlfiles[] = $file . '.xml';
        $xmlMetadata = null;
        
        foreach ($xmlfiles as $xmlfile) {
            if (file_exists($xmlfile)) {
                $xmlMetadata = $xmlfile;
                break;
            }
        }
        $prjfile = ImportUtil::RenameFile($file, 'shp', 'prj', $this->baseName);
        
        $layer =parent::SetupLayer($params, $user, $file); 
        
        if(!is_null($xmlMetadata)) $layer->importMetadata( $xmlMetadata);
        if (! file_exists($prjfile)) {
            file_put_contents($prjfile, $this->srs);
        }
        return $layer;
    }
    
 

    protected function ProcessErrorMessage($message)
    {
        switch ($message) {
            case 'Source projection unknown':
                $message = "Unable to identify a projection for your layer(s). Please use the projection selector on the import page to identify which projection your layer uses, or, include a .prj file with your Shp file(s)";
                break;
        }
        return $message;
    }
}

?>
                					                					
