<?php

namespace comms;

/**
 * Sample structure:
 * _id: mongo id,
 * subject: "some sobject",
 * comments: [] // array of comment structures (see comment.php
 * num_comments: 1234; // total number of comments.
 */
use model\MongoCRUD;
class Thread
extends MongoCRUD
{
	
	//protected $subject = "";
	//protected $comments = array();
	//protected $moderators = array();
	protected $cursor  = null;
	

	protected $collection = 'threads';
	
	public function __construct( $id=null) {
		parent::__construct();
		if(!is_null($id)) {
			$cursor = $this->GetById($id);
		}
	}
	
	public function AddModerator($userId) {
		
	}
	
	public function RemoveModerator($userId) {
		
	}
	
	public function IsModerator($userId) {
		
	}
	
	public function AddComment($comment) {
		
	}
	public function GetComments( $format ) {
		
	}
	
	public function NumComments() {
		
	}




	
}

?>