<?php
/**
 * The data management interface (DMI) for CartoGraph.
 *
 * This program uses a series of "dispatchers" (which are actually just functions with a naming convention)
 * and the $_REQUEST['do'] variable to determine its action mode. It verifies any supplied username and password,
 * instantiates the model and Person object used by the dispatchers, prints appropriate HTML and HTTP headers, then
 * calls the dispatcher function to provide the actual page content.
 *
 * 99% of the work of customization will be within the dispatcher files and the template files. See the dispatchers.php
 * documentation for a description of how dispatchers and templates work and work together.
 *
 * The $_REQUEST['do'] variable will be forcibly changed by index.php under certain conditions, those
 * conditions being part of the coding and naming conventions for dispatchers. These conventions are
 * as follows:
 *
 * 1. If the user is not logged in, they may only use the 'login' action and actions whose name starts
 *    with 'demo' and 'viewer' If they try to use other actions, they will be redirected to the login page.
 * 2. If no action is specified, or there is no dispatcher defined for the requested action,
 *    they will be sent to the 'mainmenu' action.
 * 3. Any action whose name starts with 'admin' is accessible only to the administrative user.
 *    Anybody else trying to access these functions will be sent to the 'mainmenu' action.
 * 4. If the user is logged in and their account is past expiration, they may only access the following
 *    actions, or else be redirected to the 'addtime1' action: addtime1, addtime2, addtime3
 * 5. If the action's name ends in a the number 2, it is designed to process a form generated by
 *    a corresponding action whose name ends with 1. All such forms have their submit button named "submit"
 *    and if this parameter is not present in the request, they will be redirected to the corresponding
 *    form action.
 *
 * The DMI has a concept of "sandbox mode" Our calling convention is that the program is called via an Apache
 * VirtualHost using / as the URL. If the URL starts with /~ as is the case with a "sandbox" then the constant
 * SANDBOX_MODE is set to true. In this case, some functioning changes:
 *
 * 1. The World's custom templates and dispatchers are loaded from the World's 'templates-dev' and 'dispatchers-dev'
 *    directories, rather than the usual 'templates' and 'dispatchers' This allows development of a World's custom
 *    code in a sandbox setting, without affecting the World's custom functioning in the live setting.
 *
 *
 *
 * @package    Overview
 * @link       http://www.CartoGraph.com/
 * @license    Commercial
 * @copyright  2005-2006 CartoGraph. Distribution, reverse-engineering, and copying is prohibited!
 */


if( isset($_REQUEST['world']) ) $_SERVER['SIMPLE_LAYERS'] = $_REQUEST['world'];

/**
 * @ignore
 * Determine whether we're running in "sandbox mode" in a ~ directory.
 * Then load up all required libraries, functions, defines, etc.
 */
if(isset($_REQUEST['do'])) 
{
	if(stripos($_REQUEST['do'],'ria.') ===0 ) {
			$dir = dirname(__FILE__).'/v2.5/';
			chdir($dir);
			include($dir.'index.php');
			exit();
		}
}	
define('SANDBOX_MODE', substr($_SERVER['PHP_SELF'],0,2)=='/~' );
require_once 'lib/library.php';


/**
 * Instantiate the World we live in, our little microcosm
 * Then verify their password, setting $_SESSION to their username and $user to their Person object.
 * Note that if password verification fails, their login credentials are NOT changed; they are still
 * logged in (or not) as whoever they were before.
 */
$world = new World(WORLD_NAME);
if (!isset($_SESSION)) { $_SESSION = false; $user = false; }
if (isset($_REQUEST['username']) and isset($_REQUEST['password']) and $world->verifyPassword($_REQUEST['username'],$_REQUEST['password'])) {
   $_SESSION = $_REQUEST['username'];
   if (substr($_REQUEST['do'],0,4) != 'wapi' and substr($_REQUEST['do'],0,6) != 'viewer') $world->logUserLogin($_REQUEST['username'],$_SERVER['REMOTE_ADDR']);
}

$template = new SLSmarty();
$user = $world->getPersonByUsername($_SESSION);

/**
  * Go through all of the dispatcher files, and load them.
  * Then, override their requested do-action as necessary.
  */
