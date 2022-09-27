<?php
namespace model;


class SmartLayerSrc {

	protected $isSpatialLayer = false;
	
	public function __construct($layerId, $fields=null,$filters=null,$joinType=null) {
		if(is_null($joinType)) $isSpatialLayer = true;
		
		
	}

}

?>