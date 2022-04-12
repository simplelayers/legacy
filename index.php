<?php

use utils\DOUtil;
use auth\Context;
use utils\ParamUtil;
use utils\AssetUtil;

require_once ('includes/main.inc.php');
//System::RequireZend();
//\Zend_Loader::loadClass('Zend_Debug');
//\Zend_Debug::dump('Starting','index',false);
RequestUtil::SetEnv();
$pathExtra = RequestUtil::Get(RequestUtil::PATH_PARAM, '');

$contextParam = RequestUtil::Get('context');

$action = DOUtil::Get('project.list');

if (isset($_REQUEST['token'])) {
    if ($_REQUEST['token'] == '')
        unset($_REQUEST['token']);
}
if ($contextParam == 'lib') {
    //return readfile(BASEDIR.'/lib/'.$action);	
};

if (ParamUtil::Get($_GET, 'do')) {
    if ($_GET['do'] != $action)
        $action = $_GET['do'];
}

if ($action == 'start') {
    $action = "start";
    $contextParam = 'app';
    foreach ($_GET as $key => $val) {
        $action .= '/' . $key . ':' . $val;
    }
    RequestUtil::Set('do', $action);
}
if (RequestUtil::HasParam('application') && !$contextParam == 'wapi' || $contextParam == 'app') {
    RequestUtil::Set('context', 'app');
}

if ($contextParam == 'get') {
    $asset = RequestUtil::Get('do');
    RequestUtil::Set('asset', $asset);
    RequestUtil::Set('do', 'get');
}



if ($action == 'get') {
    return AssetUtil::GetAsset($_REQUEST);
}

$context = Context::Get();

if (is_a($context, 'auth\WAPIContext')) {
    return $context->Exec($_REQUEST);
}


$params = explode('/', $action);

if (is_a($context, 'auth\AppContext')) {

    $params = explode('/', $action);
    $type = array_shift($params);

    if ($contextParam == 'embed') {
        $_GET ['embedded'] = '1';
        $contextParam = $type;
    }
    if ($type == 'viewer')
        $contextParam = 'viewer';

    if ($contextParam == 'viewer') {

        RequestUtil::Set('do', 'start');

        $_GET ['do'] = 'start';
        $_GET ['app'] = 'flexapp';
        if ($type == 'viewer') {
            $swf = array_shift($params);
            $params = ParamUtil::ParseParams($params);

            $projectId = $params['map'];
            foreach ($params as $param => $value) {
                $_GET [$param] = $value;
            }

            $_GET ['application'] = $swf;
            $_GET ['project'] = $projectId;
        }
    } else if ($contextParam == 'app') {

        if ($type != 'start') {
            $app = $contextParam;
            // info = array_shift($params);
            $_GET ['app'] = $type;
            $params = ParamUtil::ParseParams($params);

            //ParamUtil::RequiresOne($params,'map','project');
            //$map = ParamUtil::GetOne($params,'map','project');
            if (!is_null($params)) {
                foreach ($params as $param => $value) {
                    $_GET [$param] = $value;
                }
            }
        } else {
            $_GET['app'] = 'flexapp';
        }
    } else {
        $_GET['app'] = 'flexapp';
    }
    return $context->Exec($_GET);
}

if (is_a($context, 'auth\DMIContext')) {
    $isApp = RequestUtil::HasParam('application') || $contextParam == 'app';

    return $context->Exec($_REQUEST);
}

if (is_a($context, 'auth\MailActionContext')) {
    return $context->Exec($_REQUEST);
}




// if($do=='get')
?>
