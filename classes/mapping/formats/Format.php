<?php

namespace mapping\formats;
use utils\ColorUtil;

class Format {
	protected $bgcolor_web = '#ffffff';
	protected $name = '';
	protected $mimetype = '';
	protected $driver = '';
	protected $extension = '';
	protected $imagemode = '';
	protected $transparent = null;
	protected $compress =  null;
	protected $quality=null;
	protected $interlace = null;
	protected $quantize_force = null;
	protected $quantize_colors = null;
	protected $quantize_new = null;
	
	public function SetOutputFormat($map ) {
		$map->outputformat->name = $this->name;
		$map->outputformat->mimetype = $this->mimetype;
		$map->outputformat->driver = $this->driver;
		$map->outputformat->extension=$this->extesion;
		$map->outputformat->extension=$this->imagemode;
		if(!is_null($this->transparent)) $map->outputformat->extension=$this->transparent;
		if(!is_null($this->compress)) $map->outputformat->setOption('COMPRESS',$this->compress);
		if(!is_null($this->quality)) $map->outputformat->setOption('quality',$this->quality);
		if(!is_null($this->interlace)) $map->outputformat->setOption('INTERLACE',$this->interlace);
		if(!is_null($this->quantize_force)) $map->outputformat->setOption('QUANTIZE_FORCE',$this->quantize_force);
		if(!is_null($this->quantize_colors)) $map->outputformat->setOption('QUANTIZE_FORCE',$this->quantize_colors);
		if(!is_null($this->quantize_new)) $map->outputformat->setOption('QUANTIZE_NEW',$this->quantize_new);
		
		$r = $g = $b = -1;
		ColorUtil::Web2RGB($this->bgcolor_web,$r,$g,$b);
		$map->imagecolor->setRGB($r,$g,$b);
	}
}


?>