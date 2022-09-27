<?php
namespace utils\OGR;

use utils\OGR\OGRUtil;
use utils\ParamUtil;
use model\ProjectionList;

/**
 * OGR, part of GDAL, is a set of libraries that are used for working with
 * feature data stored in a variety of formats.
 *
 * In particular there is a command line app ogr2ogr and shp2pgsql which we use for
 * converting Shp files to PostGres insert statements.
 *
 * The main problem with shp2pgsql is that is a little too strict when creating
 * tables and inserting stuff, and we need to be in control of the table name(s).
 * It is too strict in the sense that it differentiates MULTILINESTRING vs LINESTRING
 * which technically are both LINESTRING types and can be in teh same layer as far as
 * we are concerned. When it creates a table it tries to add a constraint to limit to
 * one type or another, which is fine generally. However, we have run into situations
 * where a customer is attempting to uplaod a layer with MULTI and non-MULTI geometries
 * and the import fails due to the constraint shp2pgsql adds.
 *
 * As a workaround, the functionality in this class is designed to let shp2pgsql do
 * what it does, but we run it once to get the table creation code. We modify it to
 * a) include the table name we want rather than what it is attempting
 * b) we modify the addGeometryColumn function so that we are adding a field called the_geom using constraints
 * c) we modify the_geom constraint to work with multi and non-multi variant of the type.
 *
 * Then we call shp2pgsql to the inserts. However, unlike previous imports, we now pipe
 * the output from shp2pgsql to psql to directly insert the data into the db, avoiding disc
 * writes and loading anything into memory/php.
 *
 * So, in short, this is a wrapper for ogr2ogr and shp2pgsql for facilitating Shp File imports.
 *
 *
 * @author Arthur
 *        
 */
class OGR_CSV_Util
{

    /**
     * Stores info retrieved from ogrinfo about the target shp file.
     *
     * @var array
     */
    public $metadata;

    
    protected $commands;

    protected $layerId;

    protected $srcFile;

    protected $dbInfo;

    protected $table;

    protected $layerName;
    
    protected $file;

    protected $srs;

    protected $sqlFile;

    protected $vrtFile;
    
    protected $csvFile;

    protected $prjFile;

    protected $x;
    protected $y;
    protected $wkt;
    protected $wkb;
    
    protected $format = "CSV";

