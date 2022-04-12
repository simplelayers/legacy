<?php
use utils\ParamUtil;
use model\Permissions;
use utils\LayerUtil;
use utils\PageUtil;
use model\media\FeatureImage;

function _exec()
{
    $user = SimpleSession::Get()->GetUser();
    $args = WAPI::GetParams();
    $goto = ParamUtil::Get($args, 'goto');
    $action = ParamUtil::RequiresOne($args,'action');
    
    $wapi = System::GetWapi();
    $layer = $wapi->RequireLayer();
    $attributes = $layer->getAttributes();
    unset($attributes['gid']);
    $image_field = ParamUtil::Get($args,'image_field');
    $locate = ParamUtil::Get($args,'locate');
    
   
    $recordData = array();

    foreach($attributes as $att=>$atype) {
        $attVal = ParamUtil::Get($_REQUEST,$att);
          
        if(stripos($attVal,' GMT')) {
            $attVal = str_replace('GMT','',$attVal);
            $attValSegs = explode(' ',trim($attVal));
            $lastSeg = array_pop($attValSegs);
            
            if(strlen($lastSeg)==5) {
                //$attVal = substr($attVal,0,strlen($attVal)-3);
            }
        }    
        if($attVal) $recordData[$att] = $attVal;
    }
   
    $featureId = ParamUtil::Get($args,'featureId');
    if(!$featureId) {
        if($action == 'save') $action='save_as';   
    }
    
    switch ($action) {
        default:
        case 'get':
            $record = $layer->getRecordById($featureId,true,true);
            break;
        case 'get_wkt':
            $record = $layer->getRecordById($featureId,true,false);
            
            break;
        case 'save_as':
            $featureId = $layer->MakeRecord();            
        case 'save':
            
            $accessPerm = $layer->getPermissionById($user->id);
            if($accessPerm < Permissions::EDIT) {
                throw new Exception('You do not have permission to edit this layer.');
            }
            
            if($locate) {
                $wkt = ParamUtil::Get($args,'wkt_geom');
                $recordData['wkt_geom'] = $wkt;
            }
            $record = $layer->updateRecordById($featureId, $recordData);
            
            if($image_field) {
               $img = FeatureImage::CreateImage($_FILES[$image_field], $layer->id, $featureId, $image_field);
            }        
             
            break;
    }
    $goto = ParamUtil::Get($args,'goto');
    print javascriptalert('Record '.$record['gid'].' submitted');
    if($goto) { PageUtil::FullRedirect($goto); die();}
    WAPI::SendSimpleResponse(array('record'=>$record));

}