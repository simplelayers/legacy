<?php

namespace formats;

use model\reporting\ImportReport;
use utils\ImportUtil;
use utils\ParamUtil;
use model\ProjectionList;
use model\reporting\ReportError;
use model\logging\Log;
use v5\Projections;

class LayerFormat
{

    public $importReport = null;
    public $inputTemplate = null;
    public $reimportTemplate = null;
    public $params = array();
    protected $zipExts = array(
        'zip'
    );
    protected $moveExts = array(
        'zip'
    );
    protected $targetPattern = '*.*';
    protected $basename = '';

    protected $directory = '';

    protected $zipFile = '';

    protected $importTargets = array();

    protected $importOk = false;
    protected $import_ok = array();
    protected $import_err = array();

    protected $reimport = false;

    protected $lastInfo = array();

    protected $targets = array();

    protected $ext = '';

    protected $srs;

    protected $srsDetected = false;

    protected $desiredNames = array();

    protected $debugging = true;
    // public $minAccountLevel = AccountTypes::GOLD;
    public function Import($params)
    {
        // Setup: Process parameters and setup variables
        $user = \SimpleSession::Get()->GetUser();
        $ini = \System::GetIni();

        $this->importReport = new ImportReport();
        $this->basename = ParamUtil::Get($params, 'basename', null);

        $this->srs = \RequestUtil::Get('projection', null);
        if ($this->srs == '')
            $this->srs = null;
        if (is_null($this->srs)) {
            $this->srsDetected = false;
            $this->srs = '4326';
        } else {
            $this->srsDetected = true;
        }
        
        if (substr($this->srs, 0, 1) != '+') {
            $code = explode(':', $this->srs);
            if (count($code) > 1)
                $this->srs = $code[1];
            $this->srs = ProjectionList::GetProj4($this->srs);
        }



        // Step 1: Move up/down-loaded content into a work directory
        $this->directory = ImportUtil::MoveUploaded(null, true, 'source', $this->moveExts);
        #var_dump($this->directory);

        // Step 2: With a work directory, decompress any zip content
        $zipFile = $this->directory . $_FILES['source']['name'];
        $isCompressed = false;
        $zipPaths = array();



        foreach ($this->zipExts as $ext) {
            $ext = '.' . $ext;
            if (substr($zipFile, -strlen($ext)) != $ext) continue;


            ImportUtil::UnpackZip($zipFile, $ext, $zipPaths);
        }



        if (count($zipPaths)) {
            $this->targets = ImportUtil::GetTargets($zipPaths, $this->targetPattern);
        } else {
            $this->targets = ImportUtil::GetTargets($this->directory, $this->targetPattern);
        }



        // Step 3: With a list of targets to import, loop through the
        // targets and attempt to import each one,
        // The $this->... variables beloe
        // are used for reporting purposes and may be set by other functions.
        $this->import_ok = array();
        $this->import_err = array();
        $this->reimport = false;
        $this->lastInfo = array();


        foreach ($this->targets as $fmtFile) {


            $layer = self::SetupLayer($params, $user, $fmtFile);
            $desiredName = $this->desiredNames[$fmtFile];


            $table = $layer->url;
            $report = null;
            try {
                // Each format extending LayerFormat should implement its own GetOGRLayer function.

                $ogrLayer = $this->GetOGRImportLayer($fmtFile, $layer);
                $report = $this->ProcesOGRLayer($ogrLayer, $layer);
                array_push($this->import_ok, $desiredName);
            } catch (ReportError $e) {
                chdir('..');
                $this->HandleReportError($e, $desiredName);
            }
        }
        if (count($this->targets) == 0) {
            try {
                $layerId = ParamUtil::Get($params, 'layerid');

                throw  new ReportError('Missing files to import.', $report);
            } catch (ReportError $e) {
                $layerId = ParamUtil::Get($params, 'layerid');
                $desiredName = ($layerId) ? \Layer::GetLayer($layerId) : "";
                if ($this->basename && $desiredName == '') $desiredName = $this->basename;
                if ($desiredName == "") $desiredName = "New Layer";
                $this->HandleReportError($e, $desiredName);
            }
        }

        if (!$this->debugging) {
            shell_exec('rm -rf ' . escapeshellarg($this->directory));
        } else {
            Log::Debug('attempted upload folder: $this->directory');
        }

        return $this->PrepareFinalReport();
    }

