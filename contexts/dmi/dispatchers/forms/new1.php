<?
use utils\PageUtil;
function _config_new1() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_new1($template, $args, $org, $pageArgs) {
    
    
    $pageArgs['pageTitle'] = 'New Form';
    $pageArgs['pageSubnav'] = 'data'; 
    PageUtil::SetPageArgs($pageArgs, $template);
    PageUtil::MixinLayerArgs($template);
    PageUtil::SetPageArgs($pageArgs, $template);
	$template->assign('select', true);
	$template->assign('dataSelectorString', '{"source" : {"layer":{	1:["My Layers",1,function(){return \'./?do=wapi.views&type=mine&object=layer&format=json\';}],
										2:["Bookmarked Layers",1,function(){return \'./?do=wapi.views&type=marks&object=layer&format=json&and=or&filter=%5B%5B"permission"%2C"%3D"%2C"3"%5D%2C%5B"sharelevel"%2C"%3D"%2C"3"%5D%5D\';}],
										3:["Shared With Me",2,function(){return \'./?do=wapi.views&type=owners&object=layer&format=json&min=3\';},function(id){return \'./?do=wapi.views&type=owner&object=layer&owner=\'+id+\'&format=json&min=3\';},[{id:"-1",name:"All Shared"}]]
									}
						}}');
	$template->display('forms/new1.tpl');
}
?>