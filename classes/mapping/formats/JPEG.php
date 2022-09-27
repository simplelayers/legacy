<?php

namespace mapping\formats;

class JPEG extends Format {
	protected $bgcolor_web = '#cccccc';
	protected $name = 'jpeg';
	protected $mimetype = 'image/jpeg';
	protected $driver = 'AGG/JPEG';
	protected $extension = 'jpg';
	protected $imagemode = MS_IMAGEMODE_RGB;
	protected $transparent = MS_OFF;
	protected $compress =  null;
	protected $quality=100;
	protected $interlace = MS_OFF;
	protected $quantize_force = null;
	protected $quantize_colors = null;
	protected $quantize_new = null;
	
	const QUALITY_LOW = 0;
	const QUALITY_HIGH = 1;
	
	public function __construct($qualityLevel) {
		switch($qualityLevel) {
			case QUALITY_LOW:
				$this->driver = 'GD/JPEG';
				break;
			default:
				//leave default
				break;
		}
		
	}
}

?>