    protected function SetupLayer($params, \Person $user, $file, $layerType = \LayerTypes::VECTOR)
    {
        /*$dir = explode('/',$file);
        array_pop($dir);
        $dir = implode('/',$dir);
        chdir($dir);
       */
        if ($params['layerid'] == '')
            unset($params['layerid']);
        $layerId = ParamUtil::Get($params, 'layerid');

        // Step 4.1 Determine the layerid and table to use,
        // if reimporting we should have the data
        // if not then we need to create the layer record and determine the table name.

        $desiredName = ImportUtil::RenameFile($file, $this->ext, null, $this->baseName, false);

        $this->desiredNames[$file] = $desiredName;
        $projection = ParamUtil::Get($params, 'projection');
        if ($projection == '')
            $projection = null;


        if ($layerId) {
            $this->reimport = true;
            $layer = \Layer::GetLayer($layerId);
            $this->lastInfo[$layer->id] = $layer->field_info;
            $layer->field_info = null;
            $layer->setDBOwnerToDatabase();
            $table = $layer->url;
            $layer->setLayerGeomType();
            $layer->backup();
            $layer->name = $layer->name;
            $layer->DropData();
        } else {
            $this->reimport = false;

            $org = \Organization::GetOrgByUserid($user->id);

            if (! $org->CanAddLayers()) {
                throw new ReportError('Your organization has reached its maximum number of allowed layers for its current license.', null);
                /*
                 * $report['status'] = 'problem';
                 * $report['sl_message'] = 'Your organization has reached its maximum number of allowed layers for its current license.';
                 * return $report;
                 */
            }

            // create the Layer object we'll be populating

            $layer = $user->createLayer($desiredName, $layerType);
            if ($layer) {
                $layer->colorscheme->setSchemeToSingle();
                $layer->setDBOwnerToDatabase();
            }
        }
        return $layer;
    }
    protected function GetOGRImportLayer($file,$layer)
    {
        // override me
    }
    public function ProcesOGRLayer($ogrLayer, $layer)
    {

        $metadata = $ogrLayer->metadata;

        $db = \System::GetDB(\System::DB_ACCOUNT_SU);
        $layerId = $layer->id;

        if ($ogrLayer->hasGeom === false) {
            //$layer->setLayerGeomType(\GeomTypes::RELATABLE);
            $layer->setLayerType(\LayerTypes::RELATABLE);
        } else {
            $layer->setLayerGeomType($layer->geomtype);
        }

        /*if ($ogrLayer->hasGeom === false) {
            $layer->setLayerGeomType(\GeomTypes::RELATABLE);
        } else {
            $layer->setLayerGeomType($layer->geomtype);
        }*/

        $report = $ogrLayer->InsertRecords();
        /*@var $layer \Layer  */
        //var_dump(base64_encode($ogrLayer->metadata));
        // $layer->metadata = $metadata;
        
        $isOk = $report['status'] == 'ok';

        $report['import']['isReimporting'] = $this->reimport;
        if (! $this->reimport) {
            if ($report['import']['prev_layer'])
                unset($report['import']['prev_layer']);
            if ($report['prev_layer'])
                unset($report['prev_layer']);
        }

        $layer->setDBOwnerToDatabase();

        if (! $isOk) {
            if ($report['sl_message']) {
                throw new \Exception($report['sl_message']);
            }
            throw new ReportError('There was a problem creating the table for:' . $layer->name . ' ;', $report);
        }
        // Step 4.3 Insert records

        $testSQL = "select count(*) as numRecords from {$layer->url}";

        $countRecords = $db->GetOne($testSQL);
        $isOk = ($countRecords > 0);

        if (! $isOk) {
            throw new ReportError("There was a problem inserting records {$report['import']['num_attempted']} of {$countRecords} attempted to be inserted. Rolling back changes.", $report);
        }

        if ($ogrLayer->hasGeom) {

            $problemQuery = "Select count(*) as invalidCount from {$layer->url} where st_isValid(the_geom)=false";
            $invalidCount = (int) $db->GetOne($problemQuery);


            $report['import']['invalidCount'] = $invalidCount;

            $nullCount = $db->GetOne('select count(the_geom) from ' . $layer->url . ' where the_geom IS NULL');
            $report['import']['nullCount'] = ($nullCount) ? 0 : $nullCount;
            $report['import']['nullCount'] = intval($nullCount);

            $bboxQuery = "select min(xmin(bbox)) as x1,min(ymin(bbox)) as y1,max(xmax(bbox)) as x2,max(ymax(bbox)) as y2 from ( select ST_Envelope(the_geom) as bbox from {$layer->url} )as q1";
            $data = $db->GetRow($bboxQuery);
            if ($data) {
                $layer->bbox = implode(',', array_values($data));
            }
        }
        $layer->import_info = $ogrLayer->metadata;

        $layer->fixDBPermissions();
        $layer->setDBOwnerToOwner();

        $layer->ValidateColumnNames();
        $this->importReport->AddLayerReport($layer->id, $report);
        $layer->report_id = $report['id'];
        chdir('..');
        return $report;
    }

    protected function HandleReportError(ReportError $e, $desiredName)
    {
        $message = $e->getMessage();
        $message = $this->ProcessErrorMessage($message);

        $layer = $e->getLayer();
        $layerId = $layer->id;

        if ($layerId) {
            if ($this->reimport) {
                $layer->rollback();
                if ($this->lastInfo[$layer->id]) {
                    $layer->field_info = $this->lastInfo[$layer->id];
                }
            } else {
                $layer->delete();
            }
        }
        $report = $e->getReport();

        if (! isset($report))
            $stats = array();
        $report['status'] = 'problem';
        $report['sl_message'] = $message;
        $data = $report['error_info'];
        if (isset($report['error_info'])) {
            Log::Error($message . "\nerror info:" . json_encode($report['error_info']));
        } else {
            Log::Error($$message);
        }
        $this->importReport->AddLayerReport($layerId, $report);

        // print javascriptalert($message);
        array_push($this->import_err, $desiredName);
    }

    protected function PrepareFinalReport()
    {
        $message = "";
        // done, print 2 JS alerts showing them what worked and what failed, then send them to their list of layers
        // This is also effective at notifying them when layers had to be renamed to avoid a naming conflict
        $okCount = count($this->import_ok);
        $errCount = count($this->import_err);

        if ($okCount)
            $message .= "\n$okCount layers were imported successfully.\n\n";
        if ($errCount)
            $message .= "\n$errCount layers WERE NOT imported:\n\n";
        // print javascriptalert($message);
        $importDoc = $this->importReport->CreateImportReport();

        return $importDoc;
    }

    protected function ProcessErrorMessage($message)
    {
        switch ($message) {
            case 'Source projection unknown':
                $message = "Could not identify a projection for your layer(s). Please use the projection selector on the import page to identify which projection your layer uses, or, include a .prj file with your Shp file(s)";
                break;
        }
        return $message;
    }

    protected function GetOGRLayer()
    {
        return null;
    }

    public function Export($parameters) {}

    public function HandleZip() {}
}
