<?php

use utils\ParamUtil;
/**
 * @api 
 * @param boolean geom optional default false
 * @param string orderby optional default 'gid'
 * @param string idfield optional default 'gid'; the field nam to use as an id.
 * @param string bbox optional bbox as 'minx miny max maxy' in degrees; if present constrains search to bbox
 * @param string pxrect optional pixel rectangle as 'x1,y1,x2,y2' bbox should be present too, performs a query at the specified pixel rectangle within the specified bbox.
 * @param int width screen width in pixels corresponding with provided bbox; for use with pxrect and bbox;
 * @param int width screen height in pixels corresponding with provided bbox; for wuse with pxrect and bbox
 * @param string filters; json encoded search criteria   
 */
function _exec() {
    
    $user = SimpleSession::Get()->GetUser();
    $args = WAPI::GetParams();
    $wapi = System::GetWapi();
    $layer = $wapi->RequireLayerId(AccessLevels::READ);
    $params =  WAPI::GetParams();
    $getGeom = ParamUtil::Get($params,'geom', false);
    $orderBy = ParamUtil::Get($params,'orderby', 'gid');
    $idField =ParamUtil::Get($params,'idfield', 'gid');
    list($bbox,$pxrect,$width,$height) = ParamUtil::ListValues( $params,'bbox','pxrect','width','height' );    
    
    
   $filters = ParamUtil::RequireJSON($args,'filters');
   
   $fields = $layer->getAttributesVerbose(false,false,false,true);
   $args['fields'] = array();
   
   foreach($fields as $fieldName=>$field) {
       $args['fields'][] = array('field'=>$fieldName,'type'=>$field['requires'],'distinct'=>0,'as'=>$fieldName);
       //$args['fields'] = array('field')
   }
   
   $searchCriteria = new SearchCriteria($layer->url,$args);
   $countOnly = ParamUtil::Get($args,'count_only',false);
    
   $paging = new Paging();
   $searchResults = $layer->SearchByCriteria($searchCriteria,$countOnly);
   $searchResults['status'] = 'ok';
   WAPI::SendSimpleResponse($searchResults);
    
}



?>