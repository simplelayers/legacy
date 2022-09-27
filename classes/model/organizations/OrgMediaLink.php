<?php

namespace model\organizations;

class OrgMediaLink {
	
	public $data;
	private $baseURL;
	
	public function __construct($data=null,$baseURL=null) {
		if(is_null($baseURL))$this->baseURL = BASEURL;
		if(substr($this->baseURL,-1,1)!='/') $this->baseURL.='/';
		$this->data = $data;
	}
	
	public function MakeLink($relPath) {
		$this->data = array('id'=>\System::MakeID(),'media_type'=>'org_media_link','relPath'=>$relPath);		
		return $this;
	}
	
	
	public function MakeURL($baseURL= null) {
		if(is_null($baseURL)) $baseURL = BASEURL;
		return $baseURL.'/wapi/organization/organizations/action:get/target:org_media/id:'.$this->data['id'].'/type:'.OrgMedia::MEDIATYPE_LINK;
	}
	
	public function GoToink($params=array()) {
		$relPath = $this->data['relPath'];
		foreach($params as $key=>$val) {
			$relPath = str_replace('%'.$key.'%',$val,$relPath);
		}
		if(substr($relPath,0,2)=='//') return $this->Redirect( $relPath );
		if(substr($relPath,0,4)=='http')return  $this->Redirect($relPath);
		return $this->Redirect($this->baseURL.$relPath);
		
	}
	
	public function Redirect($path) {
		header('Location: '.$path);
	}
	
	
}

?>