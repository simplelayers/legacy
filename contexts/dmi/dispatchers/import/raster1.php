<?php
use utils\PageUtil;

/**
 * The form to import a raster image as a new layer.
 * 
 * @package Dispatchers
 */
/**
 */
function _config_raster1()
{
    $config = Array();
    // Start config
    $config["sendWorld"] = false;
    // Stop config
    return $config;
}

function _dispatch_raster1($template, $args)
{
    $user = $args['user'];
    /*
     * if ($args['user']->community && count($args['user']->listLayers()) >= 3) {
     * print javascriptalert('You cannot create more than 3 layers with a community account.');
     * return print redirect('layer.list');
     * }
     */
    $pageArgs = PageUtil::MixinPlanArgs($template);
    if (! $pageArgs['canCreate']) {
        print javascriptalert('You are at or above your layer limit.');
        redirect('?do=layer.list');
    }
    // are they allowed to be doing this at all?
    /*
     * (if ($user->accounttype < AccountTypes::GOLD) {
     * print javascriptalert('You must upgrade your account in order to import this format.');
     * return print redirect("layer.list");
     * }
     */
    
    global $PROJECTIONS;
    
    $template->assign('projectionlist', $GLOBALS['PROJECTIONS']);
    $template->assign('maxfilesize', (int) ini_get('upload_max_filesize'));
    $template->display('import/raster1.tpl');
}
?>
