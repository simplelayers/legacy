<?php

namespace model;

use utils\ParamUtil;
use auth\Context;
class SeatAssignments extends MongoCRUD {
	
	protected $collectionName = 'seat_assignments';
	
	public function __construct() {
		parent::__construct();
		//$this->collection->ensureIndex(array('data.orgId'=>1),array('unique'=>1));
	}
	
	public static function GetUserRole($userId,$orgId=null) {
		$me = new SeatAssignments();
		$assignment = $me->GetAssignment($userId,$orgId);
		#var_dump(iterator_to_array($assignments));
		$seat = Seats::GetSeat($assignment['data']['seatId']);
		return $seat['data']['roleId'];
	}

	public function GetAssignment($userId,$orgId=null) {
		$criteria[] = \Comparisons::ToMongoCriteria('data.userId', \Comparisons::COMPARE_EQUALS,''.$userId);
		if($orgId) 	$criteria[] = \Comparisons::ToMongoCriteria('data.orgId', \Comparisons::COMPARE_EQUALS,''.$orgId);
		$criteria =  (count($criteria) > 1) ?  \Comparisons::GroupMongoCriteria($criteria, \Comparisons::OPERATOR_MONGO_AND) : $criteria[0];
		
		$assignment = $this->FindOneByCriteria($criteria);
		return $assignment;
	}
	
	public function AssignSeat($params) {
	    
		$seats = new Seats();
		$params['orgId'] = ParamUtil::Get($params,'orgId', \System::Get()->getPersonById($params['userId'])->orgid);
		list($orgId,$userId,$seatId) = ParamUtil::Requires($params,'orgId','userId','seatId');
		$sysOwner = \System::GetSystemOwner(true);
		$assignment = self::GetAssignment($userId,$orgId);
	   
	   
		if($userId == $sysOwner) $seatId = $seats->GetSeatIdByName(Seats::SEATNAME_SYSADMIN);
		$seats = new Seats();
		$seat = $seats->GetSeat($seatId);
		
		if(!$seat) {
			throw new \Exception('Seat assignment error: seat with id '.$seatId.' not recognized');				
		}
		
        if($assignment) {
            $wasSameSeat = ($assignment['data']['seatId'] == $seatId);
			$assignment['data']['seatId'] = $seatId;
			$this->Update($assignment);
			$assignment = $this->GetAssignment($userId,$orgId);
			if(!$wasSameSeat) {
			   $session = \SimpleSession::Get();
			   if($userId) {
			     $session->EndAllSessions($userId);
			   }
			}
			return $assignment;
		}
		
		$doc = $this->MakeDocument(array('orgId'=>$orgId,'userId'=>$userId,'seatId'=>$seatId));
		
		return $doc;
	}
	
	public function GetSeat($assignment,$infoOnly=false) {
		$seats = new Seats();
		$seat = $seats->getSeat($assignment['data']['seatId']);
		if($infoOnly) return $seat['data'];
		return $seat;			
	}
	
	public static function GetUserSeat($userId,$infoOnly=false) {
		$me = new SeatAssignments();
		$assignment  = $me->GetAssignment($userId);
		return $me->GetSeat($assignment,$infoOnly);
	}
	
	public function ListSeats($params) {
		list($orgId) = ParamUtil::Requires($params,'orgId');
		$seats = new Seats();
		
		$criteria[] =  \Comparisons::ToMongoCriteria('data.orgId', \Comparisons::COMPARE_EQUALS,$orgId);
		//$criteria[] =  \Comparisons::ToMongoCriteria('data.seatId',\Comparisons::COMPARE_NOT_IN,array($seats->GetSeatIdByName(Seats::SEATNAME_ORGOWNER),$seats->GetSeatIdByName(Seats::SEATNAME_ORGOWNER_BASIC)));
		$list = $this->FindByCriteria(\Comparisons::ToMongoCriteria('data.orgId', \Comparisons::COMPARE_EQUALS,$orgId));
		
		return $list;
	}
	
	public function GetMissingAassignments($params) {
	    $memberList = ParamUtil::Get($params,'members',array());
	    $seatUsers = ParamUtil::GetSubValues($this->ListSeats($params),'data.userId');
	    return array_diff($memberList,$seatUsers);
	}
	
	public function GetSeatsLeft($orgId) {
		$assignments = $this->ListSeats(array('orgId'=>$orgId));
		$license = License::GetPlan($orgId,true);
		
		$seatsAvailable = $license['seats'];
		
		$assignCount = array();
		foreach($assignments as $assignment) {
			$assignCount[$assignment['data']['seatId']]+=1;
		}
		
		$seatsLeft = array();
		
		$seats = new Seats();
		$lookup = array_values($seats->GetLookup('id',Context::Get()->IsSysAdmin(),Context::Get()->IsSysAdmin()));
		
		foreach($seatsAvailable as $seat) {
			
			if(in_array($seat['name'], $lookup)) { 
				$seatsLeft[$seat['name']] = isset($assignCount[$seat['id']]) ? $assignCount[$seat['id']] - $seat['count'] : 0;
			}
		}
		
	}
	
	
	public function UpdateSeatAssignment($doc) {
		if(!isset($doc['id'])) return false;
		if(isset($doc['isDeleted'])) {
			$this->DeleteItem($doc['id']);
			return;
		}
		if(isset($doc['isChanged'])) {
			unset($doc['isChanged']);
			$this->Update($doc);
		}
	}
	
	

	

	
}

?>