<?php
namespace custom_types;

class Type_CG_URL
implements ICustomType
{
	private $name="cg_url";
	private $db;

	public function __construct($db) {
		$this->db = $db;
	}

	public function AddCustomType() {
		if( CustomTypeUtil::DomainExists($this->db,$this->name) ) return;
		$this->db->Execute("CREATE DOMAIN $this->name AS varchar(2000)");
	}

	public function RemoveCustomType() {
		if( !CustomTypeUtil::DomainExists($this->db,$this->name) ) return;
		$this->db->Execute("DROP DOMAIN IF EXISTS $this->name CASCADE");
	}

}
?>