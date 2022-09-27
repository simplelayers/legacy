<?php

namespace utils\OGR;

class OGRUtil {

    public static function GetDBInfo() {
        $ini = \System::GetIni();
        $dbInfo = array (
            'db' => $ini->pg_sl_db,
            'host' => $ini->pg_host,
            'user' => $ini->pg_admin_user,
            'pw' => $ini->pg_admin_password
        );
        return $dbInfo;
    }
    
	public static function GetLayerInfo($filePath=null,$hasGeom=true) {
                $cmd = "ogrinfo \"{$filePath}\"";
                $info = shell_exec ( $cmd);
                $info = explode ( "\n", $info );
		$layers = array ();
		$begun = false;
	
		foreach ( $info as $i => $line ) {
			
			if (trim ( $line ) == "")
				continue;
			
			$isStartLine = (stripos($line, "using driver")!== false);
			
			
			if(!$begun && !$isStartLine) continue;
			if($isStartLine) {
				$begun =true;
				continue;
			}
			
			list ( $index, $layername ) = explode ( ":", $line );
			$index = ( int ) $index;
			// $layername = trim(preg_replace(['/\([Nn]one\)$/'], [''], trim($layername)));
                       
		    $nameSegs = explode('(',$layername);
		    $type = array_pop($nameSegs);
		    $nameSegs = implode('(',$nameSegs);
			
			$layer = trim ( $nameSegs );
			$type = trim ( str_replace ( array (
					'(',
					')'
			), '', $type ) );
			// type = str_replace(')','',$type);
			$layer = array (
					'layer' => $layer,
					'type' => strtolower ( $type )
			);
		
			$layer ['info'] = self::GetInfo ( $filePath, $layer );
			return $layer;
		}
		
		$layer = array (
		    'layer' => $layer,
		    'type' => strtolower ( $type )
		);
                return $layer;

	}

	public static function GetInfo($fileName, $layerMeta) {
	    
		$layerName = $layerMeta ['layer'];
		
		$cmd = "ogrinfo -so \"$fileName\" \"$layerName\"";
		   
		$info = shell_exec ( $cmd );
		
		$info = explode ( "\n", $info );
		
		$lastItem = "";
		$isMultiLine = false;
		$layerInfo = array ();
		$layerInfo ['fields'] = array ();
		foreach ( $info as $i => $line ) {
			if (trim ( $line ) == "")
				continue;
			if (strpos ( $line, ":" ) !== false) {
				$data = explode ( ':', $line );
				if (count ( $data ) == 2) {
					switch ($data [0]) {
						case 'Layer name' :
							$layerInfo ['name'] = trim ( $data [1] );
							break;
						case 'Extent' :
							$layerInfo ['extent'] = $data [1];
							break;
						case 'Feature Count' :
							$layerInfo ['count'] = $data [1];
							break;
						case 'Layer SRS WKT' :
							$i2 = $i + 1;
							$wkt = "";
							while ( strrpos ( $info [$i2], ',' ) == (strlen ( $info [$i2] ) - 1) ) {
								$wkt .= trim ( $info [$i2] );
								$i2 ++;
							}
							$i = $i2;
							$i = $i2;
							$wkt .= trim ( $info [$i2] );
								
							$layerInfo ['srs'] = trim ( $wkt );
							break;
						default :
							$layerInfo ['fields'] [trim ( $data [0] )] = trim ( $data [1] );
							break;
					}
				}
			}
		}
	
		return $layerInfo;
	}
	
	
}

?>