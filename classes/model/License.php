<?php

namespace model;

use utils\ParamUtil;

class License extends MongoCRUD {
	const STATUS_CREATED = 'created';
	const STATUS_ACTIVE = 'active';
	const STATUS_EXPIRED = 'expired';
	protected $collectionName = 'licenses';
	public function __construct() {
		parent::__construct ();
		// $this->collection->ensureIndex(array('data.seatName'=>1),array('unique'=>1));
	}
	public function Create($params) {
		$docData = ParamUtil::GetValues ( $params, 'orgId', 'planId', 'starts', 'expires' );
		$docData ['status'] = self::STATUS_CREATED;
		$docData ['plan'] = Plans::GetPlanById($docData['planId']);
		
		$docData ['seats'] = array ();
		$docData ['startdate'] = ParamUtil::Get($params,'starts');
		$docData ['expires'] = ParamUtil::Get($params,'expires');
		
		$this->Validate($docData);
		$doc = $this->MakeDocument( $docData );
		
		return $doc;
	}
	
	public static function GetPlan($orgId,$infoOnly=false) {
		$me = new License();
		$license = $me->Get($orgId);
		if(!$license) return null; 
		$planId = $license['data']['planId'];
		$plans = new Plans();
		$plan = $plans->GetPlan($planId);
		if($infoOnly) return $plan['data'];
		return $plan;
	}
	
	public  function Validate(&$doc) {
	
		if (! isset ( $doc['data']['planId'] ))
			return false;
		$plans = new Plans ();
		
		
		/*if ($doc['data']['plan'] === null) {
			
			$doc['data']['plan'] = $plans->GetPlan ( $doc ['planId'] );
			$doc['data']['planId'] = $doc ['plan'] ['id'];
			$doc['data']['seats'] = $plans->GetPlan ( $doc ['planId'] );
		}*/
		
		$plans = new Plans();
		$plan = $doc['data']['plan'];
		$plan = $plans->GetPlan($plan['id']);
		$doc['data']['plan'] = $plan;
		$planSeats = $plan['data']['seats'];
		$licSeats = $doc['data']['seats']['data']['seats'];
		
		$doc['data']['seats'] = $plan;
		
		foreach ( $plan ['data'] as $key => $val ) {
			
			if ($key == 'seats') {
				
				//$licSeats = $doc['data']['seats'];
				foreach ( $planSeats as $i => $seat ) {
					foreach($licSeats as $ii=>$licSeat) {
						if($licSeat['id']==$seat['id']) {
							if($licSeat['count']=='') {
								
								$doc['data']['seats']['data']['seats'][$i]['count'] == '';
							} else {
								$doc['data']['seats']['data']['seats'][$i]['count'] = $this->RequireMin($seat['count'],$licSeat['count']);
							}
							break;
						}
						
					} 
					
				}
				
				
			} else {
				if (stripos ( $key, 'max_' ) === 0) {
					
					if(isset($doc['data']['plan'][$key])) $doc['data']['plan'][$key] = $plan[$key]; 
					
					$planVal = $plan['data'][$key];
					
					$doc['data']['plan']['data'][$key] = $this->RequireMin ( $plan['data'][$key], @$doc['data']['plan']['data'][$key] );
				}
			}
		}
		
		
	}
	public function GetLimitLookup($orgId) {
		$doc = $this->Get($orgId,false);
		
		if(!$doc) return array();
		$lookup = ParamUtil::GetValues($doc['data'],'max_space','max_layers');
		
		if(!isset($doc['data']['seats']['data'])) return array('seats'=>array());
		$seatData = $doc['data']['seats']['data']['seats'];
		
		foreach($seatData as $seat) {
			$lookup['seats'][$seat['id']]= $seat;  
			
		}
		
		return $lookup;
	}
	protected function RequireMin($targetMin, $compareVal) {
		if(is_null($compareVal)) return $targetMin;
		if ($targetMin <= $compareVal)
			return $compareVal;
		return $targetMin;
	}
	public function Get($orgId, $newPlan = null) {
		$document = $this->FindOneByCriteria ( \Comparisons::ToMongoCriteria ( 'data.orgId', \Comparisons::COMPARE_EQUALS, $orgId ) );
		$plans = new Plans();
		if (is_null ( $document ))
			return null;
		$data = $document ['data'];
		
		if(!count($document['data']['seats'])) $document['data']['seats'] = $plans->GetPlan($document['data']['planId']);
		
		if ($newPlan) {
			
			$document['data']['plan'] = $plans->GetPlan ( $newPlan);
			
			$document['data']['planId'] = $document['data']['plan']['id'];
			
			if(!count($document['data']['seats'])) $document['data']['seats'] = $plans->GetPlan($document['data']['planId']);
		
			$this->Validate($document);
		}
		
		return $document;
	}
	public function GetLookup($orgId) {
		$items = $this->GetByOrg ( $orgId );
		$lookup = array ();
		foreach ( $items as $item ) {
			$lookup [$item ['id']] = ParamUtil::GetValues ( $item ['data'], 'orgId', 'planName', 'startdate', 'status' );
		}
		return $lookup;
	}
	public function UpdateLicense($document) {
		
		if (isset ( $document ['isDeleted'] )) {
			if ($document ['isDeleted']) {
				$this->Remove ( $document );
			}
			return;
			unset ( $document ['isDeleted'] );
		}
		$isChanged = $document['data']['isChanged'];
		if ($isChanged) {
			unset ( $document['data']['isChanged'] );
			#unset($document['data']['seats']['max_space']);
			
		    $max_space = $document['data']['seats']['data']['max_space'];
		    $max_layers = $document['data']['seats']['data']['max_layers'];
		    $this->Validate ( $document );
		    $document['data']['seats']['data']['max_space'] = $max_space;
		    $document['data']['seats']['data']['max_layers'] = $max_layers;
		    $document['data']['max_space'] = $max_space;
		    $document['data']['max_layers'] = $max_layers;
		    
			//var_dump($document['data']['plan']['data']['max_space']);
			
			//var_dump($document['data']['seats']['data']['max_space']);
				
			$this->Update ( $document );
		}
	}
	public function FixDate($date, $to = 'int') {
		switch ($to) {
			case 'int' :
				return ( int ) str_replace ( '-', '', $date );
				break;
			case 'str' :
				$pattern = '/(\d{4})(\d{2})(\d{2})/';
				$replacement = '$1-$2-$3';
				break;
		}
	}
	public function Remove($doc) {
		$this->DeleteItem ( $doc ['id'] );
	}
}

?>