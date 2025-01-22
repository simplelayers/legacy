<?php

namespace utils\OGR;

use formats\Shp;
use utils\EncodingUtil;
use utils\ParamUtil;
use utils\OGR\OGRUtil;
use v5\Projections;

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
class OGRShpUtil
{

    /**
     * Stores info retrieved from ogrinfo about the target shp file.
     *
     * @var array
     */
    public $metadata;

    protected $layerId;

    protected $layer;

    protected $srcFile;

    protected $dbInfo;

    protected $table;

    protected $file;

    protected $srs;

    protected $sqlFile;

    protected $dbfFile;

    protected $prjFile;

    protected $layerInfo;

    public $hasGeom = true;

    public $xmlFile;

    public function Import($params)
    {
        list($file, $layerId, $layerBaseName, $srs) = ParamUtil::ListValues($params, 'file', 'layerId', 'basename', 'srs');
        $dbInfo = OGRUtil::GetDBInfo();

        $this->layerId = $layerId;
        $this->layer = \Layer::GetLayer($this->layerId);

        $this->layerInfo = $this->layer->DumpArray($this->layerId);

        $this->srs = $srs;
        $this->dbInfo = $dbInfo;
        $file = str_replace(' ', '\\ ', $file);
        $this->srcFile = $file;
        $filename_common = explode('.', $file);
        array_pop($filename_common);
        $filename_common = implode(".", $filename_common);
        $this->dbfFile = $filename_common . '.dbf';
        $this->xmlFile = $filename_common . '.shp.xml';
        if (! file_exists($this->xmlFile))
            $this->xmlFile = null;
        $this->prjFile = $filename_common . '.prj';
        if (! file_exists($this->prjFile)) {
            $this->prjFile = null;
            throw new \model\reporting\ReportError('No Prj file: A prj file was not detected for this layer', ['status' => 'problem'], $this->layer);
        }
        $this->metadata = $this->getLayer($this->srcFile);
        #var_dump(file_get_contents($this->prjFile));
        if ($this->prjFile && file_exists($this->prjFile)) {
            $this->metadata['srs'] = file_get_contents($this->prjFile);
        }
        $this->convertFile($file);
        if ($this->xmlFile) {
            $this->layer->importMetadata($this->xmlFile);
        }
        // $this->getTableSQL ( $file, $layerId, $layerBaseName );

        // this->makeRecords($file);
        // ie();
    }

