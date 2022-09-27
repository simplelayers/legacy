<?php

namespace model;

use utils\ParamUtil;
class Plans extends MongoCRUD {
	const DEFAULT_MAX_SPACE = 1;
	const DEFAULT_MAX_FEATURE_LAYERS = '';
	const DEFAULT_MAX_RASTER_LAYERS = '';
	const DEFAULT_MAX_WMS_LAYERS = '';
	
	protected $collectionName = 'plans';
	
	public function __construct() {
		parent::__construct();
		$this->collection->createIndex(array('data.planName'=>1),array('unique'=>1));
	}

	public function AddPlan($params) {
		
		list($planName,$planSeats,$price,$advertise,$allow_pubcat,$allow_pubmap,$allow_pubutils) =ParamUtil::ListValues($params,'planName','seats','price','advertise','allow_pubcat','allow_pubmap','allow_pubutil');
		$roles = new Roles();
		$roles = $roles->GetRolesByContext();
		$seatObj = new Seats();
		if(is_array($planSeats) && count($planSeats)==0 ) {
			
			$seats = $seatObj->GetSeats();
			foreach($seats as $seat) {
				$count = 0;
				$price = 0;
				if(in_array($seat['data']['seatName'], array(Seats::SEATNAME_ORGOWNER,Seats::SEATNAME_ORGOWNER_BASIC,Seats::SEATNAME_UNASSIGNED))) continue;
				
				$planSeats[] = array('id'=>$seat['id'],'name'=>$seat['data']['seatName'],'count'=>$count,'add_price'=>$price);
			}
		}
		
		$doc = $this->MakeDocument(array('planName'=>$planName, 'seats'=>$planSeats, 'owner_seat'=>$seatObj->GetSeatIdByName(Seats::SEATNAME_ORGOWNER),
		                                  'price'=>$price,'advertise'=>$advertise,'allow_pubmap'=>$allow_pubmap,
		                                  'allow_pubcat'=>$allow_pubcat,'allow_pubutils'=>$allow_pubutils,
										 'max_space'=>self::DEFAULT_MAX_SPACE, 'max_layers'=>array('feature'=>self::DEFAULT_MAX_FEATURE_LAYERS,'raster'=>self::DEFAULT_MAX_RASTER_LAYERS,'wms'=>self::DEFAULT_MAX_WMS_LAYERS)));
		return $doc;
		
	}
	
	public static function GetPlanById($id) {
		$plan = new Plans();
		return $plan->GetPlan($id);
	}
	
	public function GetPlan($id){
		$item = $this->FindOneByCriteria( \Comparisons::ToMongoCriteria('id',\Comparisons::COMPARE_EQUALS,$id));
		
		return $item;
	}
	
	public function GetIdByName($name) {
		$item = $this->FindOneByCriteria( \Comparisons::ToMongoCriteria('data.planName',\Comparisons::COMPARE_EQUALS,$name));
		return $item['id'];
	}
	
	
	public function GetPlans() {
		$plans = $this->FindByCriteria(null,array('_id'=>false ));
		return $plans;
	}
	
	public function GetAdvertisedPlans() {
	    $plans = $this->FindByCriteria(\Comparisons::ToMongoCriteria('data.advertise',\Comparisons::COMPARE_EQUALS,true),array('_id'=>false ));
	    return $plans;
	}
	
	
	
	
	public function GetLookup($by='id') {
		$plans = $this->GetPlans();
		$lookup = array();
		foreach($plans as $plan) {
			if($by=='name') {
				$lookup[$plan['data']['planName']] = $this->RetroFit($plan);
			} else {
				$lookup[$plan['id']] = $this->Retrofit($plan);
			}
		}
		return $lookup;
	}
	
	public function Retrofit($planDocument) {
	    $setKeys = array_keys($planDocument['data']);
	    $retroKeys = array('cost','advertise','allow_pubcat','allow_pubmap','allow_pubutils');
	    
	    foreach($retroKeys as $key) {
	        if(!in_array($key,$setKeys)) {
	            $planDocument['data'][$key] = false;
	        }	        
	    }
	    return $planDocument;
	}
		
	public function CleanUp() {
		// TODO: Auto-generated method stub

	}

	
	public function UpdatePlan($document,$isChanged=false) {
	  
		if(isset($document['isDeleted'])) {
			if($document['isDeleted']) {
				$this->RemovePlan($document);
			}	return;		
			unset($document['isDeleted']);
		}
		
		$isChanged = ParamUtil::Get($document,'isChanged',$isChanged);
		
		if($isChanged) {
		    if(isset($document['isChanged'])) {
			 unset($document['isChanged']);
		    }
			$this->Update($document);
		}
				
	}
	
	public function RemovePlan($document) {
		if(!isset($document['id'])) return false;
		$this->DeleteItem($document['id']);
	}
	
	public function GetPlanAllowances() {
	    $allowances = array();
	    die();
	    foreach($this->GetPlans() as $plan) {
	        $id = $plan['id'];
	        
	    }
	}
	

	
	
}

?>
