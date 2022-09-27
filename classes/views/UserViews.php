<?php

namespace views;

abstract class UserViews
{
	protected $userid;
	protected $db;

	public $paging;

	public function __construct($userid,$db) {
		$this->userid = $userid;	
		$this->db = $db;
	}

	public abstract function GetMine($filter=false,$useAnd=true);

	public abstract function GetBookMarked($filter=false,$useAnd=true);

	public abstract function GetOwners($minPermission=1,$filter=false,$useAnd=true);

	public abstract function GetByOwner($ownerId,$minPermission=1,$filter=false,$useAnd=true);

	protected function GetResults($query,$data=null) {
		if( is_null($data) ) $data = array($this->userid);
		$results = $this->db->Execute($query,$data);
		if( !$results ) throw new \Exception("DB Error: ".$this->db->ErrorMsg());
		return $results;
	}
	
	protected function AddFilters(&$query,$filter,$useAnd){
		$i=0;
		$query .= " and (";
		foreach($filter as $criteria){
			$query .= " ".(($i != 0) ? ($useAnd ? 'and' : 'or') : '')." ".$criteria[0]." ";
			switch($criteria[1]){
				case '=':
				case '>=':
				case '<=':
				case '>':
				case '<':
					$query .= $criteria[1]." '".$criteria[2]."'"; break;
				case 'contains': $query .= "~* '.*".$criteria[2].".*'"; break;
				default: $query .= "="; break;
			}
			$i++;
		}
		$query .= ")";
	}
}


?>