<?php
use model\organizations\OrgMedia;
use utils\ParamUtil;

require_once('includes/main.inc.php');

$autoDetect = ParamUtil::Get(WAPI::GetParams(),'autodetect');




$orgMedia = new OrgMedia();

$orgId = $orgId = ParamUtil::Get(WAPI::GetParams(),'orgId');
if(is_null($orgId)) {
    $sandbox = \System::GetSandbox();
    
    $orgId =1;
    if(!is_null($autoDetect)) {
        $project = Project::Get($autoDetect);
        $orgId = Organization::GetOrgByUserId($project->owner->id)->id;
    } elseif(!is_null($sandbox)) {
        
        $orgTest = \Organization::GetOrgByUserName($sandbox);
        if($orgTest) {
            $orgId = $orgTest->id;
        }   	
    } 
}
$media = ($orgId == 1)? null : $orgMedia->GetOrgMedia(OrgMedia::DATATYPE_ORGANIZATION_MEDIA, array('orgId'=>$orgId,'media_name'=>'logo','as_item'=>true));
if(!$media) $media = $orgMedia->GetOrgMedia(OrgMedia::DATATYPE_ORGANIZATION_MEDIA, array('orgId'=>1,'media_name'=>'logo','as_item'=>true));
/*@var $media  model\organizations\OrgMediaImage */
$media->WriteImage();


?>