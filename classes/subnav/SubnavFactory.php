<?php

namespace subnav;

class SubnavFactory {
	
	const SUBNAV_ADMIN = 'admin';
	const SUBNAV_CONTACT = 'contact';
	const SUBNAV_GROUP = 'group';
	const SUBNAV_LAYER = 'layer';
	const SUBNAV_ORG = 'org';
	const SUBNAV_ORGANIZATION = 'organization';	
	const SUBNAV_PROJECT = 'project';
	const SUBNAV_ACCOUNT = 'account';
	
	public static $nav_options = array('',self::SUBNAV_ADMIN,self::SUBNAV_CONTACT,self::SUBNAV_GROUP,self::SUBNAV_LAYER,self::SUBNAV_ORG,self::SUBNAV_PROJECT,self::SUBNAV_ACCTOUNT);
	/**
	 * 
	 * @param unknown $type
	 * @return Subnav
	 */
	public static function GetNav($type,$params=null) {
		return null;
		$subnav = null;
		if(is_numeric($type)) $type = self::$nav_options[$type];
		switch($type) {
			case self::SUBNAV_ADMIN:
				$subnav = new AdminSubnav($params);
				break;
			case self::SUBNAV_CONTACT:
				$subnav = new ContactSubnav($params);
				break;
			case self::SUBNAV_GROUP:
				$subnav = new GroupSubnav($params);
				break;
			case self::SUBNAV_LAYER:
				$subnav = new LayerSubnav($params);
				break;
			case self::SUBNAV_ORG:
			case self::SUBNAV_ORGANIZATION:
				$subnav = new OrganizationSubnav($params);
				break;
			case self::SUBNAV_PROJECT:
				$subnav = new ProjectSubnav($params);
				break;
			case self::SUBNAV_ACCOUNT:
				$subnav = new AccountSubnav($params);
				break;
		}
		return $subnav;
				
	}
	
	/**
	 * 
	 * @param SubNav $nav
	 * @param unknown $template

	 */
	public static function SetNav(Subnav $nav,$template) {
		$template->assign('subnav',$nav->display());
	}
	
	public static function UseNav($type,$template) {
		return null;
		$nav = self::GetNav($type);
		$nav->makeDefault();
		
		self::SetNav($nav,$template);
	}
	
	
}

?>