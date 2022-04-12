<?php


use utils\ParamUtil;
function _exec()
{
    $wapi = System::GetWapi();
    $params = $wapi->Getparams();
    $session = SimpleSession::Get();
    $user = $session->GetUser();
    
    list($playerId,$layerId) = ParamUtil::Requires($params,'playerId','layerId');
    
    $projectLayer = ProjectLayer::Get($playerId);
    $layer = Layer::GetLayer($layerId,true);
    
    $project = $projectLayer->project;
    $permission = $project->getPermissionById($user->id);
    if($permission < AccessLevels::EDIT) {
        WAPI::HandleError(new Exception('Insufficient Privilege: You do not have permission to edit this map'));
        die();
    }        
    if($projectLayer->layer->id !== $layer->id) {
        WAPI::HandleError(new Exception('MapLayer/Layer Id mismatch: map layer source layer id does not match provided layer id'));
        die();
    }
    
    list($inheritPopup,$inheritHover,$classes,$labelStyle) = ParamUtil::GetBoolean($params,'inheritPopup','inheritHover','classes','labelStyle');
    $subs = ParamUtil::get($params,'subs');
    
    if($inheritPopup === true) {
        
        $projectLayer->rich_tooltip = null;
        
    }
    
    if($inheritHover === true) {
        $projectLayer->tooltip = null;
    }
    
    if($labelStyle === true) {
        $projectLayer->labelitem = $layer->labelitem;
        $projectLayer->label_style = $layer->label_style;
    }
    if($classes === true) {
        $projectLayer->CopyColorScheme();
    }
    
    if(!is_null($subs)) {
        switch($subs) {
            case 'update':
                $projectLayer->UpdateSubs(false);
                break;
            case 'updateReorder':
                $projectLayer->UpdateSubs(true);
                break;
            case 'reset':
                $projectLayer->ResetSubs();
                break;
        }
    }   
    $project->UpdateProjLayerZs();
    $format = ParamUtil::Get($params,'format','json');
    WAPI::SendSimpleResponse(array('action'=>'wapi/map/reset_map_layer/','mapLayer'=>$projectLayer->id,'layerId'=>$layer->id),$format,'ok');
}