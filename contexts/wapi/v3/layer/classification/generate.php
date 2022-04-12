<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
function _config_generate() {
	$config = Array ();
	// Start config
	$config ["header"] = false;
	$config ["footer"] = false;
	$config ["customHeaders"] = true;
	$config ['sendUser'] = true;
	$config ['sendWorld'] = true;
	// Stop config
	return $config;
}

function _headers_generate() {
	header ( 'Content-Type: text/xml' );
}

/**
 * @ignore
 */
function _dispatch_generate($template, $args) {
  _headers_generate();
	/* @var $world World */
	$world = $args ['world'];
	
	/* @var $wapi WAPI */
	$wapi = $world->wapi;
	
	$projectInfo = $wapi->RequireProject ();
	
	/* @var $project Project */
	list ( $project, $permission ) = array_values ( $projectInfo );
	
	if ($permission < AccessLevels::EDIT)
		$wapi->HandleError ( new Exception ( DENIED_NEEDEDIT ) );
		
	/* @var $projectLayer projectLayer */
	$projectLayer = $wapi->RequireProjectLayer ( LayerTypes::VECTOR );

	$template->assign ( 'layer', $wapi->layer );
	$template->assign ( 'project', $project );
	$template->assign ( 'projectLayer', $projectLayer );
	
	// fetch the permissions and embed status being used for this call
	//// generate the ruleset; easy!
	
	$colorScheme = RequestUtil::Get ( 'colorscheme' );
	$fill_color = RequestUtil::Get('fill_color',RequestUtil::Get('fill'));
	$stroke_color = RequestUtil::Get('stroke_color',RequestUtil::Get('stroke'));
	$symbol = RequestUtil::Get('symbol');
	$symbol_size = RequestUtil::Get('symbol_size');
	$column = RequestUtil::Get('column');
	$fill_color = str_replace('#','', $fill_color);
	$stroke_color = str_replace('#','', $stroke_color);
	
    if($colorScheme != 'single') {
        list ( $palettename, $palettenumber, $palettetype ) = explode( '_',$fill_color);
    }
	switch ($colorScheme) {
		case 'single' :
			$ruleset = $projectLayer->colorscheme->generateSingle ($fill_color, $stroke_color, $symbol, $symbol_size);
			break;
		case 'equalinterval' :
			$ruleset = $projectLayer->colorscheme->generateEqualInterval ( $column, $palettetype, $palettenumber, $palettename, $stroke_color, $symbol, $symbol_size );
			break;
		case 'quantile' :
			$ruleset = $projectLayer->colorscheme->generateQuantile ( $column, $palettetype, $palettenumber, $palettename, $stroke_color, $symbol, $symbol_size);
			break;
		case 'unique' :
			$ruleset = $projectLayer->colorscheme->generateUnique ( $column, $palettenumber, $palettename, $stroke_color, $symbol, $symbol_size );
			break;
	}
	if (sizeof ( $ruleset ) > 100)
		$wapi->HandleError(new Exception( 'colorscheme too large'));//DENIED_COLORSCHEMETOOLARGE ));
	
	$template->assign('fill_color',$fill_color);
	$template->assign('stroke_color',$stroke_color);
	$template->assign('colorscheme',$colorScheme);
	$template->assign ( 'ruleset', $ruleset );
	
	// all set
	$template->display ( 'wapi/layer/generatedscheme.tpl' );
	
}
?>
