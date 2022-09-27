<?php

namespace utils;

class AssetUtil {
	
	public static function GetAsset(array $params) {
		
	    
	    
		ParamUtil::Requires ( $params, 'asset','format' );
		$asset = ParamUtil::Get ( $params, 'asset' );
		$assetParts = explode('.',trim($asset));
		$format = ParamUtil::Get( $params, 'format',array_pop($assetParts));
		
		$path = explode('/',$asset);
		$asset = array_pop($path);
		array_unshift($path,'media');
		$path = implode('/',$path);
	
		$ini = \System::GetIni ();
		$assets = ($path) ? self:: GetAssetPath($format,$path) : self::GetAssetPath($format);
		
		$fileName = $assets . $asset;
		
		self::SetHeader ( $format );
		#self::SetContentLength($fileName);
		self::ReadFile( $fileName  );
		return;
	}
	
	public static function GetTempAsset(array $params) {
	    ParamUtil::Requires ( $params, 'asset','format' );
	    $asset = ParamUtil::Get ( $params, 'asset' );
	    $format = ParamUtil::Get ( $params, 'format',array_pop(explode('.',trim($asset))));
	    
	    $path = explode('/',$asset);
	    $asset = array_pop($path);
	    array_unshift($path,'media');
	    $path = implode('/',$path);
	    
	    $ini = \System::GetIni ();
	    $path = $ini->tempdir.$asset;
	    self::SetHeader ( $format );
	    readfile($path);
	    
	}
	
	public static function GetAssetPath($format,$path=null) {
		$ini = \System::GetIni ();
		$path = is_null($path) ? '' : $path;
		
		$isSandboxed = \System::IsSandboxed ();
		
		switch ($format) {
			case 'swf' :
				$path = WEBROOT.'media/swfs/';//($isSandboxed) ? WEBROOT . $ini->swf_path_sandbox : $ini->swf_path;
				break;
			case 'text' :
			case 'js' :
				$path .= WEBROOT . $ini->js_path;
				break;
			case 'libjs' :
				$path .= WEBROOT . $ini->libjs_path;
				break;
			case 'png' :
				if($path != "") {
					return WEBROOT."/$path/";
				}
				$path = $ini->tempurl;
				break;
		}
		return $path;
	}
	
	public static function SetContentLength($fileName) {
		header ( 'Content-Length: ' . filesize ( $fileName ) );
	}
	
	public static function SetHeader($format) {
		switch ($format) {
			case "swf" :
				header ( "Content-Type: application/octet-stream" );
				break;
			case "png" :
				header ( 'Content-type: image/png', true );
				break;
			case "text" :
				header ( 'Content-type: text/javascript', true );
		}
	}
	
	public static function ReadFile($filename) {
		
		if (! file_exists ( $filename ))
			return;
		$fh = fopen ( $filename, 'rb' );
		if ($fh) {
			while ( ! feof ( $fh ) )
				print fread ( $fh, 1048576 );
			fclose ( $fh );
		}
	}
}

?>
