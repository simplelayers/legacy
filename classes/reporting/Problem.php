<?php

namespace reporting;
use model\MongoCRUD;

class Problem 
extends MongoCRUD  {
	
	protected $collectionName = 'reported_problems';
	
	const VICTEM_ALL = 'Everybody';
	const VICTEM_REPORTER = 'Reporter';
	const VICTEM_PUBLIC = 'Public';
	const VICTEM_USER = 'Person';
	const VICTEM_GROUP = 'Group';
	
	const VIEW_ORG = 'org';
	const VIEW_MINE = 'mine';
	const VIEW_USER = 'user';
	const VIEW_GROUP = 'group';
	
	const IS_NEW = true;
	const NOT_NEW = false;
	
	
	private $document;
	private $victemEnum;
	
	public static function GetEmptyDocument() {
		$document = array();
		$id = new \MongoId();
		$document['id'] = "$id";
		$document['type'] = 0;
		$document['reporter'] = array('id'=>null,'name'=>'','org'=>null);
		$document['victem'] = array('id'=>null,'type'=>null,'realname'=>'');
		$document['subject'] = array('id'=>null,'type'=>null);
		$document['urls'] = array();
		$document['files'] = array();
		$document['status'] = array();
		$document['updated'] = time();
		return $document;		
	}
	
	public function __construct($id=null,$isMongoId=self::NOT_MONGO_ID) {
		parent::__construct();
		$this->collection->ensureIndex('id',array('unique'=>true));
		$this->collection->ensureIndex('reporter.id');
		$this->collection->ensureIndex('reporter.org');
		$this->collection->ensureIndex('victem.id');
		$this->collection->ensureIndex('updated');
		
		if($id) {
			return;
		} else {
			$this->document = self::GetEmptyDocument();
		}		
		
		
	}
	
	public static function GetVictemEnum() {
		if($this->victemEnum ) return $this->victemEnum;
		$victemEnum = new \Enum();
		$victemEnum->AddItem(self::VICTEM_ALL);
		$victemEnum->AddItem(self::VICTEM_REPORTER);
		$victemEnum->AddItem(self::VICTEM_PUBLIC);
		$victemEnum->AddItem(self::VICTEM_USER);
		$victemEnum->AddItem(self::VICTEM_GROUP);		
		$this->victemEnum = $victemEnum;
	}
	
	
	public function GetDocument() {
		error_log(var_export($this->document,true));
		return $this->document; 
	}
	
	public function ToJSON($resultSet=false) {
		if(!$resultSet) return json_encode($this->document);
		echo '{results:[';
		$notFirst = false;
		foreach($resultSet as $result) {
			echo $notFirst ? ','.json_encode($result) : json_encode($result);
			$notFirst = true;
		}
		echo ']}';
	}
	
	public function __set($target,$val) {
		$targetFunction = '_'.$target;
		if(method_exists($this,$targetFunction)) {
			call_user_func(array($this,$targetFunction), $val);
			$this->$targetFunction($val);
		} else {
			$this->document[$target] = $val;
		}
	}
	
	public function __get($target) {
		$targetFunction = '_'.$target;
		if(method_exists($this,$targetFunction)) {
			
			return call_user_func(array($this,$targetFunction));
		} else {
			if(isset($this->document[$target])) return $this->document['target'];
		}
		return null;
	}
	

	
	public function _reporter($reporter=null) {
		if(is_null($reporter)) return $this->document['reporter'];
		if(!is_array($reporter)) {
			$r = $this->document['reporter'];
			$r['id'] = $reporter;
			$reporter = $r;
			unset($r);
		}
		error_log(var_export($reporter,true));
		$user = \System::Get()->getPersonById($reporter['id']);
		$reporter['org'] = $user->getOrganization(true);
		$reporter['name'] = $user->realname;
			
		$this->document['reporter'] = $reporter;
	}
	
	
	public function _victem(array $victemInfo=null) {
			
		if(is_null($victemInfo)){
			error_log('victem info is null');
			return $this->document['victem'];
		}
		/*if(is_int($victemInfo['type'])) {
			$enum = self::GetVictemEnum();
			$victemInfo['type'] = $enum[$victemInfo['type']];
		}*/
		$this->document['victem']['type'] = $victemInfo['type'];
		$this->document['victem']['id'] = $victemInfo['id'];
		error_log($victemInfo['type']);
		if($victemInfo['type']==Problem::VICTEM_GROUP) {
			$group = \System::Get()->getGroupById($victemInfo['id']);
			error_log($group);
			$this->document['victem']['name'] = $group->title;
			error_log($group->name);	
		} else {
			$user = \System::Get()->getPersonById($victemInfo['id']);
			$this->document['victem']['name'] = $user->realname;
		}
		
	}
	
	public function _problem($problemType=null) {
		if(is_null($problemType)) return $this->document['type'];
		$this->document['type'] = $problemType;
	}
	
	public function _id( $id=null) {
		if(!is_null($id)) throw new \Exception('Trying to set read only property: id is a read only property');
		return $this->document['id'];
	}
	
	public function AddFile(array $fileInfo) {
		$mId = new \MongoId();
		$fileInfo['id'] = "$mId";
		$this->document['files'][] = $fileInfo;
		return $fileInfo['id'];
	}
	
	public function RemoveFile($fileId) {
		foreach($this->document['files'] as $i=>$file) {
			if($file['id']==$fileId) {
				$criteria = \Comparisons::ToMongoCriteria('files.id', \Comparisons::COMPARE_EQUALS,$fileId);
				$this->collection->remove($criteria);
				
				unset($this->document['files'][$i]);
				return; 
			}
		}
	}
	
	public function AddURL(array $urlInfo) {
		$mId = new \MongoId();
		$urlInfo['id'] = "$mId";
		$this->document['urls'][] = $urlInfo;
		return $urlInfo['id'];
	}
	
	public function RemoveURL($urlId) {
		foreach($this->document['urls'] as $i=>$url ) {
			if($url['id']==$urlId) {
				$criteria = \Comparisons::ToMongoCriteria('urls.id', \Comparisons::COMPARE_EQUALS,$urlId);
				$this->collection->remove($criteria);
				unset($this->document['urls'][$i]);
				return;
			}
		}
	}
	
	public function Save($isNew=false) {
		
		$criteria = \Comparisons::ToMongoCriteria('id', \Comparisons::COMPARE_EQUALS,$this->document['id']);
		
		if($isNew) $this->UpdateStatus('Reported');
		$doc = $this->document;
		$this->collection->update($criteria,array('$set'=>$doc),array('upsert'=>true));
		return $this->document;
	}
	
	public function Get(array $keyVals) {
		$criteria = array();
		foreach($keyVals as $key=>$val) {
			$criteria[] = \Comparisons::ToMongoCriteria($key, \Comparisons::COMPARE_EQUALS,$val);
			
		}
		return $this->collection->find($criteria);
	}
	
	public function UpdateStatus($status) {
		$time = time();
		$this->document['status'][] = array('status'=>$status,'updated'=>$time);
		$this->document['updated'] = $time;
	}
	
	public function GetView($view,$id,$probType=null) {
		error_log($view);
		switch($view) {
			case self::VIEW_MINE:
			case self::VIEW_USER:
				$query = array('$or'=>array(array('reporter.id'=>$id),array('victem.id'=>$id)));
				error_log(var_export($query,true));
				return $this->collection->find($query,array('_id'=>0))->sort(array('updated'=>-1));
					
			case self::VIEW_ORG:
				return $this->collection->find(array('reporter.org'=>$id));
			case self::VIEW_GROUP:
				$group = \System::Get()->getGroupById($id);
				$members =$group->members;
				$memberIds = array();
				foreach($members as $member) {
					$memberIds[] = $member.id;	
				}
				$or = array();
				$or[] = array('reporter.id'=>array('$in'=>$memberIds));
				$or[] = array('victem.id'=>array('$in'=>$memberIds));
				if($probType) {
					$query = array('$and'=>array('$or'=>$or,array('type'=>$probType)));
				} else {
					$query = $or;
				}
				return $this->collection->find($query,array('_id'=>0));
		}
	}

	
}

?>