<?php

namespace model;

class MongoCRUD {
	
	const IS_MONGO_ID = true;
	const NOT_MONGO_ID =false;
	
	const SORT_ASC = 1;
	const SORT_DESC = -1;
	
	protected $collectionName;
	
	protected $mongo = null;
	protected $db;
	protected $collection;
	
	
	
	
	public function __construct() {
	    
		if(is_null($this->collectionName)) throw new \Exception('Trying to construct MongoCRUD without a collectionName defined');
		$ini = \System::GetIni();
		
		$this->mongo = \System::GetMongo ();
		$this->db = $ini->mongo_db;
		
		$this->collection = $this->mongo->selectCollection ( $this->db, $this->collectionName );		
	}
	
	public function CleanUp() {
		//
	}
	
	public function Count() {
		return $this->db->runCommand("{count:'{$this->collectionName}'}");
	}
	
	public function MakeDocument($data,$mergeData=false,$insert=true) {
		$mongo = \System::GetMongo();
		$id = new \MongoDB\BSON\ObjectId();
		$slid = "$id";
		$document = array('_id'=>$id,'id'=>$slid,'created'=>\mktime(),'updated'=>mktime());
		
		if($mergeData) {
			$document = array_merge($document,$data);
		} else {
			$document['data'] = $data;
		}
		if($insert)	{
			$ins = $this->InsertDocument($document);
		}
		
		return $document;	
	}
	
	public static function NewID() {
		$id = new \MongoDB\BSON\ObjectId();
		$slid = $id;//->{'$id'};
		return $slid;
	}
	
	public function MakeId() {
		return self::NewID();
		
	}
	
	public function InsertDocument($document) {
		return $this->collection->insertOne($document);
	}
	
	public function MakeSubDoc($data) {
		
		$id = new \MongoDB\BSON\ObjectId();
                $slid = "$id";
		$data['id'] = $sid;
		return $data;
	}
	
	public function FindOneByCriteria($criteria,$fields=null,$include_id=false) {
		if(is_null($fields)) $fields = array();
		if(!$include_id) $fields['_id'] = false;
		
		return $this->collection->findOne($criteria,$fields);
	}
	
	public function FindByCriteria($criteria=null,$fields=null,$include_id=false,$sort=false) {
		
		if(is_null($criteria)) $criteria = array();
		
		/*if(count($criteria >1)) {
			foreach($criteria as $key=>$val) {
				$criteria[$key] = (object)$val;
			}
			$criteria = array('$and'=>$criteria);
		}*/
		if(is_null($fields)) $fields = array();
		if(!$include_id) $fields['_id'] = false; 
		if($sort !== false) {
			$fields['sort']=$sort;
		}
		return $this->collection->find($criteria,$fields);
	}
	
	public function FindDocumentById($id,$isMongoId=IS_MONGO_ID) {
		
		$idField = ($isMongoId) ? '_id' : 'id';
		
		$document = $this->collection->findOne ( array (
				$idField => $id 
		) );
		return $document;
	}
	
	public function Update($document) {
		if(!isset($document['id'])) throw new \Exception('MongoCRUD problem: attempting to update a document without an id');
		$document['updated'] = time();
		$id = $document['id'];
		unset($document['_id']);
		$res = $this->collection->replaceOne(array('id'=>"{$document['id']}"),$document);
		
		return $document;
	}
	
	public function DeleteItem($id) {
		$this->collection->deleteOne(array('id'=>$id));
	}
	public function DeleteByCriteria($criteria) {
		$this->collection->removeOne($criteria);
	}
	
	public function GetDistinct($field) {
		$this->db->Execute(array('distinct'=>$this->collectionName, 'key'=>$field));
	}
	
	public function MakeRef($document) {
		$ref =  array('$ref'=>$this->collectionName,'$id'=> $document['_id']);
		return $ref;
	}
	
	
}

?>