//foreach (glob('dispatchers/*.php') as $i) require_once $i;
foreach (glob("{$world->config['custom_dispatchers']}/*.php") as $i) require_once $i;
// If the query string is just numbers, then someone's using the short form to call a project opening
//echo "wtf"; return;
if (preg_match('/^\d+/',@$_SERVER['QUERY_STRING'])) {
   header("Location: .?do=vieweropen&project={$_SERVER['QUERY_STRING']}");
   exit;
}
// No action specified, or an invalid action? Send them to the main menu.
if (!@$_REQUEST['do']) $_REQUEST['do'] = 'mainmenu';

// Not an admin and accessing an admin function? Send them to the main menu.
if ((!$user or !$user->admin) and substr($_REQUEST['do'],0,5)=='admin' ) $_REQUEST['do'] = 'mainmenu';
// Are they specifying an action whose name ends in 2? If so, ensure that they came from a form.
if (substr($_REQUEST['do'],-1)=='2' and !isset($_REQUEST['submit'])) {
   $_REQUEST['do'] = preg_replace('/2$/','1',$_REQUEST['do']);
}
// Account expired? They better be adding time.
if ($user and $user->daysUntilExpiration()<0 and !in_array($_REQUEST['do'],array('addtime1','addtime2','addtime3'))) {
   if (substr($_REQUEST['do'],0,4) == 'wapi') {
      $template->assign('message','The WAPI account is expired.');
      $template->display('wapierror.tpl');
      exit;
   }
   else {
      $_REQUEST['do'] = 'addtime1';
   }
}

// Not logged in? They better be using a "demo" function or the viewer, or one of the OGC WxS services.
// Or else send them to the login screen.
if (!$user) {
   if (substr($_REQUEST['do'],0,4) == 'demo') { true; }
   elseif (substr($_REQUEST['do'],0,6) == 'viewer') { true; }
   elseif (substr($_REQUEST['do'],0,3) == 'ogc') { true; }
   else $_REQUEST['do'] = 'login';
}


if( file_exists('dispatchers/'.$_REQUEST['do'].'.php')) require_once('dispatchers/'.$_REQUEST['do'].'.php');
if (!function_exists('_dispatch_'.$_REQUEST['do'])) $_REQUEST['do'] = 'mainmenu';



/**
 * This is the main execution, where the dispatcher for the $_REQUEST['do'] action is called.
 * If the $_REQUEST['do'] contains "download" or starts with "image" then HTTP headers
 * are sent for a file download or for an image output. Otherwise, HTTP headers are output for a HTML page
 * and a Templater is called up to display the header and footer.
 */
// these HTTP headers help IE work over SSL, and to handle file downloads
if(!defined('supress_headers')) {
header("Expires: invalid");
header("Pragma: invalid");
header("Cache-control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');
}
// instantiate a Templater
$template = new SLSmarty();
$template->assign('world',$world);
$template->assign('user',$user);

// now decide what content-type header to send, and call the dispatcher!
if (preg_match("/download/",$_REQUEST['do'])) {
   print_download_http_headers(@$_REQUEST['filename']);
   call_user_func( '_dispatch_'.$_REQUEST['do'] , $world , $user, $template);
}
elseif ($_REQUEST['do'] == 'proxy') {
   call_user_func( '_dispatch_proxy' , $world , $user, $template);
}
elseif (preg_match("/^image/",$_REQUEST['do'])) {
   header('Content-type: image/jpeg');
   call_user_func( '_dispatch_'.$_REQUEST['do'] , $world , $user, $template);
}
elseif (preg_match("/^viewer/",$_REQUEST['do'])) {
   header('Content-type: text/html');
   call_user_func( '_dispatch_'.$_REQUEST['do'] , $world , $user, $template);
}
elseif (preg_match("/^wapi/",$_REQUEST['do'])) {
   header('Content-type: text/xml');
   call_user_func( '_dispatch_'.$_REQUEST['do'] , $world , $user, $template);
}
elseif (preg_match("/^ogc/",$_REQUEST['do'])) {
   call_user_func( '_dispatch_'.$_REQUEST['do'] , $world , $user, $template);
}
else {
	if(!defined('supress_headers')){
		header('Content-type: text/html');
   		$template->display('header.tpl');
	}
   call_user_func( '_dispatch_'.$_REQUEST['do'] , $world , $user, $template);
	if(!defined('supress_headers')) {
		$template->display('footer.tpl');
	}
}

?>
