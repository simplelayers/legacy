<?php
use model\Permissions;

/**
 * This simply redirects the browser to the proper layeredit*1 action; this makes for a consistent interface.
 * 
 * @package Dispatchers
 */
/**
 */
function _config_edit1()
{
    $config = Array();
    // Start config
    $config["sendUser"] = false;
    $config["customHeaders"] = true;
    // Stop config
    return $config;
}

// Using headers should prevent the white flash when clicking edit. Also prevents a blank page from loading making the time shorter.
function _headers_edit1()
{
    $world = System::Get();
    
    // this action is just a shell, and it redirects them to the proper dispatcher
    // depending on the layer type
    
    $layer = $world->getLayerById($_REQUEST['id']);
    if (! $layer) {
        return false;
    }
  
    switch ($layer->type) {
        case LayerTypes::VECTOR:
            header("Location: ./?do=layer.editvector1&id={$_REQUEST['id']}");
            break;
        case LayerTypes::WMS:
            header("Location: ./?do=layer.editwms1&id={$_REQUEST['id']}");
            break;
        case LayerTypes::RASTER:
            header("Location: ./?do=layer.editraster1&id={$_REQUEST['id']}");
            break;
        case LayerTypes::ODBC:
            header("Location: ./?do=layer.editodbc1&id={$_REQUEST['id']}");
            break;
        case LayerTypes::RELATIONAL:
            header("Location: ./?do=layer.editrelational1&id={$_REQUEST['id']}");
            break;
        case LayerTypes::COLLECTION:
            header("Location: ./?do=layer.collection.edit1&id={$_REQUEST['id']}");
            break;
    }
}

// Keep this as a back up.
function _dispatch_edit1($template, $args, $org, $pageArgs)
{
    $user = SimpleSession::Get()->GetUser();
    $world = System::Get();
    
    if (! Permissions::HasPerm($pageArgs['permissions'], ':Layers:General:',Permissions::VIEW)) {
        var_dump($pageArgs['permissions']);
        die('here');
        javascriptalert('You are not allowed to view layer details.');
        redirect('layer.list');
    }
    
    
    // this action is just a shell, and it redirects them to the proper dispatcher
    // depending on the layer type
    
    $layer = \Layer::GetLayer($_REQUEST['id']);
    
    
    if (! $layer) {
        print javascriptalert('The requested layer does not exist.');
        return print redirect('layer.list');
    }
    
    switch (+$layer->type) {
        case LayerTypes::VECTOR:
            print redirect("layer.editvector1&id={$_REQUEST['id']}");
            break;
        case LayerTypes::WMS:
            print redirect("layer.editwms1&id={$_REQUEST['id']}");
            break;
        case LayerTypes::RASTER:
            print redirect("layer.editraster1&id={$_REQUEST['id']}");
            break;
        case LayerTypes::ODBC:
            print redirect("layer.editodbc1&id={$_REQUEST['id']}");
            break;
        case LayerTypes::RELATIONAL:
            print redirect("layer.editrelational1&id={$_REQUEST['id']}");
            break;
        case LayerTypes::COLLECTION:
            print redirect("layer.collection.edit1&id={$_REQUEST['id']}");
            break;
        case LayerTypes::RELATABLE:
            print redirect("layer.tabular.edit&id={$_REQUEST['id']}");
            break;
            
    }
}
?>
