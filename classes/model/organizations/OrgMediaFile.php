<?php

namespace model\organizations;

use utils\ParamUtil;
class OrgMediaFile {
	
	
	public $data = null;
	private $fileFxt ='.gzip';
	private $baseURL;
	
	public function __construct($data=null,$baseURL=null) {
	    
		$this->data = $data;
		if(!is_null($baseURL)) $baseURL = BASEURL;
		$this->baseURL = $baseURL;
		if(substr($this->baseURL,-1,1)!='/') $this->baseURL.='/';
			
	}

	public function MakeOrgFile($params) {
		list($filePath,$format) = ParamUtil::Requires($params,'filePath','format');
		
		$ini = \System::GetIni();
		$id = \System::MakeID();
		
		$storageInfo  = $this->StoreContents($id,$filePath);
		
		$hash  = $storageInfo['hash'];
		$newPath = $storageInfo['newPath'];
		$diskUsage = filesize($newPath);
		$mediaType = ParamUtil::Get($params,'media_name');
		$this->data = array('id'=>$id, 'media_type'=>OrgMedia::MEDIATYPE_FILE,'format'=>$format,'size'=>filesize($filePath),'disk_usage'=>$diskUsage,'hash'=>$hash,'path'=>$newPath,'media_type');
		return $this;
	}
	
	public function StoreContents($id,$filePath) {
		if(!file_exists($filePath)) return false;
		$ini = \System::GetIni();
		
		$contents = gzcompress(file_get_contents($filePath));
		$hash = md5($contents);
		$newPath = $ini->orgmedia.$id.$this->fileFxt;
		file_put_contents($newPath, $contents);		
		return array('hash'=>$hash,'newPath'=>$newPath);
	}
	
	public function MakeURL($baseURL = null,$getTarget) {
		if(is_null($baseURL)) $baseURL = BASEURL;
		
		return $baseURL.'wapi/organization/organizations/action:'.\WAPI::ACTION_GET.'/target:'.OrgMedia::DATATYPE_ORGANIZATION_MEDIA.'/id:'.$this->data['id'].'/type:'.OrgMedia::MEDIATYPE_FILE;
	}
	
	public function SendFileHeader() {
		
		header('Content-Type: '.$this->data['format']);
	}
	
	public function WriteFile($includeLengthHeader=true,$path=null) {
		#var_dump($this->data['path']);
		
		if(!file_exists($this->data['path'])) return false;
		$content = file_get_contents($this->data['path']);
		
		$content = gzuncompress($content);
		$this->SendFileHeader();
		//header('content-type: image/png');
		if($includeLengthHeader) header('Content-Length: '.$this->data['size']);
		echo $content;
	}
	
	public function Remove() {
	    if(!file_exists($this->data['path'])) unlink($this->data['path']);
	}

}

?>