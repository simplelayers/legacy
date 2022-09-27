<?php
namespace utils\OGR;

use utils\OGR\OGRUtil;
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
class OGR_KML_Util {
	/**
	 * Stores info retrieved from ogrinfo about the target shp file.
	 *
	 * @var array
	 */
	public $metadata;
	public $hasGeom = true;
	protected $commands;
	protected $layerId;
	protected $srcFile;
	protected $dbInfo;
	protected $table;
	protected $file;
	protected $tmpFile;
	protected $copyFile;
	protected $srs;
	protected $sqlFile;
	protected $kmlFile;
	protected $prjFile;
	protected $format = "KML";
	
	/* OGR call to create just the copy commands.
	 * ogr2ogr -lco LAUNDER=YES  -lco CREATE_SCHEMA=OFF  -lco CREATE_TABLE=OFF  -lco FID=gid --config PG_USE_COPY YES -f PGDump  /home/art/public_html/simplelayers/_tests/temp/files/test.tmp.sql /home/art/public_html/simplelayers/_tests/temp/files/test.kml
	 */
	public function Import($file, $layerId) {
		$srs =null;
		$ini = \System::GetIni();
		
		$this->layerId = $layerId;
		$this->srs = $srs;
		$this->dbInfo = OGRUtil::GetDBInfo();
		$file = str_replace ( ' ', '\\ ', $file );
		$this->srcFile = $file;
		$filename_common = explode ( '.', $file );
		$filename_common = implode ( ".", $filename_common );
		
		$this->metadata = OGRUtil::GetLayerInfo( $file );
		$this->ConvertFile ( $file );
		$this->GetTableSQL ( $this->tmpFile, $layerId );
		$this->InsertRecords();

	}
	public function InsertRecords() {
		$stats = array ();
		$stats ['problems'] = array ();

		if (! file_exists ( $this->copyFile )) {
			throw new \Exception ( "Unable to produce sql file: " . $this->copyFile );
		}
		
		$pgsql = "psql -h {$this->dbInfo['host']} --username={$this->dbInfo['user']} -w -1 --dbname={$this->dbInfo['db']}";		

		$db = \System::GetDB ( \System::DB_ACCOUNT_SU );
		$file = $this->srcFile;
		
		$cmds = implode ( ' ', $this->commands );
		
		$cmd = "export PGPASSWORD='{$this->dbInfo['pw']}';";
		$cmd .= "echo " . escapeshellarg ( $cmds ) . " |";
		$cmd .= $pgsql." ;";
		$res = shell_exec ( $cmd );
		
		// $cmd = "export PGPASSWORD='{$this->dbInfo['pw']}';";
		
		$cmd = "export PGPASSWORD='{$this->dbInfo['pw']}';";
		$cmd.= 	"$pgsql < {$this->copyFile}";
	
		$res = shell_exec ( $cmd );
			
		$cmd = "export PGPASSWORD='';";
		$res = shell_exec ( $cmd );
		
		return;
	}
	protected function GetEncodingCode($dbf) {
		$file = fopen ( $dbf, 'r' );
		if (! $file)
			return false;
		fseek ( $file, 29 );
		$code = ord ( fread ( $file, 1 ) );
		fclose ( $file );
		return $code;
	}
	protected function GetRecordInfo($record, &$stats) {
		if (! isset ( $stats ['url_fields'] )) {
			$stats ['url_fields'] = array ();
			$stats ['tagged_fields'] = array ();
			$stats ['added'] = 0;
			$stats ['fields'] = array ();
		}
		foreach ( $record as $field => $data ) {
			if (strlen ( $field ) == 1)
				continue;
			
			$isURL = (stripos ( $data, 'http' ) === 0);
			$isURL = $isURL || (stripos ( $data, 'ftp' ) === 0);
			$isURL = $isURL || (stripos ( $data, 'sftp' ) === 0);
			$isURL = $isURL || (stripos ( $data, 'sftp' ) === 0);
			$isURL = $isURL || (stripos ( $data, 'mailto' ) === 0);
			$isURL = $isURL || (stripos ( $data, 'gopher' ) === 0);
			$isURL = $isURL || (stripos ( $data, 'news' ) === 0);
			$isURL = $isURL || (stripos ( $data, 'telnet' ) === 0);
			$isURL = $isURL || (stripos ( $data, 'ssh' ) === 0);
			$isURL = $isURL || (stripos ( $data, '\\' ) === 0);
			
			if ($isURL) {
				if (! in_array ( $field, $stats ['url_fields'] ))
					$stats ['url_fields'] [] = $field;
			}
			if ($data != strip_tags ( $data )) {
				if (! in_array ( $field, $stats ['tagged_fields'] ))
					$stats ['tagged_fields'] [] = $field;
			}
			if (is_int ( $data ) || is_float ( $data )) {
				if (! isset ( $stats ['fields'] [$field] )) {
					$stats ['fields'] [$field] ['nulls'] = ($data == 'NULL') ? 0 : 1;
					if (! ($data == 'NULL')) {
						$stats ['fields'] [$field] ['min'] = $data;
						$stats ['fields'] [$field] ['max'] = $data;
					}
				} else {
					if ($data == 'NULL') {
						$stats ['fields'] [$field] ['nulls'] += 1;
					} else {
						$stats ['fields'] [$field] ['min'] = min ( $stats ['fields'] [$field] ['min'], $data );
						$stats ['fields'] [$field] ['max'] = max ( $stats ['fields'] [$field] ['max'], $data );
					}
				}
			} else {
				if (! isset ( $stats [$field] )) {
					$stats ['fields'] [$field] = array ();
				}
				$stats ['fields'] [$field] ['nulls'] = ($data == 'NULL') ? 0 : 1;
			}
		}
		$stats ['added'] += 1;
	}
	protected function ConvertFile($file,  $srid = 4326) {
		
		$srs = "";
		if (file_exists ( substr ( $file, 0, - 4 ) . ".tmp.sql" ))
			shell_exec ( "rm " . substr ( $file, 0, - 4 ) . ".tmp.*" );
		$tmp = substr ( $file, 0, - 4 ) . ".tmp.sql";
		
		if ($this->metadata ['info']) {
			$infoSrs = $this->metadata ['info'] ['srs'];
			if ((stripos ( $infoSrs, 'unknown' ) > - 1)) {
				$this->srs = $infoSrs;
				$srs = '-s_srs ' . $this->srs;
			} else {
				if (! is_null ( $this->srs ))
					$srs = '-s_srs ' . $this->srs;
			}
		}
		$command = "ogr2ogr -lco FID=gid -lco DROP_TABLE=IF_EXISTS -lco GEOMETRY_NAME=the_geom -lco LINEFORMAT=LF -f PGDump -t_srs EPSG:4326  $tmp $file -nln vectordata_".$this->layerId." 2>&1";
		$res = shell_exec ( $command );
		$file2 = $tmp.'.copy';
		$this->tmpFile = $tmp;
		
		$command = "ogr2ogr -lco LAUNDER=YES -lco GEOMETRY_NAME=the_geom -lco CREATE_SCHEMA=OFF  -lco CREATE_TABLE=OFF  -lco FID=gid  -f PGDump $file2 $file -nln vectordata_".$this->layerId;
		$res = shell_exec ( $command );
		$this->copyFile = $file2;
	}
	
