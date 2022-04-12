<?php
use views\OrganizationViews;
use utils\ParamUtil;
use model\organizations\OrgMedia;
use model\organizations\OrgReferrals;
use model\organizations\OrgMediaLink;
use model\organizations\OrgMediaImage;
use model\License;
use model\organizations\OrgMediaFile;
use utils\PageUtil;

function _exec()
{
    define('TARGET_ORG', 'org');
    
    define('TARGET_MEDIA', 'org_media');
    define('TARGET_MEDIA_EMPLOYEES', 'employee_media');
    define('TARGET_MEDIA_OPTIONS', 'media_options');
    define('TARGET_INVITES', 'invites');
    
    $session = SimpleSession::Get();
    $params = WAPI::GetParams();
    $user = $session->GetUser();
    $system = System::Get();
    
    list ($action, $target) = ParamUtil::Requires(WAPI::GetParams(), 'action', 'target');
    
    switch ($action) {
        case WAPI::ACTION_LIST:
            switch ($target) {
                case TARGET_ORG:
                    $views = new OrganizationViews($user->id, System::Get()->db);
                    $orgs = $views->GetAll();
                    WAPI::SendSimpleResults($orgs);
                    break;
                case TARGET_MEDIA:
                    $orgMedia = new OrgMedia();
                    $media = $orgMedia->GetOrgMedia(OrgMedia::DATATYPE_ORGANIZATION_MEDIA, ParamUtil::GetValues(WAPI::GetParams(), 'orgId'));
                    
                    WAPI::SendSimpleResults($media);
                    break;
                case TARGET_MEDIA_OPTIONS:
                    $orgMedia = new OrgMedia();
                    WAPI::SendSimpleResults($orgMedia->GetMediaTypes('org'));
                    break;
                case TARGET_INVITES:
                    $orgId = ParamUtil::Get(WAPI::GetParams(), 'orgId'); // ,$user->getOrganization(true));
                    
                    $orgReferrals = new OrgReferrals();
                    
                    WAPI::SendSimpleResults($orgReferrals->GetReferrals($orgId), null, true);
                    break;
            }
            break;
        case WAPI::ACTION_SAVE:
            $changes = ParamUtil::Get(WAPI::GetParams(), 'changeset', array(), true);
            switch ($target) {
                case TARGET_ORG:
                    Organization::HandleChanges($changes);
                    break;
                case TARGET_MEDIA:
                    $orgId = ParamUtil::RequiresOne(WAPI::GetParams(), 'orgId');
                    $orgMedia = new OrgMedia();
                    $params = ParamUtil::GetValues(WAPI::GetParams(), 'orgId', 'employeeId');
                    $params['dataType'] = OrgMedia::DATATYPE_EMPLOYEE_MEDIA;
                    $orgMedia->HandleChanges(OrgMedia::DATATYPE_ORGANIZATION_MEDIA, $changes);
                    break;
                case TARGET_MEDIA_EMPLOYEES:
                    $orgId = ParamUtil::RequiresOne(WAPI::GetParams(), 'orgId');
                    $orgMedia = new OrgMedia();
                    $params = ParamUtil::GetValues(WAPI::GetParams(), 'orgId', 'employeeId');
                    $params['dataType'] = OrgMedia::DATATYPE_EMPLOYEE_MEDIA;
                    $orgMedia->HandleChanges($params, $changes);
                    break;
                case TARGET_INVITES:
                    $orgReferrals = new OrgReferrals();
                    $orgReferrals->HandleChanges($changes);
                    break;
            }
            WAPI::SendSimpleResults(array(
                'status' => 'ok',
                'message' => 'changes saved'
            ));
            break;
        
        case WAPI::ACTION_ADD:
            switch ($target) {
                case TARGET_ORG:
                    $params = WAPI::GetParams();
                    $org = Organization::CreateOrg($params);
                    $orgId = ($org) ? $org->id : null;
                    $license = License::GetPlan($orgId);
                    $licenses = new License();
                    $license['data']['isChanged'] = true;
                    
                    WAPI::SendSimpleResponse(array(
                        'status' => 'ok',
                        'message' => 'Organization added',
                        'orgId' => $orgId
                    ));
                    break;
                case TARGET_INVITES:
                    $params = WAPI::GetParams();
                    if (! ParamUtil::Get($params, 'orgId')) {
                        $params['orgId'] = $user->getOrganization(true);
                    }
                    WAPI::SendSimpleResults(OrgReferrals::CreateReferral($params));
                    break;
            }
            break;
        case WAPI::ACTION_UPSERT:
        case TARGET_MEDIA:
            $params = WAPI::GetParams();
            $orgId = ParamUtil::Get(WAPI::GetParams(), 'orgId');
            list ($orgId,$mediaName) = ParamUtil::Requires($params, 'orgId','media_name');
            
            $returnTo = ParamUtil::Get($params, 'return_to');
            $mediaName = ParamUtil::Get($params,'media_name');
            $returnTo.='/orgId:'.$orgId;
            $om = new OrgMedia();
            $types = $om->GetMediaTypes();
            $info = $types[$mediaName];
            
            $type = $info['media_type'];
            
            switch ($type) {
                case OrgMedia::MEDIATYPE_IMAGE:
                    $name = $mediaName;
                    $content = ParamUtil::Get($_FILES, 'file_content');
                    $params = ParamUtil::GetValues($content, 'tmp_name:filePath', 'type:format');
                    $content = new OrgMediaImage();
                    $content->MakeOrgImage($params);
                    break;
                case OrgMedia::MEDIATYPE_FILE: 
                    $name = $mediaName;
                    $content = ParamUtil::Get($_FILES, 'file_content');
                    $params = ParamUtil::GetValues($content, 'tmp_name:filePath', 'type:format');
                    $content = new OrgMediaFile();
                    $content->MakeOrgFile($params);
                    break;                            
                case OrgMedia::MEDIATYPE_LINK:
                    $relPath = ParamUtil::RequiresOne($params, 'link_content');
                    $name = $mediaName;
                    $link = new OrgMediaLink();
                    $content = $link->MakeLink($relPath);
                    break;                
            }
            
            $media = new OrgMedia();
            
            $media->UpsertOrgMedia($orgId, $name, $content);
            
            PageUtil::RedirectTo( str_replace('__', '/', $returnTo));
            //header('Location: ' . BASEURL . str_replace('__', '/', ));
            break;
        case TARGET_MEDIA_EMPLOYEES:
            $params = WAPI::GetParams();
            $employeeId = ParamUtil::RequiresOne($params, 'employeeId');
            if ($type == OrgMedia::MEDIATYPE_IMAGE) {
                $content = ParamUtil::GetRequiredValues($_FILES, 'content');
                $params = ParamUtil::GetValues($content, 'tmp_name:filePath', 'type:format');
                $content = new OrgMediaImage();
                $content->MakeOrgImage($params);
            } elseif ($type == OrgMedia::MEDIATYPE_LINK) {
                list ($relPath) = ParamUtil::RequiresOne($params, 'content');
                $link = new OrgMediaLink();
                $content = $link->MakeLink($relPath);
                $content = $link;
            }
            $media = new OrgMedia();
            
            $media->UpsertEmployeeMedia($orgId, $employeeId, $type, $content);
            break;
            break;
        case WAPI::ACTION_GET:
            
            switch ($target) {
                case TARGET_ORG:
                    break;
                case TARGET_MEDIA:
                    $media = new OrgMedia();
                    $res = $media->GetMediaContent($params, true);
                    if (is_null($res)) {
                        readfile(BASEURL . 'wapi/media/icons/action:get/target:icon/category:Icons/icon:Content-33/size:64/token:' . $session->GetId());
                    } else {
                        echo $res;
                    }
                    break;
                case TARGET_MEDIA_EMPLOYEES:
                    break;
                case TARGET_INVITES:
                    break;
            }
            break;
        case WAPI::ACTION_DELETE:
            switch($target) {
                case TARGET_MEDIA:
                    $media = new OrgMedia();
                    list($orgId,$mediaId) = ParamUtil::Requires(WAPI::GetParams(), 'orgId','id');
                    $media->RemoveOrgMediaById($orgId,OrgMedia::DATATYPE_ORGANIZATION_MEDIA,$mediaId);
                    WAPI::SendSimpleResponse(array('status'=>'ok'));
            }
    }
}

?>