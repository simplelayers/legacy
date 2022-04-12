<?php
use utils\ParamUtil;
use model\mapping\Labels;

function _exec()
{
    $args = WAPI::GetParams();
    
    $wapi = System::GetWapi();
    $alayer = $wapi->RequireALayer(null, 'mlayerId', 'layerId');
    
    switch (ParamUtil::Get($args, 'action')) {
        case WAPI::ACTION_GET:
            if (is_a($alayer, 'Layer')) {
                if ($alayer->getPermissionById(SimpleSession::Get()->GetUser()->id) < AccessLevels::READ) {
                    WAPI::HandleError(new Exception('Need view privilege'));
                }
            } else {
                $project = $alayer->project;
                if ($project->getPermissionById(SimpleSession::Get()->GetUser()->id) < AccessLevels::EDIT) {
                    WAPI::HandleError(new Exception('Need edit privilege for map'));
                }
            }
            $labelObj = Labels::GetLabelsFromALayer($alayer);
            
            WAPI::SendSimpleResponse($labelObj->GetInfo());
            
            break;
        case WAPI::ACTION_SAVE:
            if (is_a($alayer, 'Layer')) {
                if ($alayer->getPermissionById(SimpleSession::Get()->GetUser()->id) < AccessLevels::EDIT) {
                    WAPI::HandleError(new Exception('Need edit privilege for layer'));
                }
            } else {
                /* @var ProjectLayer $alayer */
                
                $project = $alayer->project;
                /* @var $project Project  */
                if ($project->getPermissionById(SimpleSession::Get()->GetUser()->id) < AccessLevels::EDIT) {
                    WAPI::HandleError(new Exception('Need edit privilege for map'));
                }
            }
            $info = ParamUtil::Get($args, 'changeset', null, true);
            Labels::SaveLabelStyle($alayer, $info);
            // $aLayer->label_style = $info;
            WAPI::SendSimpleResults();
            break;
    }
}