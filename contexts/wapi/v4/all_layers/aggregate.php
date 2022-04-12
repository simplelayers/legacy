<?php
use \AccessLevels;
use \GeomTypes;
use \LayerTypes;
use \RequestUtil;
use \Paging;
use \Projector_MapScript;
use \System;
use \SimpleSession;
use \WAPI;
use utils\ParamUtil;
use utils\Pixospatial;
use views\LayerUserViews;
use utils\ResponseUtil;

error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 *
 * @package WebAPI
 */
/**
 *
 * @ignore
 *
 */
function _exec($termplate, $args)
{
    $sys = System::Get();
    $wapi = System::GetWapi();
    $user = SimpleSession::Get()->GetUser();
    $db = System::GetDB(System::DB_ACCOUNT_SU);
    list($view,$userId) = ParamUtil::Requires($args,'view','owner');
    
    // System::GetDB()->debug=true;
    $layerRecords = LayerUserViews::GetView($view, $user->id, $args);
    $agg_atts = array();
    $maxX = - 180;
    $minX = 180;
    $maxY = - 90;
    $minY = 90;
    $ext = array();
    foreach ($layerRecords as $record) {
        $layer = Layer::GetLayer($record['id']);
        if (! LayerTypes::IsTabularSource($layer->type))
            continue;
        
        $ext = $layer->getExtent();
        if (is_null($ext)) {
            $minX = min($minX, $ext[0]);
            $minY = min($minY, $ext[1]);
            $maxX = max($maxX, $ext[2]);
            $maxY = max($maxY, $ext[3]);
            $ext[0] = $minX;
            $ext[1] = $minY;
            $ext[2] = $maxX;
            $ext[3] = $maxY;
        }
        try {
            $atts = $layer->getAttributesVerbose();
        } catch (\Exception $e) {}
        
        foreach ($atts as $att => $attVal) {
            if($att == 'objectid') $att = 'gid';
            if ($attVal['visible']) {
                if (! in_array($att, array_keys($agg_atts))) {
                    $agg_atts[$att] = array();
                    
                }
                $agg_atts[$att][] = $layer->id;
            }
        }
    }
   ksort($agg_atts);
    $response = new ResponseUtil(ParamUtil::Get($args,'format','json'), 'all_layers/aggregate');
    $response->StartResponse();
    $response->StartResults();
    $searchTip = "Search accross all available layers.";
    $response->WriteResult(array('bbox'=>implode(',',$ext),'field_names'=>$agg_atts,'searchtip'=>$searchTip));
    $response->EndResults();    
    $response->EndResponse('ok');  
    
    
    
    // array_unique($agg_atts);
    
}

?>