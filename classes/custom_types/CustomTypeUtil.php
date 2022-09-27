<?php
namespace custom_types;

class CustomTypeUtil {

	public static function DomainExists($db,$domain) {
		$query = <<<DOMAIN_QUERY
select count(domain_name) as count from information_schema.domains where domain_name='$domain'
DOMAIN_QUERY;
		$result = $db->GetRow($query);
		if(!$result) return false;
		return ($result['count'] > 0 );

	}


	public static function TypeExists($db,$type) {
		$query = <<<TYPE_QUERY
select count(typename) as count from information_schema.domains where typename='$type'
TYPE_QUERY;
		$result = $db->GetRow($query);
		if(!$result) return false;
		return ($result['count'] > 0 );
	}

	public static function CustomTypeExists($db,$type) {

		if( self::DomainExists($db,$type) ) return true;
		if( self::TypeExists( $db,$type) ) return true;
		return false;

	}


}
?>