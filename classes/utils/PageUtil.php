<?php
namespace utils;

use model\Permissions;
use model\License;
use model\SeatAssignments;
use model\reporting\Reports;

class PageUtil
{

    public static $hasMixedInPlan = false;

    public static function SetPageArgs($args, \SLSmarty $template)
    {
        
        $args['sl_special'] = \RequestUtil::GetSpecial();
        
        $template->assign('pageArgs', json_encode($args));
        $template->assign('pageArgsInfo', $args);
    }

    public static function GetPageArgs(\SLSmarty $template)
    {
        
        $data = $template->getVar('pageArgs');
        
        if ($data) {
            return json_decode($data, true);
        }
        return null;
    }

    public static function MixinPlanArgs($template)
    {
        $pageArgs = self::GetPageArgs($template);
        
        $orgId = $pageArgs['userOrgId'];
        if(is_null($orgId)) return $pageArgs;
        $org = \Organization::GetOrg($orgId);
        
        $layerLimit = $org->max_layers;
        $numLayers = $org->countLayers();
        
        $canCreate = $org->CanAddLayers();
        
        if ($layerLimit == "") {
            $limitState = "limitstate_none";
        } else {
            $limitState = 'limitstate_ok';
            if (($numLayers >= ceil($layerLimit / 2.0)) && ($numLayers < $layerLimit)) {
                $limitState = 'limitstate_warn';
            } elseif ($numLayers >= $layerLimit) {
                $canCreate = false;
                $limitState = 'limitstate_limited';
            }
        }
        
        $pageArgs['canCreate'] = (! isset($pageArgs['layerId']) && $canCreate);
        // $pageArgs['canCreate'] =false;
        $pageArgs['plan_limits'] = array(
            'layerLimitState' => $limitState,
            'layerCount' => $numLayers,
            'layerLimit' => $layerLimit
        );
        self::SetPageArgs($pageArgs, $template);
        return $pageArgs;
    }

    public static function MixinMapArgs($template)
    {
        $pageArgs = self::GetPageArgs($template);
        if (self::$hasMixedInPlan)
            return $pageArgs;
        if (! isset($pageArgs['mapId']))
            return $pageArgs;
        $map = \Project::Get($pageArgs['mapId']);
        $pageArgs['mapOwnerId'] = $map->ownerid;
        $pageArgs['isMapOwner'] = (($pageArgs['userId'] == $pageArgs['mapOwnerId']) || ($pageArgs['pageActor'] == 'admin')) ? 'true' : 'false';
        $pageArgs['isMapEditor'] = (($pageArgs['pageActor'] == 'admin') || \AccessLevels::HasAccess($map->getPermissionById($pageArgs['userId']), \AccessLevels::EDIT)) ? 'true' : 'false';
        if (! Permissions::HasPerm($pageArgs['permissions'], ':MapsGeneral:', Permissions::EDIT))
            $pageArgs['isMapEditor'] = 'false';
        $pageArgs['isMapEmbeddable'] = (! $map->allowlpa or ($map->getPermissionById(0) < \AccessLevels::READ)) ? 'false' : 'true';
        self::SetPageArgs($pageArgs, $template);
        self::$hasMixedInPlan = true;
        return $pageArgs;
    }

    public static function MixinLayerArgs($template)
    {
        $user = \SimpleSession::Get()->GetUser();
        $pageArgs = self::GetPageArgs($template);
        
        if (! isset($pageArgs['layerId']))
            return $pageArgs;
        $layerId = $pageArgs['layerId'];
        
        $layer = \System::Get()->getLayerById($layerId);
        $perm = $layer->getPermissionById($user->id);
        $accessLevel = \AccessLevels::GetLevel($perm);
        
        $hasRolePerm = Permissions::HasPerm($pageArgs['permissions'],':Layers:General:',Permissions::COPY);
        $hasLayerOwner	= $layer->owner->id == $user->id;
        
        $hasOrgActor = $pageArgs['orgActor'] == 'owner';
        $hasActor = $hasLayerOwner || $hasOrgActor || ($pageArgs['pageActor'] == 'admin'); 
        
        $hasAccess = ($perm >= \AccessLevels::COPY) || $hasActor;
        $hasOrigin = !is_null($layer->originalid);
        
        $hasReplaced = !is_null($layer->replacedid);
        $originalLayer = null;
        if($hasOrigin) {
            $originalLayer = \Layer::GetLayer(+$layer->originalid);
        }
        
   
        $ownsBoth	= ($hasOrigin) ? ($layer->ownerId == $originalLayer->ownerId) : false;
        $pageArgs['layerCanDuplicate'] = $hasRolePerm && $hasAccess;
      
        $pageArgs['layerCanReplaceOriginal'] = $hasRolePerm && $hasActor && $hasAccess && $ownsBoth && $hasOrigin;
        $pageArgs['layerCanRevertToOriginal'] = $hasRolePerm && $hasActor && $hasAccess && $ownsBoth && $hasOrigin && $hasReplaced;
         
        $geomType = $layer->geom_type;
        $pageArgs['geomType'] = $geomType;
        $pageArgs['isVector'] = \GeomTypes::isVector($geomType) ? 'true' : 'false';
        
        $pageArgs['isRaster'] = \LayerTypes::IsRaster($layer->type) ? 'true' : 'false';
        
        $pageArgs['hasRecords'] = $layer->getHasRecords() ? 'true' : 'false';
        $pageArgs['hasEditableRecords'] = ($layer->getHasEditableRecords()) ? 'true' : 'false';
        
        $pageArgs['hasCopyableRecords'] = ($layer->getHasEditableRecords() && $layer->getPermissionById($user->id) >= \AccessLevels::COPY) ? 'true' : 'false';
        $pageArgs['hasBackup'] = ($layer->backup) ? 'true' : 'false';
        $pageArgs['layerOwnerId'] = "" . $layer->owner->id;
        $pageArgs['isOwner'] = ($pageArgs['userId'] == $pageArgs['layerOwnerId']) || ($pageArgs['pageActor'] == 'admin') ? "true" : "false";
        
        
        $pageArgs['isLayerEditor'] = (($pageArgs['pageActor'] == 'admin') || \AccessLevels::HasAccess($layer->getPermissionById($pageArgs['userId']), \AccessLevels::EDIT)) ? 'true' : 'false';
        if (! Permissions::HasPerm($pageArgs['permissions'], ':Layers:General:', Permissions::EDIT))
            $pageArgs['isLayerEditor'] = 'false';
        $template->assign('isRecordEditor', $pageArgs['isLayerEditor'] == 'true');
        
        if ($layer->backup) {
            $pageArgs['layerBackupTime'] = $layer->backuptime;
        }
        $pageArgs['isExportable'] = ((\GeomTypes::IsExportable($geomType) || $layer->getHasRecords()) and (($pageArgs['isLayerEditor'] == 'true') || $pageArgs['layerAccess']=='copy')) ? 'true' : 'false';
        $pageArgs['layerAccess'] = $accessLevel;
        
        $license = new License();
        $orgId = $pageArgs['userOrgId'];
        $org = \Organization::GetOrg($orgId);
        $limits = $license->GetLimitLookup($org->id);
        if ($limits['max_layers']) {
            if (! is_array($limits['max_layers]'])) {
                $max_layers = array(
                    'feature' => $limits['max_layers'],
                    'raster' => '',
                    'wms' => ''
                );
            }
            $pageArgs['max_layers'] = $limits['max_layers'];
            if ($user->countLayers() >= $limits['max_layers']) {
                $pageArgs['reachedLayerLimit'] = 'true';
            }
        } else {
            $pageArgs['max_layers'] = '';
            $pageArgs['reachedLayerLimit'] = 'false';
        }
        
        self::SetPageArgs($pageArgs, $template);
        
        return self::GetPageArgs($template);
    }

