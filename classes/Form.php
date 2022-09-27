<?php
use model\CRUD;
/*
 * Created on Sep 14, 2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
class Form extends CRUD{
	protected $fields;
	function __construct(&$world, $id) {
		$this->world = $world;
		$this->table = "forms";
		$this->idField = 'id';
		$this->objectId = $id;
		$this->arrayOfObjectsRetrivedRowData = $this->getAllFieldsAsArray();
		if($this->arrayOfObjectsRetrivedRowData == null) throw new Exception("No such form: $id.");
		$this->isReadyOnly = false;
	}
	public function Update($updates) {
		parent::Update($updates);
		$this->arrayOfObjectsRetrivedRowData = $this->getAllFieldsAsArray();
	}
	public function __get($name) {
		if($name == "owner") return $this->world->getPersonById($this->arrayOfObjectsRetrivedRowData[$name]);
		if($name == "layer") return $this->world->getLayerById($this->arrayOfObjectsRetrivedRowData[$name]);
		if($name == "fields"){
			$temp = json_decode($this->arrayOfObjectsRetrivedRowData[$name]);
			$attributes = $this->layer->getAttributesVerbose(false,true);
			foreach($temp as $name=>&$info){
				$info->display = $attributes[$name]["display"];
				$info->requires = $attributes[$name]["requires"];
				if($info->dataType == 3) $info->default = date('Y-m-d', time()+($info->offset*86400));
			}
			return $temp;
		}
		if(isset($this->arrayOfObjectsRetrivedRowData[$name])) return $this->arrayOfObjectsRetrivedRowData[$name];
	}
}
?>
