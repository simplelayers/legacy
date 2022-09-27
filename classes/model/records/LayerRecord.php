<?php
use model\records\SharedItem;
class LayerRecord 
extends SharedItem {



	public function __construct($record,$table) {
		$this->hasModified = false;
		$this->idField = 'id';
	}
	

		
}


?>