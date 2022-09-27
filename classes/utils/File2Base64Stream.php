<?php

namespace utils;

class File2Base64Stream {
	public static function Serialize($filename, $outputStream = 'php://stdout') {
		if (! file_exists ( $filename )) {
			throw new \Exception ( "File not found" );
		}
		$out = fopen ( $outputStream, 'w' );
		$fh = fopen ( $filename, 'r+' );
		$size = filesize ( $filename )-1;
		$extra = $size % 3;
		$triplets =  floor ( $size / 3 );
		
		$pad = $extra > 0 ? str_repeat ( '=', 3 - $extra ) : '';
		$leftMask = 63 << 18;
		$midLeftMask = 63 << 12;
		$midRightMask = 63 << 6;
		$rightMask = 63;
		$encodingTable = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
		$chars = 0;
		for($ctr = 0; $ctr < $triplets; $ctr ++) {
			
			$triplet = ord ( fread ( $fh, 1 ) ) << 16;
			$triplet += ord ( fread ( $fh, 1 ) ) << 8;
			$triplet += ord ( fread ( $fh, 1 ) );
			
			fwrite ( $out, substr ( $encodingTable, (($triplet & $leftMask) >> 18), 1 ) );
			fwrite ( $out, substr ( $encodingTable, (($triplet & $midLeftMask) >> 12), 1 ) );
			fwrite ( $out, substr ( $encodingTable, (($triplet & $midRightMask) >> 6), 1 ) );
			fwrite ( $out, substr ( $encodingTable, $triplet & $rightMask, 1 ) );
			$chars+=4;
			if($chars == 76) {
				fwrite( $out, "\r\n");
				$chars = 0;
			}
		}
		if ($extra) {
			$triplet = ord ( fread ( $fh, 1 ) ) << 16;
			
			if ($extra == 2) {
				$triplet += ord ( fread ( $fh, 1 ) ) << 8;
			} 
			
			if ($triplet > 0) {
				$left = substr ( $encodingTable, ($triplet & $leftMask) >> 18, 1 );
				$midLeft = substr ( $encodingTable, ($triplet & $midLeftMask) >> 12, 1 );
				$midRight = substr ( $encodingTable, ($triplet & $midRightMask) >> 6, 1 );
				$right = substr ( $encodingTable, $triplet & $rightMask, 1 );
					
				if ($extra == 2) {
					fwrite ( $out, $left . $midLeft.$midRight );
				} elseif ($extra == 1) {
					
					fwrite ( $out, $left.$midLeft);
					
				} 
			}
			fwrite($out, $pad);
		}
		fclose ( $fh );
		fclose ( $out );
	}
}

?>