	protected function GetTableSQL($file, $layerId, $layerBaseName = "") {
		//$cmd = "shp2pgsql -p -s 4326 " . substr ( $file, 0, - 4 ) . ".tmp.shp";
 
		$tableInfo = file_get_contents($file);
		
		$tableInfo = explode ( "\n", $tableInfo );
		$layerName = $layerBaseName == "" ? $this->metadata ['layer'] : $layerBaseName . "_" . $this->metadata ['layer'];
		$this->table = strtolower ( "vectordata_$layerId" );
		$commands = array ();
		//$commands [] = "DROP TABLE IF EXISTS {$this->table};";
		
		foreach ( $tableInfo as $i => $line ) {
			
			$targetLayer = '"public"."'.strtolower ( $this->metadata ['layer'] ).'"';
			
			if(stripos(trim($line),'INSERT')!== false) {
				break;
			} elseif(stripos ( $line, 'SELECT AddGeometryColumn' ) === 0) {
				$geomColumnSQL = str_replace ( "'$targetLayer'", "'{$this->table}'", $line );
				
				$geomColumnSQL = str_replace ( "'geom'", "'the_geom'", $geomColumnSQL );
				
				$geomColumnSQL = str_replace ( 'MULTI', '', $geomColumnSQL );
				
				$params = explode ( ',', $geomColumnSQL );
				$type = str_replace ( "'", '', $params [4] );
				$multitype = 'MULTI' . $type;
			}
			 
			$commands[] = $line; 
			
		}
		
		$commands [] = "alter table $this->table drop constraint IF EXISTS enforce_geotype_the_geom ;";
		$commands [] = "alter table $this->table add constraint enforce_geotype_the_geom CHECK(geometrytype(the_geom) = '$type' OR geometrytype(the_geom) = '$multitype' OR the_geom IS NULL);";
		$commands[] = "COMMIT;";
		$this->commands = $commands;
	}
	
}

?>
