<?php

namespace mapping\formats;

class GeoTIFF extends Format {
	protected $bgcolor_web = '#cccccc';
	protected $name = 'gif';
	protected $mimetype = 'image/tiff';
	protected $driver = 'GDAL/GTiff';
	protected $extension = 'tiff';
	protected $imagemode = MS_IMAGEMODE_RGB;
	protected $transparent = MS_OFF;
	protected $compress =  DEFLATE;
	protected $quality=null;
	protected $interlace = null;
	protected $quantize_force = null;
	protected $quantize_colors = null;
	protected $quantize_new = null;
}

?>