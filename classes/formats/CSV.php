<?php
namespace formats;

use utils\ParamUtil;
use model\reporting\ImportReport;
use utils\PageUtil;
use utils\ImportUtil;
use model\logging\Log;
use utils\OGR\OGR_CSV_Util;
use model\ProjectionList;
use auth\Context;

// equire_once(dirname(__FILE__).'/../../lib/PHP-SQL-Parser/php-sql-parser.php');
// equire_once (dirname ( __FILE__ ) . '/../OGRShpUtil.class.php');

// error_reporting(E_ALL);
class CSV extends LayerFormat
{
    /* @var $importReport ImportReport  */
    public $importReport = null;

    public $inputTemplate = 'import/csv1.tpl';

    public $reimportTemplate = 'import/csv1.tpl';

    public function __construct()
    {
        $this->label = 'CSV Format';
        $this->ext = 'csv';
        $this->targetPattern='*.[cC][sS][vV]';
        array_push($this->moveExts,'csv','text','txt');
    }
    
    protected function GetOGRImportLayer($file,$layer)
    {
        /*$dbInfo = array(
            'db' => $ini->pg_sl_db,
            'host' => $ini->pg_host,
            'user' => $ini->pg_admin_user,
            'pw' => $ini->pg_admin_password
        );*/
        
        $layerArgs = array('file'=>$file,'layerId'=>$layer->id);
        $csvLayer = new OGR_CSV_Util();//$file, $layer->id);
        $csvLayer->Import($file,$layer->id);
        return $csvLayer;
    }

    protected function ProcessErrorMessage($message) {
        switch ($message) {
            case 'spatial fields not found':
                $message = 'CSV data does not contain the specified spatial columns.';
                break;
        }
        return $message;
    }
}
?>
                					                					
