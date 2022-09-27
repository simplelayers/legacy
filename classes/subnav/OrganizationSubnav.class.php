<?php
namespace subnav;

class OrganizationSubnav extends Subnav {
	function makeDefault( $user,$title=null,$org=null,$pageArgs=null){
		$org = (is_null($org))? \Organization::GetOrgByUserId($user->id) : $org;
		
		$this->switchForOwner($org, $user);
		$this->add("View", "Details", "organization.info&id=<!--{\$id}-->");
		$this->add("View", "Group", "group.info&groupId=<!--{\$groupId}-->");
		
		//if($user->admin)$this->add("View", "Edit", "?do=organization.info&id=<!--{\$id}-->");
		/*var_dump($pageArgs);
		var_dump($pageArgs['actorType']);
		var_dump($pageArgs['org_owner']);*/
		if(($pageArgs['actorType']=='admin') || ($pageArgs['actorType'] = 'org_owner')) {
			$this->add("View", "Resources", "/organization/resources/orgId:<!--{\$id}-->");
			$this->add("View", "Usage Report", "organization.report&id=<!--{\$id}-->");
			$this->add("Administrate", "Employees", "/organization/employees/orgId:<!--{\$id}-->/");
			$this->add("Administrate", "Invites", "/organization/invites/orgId:<!--{\$id}-->");
			$this->add("Administrate", "License", "/organization/license/orgId:<!--{\$id}-->");
		}
	}
	function switchForOwner($org, $user){
		$this->assign("id", $org->id);
		$this->assign("groupId", $org->group->id);
		
		if($user->admin)$this->assign("objectData", '<a href="'.BASEURL.'/admin/organization/list">Organizations</a>');
		else$this->assign("objectData", '<a href="./?do=organization.list">Organizations</a>');
		$this->assign("ownerData", htmlspecialchars ($org->name));
	}
}
?>