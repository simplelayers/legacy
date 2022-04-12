<?php
use utils\HTMLUtil;
use utils\ParamUtil;
use auth\Context;
use auth\AppContext;

class SLApplication
{

    public static function Exec()
    {

        die();
        
        /* @var $context AppContext */ 
        $context = Context::Get();
        
        
        $args = WAPI::GetParams();
        if (! defined('APPDIR'))
            throw new Exception('This page may not be called directly.');
        
        $app = 'sl_basic';
        
        require_once (dirname(__FILE__) . "/../../../includes/main.inc.php");
        
        $project = ParamUtil::GetOne($args, 'project', 'map');
        if(isset($project)) {
            $sys = System::Get();
            $isEmbedded = ParamUtil::GetBoolean($args,'embedded');

            $sys->logProjectUsage(($isEmbedded ? 'embedded' : SimpleSession::Get()->GetUserInfo()), $project, $_SERVER['REMOTE_ADDR']);
        }
        $dojo = BASEURL .  BASEURL .'/lib/js/dojo.1.9.1/';
        $themeBase = BASEURL . '/lib/js/dojo.1.9.1/dijit/themes/tundra/';
        $themeBaseGridX = BASEURL . '/lib/js/dojo.1.9.1/dijit/themes/tundra/';
        
        
        
        $themeCSS = $themeBase.'tundra.css';
        $clientUI = 'client_ui/components/';
        $baseURL = BASEURL;
        
        $styles = array();
        $styles[] = BASEURL . "lib/js/".DOJO."/themes/tundra.css";
        //$styles[] = BASEURL . "/lib/bootstrap-3.3.5-dist/css/bootstrap.css";
        $styles[] = BASEURL . "/styles/style.css";
        $styles[] = BASEURL . "/styles/buttons.css";
        $styles[] = BASEURL . "/styles/weblay.css";
        $styles[] = BASEURL . "/styles/login.css";
        $styles[] = BASEURL . $themeBase."document.css";        
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
            "pages"=>"sl_modules/Pages"           
        );
        
        HTMLUtil::WriteBody($dojo, $requiredJS, $args);
        HTMLUtil::EndDoc();
    }
}
?>