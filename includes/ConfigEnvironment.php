<?php

/**
  ConfigEnvironment allows for setup of general defines for pointing to where things are setup on a given environment.
  This should provide
 */
$addr = (isset($_SERVER ['SERVER_ADDR']) ? $_SERVER ['SERVER_ADDR'] : '198.89.106.158');
$server = isset($_SERVER ['SERVER_NAME']) ? $_SERVER ['SERVER_NAME'] : (isset($_SERVER ['SERVER_ADDR']) ? $_SERVER ['SERVER_ADDR'] : '198.89.106.158');


if (in_array($server, array('198.89.106.156', 'secure.simplelayers.com')))
    $server = 'live';
switch ($server) {

    case 'release.simplelayers.com' :
        ini_set('display_errors', false);
        error_reporting(E_ALL);
        define('SIMPLE_INI', '/etc/simplelayers/simple.ini');
        break;
    case 'staging.simplelayers.com':
        ini_set('display_errors', true);
        define('SIMPLE_INI', '/etc/simplelayers/simple.ini');
        define('SVCS', 'https://app-svc-staging.simplelayers.com/');
        define('APPS', 'https://apps-staging.simplelayers.com');
        break;
    case 'dmi.simplelayers.com' :
        ini_set('display_errors', false);
        define('SIMPLE_INI', '/etc/simplelayers/simple.ini');
        define('SVCS', 'https://services.simplelayers.com/');
        define('APPS', 'https://apps.simplelayers.com');
    case 'dev.simplelayers.com' :
        define('IS_DEV_SANDBOX', true);
        ini_set('display_errors', true);
        error_reporting(E_ALL);
        define('SIMPLE_INI', '/etc/simplelayers/simple.ini');
        define('SVCS', 'https://app-svc-dev.simplelayers.com/');
        define('APPS', 'https://apps-dev.simplelayers.com');
        break;
    case 'live':
    case 'secure.simplelayers.com':
        define('SVCS', 'https://services.simplelayers.com/');
        define('APPS', 'https://apps.simplelayers.com');
        ini_set('display_errors', false);        
        error_reporting(E_ALL & -E_DEPRECATED & -E_STRICT);
        define('SIMPLE_INI', '/etc/simplelayers/simple.ini');
        break;
    default :
        define('SVCS', 'https://services.simplelayers.com/');
        define('APPS', 'https://apps.simplelayers.com');
        ini_set('display_errors', false);
        define('SIMPLE_INI', '/etc/simplelayers/simplelayers.ini');
        break;
}
?>
