<?php
use utils\PageUtil;
/**
 * The form for defining a remote ODBC database as a new Layer.
 * @package Dispatchers
 */
/**
  */
function _config_odbc1() {
	$config = Array();
	// Start config
	$config["sendWorld"] = false;
	// Stop config
	return $config;
}

function _dispatch_odbc1($template, $args,$org,$pageArgs) {
$user = $args['user'];
$pageArgs['pageSubnav'] = 'data';
$pageArgs['pageTitle'] = 'Data - Import Remote Data via ODBC';

PageUtil::SetPageArgs($pageArgs, $template);

if ($args['user']->community) {
   print javascriptalert('You cannot create this type of layers with a community account.');
   return print redirect('layer.list');
}
/*@var $ODBCSERVERPORTS Enum */
$ports = System::GetODBCPorts();

$template->assign('odbcserveroptions', array_keys($ports) );
$template->assign('odbcserverports', $ports);
$template->assign('odbcservernames', implode(', ',array_keys($ports)) );

$template->display('import/odbc1.tpl');
}?>
