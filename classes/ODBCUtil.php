<?php
class ODBCUtil {
	// constants pertaining to ODBC data sources
	const MYSQL = 'MySQL';
	const PGSQL = 'PostgreSQL';
	const MSSQL = 'Microsoft SQL Server';
	const MYSQL_PORT = 3306;
	const PGSQL_PORT = 5432;
	const MSSQL_PORT = 1433;
	private static $ports = NULL;
	public static function GetPorts($replace = false) {
		if ((self::$ports !== NULL) and ! $replace)
			return self::$ports;
		self::$ports = new Enum ( array () );
		self::$ports->AddItem ( self::MYSQL, self::MYSQL_PORT );
		self::$ports->AddItem ( self::PGSQL, self::PGSQL_PORT );
		self::$ports->AddItem ( self::MSSQL, self::MSSQL_PORT );
		
		return self::$ports;
	}
	public static function GetPort($server) {
		self::GetPorts ();
		return self::$ports [$server];
	}
	public static function GetDB($odbcInfo) {
		switch ($odbcInfo['serverType']) {
			case self::MYSQL :
				return NewADOConnection ( "mysql://{$odbcInfo->odbcuser}:{$odbcInfo->odbcpass}@{$odbcInfo->odbchost}/{$odbcInfo->odbcbase}?port={$odbcInfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC );
			case self::PGSQL :
				return NewADOConnection ( "postgres://{$odbcInfo->odbcuser}:{$odbcInfo->odbcpass}@{$odbcInfo->odbchost}/{$odbcInfo->odbcbase}?port={$odbcInfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC );
			case self::MSSQL :
				list ( $odbc, $odbcini, $freetdsconf ) = self::SetupODBCIni( $odbcInfo, 'NOCONNECT' );
				return NewADOConnection ( "mssql://{$odbcInfo->odbcuser}:{$odbcInfo->odbcpass}@dsn/{$odbcInfo->odbcbase}?port={$odbcInfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC );
				break;
		}
	}
	
	
	public static function Truncate($odbcInfo) {
		$db = self::GetDB($odbcInfo);
			
		switch ($odbcinfo->driver) {
			case self::MYSQL :
				$db->Execute ( "TRUNCATE TABLE `{$odbcinfo->table}`" );
				break;
			case self::PGSQL :
				$db->Execute ( "TRUNCATE TABLE \"{$odbcinfo->table}\"" );
				$db->Execute ( "SELECT SETVAL ('{$odbcinfo->table}_id_seq',1)" );
				break;
			case self::MSSQL :
				$db->Execute ( "DELETE FROM \"{$odbcinfo->table}\"" );
				break;
		}
	}
	
	public static function SetupODBCIni($odbcInfo, $connect = false) {
		$ini = System::GetIni ();
		$filename = md5 ( var_export ( $odbcInfo, true ) );
		$dirname = $ini->tempdir . "/odbc";
		if (! isset ( $dirname )) {
			
			mkdir ( $dirname );
		}
		$odbcini_filename = sprintf ( "%s/%s.odbc.ini", $dirname, $filename );
		$freetdsconf_filename = sprintf ( "%s/%s.freetds.conf", $dirname . $filename . '.freetds.conf' );
		$tds = <<<TDS
[global]
tds version=7.0
text size = 64512
host=%s
port = %d
TDS;
		$freetdsconf_content = sprintf ( $tds, $odbcInfo->odbchost, $odbcInfo->odbcport );
		file_put_contents ( $freetdsconf_filename, $freetdsconf_content );
		
		putenv ( "FREETDSCONF=$freetdsconf_filename" );
		
		$odbcini = <<<ODBCINI
[dsn]
Driver = %s
%s = %s
Port = %d
Database = %s
ODBCINI;
		$odbcini_content = sprintf ( $odbcini, $odbcInfo->driver, $odbcInfo->driver == ODBCUtil::PGSQL ? 'Servername' : 'Server', $odbcInfo->odbchost, $odbcInfo->odbcbase );
		if ($odbcInfo->driver == ODBCUtil::MSSQL)
			$odbcini_content .= "TDS_Version = 7.0\n";
		file_put_contents ( $odbcini_filename, $odbcini_content );
		
		putenv ( "ODBCINI=$odbcini_filename" );
		
		// and do the connection, now that there's a proper DSN given
		if ($connect)
			$odbc = odbc_connect ( 'dsn', $odbcInfo->odbcuser, $odbcInfo->odbcpass, SQL_CUR_USE_ODBC );
		else
			$odbc = null;
		return array (
				$odbc,
				$odbcini_filename,
				$freetdsconf_filename 
		);
	}
}
?>