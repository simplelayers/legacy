<?php

namespace mapping\formats;

class PNG extends Format {
	protected $bgcolor_web = '#cccccc';
	protected $name = 'png';
	protected $mimetype = 'image/png';
	protected $driver = 'AGG/PNG';
	protected $extension = 'png';
	protected $imagemode = MS_IMAGEMODE_RGBA;
	protected $transparent = MS_OFF;
	protected $compress =  null;
	protected $quality=null;
	protected $interlace = MS_OFF;
	protected $quantize_force = null;
	protected $quantize_colors = null;
	protected $quantize_new = null;
	
	const QUALITY_LOW = 0;
	const QUALITY_HIGH = 1;
	const INTERLACE_ON = true;
	const INTERLACE_OFF = false;
	const QUANTIZE_ON = true;
	const QUANTIZE_OFF = false;
	
	public function __construct($qualityLevel,$interlace=false,$quantize=false) {
		switch($qualityLevel) {
			case QUALITY_LOW:
				$this->driver = 'GD/PNG';
				$this->imagemode = MS_IMAGEMODE_RGB;
				$this->transparent = MS_ON;
				break;
			default:
				//leave default
				break;
		}
		
		if($interlace) $this->interlace = MS_ON;
		if($quantize) {
			$this->quantize_force = 'ON';
			$this->quantize_colors = 256;
			$this->quantize_new = 'ON';
		}
	}
}

?>