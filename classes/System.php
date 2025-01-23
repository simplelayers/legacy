<?php

use model\MongoCRUD;
use MongoDB\Client;

class System {

    const DB_ACCOUNT = 0;
    const DB_ACCOUNT_SU = 1;

    /**
     * Presently, an alias for getting the World object.
     * 
     * @throws Exception
     * @return World
     */
    private function __construct() {
        if (isset($GLOBALS ['_SimpleSYS']))
            throw new Exception('System should not be instantiated; its functions are static');
    }

    public static function GetSandbox() {
        $url = $_SERVER['REQUEST_URI'];
        $url = explode('/', $url);
        foreach ($url as $pathItem) {
            if (substr($pathItem, 0, 1) != '~')
                continue;
            return substr($pathItem, 1);
        }
    }

    public static function SetupSystem(array $sysInfo = null, $iniFile = null) {
        if (is_null($sysInfo))
            $sysInfo = $_SERVER;
        if (!is_null($iniFile))
            define('SIMPLE_INI', $iniFile);

        $ini = self::GetIni();

        if (!isset($sysInfo['HTTP_HOST'])) {
            $sysInfo['HTTP_HOST'] = 'dev.simplelayers.com';
            $path = dirname(__FILE__);
            $path = explode('/', $path);
            $sandbox = array_pop(array_slice($path, 2, 1));
            $sysInfo['REQUEST_URI'] = 'https://dev.simplelayers.com/~' . $sandbox . '/simplelayers/';
        }

        $siteURL = $serverPath = 'https://' . $sysInfo['HTTP_HOST'] . '/';

        if (isset($sysInfo ['REQUEST_URI'])) {

            if ((strpos($sysInfo ['REQUEST_URI'], '~') !== false) && defined('IS_DEV_SANDBOX')) {
                $webURL = $siteURL . '~';
                $path = explode('/', WEBROOT);
                $sandbox = $path[2];
                $path = array_slice($path, 4);

                $webURL .= $sandbox;
                $webURL .= '/' . implode('/', $path);
                if (substr($webURL, -1) === '/') {
#$webURL = substr($webURL, 0, strlen($webURL) - 1);
                }
                $sysInfo ['REQUEST_URI'] = $webURL;
                define('BASEURL', $webURL);
            } else {
                $webURL = $siteURL;
                $sysInfo ['REQUEST_URI'] = $webURL;
                if (substr($webURL, -1) === '/') {
#$webURL = substr($webURL,0, strlen($webURL) - 1);
                }

                define('BASEURL', $webURL);
            }
        } else {
            $webURL = $siteURL;
            if (substr($webURL, -1) === '/') {
#$webURL = substr($webURL, 0, strlen($webURL - 1));
            }
            $sysInfo ['REQUEST_URI'] = $webURL;
            define('BASEURL', $webURL);
        }
        $_SERVER ['SIMPLE_LAYERS'] = 'simplelayers';

        if (isset($_SERVER ['REDIRECT_SIMPLE_LAYERS']))
            $_SERVER ['SIMPLE_LAYERS'] = $_SERVER ['REDIRECT_SIMPLE_LAYERS'];

// f (!defined('SANDBOX_MODE')) define('SANDBOX_MODE',false);
// efine('BASEDIR', dirname(dirname(__FILE__)));// VIEWER_MODE ? dirname(dirname($_SERVER['SCRIPT_FILENAME'])) : dirname($_SERVER['SCRIPT_FILENAME']) );

        if (!defined('VIEWER_MODE'))
            define('VIEWER_MODE', false);
        define('WORLD_NAME', $_SERVER ['SIMPLE_LAYERS']);
        if (isset($_SERVER ['REQUEST_URI'])) {
            if (stripos('~', $_SERVER ["REQUEST_URI"]) >= 0) {
                define('SANDBOX_MODE', true);
            }
        }
        if (!defined('SANDBOX_MODE'))
            define('SANDBOX_MODE', false);
    }

    public static function GetIni() {
        require_once (dirname(__FILE__) . '/SimpleIni.php');

        if (isset($GLOBALS ['_SL_INI']))
            return $GLOBALS ['_SL_INI'];
        $GLOBALS ['_SL_INI'] = new \SimpleIni ();
        return $GLOBALS ['_SL_INI'];
    }

    public static function GetPublicUser($idOnly = false) {
        $ini = self::GetIni();
        $public = $ini->visitor_account;
        $public = self::Get()->getPersonByUsername($public);
        if ($idOnly)
            return $public->id;
        return $public;
    }

    public static function RequireADODB() {
        $ini = self::GetIni();
        require_once ($ini->adodb_path . 'adodb.inc.php');
    }

    public static function RequireSQLParser() {
        require_once(WEBROOT . 'lib/PHP-SQL-Parser/php-sql-parser.php');
    }

    public static function RequireCharMap() {
        require_once(WEBROOT . 'lib/wendylea_charmap.php');
    }

    public static function GetODBCPorts() {
        return ODBCUtil::GetPorts()->ToOptionAssoc(true);
    }

