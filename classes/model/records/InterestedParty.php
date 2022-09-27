<?php

namespace model\records;

class InterestedParty
extends CachedRecord {
	
	/* (non-PHPdoc)
	 * @see \model\records\CachedRecord::__construct()
	 */
	public function __construct( $record, $table = 'interested_parties') {
		
		// TODO: Auto-generated method stub

		$this->record = $record;
		$this->table = 'interested_parties';
		$this->idField ='id';
		$this->nameField = 'name';
		$this->hasModified = false;
		$this->private_fields = array();
		$this->readOnly_fields = array('id');
		$this->boolean_fields = array('requested_trial','had_trial','had_demo');
		parent::__construct($record,$table);
	}

}

?>