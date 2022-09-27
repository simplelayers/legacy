<?php

namespace views;

class OrganizationViews
extends UserViews
{
	public function GetMine($filter=false,$useAnd=true){}
	public function GetBookMarked($filter=false,$useAnd=true){}
	public function GetOwners($minPermission=1,$filter=false,$useAnd=true){}
	public function GetByOwner($ownerId,$minPermission=1,$filter=false,$useAnd=true){}
	public function GetAll () {
		$query = "SELECT name,id,short from organizations order by name";
		return $this->GetResults($query, array());
	}
}

?>