    public $hasGeom = false;
    /*
     * OGR call to create just the copy commands.
     * ogr2ogr -lco LAUNDER=YES -lco CREATE_SCHEMA=OFF -lco CREATE_TABLE=OFF -lco FID=gid --config PG_USE_COPY YES -f PGDump /home/art/public_html/simplelayers/_tests/temp/files/test.tmp.sql /home/art/public_html/simplelayers/_tests/temp/files/test.kml
     */
    public function Import($file, $layerId)
    {
        $ogfile = $file;
        
        $this->hasGeom = false;
        $srs = null;
        $ini = \System::GetIni();
        $dbInfo = OGRUtil::GetDBInfo();
        $this->layerId = $layerId;
        $this->srs = $srs;
        $this->dbInfo = $dbInfo;
        // $file = str_replace ( ' ', '\\ ', $file );
        $this->srcFile = $file;
        
        $filename_common = explode('.', $file);
        array_pop($filename_common);
        $filename_common = implode(".", $filename_common);
        $filename_common = explode('/',$filename_common);
       
        $this->layerName = array_pop(array_slice($filename_common,-1));
  
        $filename_common = implode("/",$filename_common);
        $params = \WAPI::GetParams();
        
       
        $this->vrtFile = $filename_common.'.vrt';
        $geomFmt = ParamUtil::Get($params,'gfmt');
        $geomType = '';
        $enc = '';
        
        switch($geomFmt) {
            case 'latlon':
                $geomType='wkbPoint';
                $enc="encoding=\"PointFromColumns\"";
                list($this->x,$this->y) = ParamUtil::ListValues($params,'x','y');
                $colInfo = "x=\"{$this->x}\" y=\"{$this->y}\" ";
                $this->hasGeom = true;
                break;
            case 'wkt':
                $geomType='wktUnknown';
                $enc="encoding=\"WKT\"";
                 list($this->wkt) = ParamUtil::ListValues($params,'wkt');
                 $colInfo = "field=\"{$this->wkt}\"";
                 $this->hasGeom = true;
                break;
            case 'wkb':
                $geomType='wkbUnknown';
                $enc="encoding=\"WKB\"";
                list($this->wkb) = ParamUtil::ListValues($params,'wkb');
                $colInfo = "field=\"{$this->wkb}\"";
                $this->hasGeom = true;
                break;
        }
        $projection = ParamUtil::Get($params,'projection');
        
        $srs = ProjectionList::GetWKT($projection);
        
        $vrt = <<<FILE_CONTENTS
<OGRVRTDataSource>
    <OGRVRTLayer name="{$this->layerName}">
        <SrcDataSource>{$this->srcFile}</SrcDataSource>
        <GeometryType>$geomType</GeometryType>
        <LayerSRS>$srs</LayerSRS>
        <GeometryField $enc $colInfo />
    </OGRVRTLayer>
</OGRVRTDataSource>                   
        
FILE_CONTENTS;
        
        
        file_put_contents($this->vrtFile,$vrt); 
        if($hasGeom) {
            $this->metadata = OGRUtil::GetLayerInfo($this->vrtFile,$hasGeom);
        } else {
            $this->metadata = OGRUtil::GetLayerInfo($file,$hasGeom);
        }

        ini_set("auto_detect_line_endings", true);
        
        
        $this->srcFile = str_replace('\\', '', $this->srcFile);
        
        $fh = fopen($this->srcFile, 'r');
        $fieldRow = fgetcsv($fh);
        
        fclose($fh);
        $fields = array();
        foreach($fieldRow as $field) {
            $fields[] = \Layer::SanitizeColumnName($field);
        }
        
        $reqFields = 0;
        $validFields = array(strtolower($this->x),strtolower($this->y),strtolower($this->wkt),strtolower($this->wkb));
        if($this->hasGeom) {
            foreach($fields as $field) {
                if(is_null($field)) continue;

                if(in_array($field, $validFields)) $reqFields++;                       
            }
            if($reqFields == 0) {
                throw new \Exception('spatial fields not found');
            }
        } else {
            $this->vrtFile='';
        }
        
        
        $this->ConvertFile($file);
        #var_dump($this->sqlFile);
        #$this->InsertRecords();
        #die('inserted');
    }

