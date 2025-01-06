<?php
namespace formats;

use LayerTypes;
use utils\OGR\OGRShpUtil;
use utils\ImportUtil;


// equire_once(dirname(__FILE__).'/../../lib/PHP-SQL-Parser/php-sql-parser.php');
// equire_once (dirname ( __FILE__ ) . '/../OGRShpUtil.class.php');

// error_reporting(E_ALL);
class Shp extends LayerFormat
{
    public $label = '';
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

     public function SetupLayer($params, $user, $file,$layerType = LayerTypes::VECTOR) {
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
        
        $prjfile = ImportUtil::RenameFile($file, 'shp', 'prj', $this->basename);
        $layer =parent::SetupLayer($params, $user, $file); 
        
        if(!is_null($xmlMetadata)) $layer->importMetadata( $xmlMetadata);
        if (! file_exists($prjfile)) {
            if(!$this->srs || ($this->srs === '')) {
                throw new \Exception('No Projection: No prj file was found or no srs was determined for the uploaded data');
            }
            file_put_contents($prjfile, $this->srs);
        }else {
            $epsgId = self::GetSRSFromPrj($prjfile);
            if($epsgId) {
                $this->srs = 'ESPG:'.$epsgId;
            }
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

    public static function ParseWktToJson($wkt) {
        // Clean up whitespace
        $wkt = trim($wkt);
    
        // Regular expressions for parsing WKT components
        $pattern = '/([A-Z_]+)\s*\[(.*)\]/';
        $components = [];
        
        // Recursive function to parse WKT components
        function parseComponent($text) {
            $result = [];
            $pattern = '/([A-Z_]+)\s*\[(.*)\]/';
    
            if (preg_match($pattern, $text, $matches)) {
                $name = $matches[1];
                $valueText = $matches[2];
    
                // Split at commas, respecting nested brackets
                $values = [];
                $bracketLevel = 0;
                $currentPart = '';
    
                for ($i = 0; $i < strlen($valueText); $i++) {
                    $char = $valueText[$i];
    
                    if ($char === '[') {
                        $bracketLevel++;
                    } elseif ($char === ']') {
                        $bracketLevel--;
                    }
    
                    if ($char === ',' && $bracketLevel === 0) {
                        $values[] = trim($currentPart);
                        $currentPart = '';
                    } else {
                        $currentPart .= $char;
                    }
                }
                if ($currentPart !== '') {
                    $values[] = trim($currentPart);
                }
    
                // Parse values recursively
                $parsedValues = [];
                foreach ($values as $value) {
                    if (preg_match($pattern, $value)) {
                        $parsedValues[] = parseComponent($value);
                    } else {
                        $parsedValues[] = trim($value, '"');
                    }
                }
    
                // Create the result as a structured object
                $result = [
                    "name" => $name,
                    "value" => $parsedValues
                ];
            }
    
            return $result;
        }
    
        // Start parsing the top-level WKT
        $components[] = parseComponent($wkt);
    
        return $components;
    }
    
    public static function GetSRSFromPrjJSON($json) {
        // Recursive function to locate the top-level AUTHORITY code
        function findAuthority($node) {
            if(!isset($node['name'])) return null;
            if ($node["name"] === "AUTHORITY" && is_array($node["value"]) && count($node["value"]) === 2) {
                return $node["value"][1];  // EPSG code is usually the second item
            }
            return null;
        }
    
        // Look for the main AUTHORITY code in the top component (e.g., GEOGCS or PROJCS)
        foreach($json as $l1) {
            foreach($l1['value'] as $component) {
                $epsgCode = findAuthority($component);
                if ($epsgCode !== null) {
                    return $epsgCode;
                }
            }
        }
        return null;
    }
    

    public static function GetSRSFromPrj($filePath) {
        // Check if file exists
        if (!file_exists($filePath)) {
            return false;
        }
    
        // Read file content
        $wktContent = file_get_contents($filePath);
        $wktJSON = self::ParseWktToJson($wktContent);
        // Check if the content resembles WKT format (starts with GEOGCS, PROJCS, or GEOGCRS, etc.)
       
        $epsgId = self::GetSRSFromPrjJSON($wktJSON);
        return $epsgId;
    }
}

?>
                					                					
