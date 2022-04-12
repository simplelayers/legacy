<?php
use utils\PageUtil;

/**
 * The form for importing CSV data.
 * Actually, it's not CSV at all, it's tab-delimited; but the acrnym makes a convenient label.
 * 
 * @package Dispatchers
 */
/**
 */
function _config_csv1()
{
    $config = Array();
    // Start config
    $config["sendWorld"] = false;
    // $config["sendUser"] = false;
    // Stop config
    return $config;
}

function _dispatch_csv1($template, $args, $org, $pageArgs)
{
    if(!$pageArgs['canCreate']) {
        print javascriptalert('You are at or above your layer limit.');
        redirect('?do=layer.list');
    }
    $pageArgs['pageSubnav'] = 'data';
    $pageArgs['pageTitle'] = 'Data - Import CSV';
    PageUtil::SetPageArgs($pageArgs, $template);
    $pageArgs = PageUtil::MixinPlanArgs($template);
    
    global $PROJECTIONS;
    $template->assign('projectionlist',$PROJECTIONS);
    $template->display('import/csv1.tpl');
}
?>
