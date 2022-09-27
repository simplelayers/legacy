<?php


class GlobalLayerPermissions //extends Permissions
{

	private $table = "layers";
	private $userField = "owner";
	private $permField = "sharelevel";
	private $targetField = "id";
	
}

class LayerPermissions //extends Permissions
{
	private $table = "layerssharing";
	private $userField = "who";
	private $permField="permission";
	private $targetField = "layer";

}

class LayerGroupPermissions //extends Permissions
{
	private $table = "layersharing_socialgroups";
	private $permField = "permission";
	private $userField = 	"group_id";
	private $targetField = "layer_id";

}

?>