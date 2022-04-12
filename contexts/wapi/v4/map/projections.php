<?php



use utils\ParamUtil;
use model\ProjectionList;

/**
 * @throws Exception
 */
function _exec() {
 
    $params = WAPI::GetParams();
   $action = ParamUtil::RequiresOne($params,'action');
   $action = strtolower($action);
   $format = ParamUtil::Get($params,'format');
   
    switch($action) {
        case 'get':
            
            if($format=='html') {
                echo $projections = ProjectionList::GetQuickOptions(false);
                return; 
            } else {
                WAPI::SendSimpleResponse(array('projections'=> ProjectionList::GetQuickOptions(true,'projections','projection_sel')->getRows()));
            }
            break;
    }
     
			
}


?>
