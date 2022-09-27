<?php

namespace model;



user \Person;

class SmartLayer
extends MongoCRUD {
	protected $collectionName = 'smart_layers';
	protected $layer;
	
	protected $sourceInfo;
	protected $targetInfo;
	
	private $layerName;
	
	public function __construct() {
		parent::__construct();
	}
	
	public function CreateSmartLayer($layerName,$sourceInfo,$criteria=null,$targetInfo=null) {
		$user = \SimpleSession::Get()->GetUser();
		$this->layer = $user->createLayer($layerName,\LayerTypes::SMART_LAYER);
		
	}
	
	
	
	
}

?>