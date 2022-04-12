<?php
/**
 * The password for the World to login to its database.
 * @ignore
 */
#define('DATABASE_PASSWORD','UAE8fish-bellied');
/**
 * The password for the World to login to its database.
 * @ignore
 */
#define('DATABASE_SUPERPASSWORD','5l1pp3ry!');

/**
 * This is THE include that includes everything else.
 *
 * An include of library.php pulls in all the other modules and dependencies that are necessary. This includes:
 * - numerous defines
 * - setting the PATH
 * - functions.php, for additional handy functions
 * - ADOdb (database API), Smarty (template engine), the USAePay API (credit card processing)
 * - the World class, which is the root class for the entire system's model (and which pulls in its own dependencies)
 *
 * @package    Overview
 */

///// This component loads all the required modules, including the class definitions,
///// and also defines a bunch of constants for use within the system

// the PATH that will be searched for executables, e.g. gpx2shp and gpsbabel
#putenv('PATH=/usr/bin:/bin:/usr/local/bin');

/**
 * We use ADOdb as the database API.
 * @link http://phplens.com/lens/adodb/docs-adodb.htm
 */
#require_once 'adodb/adodb.inc.php';

/**
 * The CAPTCHA library.
 * @ignore
 */
#require_once 'securimage.php';
/**
 * Load up the library of convenience functions.
 * @see functions.php
 */
 
#require_once 'lib/functions.php';
/**
 * Load more defines which are simply too lengthy to keep this file readable.
 * @see projections.php
 * @see colorschemes.php
 */
#require_once 'lib/projections.php';
#require_once 'lib/colorschemes.php';

/**
 * We use Smarty as the template engine, separating the HTML display from the working logic.
 * This subclass just defines some configuration, but is otherwise a perfectly normal Smarty.
 * The only significant gotcha is that the delimiters are set to <!--{ and }--> instead
 * of the default { and } This is so the Smarty tags can hide inside HTML comments,
 * and so the delimiters don't mangle JavaScript.
 * @link http://smarty.php.net/manual/en/
 */
#require_once 'lib/smarty_wrapper.class.php';
/**
 * Load the World class, which will load the other classes for our microcosm's model.
 * @see World.class.php
 */
#require_once 'classes/World.class.php';



/**
 * @ignore
 * USAePay API; ignore this and use the run_creditcard() function instead.
 */
#require_once 'lib/usaepay.php';

/**
 * @ignore
 * a class for creating embedded Flash movies, without having to fsck around with <embed> tags
 */
#require_once 'lib/Embedder_Fl.php';
/**
 * @ignore
 * JPGraph is a class for creating charts and grapphs
 */

// The static configuration parameters for a World
#if (isset($_SERVER['REDIRECT_SIMPLE_LAYERS'])) $_SERVER['SIMPLE_LAYERS'] = $_SERVER['REDIRECT_SIMPLE_LAYERS'];
#if (!defined('VIEWER_MODE')) define('VIEWER_MODE',false); 
#define('WORLD_NAME',$_SERVER['SIMPLE_LAYERS']);
#if( stripos('~',$_SERVER["REQUEST_URI"]) >=0 ){
	#define('SANDBOX_MODE',true);
#}

if (!defined('SANDBOX_MODE')) define('SANDBOX_MODE',false);
define('BASEDIR', dirname(dirname(__FILE__)));// VIEWER_MODE ? dirname(dirname($_SERVER['SCRIPT_FILENAME'])) : dirname($_SERVER['SCRIPT_FILENAME']) );


/**
 * The db superuser
 * @ignore
 */
#define('DATABASE_SUPERUSER','pgsql');

/**
 * The db superuser
 * @ignore
 */
#define('DATABASE_USER',WORLD_NAME);



/*$WORLDCONFIG = array(
           // system identification: URLs and data paths
           'name'                    => WORLD_NAME,
           'url'                     => "https://{$_SERVER['HTTP_HOST']}",
           'rasterdir'               => "/maps/worlds/".WORLD_NAME."/userrasters/",
           'odbcdir'                 => "/maps/worlds/".WORLD_NAME."/odbc/",
           'custom_templates'        => sprintf('/maps/worlds/%s/%s/',WORLD_NAME, SANDBOX_MODE?'templates-dev':'templates'),
           'custom_dispatchers'      => sprintf('/maps/worlds/%s/%s/',WORLD_NAME, SANDBOX_MODE?'dispatchers-dev':'dispatchers'),
           'php_include_dir'         => BASEDIR.'/lib',
           'php_include_url'         => "https://{$_SERVER['HTTP_HOST']}".dirname($_SERVER['PHP_SELF']).'/lib',
           // Mapserver paths
           'thumbdir'                => '/maps/thumbnails/',
           'mapfiledir'              => '/maps/mapfiles/',
           'tempdir'                 => '/maps/images.tmp/',
           'tempurl'                 => '/images.tmp/',
           'fontdir'                 => BASEDIR.'/media/fonts',
           'symbolfile'              => BASEDIR.'/media/symbols.sym',
           // how large should thumbnails (layer previews) be?
           'thumbnail_width'         => 360,
           'thumbnail_height'        => 288,
       );
*/


#define('VIEWEREXTERNALS_DEFAULT', '' );





?>
