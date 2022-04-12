<?php
use utils\HTMLUtil;
use utils\ParamUtil;
use auth\Context;
use auth\AppContext;

class SLApplication
{

    public static function Exec()
    {
        
        /* @var $context AppContext */ 
        $context = Context::Get();
        
        
        
        $args = WAPI::GetParams();
        if (! defined('APPDIR'))
            throw new Exception('This page may not be called directly.');
        
        $app = 'sl_basic';
        
        require_once (dirname(__FILE__) . "/../../../includes/main.inc.php");
        
        $project = ParamUtil::GetOne($args, 'project', 'map');
        
        $dojo = BASEURL . 'lib/js/dojo.1.9.1/';
        $themeCSS = 'lib/js/dojo.1.9.1/dijit/themes/tundra/tundra.css';
        $clientUI = 'client_ui/components/';
        $baseURL = BASEURL;
        
        $styles = array();
        $styles[] = BASEURL . "/styles/style.css";
        $styles[] = BASEURL . "/styles/buttons.css";
        $styles[] = BASEURL . "/styles/weblay.css";
        $styles[] = BASEURL . "/styles/login.css";
        $styles[] = BASEURL . 'contexts/apps/' . $app . '/app.css';
        $componentsURL = BASEURL . '/client_ui/components';
        $modulesURL = BASEURL . '/client_ui/modules/';
        
        $slAppURL = BASEURL . 'contexts/apps/' . $app . '/';
        $ini = System::GetIni();
        
        HTMLUtil::StartDoc();
        
        $title = "SimpleLayers - Simple Map";
        HTMLUtil::StartHead();
        HTMLUtil::WriteHead($title, $styles);
        
        include (SL_INCLUDE_PATH . '/dojo_config.php');
        HTMLUtil::EndHead();
        $requiredJS = array(
            "dom" => "dojo/dom",
            "parser" => "dojo/parser",
            "container" => "dijit/layout/StackContainer",
            "sl_app" => "sl_app/app",
            "slapp_map_flat" => "sl_components/map_flat/widget",
            "pages"=>"sl_modules/Pages"
        );
        
        HTMLUtil::WriteBody($dojo, $requiredJS, $args);
        HTMLUtil::EndDoc();
    }
}
?>