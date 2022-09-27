<?php
/**
 * Main include file
 * This script is used to setup the general enviornment
 * 
 * While some configuration is taken care of in System::Setup()
 * 
 * This script should be considered a first requirement. It is used mostly
 * by WEBROOT/index.php
 * 
 * @see classes/System::Setup for additional configuration.
 * 
 */
// Setup paths.

ini_set("auto_detect_line_endings", true);

define ( 'SL_INCLUDE_PATH', dirname ( __FILE__ ) . '/' );
define ( 'WEBROOT', dirname ( dirname ( __FILE__ ) ) . '/' );
define ( 'BASEDIR', WEBROOT );
require_once(BASEDIR.'vendor/autoload.php');
// Setup an simple_autoload as the autoloader
//TODO: Consider refactoring - use ZendFramework 2's autoloader regration tech.
spl_autoload_register ( __NAMESPACE__ . '\simple_autoload' );
function simple_autoload($class) {
    global $incPath;
    $ini = System::GetIni ();
    
    if (is_null ( $incPath )) {
        $ini = System::GetIni();
        $includePath = array();
        $includePath [] = WEBROOT . 'classes/';
        $includePath [] = WEBROOT;
        foreach ( $ini->include_paths as $path ) {
            $includePath [] = $path;
        }
        $includePath [] = $ini->zend_path;
        $includePath [] = $ini->zend_path . '/Zend';
        
        $incPath = implode ( PATH_SEPARATOR, $includePath );
      
        set_include_path( $incPath );
    }
    $includePath = explode ( ':', get_include_path () );

    // namespaced content in a folders matching the folders mean that \ in the class name will be replaced with /
    $class = str_replace ( "\\", "/", $class );
    //$class = str_replace ("_","/",$class);
    if (dirname ( $class ) == 'errors') {
        require_once (WEBROOT . 'classes/Errors.php');
        return;
    }

    foreach ( $includePath as $path ) {
        
        if (file_exists ( "$path/$class.php" )) {
            include_once ("$path/$class.php");
            return;
        }

        if (file_exists ( "$path/$class.class.php" )) {
            include_once ("$path/$class.class.php");
            return;
        }
    }
}

// Require
// Require core resources used for getting starteds 
require_once (WEBROOT . 'classes/System.php');
require_once (WEBROOT . 'classes/SimpleSession.php');
require_once (dirname ( __FILE__ ) . '/ConfigEnvironment.php');
require_once (dirname ( __FILE__ ) . '/functions.inc.php');

System::SetupSystem();

if(!function_exists('ms_newStyleObj')) include('mapserver_stubs.php');


?>
