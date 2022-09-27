<?php

namespace model;

use model\records\InterestedParty;
class InterestedParties extends CRUD {
	/*
	 * "id" : 1234, 
	 * "when_submitted" : "2013-08-04 02:46 PM", 
	 * "name" : "Arthur Clifford", 
	 * "email" : 'art@simplelayers.com', 
	 * "organization" : "simplelayers.com", 
	 * "title" : 'CTO', "referred_by" : 'Solana Foo', "referred_by_email" : 'foo@cartograph.com', "comments" : 'This guy needs to wake up earlier.', "ipaddress" : '71.202.198.15', "requested_trial" : 0, "account" : 0, "is_customer" : 0, "had_trial" : 0, "region" : 'Lompoc, CA', 'lon' : -120.46988699999997, 'lat' : 34.63121599999999, "trial_started" : '2013-08-04 02:46 PM', "account_created" : '2013-08-04 02:46 PM', "demo_date" : '2013-08-04 02:46 PM', "had_demo" : 0
	 */
	public function __construct() {
		parent::__construct ();
		$this->table = 'interested_parties';
		$this->idField ='id';
		$this->nameField = 'name';
		$this->hasModified = false;
		$this->creationFields = array('name','email','ipaddress','organization','comments','when_submitted');
		$this->pre_check_params = array('name','email');
		
	}
	

	public function Create($name,$email,$ipaddress=null,$organization='',$comments='') {
		$params = array();
		$params['name'] = $name;
		$params['email'] = $email;
		if(!is_null($ipaddress) ) $params['ipaddress'] =  $ipaddress;
		$params['organization'] = $organization;
		$params['comments'] = $comments;
		$params['when_submitted'] = "Now()";
		
		$record = parent::Create($params);
		
		$params['when_submitted'] = $record['when_submitted'];
		return array('params'=>$params,'record'=>$record);		
		
	}

	public function GetObject($result) {
		return new InterestedParty($result);
	}
	
}
	

?>