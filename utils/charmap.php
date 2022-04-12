<?php 
ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);
?>
<?php
// Create a 300x100 image
$font = $_REQUEST['font'];
$font_file = "/usr/local/fonts/$font.ttf";
$info = imageloadfont($font_file);

$start =33;
$image = renderGlyphs($font, $start);

function renderGlyphs($font, $start,$count=255) {
	$end = $count*2;


	$bg =  0xEEEEEE;
	$color  = 0x0;
	$cols = 25;
	$width=25;
	$height=25;
	
	$charMap = imagecreatetruecolor($width*$cols,$height*(ceil($count/$cols)));
	$row = 0;
	$size=18;
	$char = $start;
	$angle = 0;
	for($col = 0; $col < $cols; $col++ ) {
		$left = ($col*$width);
		$right = $left+$width;
		$top = ($row*$height);
		$bottom = $top+$height;
		$image = makeGlyphImage($font,$width,$height,$size,chr($char),$bg,$color,$angle);
		
		//;
		imagecopy($charMap, $image, $left, $top,0, 0,$width,$height);
		//$destroyImage($image);
			
		if($col==$cols-1) {
			$row+=1;
			$col=-1;
		}
		$char+=1;
		if($char > $end) break;
	}
	return $charMap;
}
// Output image to the browser
header('Content-Type: image/png');

imagepng($image);
imagedestroy($image);

function makeGlyphImage($font, $width,$height, $ptSize,$char,  $bg,  $color,$angle=0) {
$img = imagecreatetruecolor($width,$height);
	
$bg  = makeColor($img, $bg);
$color  = imagecolorallocate($img, $color);


// Make the background red
imagefilledrectangle($img, 0, 0, $width, $height, $bg);

// Path to our ttf font file
$font_file = "/usr/local/fonts/$font.ttf";

// First we create our bounding box for the first text
$bbox = imagettfbbox($ptSize, $angle, $font_file, $char);
list($llx,$lly,$lrx,$lry,$urx,$ury,$ulx,$uly) = $bbox;
// This is our cordinates for X and Y
$x = $llx + (imagesx($img) / 2) - ($urx / 2) ;
$y = $lly[1] + (imagesy($img) / 2) - ($ury / 2);

imagefttext($img, $ptSize,$angle, $x, $y, $color, $font_file,$char );
return $img;
}

function makeColor($img, $color ) {
	$red = ($color & 0xFF0000)>>16;
	$green = ($color & 0x00FF00)>>8;
	$blue = ($color & 0x0000FF);
	
	$color  = imagecolorallocate($img, $red, $green, $blue);
	return $color;
}



?>