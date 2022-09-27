<?php
namespace apps;

use utils\HTMLUtil;
use utils\ParamUtil;
use auth\Context;
use auth\AppContext;
use auth\Auth;
use utils\PageUtil;

class Application
{

    public static function Exec($pageArgs)
    {
     
        
        /* @var $context AppContext */
        $context = Context::Get();
        $app = $context->app;
        if($app=='start') $app='flexapp';
        $args = \WAPI::GetParams();

        $ini = \System::GetIni();
        $iniFile = WEBROOT.'contexts/apps/'.$app.'/'.'app.ini';
        
        $styles = array();
        $requiredJS = array();
        $config = array();
        if(file_exists($iniFile)) {
            $appIni = new \SimpleIni(true,$iniFile);
            
            $requiredJS = $appIni->scripts;
            $requiredJS = is_null($requiredJS) ? array() : $requiredJS;
            $styles = $appIni->styles;
            $styles = is_null($styles) ? array() : $styles;

            
            $dataSources = array(&$requiredJS,&$styles);
            foreach( $dataSources as &$dataSource) {
                
                foreach($dataSource as $key=>$val) {
                    $dataSource[$key] = BASEURL.$val;
                }
            }
           $config = $appIni->GetSection('config');           
           $config = is_null($config) ? array() : $config;
            
        }
        
        if(isset($config['requires_login'])) {
            if($config['requires_login']==1) {
                if($context->authState==Auth::STATE_ANON) {
                    
                   // \SimpleSession::MakeCookie("return_to", \RequestUtil::Get(\RequestUtil::REDIRECT_PATH_PARAM));
                   
                  $goto=ParamUtil::Get($_SERVER,\RequestUtil::REDIRECT_PATH_PARAM);
                   
                   PageUtil::RedirectTo('account/login/',array('go_to'=>$goto));
                   return;
                }
                
            }
        }
        $useBootstrap = false;
        if(isset($config['requires_bootstrap'])) {
            if($config['requires_bootstrap'] == 1) {
                $useBootstrap = true;
            }
        }
        
        
        $project = ParamUtil::GetOne($args, 'project', 'map');

        if($project) {
            $sys = \System::Get();
            $isEmbedded = ParamUtil::GetBoolean($args,'embedded');
            $sys->logProjectUsage(($isEmbedded ? 'embedded' : \SimpleSession::Get()->GetUserInfo()), $project, $_SERVER['REMOTE_ADDR']);
        }
        
        $dojo = BASEURL . 'lib/js/'.$ini->dojo_version;
        $themeURL = 'lib/js/'.$ini->dojo_version.'/dijit/themes/tundra';
        $themeCSS = $themeURL.'/tundra.css';
        $clientUI = 'client_ui/components/';
        $gridXResources=BASEURL.'lib/js/gridx.1.3.3/resources/';
        $baseURL = BASEURL;

        $slAppURL = BASEURL . 'contexts/apps/';
        
        
        //$styles[] = BASEURL . "/styles/style.css";
        $styles[] = BASEURL.$themeURL.'/document.css';
        $styles[] = $gridXResources.'/tundra/Grid.css';
        $styles[] = BASEURL . "/styles/buttons.css";
        $styles[] = BASEURL . "/styles/weblay.css";
        $styles[] = BASEURL . 'lib/js/'.$ini->dojo_version.'/dijit/themes/tundra/tundra.css';
        $styles[] = $slAppURL.$app . '/app.css';
        $styles[] = BASEURL . "lib/js/leaflet-0.7.3/leaflet.css";
        if($useBootstrap) {
            // $styles[] = BASEURL."lib/bootstrap-3.3.5-dist/css/bootstrap-theme.min.css";
            // $styles[] = BASEURL."lib/bootstrap-3.3.5-dist/css/bootstrap.min.css";
            $styles[] = BASEURL."lib/bootstrap5/css/bootstrap.min.css";
        }
        
        
        $componentsURL = BASEURL . '/client_ui/components';
        $modulesURL = BASEURL . '/client_ui/modules/';

        $scripts = array();
        if($useBootstrap) {
            $scripts[] = BASEURL.'lib/js/jquery.js';
            // $scripts[] = BASEURL.'lib/bootstrap-3.3.5-dist/js/bootstrap.js';
            $styles[] = BASEURL."lib/bootstrap5/js/bootstrap.bundle.min.js";
        }
        
        
        HTMLUtil::StartDoc();
        HTMLUtil::StartHead($scripts);
        HTMLUtil::WriteHead('Simple Layers', $styles,$scripts);

        include (SL_INCLUDE_PATH . '/dojo_config.php');
        HTMLUtil::EndHead();
        
        $requiredJS = array_merge($requiredJS, array(
            "dom" => "dojo/dom",
            "parser" => "dojo/parser",
            "container" => "dijit/layout/StackContainer",
            "sl_app"=>'sl_app/'.$app.'/app',   
            'pages'=>'sl_modules/Pages'
            )   
        );
        
        HTMLUtil::WriteBody($dojo, $requiredJS, $pageArgs,$useBootstrap);
        HTMLUtil::EndDoc();
        
    }
}
?>