    public function InsertRecords()
    {
        $report = array();
        $report['problems'] = array();      
        $report['info'] = $this->metadata;
        
        $db = \System::GetDB(\System::DB_ACCOUNT_SU);
        
        if (! file_exists($this->sqlFile)) {
            throw new \Exception("Unable to produce sql file: " . $this->copyFile);
        }
        
        $pgsql = "psql -h {$this->dbInfo['host']} --username={$this->dbInfo['user']} -w -1 --dbname={$this->dbInfo['db']}";
        #$pgsql = "psql -h {$this->dbInfo['host']} -1 --dbname={$this->dbInfo['db']}";
        $db = \System::GetDB(\System::DB_ACCOUNT_SU);
        
        // $res = shell_exec ( escapeshellcmd ( $cmd ) );
        $resultFile = $this->sqlFile . '.out';
        
        $logFile = $this->sqlFile . '.log';
        
        if (file_exists($logFile)) {
            unlink($logFile);
        }
        $report['import']['records_to_import'] = intval(trim($this->metadata['info']['count']));
        $report['prev_layer'] = $this->metadata;
        
        $cmd = <<<SED
		 perl -pi -e "s/INTO/UNTO/g" {$this->sqlFile}
SED;
        // $res = shell_exec ( escapeshellcmd ( $cmd ));
        
        $cmd = "export PGPASSWORD='{$this->dbInfo['pw']}';";
        $cmd.= "$pgsql -E -L \"$logFile\" --set ON_ERROR_STOP=on  < \"{$this->sqlFile}\" > \"{$resultFile}\"  2>&1 >/dev/null"; // | GREP ^[EL]";
        passthru($cmd, $res);
        $res = ob_get_clean();
        
       $cmd = "export PGPASSWORD='';";
        $res = shell_exec($cmd);
        
        $cmd = "grep -c 'INSERT 0 1' < \"$logFile\"";
        
        ob_start();
        passthru($cmd);
        $numAttempted = intval(trim(ob_get_clean()));
        $report['import']['num_attempted'] = $numAttempted;
        
        $numInserted = $db->GetOne("select count(*) from " . 'vectordata_' . $this->layerId);
        if ($numInserted === false)
            $numInserted = '0';
        $numInserted = intval($numInserted);
        
        $report['import']['numInserted'] = $numInserted;
        if ($numInserted < $report['import']['records_to_import']) {
            
            ob_start();
            passthru("grep ^[EL] < $resultFile");
            $errorInfo = ob_get_clean();
            $errorInfo = explode("\n", $errorInfo);
            $error = array();
            foreach ($errorInfo as $info) {
                $info = explode(':', $info);
                $key = array_shift($info);
                if ($key == "")
                    continue;
                $val = implode(":", $info);
                $error[$key] = $val;
            }
            $report['status'] = 'problem';
            
            ob_start();
            passthru("grep '^INSERT' < $logFile | tail --lines=1");
            $lastLine = ob_get_clean();
            $error['problem_insert'] = $lastLine;
            $report['error_info'] = $error;
        } else {
            $report['status'] = 'ok';
        }
        
        //$importReport = new ImportReport ();
        
        // $importReport->CreateReport ( $this->layerId, $report );
        // error_log(json_encode($report->GetReport($layerId)));
        return $report;
    }
    
    /*
     * protected function GetRecordInfo($record, &$stats) {
     * if (! isset ( $stats ['url_fields'] )) {
     * $stats ['url_fields'] = array ();
     * $stats ['tagged_fields'] = array ();
     * $stats ['added'] = 0;
     * $stats ['fields'] = array ();
     * }
     * foreach ( $record as $field => $data ) {
     * if (strlen ( $field ) == 1)
     * continue;
     *
     * $isURL = (stripos ( $data, 'http' ) === 0);
     * $isURL = $isURL || (stripos ( $data, 'ftp' ) === 0);
     * $isURL = $isURL || (stripos ( $data, 'sftp' ) === 0);
     * $isURL = $isURL || (stripos ( $data, 'sftp' ) === 0);
     * $isURL = $isURL || (stripos ( $data, 'mailto' ) === 0);
     * $isURL = $isURL || (stripos ( $data, 'gopher' ) === 0);
     * $isURL = $isURL || (stripos ( $data, 'news' ) === 0);
     * $isURL = $isURL || (stripos ( $data, 'telnet' ) === 0);
     * $isURL = $isURL || (stripos ( $data, 'ssh' ) === 0);
     * $isURL = $isURL || (stripos ( $data, '\\' ) === 0);
     *
     * if ($isURL) {
     * if (! in_array ( $field, $stats ['url_fields'] ))
     * $stats ['url_fields'] [] = $field;
     * }
     * if ($data != strip_tags ( $data )) {
     * if (! in_array ( $field, $stats ['tagged_fields'] ))
     * $stats ['tagged_fields'] [] = $field;
     * }
     * if (is_int ( $data ) || is_float ( $data )) {
     * if (! isset ( $stats ['fields'] [$field] )) {
     * $stats ['fields'] [$field] ['nulls'] = ($data == 'NULL') ? 0 : 1;
     * if (! ($data == 'NULL')) {
     * $stats ['fields'] [$field] ['min'] = $data;
     * $stats ['fields'] [$field] ['max'] = $data;
     * }
     * } else {
     * if ($data == 'NULL') {
     * $stats ['fields'] [$field] ['nulls'] += 1;
     * } else {
     * $stats ['fields'] [$field] ['min'] = min ( $stats ['fields'] [$field] ['min'], $data );
     * $stats ['fields'] [$field] ['max'] = max ( $stats ['fields'] [$field] ['max'], $data );
     * }
     * }
     * } else {
     * if (! isset ( $stats [$field] )) {
     * $stats ['fields'] [$field] = array ();
     * }
     * $stats ['fields'] [$field] ['nulls'] = ($data == 'NULL') ? 0 : 1;
     * }
     * }
     * $stats ['added'] += 1;
     * }
     */
    protected function ConvertFile($file, $srid = 'EPSG:4326')
    {
        
        $srs = "";
        $sqlFile = substr($file, - 4) . ".tmp.sql";
        if (file_exists($sqlFile))
            unlink($sqlFile);
        $tmp = substr($file, 0, - 4) . ".tmp.sql";
        $this->sqlFile = $tmp;
        
        $table = 'vectordata_' . $this->layerId;
        
        
        if($this->hasGeom) {
            $command = sprintf("ogr2ogr -lco FID=gid -lco DROP_TABLE=IF_EXISTS -lco GEOMETRY_NAME=the_geom -lco LINEFORMAT=LF -lco GEOM_TYPE=geometry -s_srs $srid  -t_srs  EPSG:4326 -f PGDump \"%s\"  \"%s\" -nln $table -nlt GEOMETRY 2>&1", $this->sqlFile,$this->vrtFile   );
        } else {
            $command = sprintf("ogr2ogr -lco FID=gid -lco DROP_TABLE=IF_EXISTS -lco LINEFORMAT=LF -f PGDump \"%s\"  \"%s\" -nln $table 2>&1", $this->sqlFile,$file   );
        }
       
        
        $res = shell_exec($command);
        ob_start();
        $cmd = 'file --mime-encoding '.$this->sqlFile;
        passthru($cmd);
        $meta = explode(':',ob_get_clean());
        $utf8File = substr($file, 0, - 4) . ".utf8.sql";
        $format = trim(array_pop($meta));
        $cmd = 'iconv -f '.$format.' -t utf8 '.$this->sqlFile.' -o '.$utf8File;
        shell_exec($cmd);
        if(file_exists($utf8File)) {
            #unlink($this->sqlFile);
            $this->sqlFile = $utf8File;
        }
    }

