<?php
namespace subnav;
use \AccessLevels;
class ProjectSubnav extends Subnav {
	function makeDefault($project, $user){
		$this->switchForOwner($project, $user);
		if($project->getPermissionById($user->id) >= AccessLevels::READ){
			$this->add("View", "Map", "javascript:openViewer(<!--{\$id}-->);");
		}
		if($project->getPermissionById($user->id) >= AccessLevels::COPY){
			$this->add("View", "WMS", "project.ogc&id=<!--{\$id}-->", true);
		}
		if($project->getPermissionById($user->id) >= AccessLevels::EDIT){
			$this->add("Edit", "Details", "project.edit1&id=<!--{\$id}-->");
			$this->add("Edit", "Discuss", "project.discussion&id=<!--{\$id}-->");
		}else{
			$this->add("View", "Details", "project.info&id=<!--{\$id}-->");
			$this->add("View", "Discuss", "project.discussion&id=<!--{\$id}-->");
		}
		if ($project->owner->id == $user->id) {
			$this->add("View", "Logs", "project.log&id=<!--{\$id}-->");
		}
	}
	function switchForOwner($project, $user){
		$this->assign("id", $project->id);
		$this->assign("user", $user);
		$this->assign("communitymap", true);
		$this->assign("ownerid", $project->owner->id);
		$this->assign("ownerData", 'Owner: <a href="./?do=contact.info&id='.$project->owner->id.'">'.$project->owner->username.'</a>');
		$this->assign("lastUpdated", $project->last_modified);
		$this->assign("objectData", 'Maps - '.htmlspecialchars ($project->name));
		if ($project->owner->id == $user->id) {
			$this->add("Edit", "Embed", "project.iframe1&id=<!--{\$id}-->", true);
			$this->add("Manage", "Sharing", "project.permissions&id=<!--{\$id}-->", true);
			$this->add("Manage", "Delete", 'javascript: if(confirm(\'Are you sure you want to delete this map?\nThere is no way to un-delete or recover a map once it has been deleted.\n\nClick OK to delete this map.\nClick Cancel to NOT delete this map.\')){window.location = \'.?do=project.delete&id='.$project->id.'\';}');
			$this->add("Manage", "Copy", "project.copy1&id=<!--{\$id}-->");
		}
		if ($user->isProjectBookmarkedById($project->id)) {
			$this->assign("edit", '<a href="./?do=project.removebookmark&id='.$project->id.'"><img src="media/icons/book_delete.png"/></a>');
		}else {
			$this->assign("edit", '<a href="./?do=project.addbookmark&id='.$project->id.'"><img src="media/icons/book_add.png"/></a>');
		}
	}
}
?>