<?php

class ProjectPermissions //extends Permissions
{
	private $table = "projectsharing";
	private $userField = "who";
	private $permField="permission";
	private $targetField = "project";

}

class ProjectGroupPermissions //extends Permissions
{
	private $table = "projectsharing_socialgroups";
	private $permField = "permission";
	private $targetField = "project_id";
	private $userField = "group_id";

}

?>