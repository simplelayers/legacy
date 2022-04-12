<?php
use utils\ParamUtil;
use utils\AssetUtil;

/**
 * Viewer: Given a project ID, generate a legend for it.
 * 
 * @package ViewerDispatchers
 *          Parameters:
 *         
 *          project -- The unique ID# of the project.
 *         
 *          layers -- A comma-joined list of layer-IDs, e.g. 12,34,567,89
 *         
 *          bgcolor -- The color to use for the legend's baackground, in HTML format (e.g. #000000).
 *          Optional, defaults to #FFFFFF (white)
 *         
 *          fgcolor -- The color to use for drawing the text of the legend, in HTML format (e.g. #000000).
 *          Optional, defaults to #000000 (black)
 *         
 *          width -- The width of the legend image, in pixels. Optional; if omitted, it will be automagically generated with no promises about how good it'll look.
 *         
 *          height -- The minimum height of the legend image, in pixels. Optional; if omitted, it will be automagically generated with no promises about how good it'll look.
 *         
 *          Return:
 *         
 *          If the user does not have at least AccessLevels::READ access to the project, then the string "&status=NO&" is returned.
 *          Otherwise, a JPEG image will be output, containing the legend image.
 *         
 */

// function _dispatch_generatelegend($request, $world, $user, $template, $project, $embedded, $permission) {
function _exec()
{
    $args = WAPI::GetParams();
    
    $user = SimpleSession::Get()->GetUser();
    /* @var $world World */
    $system = System::Get();
    
    $action = ParamUtil::Get($args,'action', WAPI::ACTION_LIST);
    
    /* @var $wapi WAPI */
    $wapi = System::GetWapi();
    $ini = System::GetIni();
    
    switch ($action) {
        case WAPI::ACTION_LIST:
            
            
            list ($project, $permission) = array_values($wapi->RequireProject(ParamUtil::Get($args, 'project')));
            
            $layer = $wapi->RequireALayer();
            
            $layerId = $layer->id;
            
            $mapper = Mapper::Get();
            $mapper->legendMode=true;
            $defaultProj4 = $system->projections->defaultProj4;
            
            $projector = new Projector_MapScript();
            $mapper->init(true, $projector->mapObj);
            $mapper->extent = array(
                - 180,
                - 90,
                180,
                90
            );
            $mapper->addLayer($layer, 1.0, 0);
            $mapper->quantize = true;
            $mapper->map = $mapper->_generate_mapfile(true);
            #readfile($mapper->mapfile);
            #die();
            $width = ParamUtil::Get($args, 'width', 60);
            $height = ParamUtil::Get($args, 'height', 21);
            
            $info = array();
            $entries = $layer->colorscheme->getUniqueCriteria();
            $ctr = 0;
            foreach ($mapper->map->getlayersdrawingorder() as $i) {
                
                $mapperLayer = $mapper->map->getLayer($i);
                
                for ($i = 0; $i < $mapperLayer->numclasses; $i ++) {
                    
                    $icon = sprintf("%s.png", md5(microtime() . mt_rand()));
                    $iconPath = sprintf("%s/%s", $ini->tempdir, $icon);
                    
                    $class = $mapperLayer->GetClass($i);
                    
                    $iconImg = $class->createLegendIcon($width, $height);
                    
                    $iconImg->saveImage($iconPath);
                    
                    // $icon = str_replace("/maps/","./",$icon);
                    $iconURL = BASEURL."/wapi/layers/legend/action:get/asset:$icon/format:png/token:" . SimpleSession::Get()->GetID();
                    
                    $entry = $entries[$i];
                  
                    list ($c1, $c2, $c3) =  ParamUtil::ListValues($entry,'c1','c2','c3');
                    
                    /*array(
                        $entries[$i]->criteria1,
                        $entries[$i]->criteria2,
                        $entries[$i]->criteria3
                    );*/
                    
                    $infoEntry = array(
                        'icon' => $iconURL,
                        "criteria1" => $c1,
                        "criteria2" => $c2,
                        "criteria3" => $c3,
                        'class_name' => $class->name
                    );
                    $info[] = $infoEntry;
                    $ctr ++;
                }
            }
            
            $format = WAPI::GetFormat();
            
            $legend = array(
                "layer" => $layerId,
                "name" => $layer->name,
                "type" => $layer->colorschemetype,
                "layer_type" => $layer->type
            );
            $legend['classes'] = $info;
            WAPI::SendSimpleResponse(array(
                'legend' => $legend
            ), $format);
            break;
        case WAPI::ACTION_GET:
            
            AssetUtil::GetTempAsset($args);
            break;
    }
   
    
}
?>