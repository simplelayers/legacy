<?php
use utils\DOUtil;
class Dispatch {
	protected $action;
	public function __construct( $do =null) {
		$this->action = is_null($do) ? DOUtil::Get () : $do;
		$this->LegacyTOWAPI();
	}
	
	public function LegacyTOWAPI() {
		if (! $this->action)
			return;
		if ($this->action == 'ria.getmedia') {
			RequestUtil::Set ( 'do', 'organization.media' );
			RequestUtil::Set ( 'application', 'wapi' );
			RequestUtil::Set ( 'get', RequestUtil::Get ( 'name', '' ) );
			RequestUtil::Set ( 'id', RequestUtil::Get ( 'context', '' ) );
			$this->action = DOUtil::Get ();
		}
	}
	
	
	
	
}

?>