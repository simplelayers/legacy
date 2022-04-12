<?php
use utils\ParamUtil;

/**
 * @arg map id of target map/project
 * @arg projection (optional)
 * @arg noscale (optional)
 * @arg formatOptions (optional);
 *
 * @throws Exception
 */
function _exec()
{
    $wapi = System::GetWapi();
    
    $params = $wapi->GetParams();
    
    
    
    $layersList = ParamUtil::Get($params, 'layers');
    
    $user = SimpleSession::Get()->GetUser();
    
    // $ini = System::GetIni();
    
    // load the project and verify their access
    
    list ($mapId) = ParamUtil::Requires($params, 'mapId');
    $project = Project::Get($mapId); // $world->getProjectById ( $_REQUEST ['id'] );
    
    $name = ParamUtil::Get($params, 'name', $project->name);
   
    
    if ($project->getPermissionById($user->id) < AccessLevels::EDIT) {
        throw new Exception('Insufficient privilege: You do not have permission to edit this map');
    }
    $project->name = $name;
    
    $project->description = ParamUtil::Get($params, 'description', $project->description);
    $project->tags = ParamUtil::Get($params, 'tags', $project->tags);
    $project->bbox = ParamUtil::Get($params, 'bbox', $project->bbox);
    
    $pLayerIds = array();
    foreach ($layersList as $layerEntry) {
        $isPLayer = ($layerEntry['playerId'] !== 'null');
        
        $projectLayer = null;
        if (! $isPLayer) {
            $projectLayer = $project->addLayerById($layerEntry['layerId'], null,$user->id,$z);
            $subIds = $projectLayer->GetSubIds();
            foreach($subIds as $sub) {
                $pLayerIds[] = $sub;
            }
            $playerId = $projectLayer->id;
            $layerEntry['playerId'] = $playerId;
        } else {
            $projectLayer = ProjectLayer::Get($layerEntry['playerId']);

        }
        $playerId = $layerEntry['playerId'];
        
        $pLayerIds[] = $playerId;
    }
    
    $project->DropMissingLayers($pLayerIds);
    
    $z = 0;
    foreach ($pLayerIds as $pLayerId) {
        $pLayer = ProjectLayer::Get($pLayerId);
        $pLayer->z = $z;
        $z --;
    }
    $format = ParamUtil::Get($params,'format','json');
    WAPI::SendSimpleResponse(array('action'=>'map/save_changes'),$format,'ok');
}

?>