    /**
     * 
     * @return Mandrill
     */
    public static function GetMandrill() {
        $ini = self::GetIni();
        require_once($ini->mandrill_src);
        return new Mandrill($ini->mandrill_apikey);
    }

    /**
     *
     * @param int $account        	
     * @throws errors\PGConnect
     * @return ADOConnection
     */
    public static function GetDB($account = null) {
        if (is_null($account))
            $account = self::DB_ACCOUNT_SU;
        $db = isset($GLOBALS ['_SL_DB']) ? $GLOBALS ['_SL_DB'] : null;

        if (!is_null($db))
            return $db;
        $ini = self::GetIni();

        if (!defined('ADODB_QUOTE_FIELDNAMES')) {

            define('ADODB_QUOTE_FIELDNAMES', true); //$ini->adodb_quote_fieldnames );
        }
        if (!defined('ADODB_ASSOC_CASE')) {
            define('ADODB_ASSOC_CASE', $ini->adodb_assoc_case);
        }

        self::RequireADODB();

        $db = \NewADOConnection($ini->pg_type);

        $user = ($account == self::DB_ACCOUNT_SU) ? $ini->pg_admin_user : $ini->pg_sl_user;
        $pw = ($account == self::DB_ACCOUNT_SU) ? $ini->pg_admin_password : $ini->pg_sl_password;

        if (!$db->PConnect($ini->pg_host, $user, $pw, $ini->pg_sl_db)) {
            throw new errors\PGConnect ();
        }
        $fetchMode = $ini->fetch_mode;

        $db->SetFetchMode(constant($ini->fetch_mode));
        $GLOBALS ['_SL_DB'] = $db;
        return $GLOBALS ['_SL_DB'];
    }

    public static function CloseDB() {
        $db = isset($GLOBALS ['_SL_DB']) ? $GLOBALS ['_SL_DB'] : null;
        if (!is_null($db)) {
            $db->Close();
        }
    }

    public static function GetCGDB($account) {
        $cgdb = isset($GLOBALS ['_CG_DB']) ? $GLOBALS ['_CG_DB'] : null;
        if (!is_null($cgdb))
            return $cgdb;
        $ini = self::GetIni();
        if (!defined('ADODB_QUOTE_FIELDNAMES')) {
            define('ADODB_QUOTE_FIELDNAMES', $ini->adodb_quote_fieldnames);
        }
        if (!defined('ADODB_ASSOC_CASE')) {
            define('ADODB_ASSOC_CASE', $ini->adodb_assoc_case);
        }
        $ADODB_QUOTE_FIELDNAMES = true;
        System::RequireADODB();

        $cgdb = \NewADOConnection($ini->pg_type);

        $user = ($account == self::DB_ACCOUNT_SU) ? $ini->cg_db_admin_user : $ini->cg_db_user;
        $pw = ($account == self::DB_ACCOUNT_SU) ? $ini->cg_db_admin_password : $ini->cg_db_passwords;

        if (!$cgdb->PConnect($ini->cg_db_host, $user, $pw, $ini->cg_db_database)) {
            throw new errors\PGConnect ();
        }
        $cgdb->SetFetchMode(constant($ini->fetch_mode));
        $GLOBALS ['_CG_DB'] = $cgdb;
        return $GLOBALS ['_CG_DB'];
    }

    public static function GetMongo() {

        /**
         * @var MongoDB\Client $mongoDB 
         */
        $mongoDB = isset($GLOBALS ['_SL_MONGO']) ? $GLOBALS ['_SL_MONGO'] : null;
        if (!is_null($mongoDB)) {
            try {
                $dbs = $mongoDB->listDatabases();
                return $mongoDB;
            } catch (Exception $e) {
                unset($GLOBALS['_SL_MONGO']);
                $mongoDB = null;
            }
        }
        $ini = self::GetIni();

        $options = [
            "maxIdleTimeMS" => 10000 // Set the idle timeout to 5 seconds
        ];
        $mongoDB = new \MongoDB\Client(
                "mongodb://" . $ini->mongo_server,
                $options
        );
        $GLOBALS ['_SL_MONGO'] = $mongoDB;

        return $GLOBALS ['_SL_MONGO'];
    }

    public static function CloseMongo() {
        $mongoDB = isset($GLOBALS ['_SL_MONGO']) ? $GLOBALS ['_SL_MONGO'] : null;
        if (!$mongoDB)
            return;
        unset($GLOBALS['_SL_MONGO']);
    }

    public static function CloseDBs() {
        self::CloseDB();
        self::CloseMongo();
    }

    public static function GetMailConfig() {
        $ini = self::GetIni();
        $config = array(
            'auth' => 'login',
            'username' => $ini->smtp_user,
            'password' => $ini->smtp_pass,
            'ssl' => $ini->smtp_ssl,
            'port' => (int) $ini->smtp_port
        );

        return $config;
    }

    /**
     *
     * @return World
     */
    public static function Get() {
        $system = isset($GLOBALS ['_SL_UNIVERSE']) ? $GLOBALS ['_SL_UNIVERSE'] : null;
        if (!is_null($system))
            return $system;
        $ini = self::GetIni();
        $GLOBALS ['_SL_UNIVERSE'] = new World($ini->name);
        return $GLOBALS ['_SL_UNIVERSE'];
    }