    protected function GetTableSQL($file, $layerId, $layerBaseName = "")
    {
        /*
         * //$cmd = "shp2pgsql -p -s 4326 " . substr ( $file, 0, - 4 ) . ".tmp.shp";
         *
         * $tableInfo = file_get_contents($file);
         *
         * $tableInfo = explode ( "\n", $tableInfo );
         * $layerName = $layerBaseName == "" ? $this->metadata ['layer'] : $layerBaseName . "_" . $this->metadata ['layer'];
         * $this->table = strtolower ( "vectordata_$layerId" );
         * $commands = array ();
         * //$commands [] = "DROP TABLE IF EXISTS {$this->table};";
         *
         * foreach ( $tableInfo as $i => $line ) {
         *
         * $targetLayer = '"public"."'.strtolower ( $this->metadata ['layer'] ).'"';
         *
         * if(stripos(trim($line),'INSERT')!== false) {
         * break;
         * } elseif(stripos ( $line, 'SELECT AddGeometryColumn' ) === 0) {
         * $geomColumnSQL = str_replace ( "'$targetLayer'", "'{$this->table}'", $line );
         *
         * $geomColumnSQL = str_replace ( "'geom'", "'the_geom'", $geomColumnSQL );
         *
         * $geomColumnSQL = str_replace ( 'MULTI', '', $geomColumnSQL );
         *
         * $params = explode ( ',', $geomColumnSQL );
         * $type = str_replace ( "'", '', $params [4] );
         * $multitype = 'MULTI' . $type;
         * }
         *
         * $commands[] = $line;
         *
         * }
         * $commands[] = "COMMIT;";
         * $this->commands = $commands;
         */
    }
}
// dbInfo = array('db'=>'cartograph','pw'=>'5l1pp3ry!');
// reader = new OGRReader($shpFile,'0arttest','newdata',$dbInfo);

?>
