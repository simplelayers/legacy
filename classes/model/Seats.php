<?php

namespace model;

use utils\ParamUtil;

class Seats extends MongoCRUD {
	const SEATNAME_UNASSIGNED = 'Unassigned';
	const SEATNAME_ORGOWNER_BASIC = 'Organization Owner - Basic';
	const SEATNAME_ORGOWNER = 'Organization Owner';
	const SEATNAME_SYSADMIN = 'SysAdmin';
	protected $collectionName = 'seats';
	
	public function __construct() {
		parent::__construct();
		$this->collection->createIndex(array('data.seatName'=>1),array('unique'=>1));
	}
	
	public static function GetSeat($id) {
		$me = new Seats();
		return $me->FindOneByCriteria(\Comparisons::ToMongoCriteria('id',\Comparisons::COMPARE_EQUALS,$id));
	}

	public function AddSeat($params) {
		
		list($role,$seatName) =ParamUtil::ListValues($params,'roleId','seatName');
		$doc = $this->MakeDocument(array('roleId'=>$role,'seatName'=>$seatName));
		return $doc;
		
	}
	
	public function GetSeatIdByName($name) {
		
		$seat = $this->FindOneByCriteria( \Comparisons::ToMongoCriteria('data.seatName',\Comparisons::COMPARE_EQUALS,$name));
		return $seat['id'];
	}
	
	public static function GetByName($name,$idOnly=true) {
	    $seats= new Seats();
	    $id =  $seats->GetSeatIdByName($name);
	    
	    if($idOnly) return $id;
	    return self::GetSeat($id);
	}
	
	
	public function GetUnassigned() {
		
		return $this->FindOneByCriteria( \Comparisons::ToMongoCriteria('data.seatName',\Comparisons::COMPARE_EQUALS,self::SEATNAME_UNASSIGNED));
		
	}
	
	public function GetSeats() {
		return $this->FindByCriteria();
	}
	
	public function GetLookup($by='id',$full=true,$includeSysAdmin=false) {
		$seats = $this->GetSeats();
		$lookup = array();
		$exclusions = array(self::SEATNAME_ORGOWNER,self::SEATNAME_ORGOWNER_BASIC);
		if(!$includeSysAdmin) $exclusions[] = self::SEATNAME_SYSADMIN;

		foreach($seats as $seat) {
			if(!$full) {
				if(in_array($seat['data']['seatName'],$exclusions)) continue;
			}
			if($by=='name') {
				$lookup[$seat['data']['seatName']] = $seat['id'];
			} else {
				$lookup[$seat['id']] = $seat['data']['seatName'];
			}
		}
		
		return $lookup;
	}
		
	public function CleanUp() {
		// TODO: Auto-generated method stub

	}

	
	public function UpdateSeat($document) {
		if(isset($document['isDeleted'])) {
			if($document['isDeleted']) {
				$this->RemoveSeat($document);
			}	return;		
			unset($document['isDeleted']);
		}
		
		$isChanged = ParamUtil::Get($document,'isChanged',false);
		
		if($isChanged) {
			unset($document['isChanged']);
			$this->Update($document);
		}
				
	}
	
	public function RemoveSeat($seatDoc) {
		$this->DeleteItem($seatDoc['id']);
	}
	

	
	
}

?>