    public function insertRecords()
    {

        $report = array();
        $report['problems'] = array();
        $code = $this->GetEncodingCode($this->dbfFile);
        $W = ""; // Holds the -W encodingname flag for the shp2pgsql command.
        $info = EncodingUtil::GetEncoding($code);
        if (! is_null($info)) {
            $report['encoding'] = $info['title'] . '(' . $info['code'] . ')';
            $W = "-W " . $info['code'];
        } else {
            $report['encoding'] = 'Unknown Encoding';
        }

        $report['info'] = $this->metadata;
        $db = \System::GetDB(\System::DB_ACCOUNT_SU);

        $pgsql = "psql -h {$this->dbInfo['host']} --username={$this->dbInfo['user']} -w -1 --dbname={$this->dbInfo['db']}";
        
        /*
         * $cmds = implode ( ' ', $this->commands );
         * $cmd = "export PGPASSWORD='{$this->dbInfo['pw']}';";
         * $cmd .= "echo " . escapeshellarg ( $cmds ) . " |";
         * $cmd .= "psql -h {$this->dbInfo['host']} --username={$this->dbInfo['user']} -w -1 --dbname={$this->dbInfo['db']};";
         * $cmd .= "export PGPASSWORD='';";
         */

        // $res = shell_exec ( escapeshellcmd ( $cmd ) );
        $resultFile = $this->sqlFile . '.out';

        $logFile = $this->sqlFile . 'log';

        $report['import']['records_to_import'] = intval(trim($this->metadata['info']['count']));
        $report['prev_layer'] = $this->layerInfo;

        $cmd = <<<SED
		 perl -pi -e "s/INTO/UNTO/g" {$this->sqlFile}
SED;
        // $res = shell_exec ( escapeshellcmd ( $cmd ));

        $cmd = "export PGPASSWORD='{$this->dbInfo['pw']}';";
        $cmd .= "$pgsql -E -L $logFile --set ON_ERROR_STOP=on  < {$this->sqlFile} > $resultFile  2>&1 >/dev/null | GREP ^[EL]";
        ob_start();
        passthru($cmd, $res);

        $res = ob_get_clean();
        $cmd = "export PGPASSWORD='';";
        $res = shell_exec($cmd);

        $cmd = "grep -c 'INSERT 0 1' < $logFile";

        ob_start();
        passthru($cmd);
        $numAttempted = intval(trim(ob_get_clean()));
        ob_end_flush();

        $report['import']['num_attempted'] = $numAttempted;

        $numInserted = $db->GetOne("select count(*) from " . $this->layer->url);
        if ($numInserted === false)
            $numInserted = '0';
        $numInserted = intval($numInserted);

        $report['import']['numInserted'] = $numInserted;
        if ($numInserted < $report['import']['records_to_import']) {

            ob_start();
            passthru("grep ^[EL] < $resultFile");
            $errorInfo = ob_get_clean();
            ob_end_flush();
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

        // $importReport = new ImportReport ();

        // $importReport->CreateReport ( $this->layerId, $report );
        // error_log(json_encode($report->GetReport($layerId)));
        return $report;
    }

    protected function GetEncodingCode($dbf)
    {
        $file = fopen($dbf, 'r');
        if (! $file)
            return false;
        fseek($file, 29);
        $code = ord(fread($file, 1));
        fclose($file);
        return $code;
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
    protected function convertFile($file, $srid = 4326)
    {
        $srs = "";
        if (file_exists(substr($file, 0, -4) . ".tmp.sql"))
            shell_exec("rm " . substr($file, 0, -4) . ".tmp.*");
        $tmp = substr($file, 0, -4) . ".tmp.sql";
        $this->sqlFile = $tmp;

        if ($this->metadata['info']) {
            $infoSrs = $this->metadata['info']['srs'];
            if ((stripos($infoSrs, 'unknown') === false)) {
                $this->srs = $infoSrs;
                $srsAuth = Projections::FindAuthority($infoSrs);
                if ($srsAuth) {
                    //list($a,$c) = explode(':',$srsAuth);
                    if (stripos($srsAuth, 'ESPG')) {
                        $srs = "-s_srs $srsAuth";
                    } else {
                        $this->srs = Projections::GetSRS($srsAuth);
                        $srs = "-s_srs  '{$this->srs}'";
                    }
                }
            }
        }

        if ($srs == '') {
            $epsgId = Projections::FindAuthority($this->prjFile);
            if ($epsgId) {
                if (stripos($epsgId, 'ESPG') > -1) {
                    $srs = "-s_srs $epsgId";
                }
            }
            if ($srs == '') {
                if (! is_null($this->srs)) {
                    $srs = "-s_srs '{$this->srs}'";
                }
            }
        }
        $table = $this->layer->url;

        $command = "ogr2ogr $srs -lco FID=gid -lco DROP_TABLE=IF_EXISTS -lco PRECISION=NO -lco GEOMETRY_NAME=the_geom -lco GEOM_TYPE=geometry -t_srs EPSG:4326 -f PGDump $tmp $file -nln $table -nlt GEOMETRY 2>&1";
        // "ogr2ogr -t_srs EPSG:4326 $tmp $file 2>&1";
        #var_dump($command);
        $res = shell_exec($command);
        #var_dump($res);

    }

    protected function getTableSQL($file, $layerId, $layerBaseName = "")
    {
        /*
         * $cmd = "shp2pgsql -p -s 4326 " . substr ( $file, 0, - 4 ) . ".tmp.shp";
         *
         * $tableInfo = shell_exec ( ($cmd) );
         *
         * $tableInfo = explode ( "\n", $tableInfo );
         * $layerName = $layerBaseName == "" ? $this->metadata ['layer'] : $layerBaseName . "_" . $this->metadata ['layer'];
         * $this->table = strtolower ( "vectordata_$layerId" );
         * $commands = array ();
         * $commands [] = "DROP TABLE IF EXISTS {$this->table};";
         *
         * foreach ( $tableInfo as $i => $line ) {
         *
         * $targetLayer = strtolower ( $this->metadata ['layer'] );
         *
         * if (strpos ( trim ( $line ), 'CREATE' ) === 0) {
         *
         * $i2 = $i + 1;
         * $line = str_replace ( "\"$targetLayer\"", "\"{$this->table}\"", $line );
         * $select = $line;
         *
         * while ( strrpos ( $tableInfo [$i2], ',' ) == (strlen ( $tableInfo [$i2] ) - 1) ) {
         * $select .= trim ( $tableInfo [$i2] );
         * $i2 ++;
         * }
         * $select .= trim ( $tableInfo [$i2] );
         * $i = $i2;
         *
         * $commands [] = $select;
         * } elseif (strpos ( $line, 'ALTER' ) === 0) {
         * $alter = str_replace ( $targetLayer, $this->table, $line );
         * $commands [] = $alter;
         * } elseif (stripos ( $line, 'SELECT AddGeometryColumn' ) === 0) {
         *
         * $geomColumnSQL = str_replace ( "'$targetLayer'", "'{$this->table}'", $line );
         * $geomColumnSQL = str_replace ( "'geom'", "'the_geom'", $geomColumnSQL );
         *
         * $geomColumnSQL = str_replace ( 'MULTI', '', $geomColumnSQL );
         * $params = explode ( ',', $geomColumnSQL );
         * $type = str_replace ( "'", '', $params [4] );
         *
         * $lastParam = array_pop ( $params );
         * $lastParam = str_replace ( ')', ',false)', $lastParam );
         * array_push ( $params, $lastParam );
         * $geomColumnSQL = implode ( ',', $params );
         * $multitype = 'MULTI' . $type;
         * $commands [] = $geomColumnSQL;
         * break;
         * }
         * }
         *
         * $commands [] = "alter table $this->table drop constraint enforce_geotype_the_geom;";
         * $commands [] = "alter table $this->table add constraint enforce_geotype_the_geom CHECK(geometrytype(the_geom) = '$type' OR geometrytype(the_geom) = '$multitype' OR the_geom IS NULL);";
         * // Alter table vectordata_366 drop constraint enforce_geotype_the_geom_2;
         * // Alter table vectordata_366 add constraint enforce_geotype_the_geom_2 CHECK(geometrytype(the_geom_2) = 'LINESTRING'::text OR geometrytype(the_geom_2) = 'MULTILINESTRING'::text OR the_geom_2 IS NULL);
         * $this->commands = $commands;
         */
    }

    protected function getLayer($shpFile)
    {
        $info = shell_exec('ogrinfo ' . $shpFile);
        $info = explode("\n", $info);
        array_shift($info);
        array_shift($info);

        $layers = array();

        foreach ($info as $i => $line) {
            if (trim($line) == "")
                continue;

            list($index, $layername) = explode(":", $line);
            $index = (int) $index;
            $layername = trim($layername);
            list($layer, $type) = explode(' ', $layername);
            $layer = trim($layer);
            $type = trim(str_replace(array(
                '(',
                ')'
            ), '', $type));
            // type = str_replace(')','',$type);
            $layer = array(
                'layer' => $layer,
                'type' => strtolower($type)
            );
            $layer['info'] = $this->getInfo($shpFile, $layer);

            return $layer;
        }
    }

    protected function getInfo($shpFile, $layerMeta)
    {
        $layerName = $layerMeta['layer'];
        $info = shell_exec("ogrinfo -so $shpFile $layerName");
        $info = explode("\n", $info);
        array_shift($info);
        array_shift($info);
        $lastItem = "";
        $isMultiLine = false;
        $layerInfo = array();
        $layerInfo['fields'] = array();
        foreach ($info as $i => $line) {
            if (trim($line) == "")
                continue;
            if (strpos($line, ":") > 0) {
                $data = explode(':', $line);
                if (count($data) == 2) {
                    switch ($data[0]) {
                        case 'Layer name':
                            $layerInfo['name'] = trim($data[1]);
                            break;
                        case 'Extent':
                            $layerInfo['extent'] = $data[1];
                            break;
                        case 'Feature Count':
                            $layerInfo['count'] = $data[1];
                            break;
                        case 'Layer SRS WKT':
                            $i2 = $i + 1;
                            $wkt = "";
                            while (strrpos($info[$i2], ',') == (strlen($info[$i2]) - 1)) {
                                $wkt .= trim($info[$i2]);
                                $i2++;
                            }
                            $i = $i2;
                            $i = $i2;
                            $wkt .= trim($info[$i2]);

                            $layerInfo['srs'] = trim($wkt);
                            break;
                        default:
                            $layerInfo['fields'][trim($data[0])] = trim($data[1]);
                            break;
                    }
                }
            }
        }

        return $layerInfo;
    }
}
// dbInfo = array('db'=>'cartograph','pw'=>'5l1pp3ry!');
// reader = new OGRReader($shpFile,'0arttest','newdata',$dbInfo);
