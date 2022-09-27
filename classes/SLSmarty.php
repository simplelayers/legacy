<?php

use utils\ParamUtil;
/**
 * This is a thin wrapper around a Smarty instance, with various configurations being set.
 * 
 * Most notable to a SMarty person is the new delimiters: <!--{ }-->
 * 
 * Also notable is the disabling of caching, since the template generation isn't nearly as
 * bad as the confusion caused by dev/live templates being mixed up by the caching engine.
 * 
 * Lastly, a few custom | modifiers are created and registered, to perform very template-ish functions
 * which Smarty lacks.
 * 
 */
System::RequireSmarty ();
if (! defined ( 'WEBROOT' ))
	throw new Exception ( 'Smarty initialized before environment ready' );
class SLSmarty extends Smarty {
	function __construct($templateDir = null) {
		// call the Smarty constructor
		parent::__construct ();
		if(is_null($templateDir)) $templateDir = WEBROOT . 'templates';
		// set the template location and cache locations
		$this->template_dir = $templateDir;
		$this->compile_dir = '/tmp/smarty/templates_c';
		$this->config_dir = '/tmp/smarty/configs';
		$this->cache_dir = '/tmp/smarty/cache';
		$this->use_sub_dirs = true;
		
		// set the delimiters to <!--{ }-->
		$this->left_delimiter = '<!--{';
		$this->right_delimiter = '}-->';
		
		// disable caching, cuz the templates themselves are pretty swift and caching just causes irritation!
		$this->caching = 0;
		$this->compile_check = true;
		$this->force_compile = true;
		
		$this->clearAllAssign ();
		$this->clearAllCache ();
		
		// and add custom functions usable as | modifiers
		$this->registerPlugin ( 'modifier', 'commafy', 'commafy' );
		$this->registerPlugin ( 'modifier', 'strippound', 'strippound' );
		$this->registerPlugin ( 'modifier', 'unquote', 'unquote' );
		$this->registerPlugin ( 'modifier', 'bbox_wms_111', 'bbox_wms_111' );
		$this->registerPlugin ( 'modifier', 'dump', 'dump' );
		$this->registerPlugin ( 'modifier', 'box2bbox','box2bbox');
		$this->registerPlugin ( 'modifier', 'sanitize_html','sanitize_html');
		
		$this->registerPlugin ( 'block', 'ico_button', 'ico_button' );
		$this->registerPlugin ( 'block', 'button', 'button' );
		
		$this->registerPlugin ( 'function', 'icon', 'icon' );
		$this->registerPlugin ( 'function', 'loading_icon', 'loading_icon' );
		
		$this->registerPlugin ( 'function', 'org_logo_url', 'org_logo_url' );
		$this->registerPlugin ( 'function', 'sl_logo_url', 'sl_logo_url' );
		$this->registerPlugin ( 'function', 'url_href', 'url_href' );
		$this->registerPlugin ( 'function', 'url_name', 'url_name' );
		
	}
	public static function GetTemplater($templateDir=null) {
		if (isset ( $GLOBALS ['template'] ))
			return $GLOBALS ['template'];
		$GLOBALS ['template'] = new SLSmarty ($templateDir);
		return $GLOBALS ['template'];
	}
	function getVar($var) {
		$valKey = 'value';
		return $this->tpl_vars[$var]->$valKey;
	}	
	
   
}


function sanitize_html($var) {
    return htmlspecialchars($var);
}
function commafy($number, &$template) {
	return strrev ( ( string ) preg_replace ( '/(\d{3})(?=\d)(?!\d*\.)/', '$1,', strrev ( $number ) ) );
}
function strippound($hexcode) {
	return str_replace ( '#', '', $hexcode );
}
function unquote($string) {
	$string = str_replace ( '"', '', $string );
	$string = str_replace ( "'", '', $string );
	return $string;
}
function dump($thingy) {
	var_dump ( $thingy );
}

function box2bbox($box) {
	$box = str_replace('BOX(','',$box);
	$box = str_replace(')','',$box);
	$box = trim($box);
	$box = str_replace(' ',',',$box);
	$box = explode(',',$box);;
	return  $box;
	
}

function url_href($url) {
    if(stripos($url,'feature_img://')===0) {
        $url = substr($url,14);
        $url = BASEURL.'wapi/media/featureImg/action:get/'.$url;
        return $url;
    }
    return $url;
}
function url_name($url) {
    if(stripos($url,'feature_img://')===0) {
        $url = substr($url,14);
        $params = explode('/',$url);
        $params = ParamUtil::ParseParams($params);
        
        $name = ParamUtil::Get($params,'name');
        return $name;
    }
    return $url;
}


