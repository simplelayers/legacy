<?php


use utils\ParamUtil;
use model\SL_Query;
function _exec() {
    
    $sys = System::Get();
    $wapi = System::GetWapi();
    $user = SimpleSession::Get()->GetUser();
    $db = System::GetDB(System::DB_ACCOUNT_SU);
    $args = $wapi->GetParams();
    
    $mapping = ParamUtil::RequireJSON($args,'mapping');
    $getGeom = ParamUtil::GetBoolean($args, 'geom');
    if(ParamUtil::GetBoolean($args,'wkt')) $getGeom = true;
    $intersectionMode = ParamUtil::Get($args, 'intersectMode');
    $bbox = ParamUtil::Get($args, 'bbox', null);
    $gids = ParamUtil::Get($args, 'gids', null);
    $memoryLayer = ParamUtil::get($args, 'memoryLayer', null);    
    $buffer = ParamUtil::Get($args, 'buffer', null);
    $format = ParamUtil::Get($args, 'format');
    
    
    
    $report = array();
   
    
    
    $slQuery = new SL_Query();
    foreach($mapping as $layerId=>$criteria) {
        $layer = Layer::GetLayer($layerId);
        $slQuery->NewQuery($layer);
        $isValid = $slQuery->SetCriteria($criteria);
        if(!$isValid) continue;
        $searchCriteria  = $slQuery->GetSearchCriteria();
        $searchCriteria->
        $query = $criteria->GetCountQuery(false, $bbox, $gids, $memoryLayer, $intersectionMode, $buffer);
        $recordCount = $db->GetOne($query);
        $report[$layerId] = array('layer_id'=>$layerId,'layer_name',$layer->name,'owner'=>$layer->realname,'result_count'=>$recordCount);
    }
    $response = WAPI::SendSimpleResponse($report,$format,$status='ok',false);
     
}