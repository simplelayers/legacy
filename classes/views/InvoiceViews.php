<?php

namespace views;

class InvoiceViews
extends UserViews
{
	public function GetMine($filter=false,$useAnd=true){}
	public function GetBookMarked($filter=false,$useAnd=true){}
	public function GetOwners($minPermission=1,$filter=false,$useAnd=true){}
	public function GetByOwner($ownerId,$minPermission=1,$filter=false,$useAnd=true){}
	public function GetByOrg($orgId){
		$query = "SELECT id, to_char(created, 'MM/DD/YYYY') AS created, to_char(paid, 'MM/DD/YYYY') AS paid FROM invoices AS i WHERE org_id = ?";
		return $this->GetResults($query, Array($orgId));
	}
	public function GetAll(){
		$query = "SELECT id, org_id, (select name FROM organizations AS o WHERE o.id = i.org_id) As org_name, to_char(created, 'MM/DD/YYYY') AS created, to_char(paid, 'MM/DD/YYYY') AS paid FROM invoices AS i";
		return $this->GetResults($query, Array());
	}
}

?>