function prettydate($datetime) {
	return date ( "F j, Y @ g:i a", strtotime ( $datetime ) ); // Oneliner.
	/*
	 * list($ymd,$hms) = explode(' ',$datetime); list($y,$m,$d) = explode('-',$ymd); list($h,$i) = explode(':',$hms); global $MONTHS; $m = $MONTHS[$m]; $d = (int) $d; $ampm = 'am'; if ($h > 12) { $h -= 12; $ampm='pm'; } elseif ($h == 12) { $ampm = 'pm'; } $h = (int) $h; return "$m $d, $y @ $h:$i $ampm";
	 */
}
function bbox_wms_111($params) {
	/* @var $layer Layer */
	$layer = $params;
	$bbox = $layer->getExtent ();
	$minx = min ( $bbox [0], $bbox [2] );
	$miny = min ( $bbox [1], $bbox [3] );
	$maxx = max ( $bbox [0], $bbox [2] );
	$maxy = max ( $bbox [1], $bbox [3] );
	return "<BoundingBox SRS=\"EPSG:4326\"  minx=\"$minx\" miny=\"$miny\" maxx=\"$maxx\" maxy=\"$maxy\" resx=\"0.01\" resy=\"0.01\" />";
}
function ll_bbox_wms_111($params) {
	/* @var $layer Layer */
	$layer = $params;
	$bbox = $layer->getExtent ();
	$minx = min ( $bbox [0], $bbox [2] );
	$miny = min ( $bbox [1], $bbox [3] );
	$maxx = max ( $bbox [0], $bbox [2] );
	$maxy = max ( $bbox [1], $bbox [3] );
	return "<LatLonBoundingBox  minx=\"$minx\" miny=\"$miny\" maxx=\"$maxx\" maxy=\"$maxy\" />";
}
function ico_button($params, $content, &$smarty, &$repeat) {
	if ($repeat)
		return;
	$icoset = isset ( $params ['icoset'] ) ? $params ['icoset'] : 'weblay_ico';
	$icon = $params ['icon'];
	$color = isset ( $params ['color'] ) ? $params ['color'] : '';
	$size = isset ( $params ['size'] ) ? $params ['size'] : '';
	if ($content == "&nbsp;")
		$content = "";
	$noLabel = $content == "" ? "noLabel" : '';
	$labelClass = ($noLabel != '') ? '' : 'label';
	$id = isset ( $params ['id'] ) ? 'id="' . $params ['id'] . '"' : '';
	$label = "<span id=\"label\" class=\"$labelClass\">$content</span>";
	
	return "<button $id class=\"color button ico_button $color $size $noLabel \"><img src=\"media/images/empty.png\" class=\"$icoset $icon\"></img>$label</button>";
}
function button($params, $content, &$smarty, &$repeat) {
	if ($repeat)
		return;
	$color = isset ( $params ['color'] ) ? $params ['color'] : '';
	if ($content == "&nbsp;")
		$content = "";
	$label = "<span id=\"label\">$content</span>";
	$id = isset ( $params ['id'] ) ? 'id="' . $params ['id'] . '"' : '';
	$class = isset ( $params ['class'] ) ? $params ['class'] : '';
	return "<button $id class=\"color button $color $class\">$label</button>";
}
function icon($params, &$smarty) {
	$id = isset ( $params ['id'] ) ? 'id="' . $params ['id'] . '"' : '';
	$icon = isset ( $params ['icon'] ) ? $params ['icon'] : '';
	$icoset = isset ( $params ['icoset'] ) ? $params ['icoset'] : 'weblay_ico';
	return "<img $id src='media/images/empty.png' class='$icoset $icon'></img>";
}
function loading_icon($params, &$smarty) {
	$id = isset ( $params ['id'] ) ? "id=\"{$params['id']}\"" : "id=\"loading\"";
	return <<<LOADINGIMG
<img src="media/images/ajax-loader.gif" id="loading" />
LOADINGIMG;
}
function sl_logo_url($params, &$smarty) {
	list ( $url ) = explode ( '?', BASEURL );
	$url .= '/logo.php';
	return $url;
}
function org_logo_url($params, &$smarty) {
	$org = SimpleSession::Get ()->GetOrg ();
	return $org->getMediaURL ( 'logo' );
}



?>
