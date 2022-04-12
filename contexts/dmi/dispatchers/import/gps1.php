<?php

use utils\PageUtil;
/**
 * The form for importing GPS data.
 * @package Dispatchers
 */
/**
 */
function _config_gps1()
{
    $config = Array();
    // Start config
    $config["sendWorld"] = false;
    $config["sendUser"] = false;
    // Stop config
    return $config;
}

function _dispatch_gps1($template, $args)
{
    $pageArgs = PageUtil::MixinPlanArgs($template);
    if (! $pageArgs['canCreate']) {
        print javascriptalert('You are at or above your layer limit.');
        redirect('?do=layer.list');
    }
    $template->display('import/gps1.tpl');
}
?>
