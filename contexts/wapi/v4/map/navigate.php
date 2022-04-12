<?php

use utils\ParamUtil;
use model\mapping\PixoSpatial;
use model\mapping\PixoROI;

/*
 * @ignore
 */
function _exec() {

    $args= WAPI::GetParams();
    list($width,$height,$bbox,$action) = ParamUtil::Requires($args,'width','height','bbox','action');
    
    $pixo = new PixoSpatial($bbox, $width, $height);
   
    
    $level = $pixo->GetLevel();
    
    switch($action) {
        case WapiMapNavigate::ACTION_CENTER:
           list($lon,$lat) = ParamUtil::Requires($args,'lon','lat');
           $pixo->CenterROI($lon,$lat);
           break;
        case WapiMapNavigate::ACTION_PAN:
        case WapiMapNavigate::ACTION_CENTER_VIEW:
            list($ptX,$ptY) = ParamUtil::Requires($args,'center_x','center_y');
            $pixo->MoveToViewPoint($ptX, $ptY);
            break;
        case WapiMapNavigate::ACTION_PAN_ZOOM;
            list($ptX,$ptY) = ParamUtil::Requires($args,'center_x','center_y');
            $pixo->MoveToViewPoint($ptX,$ptY);
            $pixo->ZoomNext();
            break;
        case WapiMapNavigate::ACTION_ZOOM_BY:
            list($amount)=ParamUtil::Requires($args,'amount');
            if($amount > 0) {
                $mode='next';
            } elseif($amount < 0) {
                $mode='prev';
            } else {
                $mode='fit';
            }
            $level = $pixo->DetectLevel();
            
            switch($mode) {
                case 'next':
                    for($i=0; $i < min($amount,PixoROI::max_levels); $i++ ) {
                        $pixo->ZoomNext();
                    }
                    //if($bbox==$pixo->GetBBox()) $pixo->ZoomNext();
                     
                    break;
                case 'prev':
                    
                    for($i=0; $i < min(abs($amount),PixoROI::max_levels); $i++) {
                        $pixo->ZoomPrev();                        
                    }
                    
                     if($bbox==$pixo->GetBBox()) $pixo->ZoomPrev();
                     $level = $pixo->DetectLevel();
                      
                      
                    break;
                case 'fit':
                  // done by defaolt.
                    break;
            }
            
            break;
        case WapiMapNavigate::ACTION_ZOOM_TO:
            list($x1,$y1,$x2,$y2) = ParamUtil::Requires($args,'x1','y1','x2','y2');
            $pixo->MoveToViewRect($x1,$y1,$x2,$y2);
            $level = $pixo->GetLevel();
            
            break;
        case WapiMapNavigate::ACTION_ZOOM_FEATURE:
            
            $wapi = System::GetWapi();
            $layerId = ParamUtil::Get($args,'layer_id');
            $layer = $wapi->RequireLayer(null,$layerId);
            $features = ParamUtil::Get($args,'features');
            $bounds = array();
            if(!is_null($features)) {
                $bounds = $layer->GetBounds($features);
                $single = ($layer->geomtypestring=='point' && (count(explode(',',$features))==1));
            } else {
                if($layer->type == LayerTypes::COLLECTION) {
                    $mapId = ParamUtil::GetOne($args,'projectId','mapId');
                    if(LayerCollection::GetSubCount($layer->id)==0) { 
                        if($mapId) {
                            $map = Project::Get($mapId);
                            $bounds = explode(',',$map->bbox);
                        } else {
                            $bounds = $layer->getExtent();
                        }
                    } else {
                        $bounds = $layer->getExtent();
                    }
                    
                } else {
                
                    $bounds = $layer->getExtent();
                }
                
                $single = false;
            }
           
            $newBBOX = implode(',',array_values($bounds));
            
            $pxBuffer= ($layer->geomtypestring=='point') ? SymbolSize::XXLARGE : SymbolSize::XSMALL;
            
            
            $pixo->MoveToROI($newBBOX,$pxBuffer);
            
            if($single) $pixo->FitToLevel(17);
            
            
            break;
        case WapiMapNavigate::ACTION_ZOOM_ROI:
            // roi set, and fit to view, done as part of initializing PixoSpatial
          break;
        case WapiMapNavigate::ACTION_RESIZE:
            list($newWidth,$newHeight) = ParamUtil::Requires($args,'new_width','new_height');
            $pixo->Resize($newWidth,$newHeight);                 
            
            
            break;
        
    }
    
    $level = $pixo->GetLevel();
    $bbox = $pixo->GetBBox();
    
    $viewInfo = $pixo->GetViewInfo();
    $response = array('view'=>$viewInfo,'bbox'=>$bbox,'level'=>intval($level));
    WAPI::SendSimpleResponse($response);
    
    
}

/**
 *
 * @api
 *
 * @package APIv4
 *
 */
/**
 * Endpoint: wapi/map/navigate/
 *
 * Standard Required Parameters:
 * width: pixel width of view
 * height: pixel height of view
 * bbox: Extents in the form of 'minLon,minLat,maxLon,maxLat'
 * action: one of center | pan | center_view | pan_zoom | zoom_by | zoom_to | zoom_feature | resize
 *
 * Action Parameters
 *
 * <b>Center on a lon,lat coordinate</b>
 * action: center
 * lon: // longitude of coordinate
 * lat: // latitude of coordinate
 *
 * <b>Center map on a pixel coordinate
 * action: pan | center_view
 * center_x: // x-coordinate in pixels
 * center_y: // y-coordinate in pixels
 *
 * <b>Pan (center px view) and zoom to next level</b>
 * action: pan_zoom
 * center_x: // x-coordinate in pixels
 * center_y: // y-coordinate in pixels
 *
 * <b>Zoom by specified amount</b>
 * action: zoom_by
 * amount: // levels to zoom in or out by
 * <p>
 * amount > 0 increases level (zooms in),
 * amount < 0 decreases level (zooms out),
 * amount = 0 adjusts extents to view at current level.
 * </p>
 *
 * <b>Zoom to pixel area</b>
 * action: zoom_to
 * x1: x value for first point in pixels
 * y1: y value for first point in pixels
 * x2: x value for second point in pixels
 * y2: y value for second point in pixels
 *
 * <b>Zoom to feature(s) in a layer</b>
 * action: zoom_feature
 * layer_id: id for layer holding features
 * {features}: comma seperated list of ids
 * <p>If features is not present or empty,
 * all features in the layer are used;
 * thus may be used for zoom to layer</p>
 *
 * <b>Resize view at current zoom level</b>
 * action: resize
 * new_width: // new view width in pixels
 * new_height: // new view height in pixels
 * <p>The upper left corner of the the extents should
 * Remain stationary and the view should extend to the
 * right and left when made larger than the current
 * view dimensions or should crop the right or bottom when
 * made smaller than the current dimensions.
 * </p>
 *
 *
 * @author Arthur Clifford
 * @copyright Simple Layers Inc.
 *
 */
class WapiMapNavigate {
    // Dummy Class for generating documentation.
    const ACTION_CENTER='center';
    const ACTION_PAN='pan';
    const ACTION_CENTER_VIEW='center_view';
    const ACTION_PAN_ZOOM='pan_zoom';
    const ACTION_ZOOM_BY = 'zoom_by';
    const ACTION_ZOOM_TO = 'zoom_to';
    const ACTION_ZOOM_FEATURE = 'zoom_feature';
    const ACTION_ZOOM_ROI = 'zoom_roi';
    const ACTION_RESIZE = 'resize';
}