    public static function MixinContactArgs($template)
    {
        $user = \SimpleSession::Get()->GetUser();
        
        $pageArgs = self::GetPageArgs($template);
        if (! isset($pageArgs['contactId'])) {
            $pageArgs['contactId'] = \SimpleSession::Get()->GetUser()->id;
        }
        $contactId = ParamUtil::Get($pageArgs, 'contactId');
        $canEditContact = ($contactId == $user->id);
        
        $contactOrg = \Organization::GetOrgByUserId($contactId);
        
        $pageArgs['contactOrgName'] = $contactOrg->name;
        $pageArgs['contactActor'] = ($user->id == $contactId) ? 'owner' : 'viewer';
        $seat = SeatAssignments::GetUserSeat($contactId);
        $contactSeat = $seat['data']['seatname'];
        $pageArgs['contactSeat'] = $seat['data']['seatName'];
        
        $contactOrgOwner = $contactOrg->owner->id;
        
        if (! $canEditContact) {
            if ($pageArgs['pageActor'] == 'admin') {
                $canEditContact = true;
            } elseif ($contactOrgOwner == $user->id) {
                $canEditContact = true;
            }
        }
        $pageArgs['canEditContact'] = $canEditContact;
        $pageArgs['contactName'] = $user->realname;
        $email = $user->email;
        
        if (! (is_null($email) || ($email == '')))
            $pageArgs['hasEmail'] = true;
        
        self::SetPageArgs($pageArgs, $template);
        return self::MixinGroupArgs($template);
    }

    public static function MixinGroupArgs($template)
    {
        $user = \SimpleSession::Get()->GetUser();
        $pageArgs = self::GetPageArgs($template);
        
        $groupId = ParamUtil::GetOne($pageArgs, 'groupId', 'group');
        if (is_null($groupId))
            return $pageArgs;
        
        $group = \Group::GetGroup($groupId);
        if (! $group)
            return;
        $moderatorId = $group->getMod();
        
        $groupActor = 'group_visitor';
        if ($moderatorId == $user->id)
            $groupActor = 'group_owner';
        
        if (! is_null($group->org_id) && ! $groupActor == 'group_owner') {
            if (\Organization::GetOrg($group->org_id)->owner == $user->id);
            $groupActor = 'group_owner';
        }
        
        if ($pageArgs['pageActor'] == 'admin') {
            $groupActor = 'group_owner';
        } else {
            if ($group->isMember($user->id)) {
                $groupActor = 'group_member';
            }
        }
        
        $pageArgs['groupDeleteAllowed'] = ($groupActor == 'group_owner');
        $pageArgs['groupStatus'] = (int) $group->getStatus($user->id);
        
        if ($group->org_id) {
            $pageArgs['groupStatus'] = - 1;
            $pageArgs['groupDeleteAllowed'] = false;
        }
        $pageArgs['groupActor'] = $groupActor;
        
        self::SetPageArgs($pageArgs, $template);
        return $pageArgs;
    }

    
    
    public static function RedirectTo($relPath = null,$args=array())
    {
      
        $url = BASEURL;
        $url .= (is_null($relPath)) ? '' : $relPath;
        
        echo "<form id='redirector' action='$url' method='POST'>";
        foreach($args as $arg=>$val) {
            $val = htmlentities($val);
            echo "<input type='hidden' name='$arg' value='$val'></input>";
            
        }
        echo "</form>";
        echo "<script>document.getElementById('redirector').submit()</script>";
        return;
    }
    
    public static function FullRedirect($full_url) {
        header('Location: '.$full_url);
        echo "<script type='text/javascript'>window.location='$full_url';</script>";
        die();
    }
}

?>