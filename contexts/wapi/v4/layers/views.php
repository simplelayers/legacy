<?php 
use utils\ParamUtil;
use views\LayerUserViews;

function _exec() {
	$args = WAPI::GetParams();

	$userId= 0+ParamUtil::Get($args,'userId',SimpleSession::Get()->GetUser()->id);
	
	$views = new LayerUserViews($userId);
	
	switch(ParamUtil::RequiresOne($args,'action','view')) {
		case 'list':
			WAPI::SendSimpleResponse(array('views'=>$views->ListViews()));
			break;
		case 'get':
		case 'view':
		    $filterArg = ParamUtil::Get($args,'fiter');
		    $filter = is_null($filterArg) ? false : json_decode(urldecode($filterArg));
		    $andArg = ParamUtil::Get($args,'and');
		    
		    $and = !is_null($andArg);
		    $and = $and ?  (($andArg == "or") ? false : true) : false;
		    
		    switch(ParamUtil::Get($args,'type')) {
		        case LayerUserViews::BOOKMARKED:
		            $results = $views->GetBookMarked($filter,$and);
		            break;
		        case LayerUserViews::GROUPS:
		            
		            $results = $views->GetGroups($filter,$and);
		            
		            break;
		        case LayerUserViews::GROUP:
		            $groupId = ParamUtil::Get($args,'groupId');
		            if($groupId == "") $groupId = null;
		            
		            if( is_null($groupId) ) throw new \Exception("Group not found: id not specified");
		            $results = $views->GetByGroup( (int)$groupId, $filter,$and);
		            break;
		        case LayerUserViews::MINE:
		            
		            $results = $views->GetMine($filter,$and);
		            
		            break;
		        case LayerUserViews::OWNERS:
		            $results = $views->GetOwners((isset($_REQUEST['min']) ? (int)$_REQUEST['min'] : 1), $filter,$and);
		            break;
		        case LayerUserViews::SHARED:
		        case LayerUserViews::OWNER:
		            $owner = ParamUtil::Get($_REQUEST,'owner');
		            if($owner == "") $owner=-1;
		            
		          
		            $results = $views->GetByOwner( $owner, (isset($_REQUEST['min']) ? (int)$_REQUEST['min'] : 1), $filter,$and);
		            
		            break;
		        case LayerUserViews::TAGS:
		            $results = $views->GetByTag( $_REQUEST['tag'],$filter,$and);
		            break;		    
		    }
		    WAPI::SendSimpleResults($results);
	}
}

?>