    /**
     * @return WAPI
     */
    public static function GetWapi() {
        return System::Get()->wapi;
    }

    public static function IsSandboxed() {
        return self::Get()->sandbox;
    }

    public static function RequireGraphing() {
        $ini = self::GetIni();

        foreach ($ini->jpgraph_incs as $inc) {
            require_once ($inc);
        }
// constants pertaining to graph generation
        define('GRAPH_BGCOLOR', '#FFFFFF'); // the background color
        define('GRAPH_GRIDCOLOR', '#BBBBBB'); // the color for grid lines
        define('GRAPH_WIDTH', 700);
        define('GRAPH_HEIGHT', 600); // width and height of graph images
        define('GRAPHMARKER', MARK_FILLEDCIRCLE); // marker is a circle; see JPGraph docs for more options
        define('MARKERSIZE_NORMAL', 3);
        define('MARKERSIZE_BIG', 5);
        define('MARKERSIZE_LARGE', 7);
        define('MARKERSIZE_HUGE', 9);
        define('GRAPH_LIMIT_ALL', 1000); // when we ask for "all data" how many do we really mean?
// a list of options for selecting how many records to show in the graph (or other report)
        $GRAPH_LIMIT_OPTIONS = array(
            7 => 'Last 7 data points',
            GRAPH_LIMIT_ALL => 'All data'
        );
// what colors should plots be in a graph? The lines will cycle through these colors.
        $GRAPHCOLORS = array(
            '#000000',
            '#009900',
            '#3333FF',
            '#9966CC',
            '#CC33CC',
            '#808080',
            '#A629A6',
            '#00E600',
            '#2424B3',
            '#802080',
            '#801700',
            '#4D4D4D',
            '#669900',
            '#FF5500',
            '#CC85B4',
            '#CC690A',
            '#FFFF03',
            '#FFFFFF',
            '#80BF00',
            '#A66C92',
            '#A6560A',
            '#8F8FB3',
            '#805371',
            '#80420A',
            '#B3B303',
            '#7F7F7F',
            '#0A9900',
            '#A639A6',
            '#FFFF0A',
            '#0ABF00',
            '#CC43CC',
            '#B3B30A',
            '#333333',
            '#669900',
            '#803080',
            '#A6A6A6',
            '#8ABF00',
            '#CC95B4',
            '#FF550A',
            '#A67C92',
            '#B33B0A',
            '#806371'
        );
    }

    public static function RequireProjections() {
        require_once (WEBROOT . 'includes/projections.php');
        return $GLOBALS ['PROJECTIONS'];
    }

    public static function RequireColorschemes() {
        require_once (WEBROOT . 'includes/colorschemes.php');
    }

    public static function RequireSmarty() {
        $ini = self::GetIni();
        $paths = array(
            'smarty_compile_dir',
            'smarty_config_dir',
            'smarty_cache_dir'
        );
        if (!file_exists($ini->smarty_tmp)) {
            mkdir($ini->smarty_tmp, 0777);
            foreach ($paths as $path) {
                if (!file_exists($ini->smarty_compile_dir)) {
                    mkdir($ini->$path, 0777);
                }
            }
        }
#require_once ($ini->smarty_inc);
    }

    public static function RequireColorPicker() {
// and the color_picker() function, which is separated out solely for readability
        require_once SL_INCLUDE_PATH . 'colorpicker.php';
    }

    function RequireSession() {
        
    }

    public static function RequireSubNav($type) {
        /* $subnav = new OrganizationSubnav ();
          $subnav->makeDefault ( $org, $user );
          $template->assign ( 'subnav', $subnav->fetch () ); */
    }

    public static function RequireReporting() {
        require_once (WEBROOT . 'classes/Report.class.php');
    }

    public static function RequireSimpleImage() {
        require_once (WEBROOT . 'lib/SimpleImage.php');
    }

    public static function RequireMail() {
        self::RequireZend();
        Zend_Loader::loadClass('Zend_Mail');
        Zend_Loader::loadClass('Zend_Mail_Transport_Smtp');
    }

    public static function RequireZend() {
        $ini = self::GetIni();
        require_once($ini->zend_path . '/Zend/Loader.php');
    }

    public static function RelPath($file, $target = null) {

        $file = substr($file, strlen(WEBROOT));
        if (is_null($target))
            return BASEURL . $file;

        $file = dirname($file);
        $path = explode("/", $target);
        foreach ($path as $dir) {
            if ($dir == '..') {
                $file = dirname($file);
            } else {
                $file .= '/' . $dir;
            }
        }

        return BASEURL . $file;
    }

    public static function GetSystemOwner($idOnly = false) {
        $ini = self::GetIni();
        $owner = $ini->system_owner;
        if ($idOnly)
            return $owner;
        return self::Get()->getPersonById($owner);
    }

    public static function MakeID() {
        return MongoCRUD::NewID();
    }

}

?>
