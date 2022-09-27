<?php
namespace model\records;

class SharedItem
extends CachedRecord {
	
	const SHARELEVEL = 'sharelevel';
	 
	public function __construct($record,$table)
	{
		$this->record = $record;
		$this->table = $table;
		$this->hasModified = false;
		$this->idField = 'id';
			
		$this->private_fields = array();
		$this->readOnly_fields = array();
		$this->boolean_fields = array();
		
	}
}
?>