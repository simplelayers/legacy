<?php
use custom_types\CustomTypeFactory;
use model\ProjectionList;

/**
 * See the World class documentation.
 *
 * @package ClassHierarchy
 */

/**
 * The World represents the entire microcosm: people, layers, projects, etc.
 *
 * The world acts as "glue" to provide a framework for accessing the people, layers, etc. that exist
 * in our little world. It primarily consists of search functions, functions for creating and authenticating
 * people, and a database connection. It is instantiated in the index.php and accepts one argument: an
 * associative array which becomes its 'config' attribute (see below).
 *
 * Global attributes:
 * - name -- The World's name. This string is guaranteed to uniquely identify a World.
 * - config -- An associative array of configuration settings. See the World class's config attribute.
 * This should be treated as read-only! Changes to the config probably won't work as intended.
 * - db -- An ADOdb database handle, already connected to our database. This is used extensively internally by
 * other classes, but if you find yourself using it outside of a class method, you're probably doing
 * something wrong.
 * - admindb -- Like 'db' above, except with a superuser account. Useful for manipulating raw table privileges.
 * - db->debug -- You can set this to true/false to effect verbose output of database activity.
 * This is invaluable during development!
 * - projections -- An instance of the Projection class.
 * - changes -- An instance of the Changes class.
 *
 * @see index.php
 * @package ClassHierarchy
 */
class World
{

    private $_wapi = null;

    public $system_uri;

    public $sandbox = false;

    public $db;

    public $admindb;

    public $auth;

    public $name;

    public $config;
    /* @var $projections ProjectionList */
    public $projections;

    public $changes;

    public $debug;

    public $isCLI;

    /**
     *
     * @ignore
     *
     */
    function __construct($name)
    {
        $this->name = $name;
        $this->db = $this->connectToDatabase(false);
        $this->admindb = $this->connectToDatabase(true);
        $this->config = $this->loadConfig();
        $this->projections = new ProjectionList();
        $this->changes = new Changes($this);
        $this->debug = new Debug($this);
        $this->auth = new TokenAuthenticator($this);
        $this->isCLI = ! isset($_SERVER['HTTP_HOST']);
        if (! $this->isCLI) {
            $protocol = 'http://';
            if (isset($_SERVER['HTTPS'])) {
                if ($_SERVER['HTTPS'] == 'on')
                    $protocol = 'https';
            }
            
            $this->system_uri = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $temp = explode('?', $this->system_uri);
            $this->system_uri = array_shift($temp);
            if (strpos($this->system_uri, '~')) {
                $parts = explode('?', $this->system_uri);
                $parts = explode('~', $parts[0]);
                $temp = explode('/', $parts[1]);
                $this->sandbox = array_shift($temp);
            }
        }
        if (! $this->config)
            throw new Exception("Invalid World specified: $name");
    }

    function __get($what)
    {
        switch ($what) {
            case 'wapi':
                if (is_null($this->_wapi))
                    $this->_wapi = new WAPI($this);
                return $this->_wapi;
                break;
        }
    }

    function updateTypes()
    {
        foreach (CustomTypeFactory::GetTypes() as $type) {
            CustomTypeFactory::GetCustomType($this->admindb, $type)->AddCustomType();
        }
    }
    
    // ///
    // /// functions for fetching and setting configuration
    // ///
    private function loadConfig()
    {
        // load the static config, then merge in the config from the database, to make one consistent config set
        global $WORLDCONFIG;
        $config = $WORLDCONFIG;
        $cx = $this->db->Execute('SELECT * FROM config')->getRows();
        foreach ($cx as $entry)
            $config[$entry['key']] = $entry['value'];
        return $config;
    }

    function setConfig($key, $value)
    {
        // if it's part of the static config, skip it
        global $WORLDCONFIG;
        if (isset($WORLDCONFIG[$key]))
            return;
            // if it's not part of the static config, go ahead and save to the database
        if (is_array($value))
            $value = implode(',', $value);
        if ($value === null or $value === false)
            $value = '';
        $this->db->Execute('INSERT INTO config (key,value) VALUES (?,?)', array(
            $key,
            $value
        ));
        $this->db->Execute('UPDATE config SET value=? WHERE key=?', array(
            $value,
            $key
        ));
    }
    
    // ///
    // /// methods for fetching the price of accounts
    // ///
    /*
     * function getAccountPrice($level) {
     * global $ACCOUNTTYPES;
     * if (! $ACCOUNTTYPES [$level])
     * return 0.00;
     * return $this->config ["accountprice_{$level}"];
     * }
     */
    
    // ///
    // /// functions pertaining to managing user accounts
    // ///
    /**
     * Return a list of all people who exist in the system.
     * This differs from searchPeople() in that it returns everybody, even if they have requested to be unlisted.
     *
     * @param string $orderby
     *            Optional, the field to sort the list by. By default, they are sorted by their username.
     * @return array An array of Person objects, being everybody in the world.
     */
    function getAllPeople($orderby = 'username')
    {
        if (preg_match('/\W/', $orderby))
            $orderby = 'username';
        $people = $this->db->Execute("SELECT id FROM people ORDER BY $orderby")->getRows();
        $people = array_map(create_function('$a', 'return $a["id"];'), $people);
        $people = array_map(array(
            $this,
            'getPersonById'
        ), $people);
        return $people;
    }

    /**
     * Return a list of numeric ID#s for all people who exist in the system.
     * This is much more efficient than getAllPeople() when you only want numeric IDs,
     * since it doesn't fetch Person objects and doesn't even sort the list of IDs.
     */
    function getAllPersonIds()
    {
        $ids = $this->db->Execute("SELECT id FROM people")->getRows();
        $ids = array_map(create_function('$a', 'return $a["id"];'), $ids);
        return $ids;
    }

    /**
     * Return a list of string usernames for all people who exist in the system.
     * This is much more efficient than getAllPeople() when you only want text usernames.
     */
    function getAllPersonUsernames()
    {
        $ids = $this->db->Execute("SELECT username FROM people ORDER BY username")->getRows();
        $ids = array_map(create_function('$a', 'return $a["username"];'), $ids);
        return $ids;
    }

    /**
     * Verify whether a given username+password pair matches.
     * This is called by index.php to authenticate the user.
     *
     * @param string $username
     *            A username.
     * @param string $password
     *            A password.
     * @return boolean True/false indicating whether the supplied password was indeed the proper password for the user.
     */
    function verifyPassword($username, $password)
    {
        if (! $password)
            return false;
        $opassword = $this->encryptPassword($password);
        $id = $this->db->Execute('SELECT id FROM people WHERE username=? AND password=?', array(
            $username,
            $opassword
        ));
        if ($id->EOF) {
            return $this->verifyPasswordNew($username, $password);
        } else {
            $this->db->Execute('UPDATE people SET newpassword=?, password=NULL WHERE username=? AND password=?', array(
                $this->encryptPasswordNew($password, (int) $id->fields['id']),
                $username,
                $opassword
            ));
            return true;
        }
    }

    function verifyPasswordNew($username, $password)
    {
        if (! $password)
            return false;
        $id = $this->db->Execute('SELECT id FROM people WHERE username=?', array(
            $username
        ));
        $password = $this->encryptPasswordNew($password, (int) $id->fields['id']);
        $id = $this->db->Execute('SELECT id FROM people WHERE username=? AND newpassword=?', array(
            $username,
            $password
        ));
        return ! $id->EOF;
    }

    /**
     * Encrypt a password using a salted MD5 hash system.
     * This isn't really useful publicly, it's modtly used internally by World::verifyPassword() and World:createPerson()
     *
     * @param string $string
     *            A password to be encrypted.
     * @return string The encrypted password.
     *         7r'/ 70 U|\|3|\|(r'/P7 7|-|1$. j00Z (4|\||\|07. pH4(3 17.
     */
    function encryptPassword($string)
    {
        return md5($string);
    }

    function encryptPasswordNew($string, $salt)
    {
        // $pepper = "7r'/ 70 U|\|3|\|(r'/P7 7|-|1$. j00Z (4|\||\|07. pH4(3 17.";
        // Do not change this, ever. The salt is used to encrypt every password. Changing it will break every password.
        $ini = System::GetIni();
        $pepper = $ini->secret_spice;
        
        return hash("sha512", hash("sha256", $pepper . $string . hash("sha256", strtolower($salt))));
    }

    /**
     * Create a new user account in the system.
     *
     * @param string $username
     *            The username for the new Person.
     * @param string $password
     *            The password for the new Person.
     * @param integer $type
     *            The account type for the new person. One of the AccountTypes::* defines.
     * @param string $comment
     *            An optional text comment about this user's creation.
     * @return A Person object if account creation was successful, false if creation failed. Creation could fail
     *         for a few reasons: blank password, invalid username, name already taken, username is banned, ...
     */
    function createPerson($username, $password, $comment = '')
    {
        $username = strtolower($username);
        // some simple sanity checks
        if (! $password)
            return false;
        if ($username == WORLD_NAME)
            return false;
            
            // create their password entry, and fetch the Person object for later use
        $this->db->Execute('INSERT INTO people (username,expirationdate) VALUES (?,?)', array(
            $username,
            date('Y-m-d')
        ));
        
        $p = $this->getPersonByUsername($username);
        $p->password = $password;
        // log that the account was created
        $this->logAccountActivity($p->username, 'create', $comment);
        
        // create the PgSQL account too; this may fail if there's already a user by this name,
        // but that's okay ecause we use the finer-grained access controls for the real work
        $this->admindb->Execute(sprintf('CREATE USER "%s" PASSWORD %s', $p->databaseusername, $this->admindb->quote($password)));
        // $p->accounttype = AccountTypes::MIN;
        
        // create default bookmarks and data: people, layers, projects
        foreach (explode(',', $this->config['autobookmark_people']) as $id)
            $p->buddylist->addPersonById($id);
        foreach (explode(',', $this->config['autobookmark_projects']) as $id)
            $p->addProjectBookmarkById($id);
        foreach (explode(',', $this->config['autobookmark_layers']) as $id)
            $p->addLayerBookmarkById($id);
        foreach (explode(',', $this->config['autocopy_layers']) as $id)
            $p->createCopyOfLayer($id);
            // foreach (explode(',',$this->config['autocopy_projects']) as $id) $p->createCopyOfProject($id);
            
        // now create a project using all of this new person's layers
        $layers = array_reverse($p->listLayers('type'));
        if ($layers) {
            $project_name = $this->config['defaultproject_name'];
            if (! $project_name)
                $project_name = 'Example Project';
            $project_desc = $this->config['defaultproject_desc'];
            if (! $project_desc)
                $project_desc = 'A demo project.';
            $project = $p->createProject($project_name);
            $project->description = $project_desc;
            foreach ($layers as $l) {
                $pl = $project->addLayerById($l->id);
                if ($l->type != LayerTypes::VECTOR)
                    $pl->opacity = 0.5;
                $pl->on_by_default = 1;
            }
        }
        
        // all done!
        return $p;
    }

    /**
     * Resolve a username into a numeric user-ID#.
     * If you only want a user's ID#, then this is a faster and
     * lighterweight alternative to calling getPersonById()
     *
     * @param string $username
     *            A username.
     * @return integer The unique ID# of the specified person, or false if they don't exist.
     */
    function getUserIdFromUsername($username)
    {
        $id = $this->db->Execute('SELECT id FROM people WHERE username=?', array(
            $username
        ));
        if ($id->EOF)
            return false;
        return $id->fields['id'];
    }
    
    // ///
    // /// functions pertaining to Groups
    // ///
    function createGroup($moderator, $title, $description, $org = null)
    {
        $id = $this->db->Execute('INSERT INTO groups (title,description,org_id) VALUES (?,?,?) RETURNING id', array(
            ($title == '' ? 'New Group ' : $title),
            $description,
            $org
        ))->fields["id"];
        
        $this->db->Execute('INSERT INTO groups_members (group_id,person_id,actor,seat) VALUES (?,?,5,?)', array(
            $id,
            $moderator,
            (is_null($org) ? $org : 1)
        ));
        if ($id) {
            if ($org)
                Organization::GetOrg($org)->group = $id;
        }
        $return = $this->getGroupById($id);
        return $return;
    }

    function getGroupById($id)
    {
        try {
            return new Group($this, $id);
        } catch (Exception $e) {
            return false;
        }
    }
    // ///
    // /// Invoice Functions
    // ///
    
    /*
     * function createInvoice($owner) { $id = $this->db->Execute('INSERT INTO organizations (name, owner, short) VALUES (?,?,?) RETURNING id', Array($name,$owner,$short))->fields['id']; $return = $this->getOrganizationById($id); return $return; }
     */
    function getInvoiceById($id)
    {
        try {
            return new Invoice($this, $id);
        } catch (Exception $e) {
            return false;
        }
    }
    // ///
    // /// Organizations Functions
    // ///
    function createOrganization($owner, $name, $short)
    {
        $id = null;
        
        $result = $this->db->GetRow('INSERT INTO organizations (name, owner, short) VALUES (?,?,?) RETURNING id', Array(
            $name,
            $owner,
            $short
        ));
        if ($result) {
            $id = $result['id'];
            $return = $this->getOrganizationById($id);
        } else {
            
            throw new Exception($this->db->ErrorMsg());
        }
        return $return;
    }

    function getOrganizationById($id = 1)
    {
        try {
            return new Organization($this, $id);
        } catch (Exception $e) {
            return false;
        }
    }

    function createForm($owner, $layer, $name = null)
    {
        if ($name === null) {
            $layerObj = $this->getLayerById($layer);
            $name = $layerObj->name . " - " . date("m/d/Y");
        }
        $id = $this->db->Execute('INSERT INTO forms (name, owner, layer) VALUES (?,?,?) RETURNING id', Array(
            $name,
            $owner,
            $layer
        ))->fields['id'];
        $return = $this->getForm($id);
        return $return;
    }

    function getForm($id)
    {
        try {
            return new Form($this, $id);
        } catch (Exception $e) {
            return false;
        }
    }
    
    // ///
    // /// functions for logging
    // ///
    /**
     * Make a log entry regarding account changes: upgrades, signups, deletions, etc.
     * Note that there are no standards for these fields, and no enforcement. The username is given as a string,
     * for instance, so that it can be completely independent of later account deletions. The type and comment,
     * too, are intended to be "whatever is useful to you" and there is no standard or official list,
     * and the log itself is intended solely for statistical purposes.
     *
     * @param string $account
     *            The username of the account.
     * @param string $type
     *            A string indicating what "type" of log this is, e.g. "'signup", "upgrade"
     * @param string $comment
     *            A more lengthy text comment about the activity.
     */
    function logAccountActivity($account, $type, $comment)
    {
        $this->db->Execute('INSERT INTO _accountlog (datetime,type,account,description) VALUES (NOW(),?,?,?)', array(
            $type,
            $account,
            $comment
        ));
    }

    /**
     * Make a log entry noting that the specified project is being viewed.
     * Note that there are no standards or enforcement on these parameters; they are intended to be "whatever is
     * useful" at the time the event is logged, and the log itself is intended solely for statistical purposes.
     *
     * @param string $username
     *            The username who's viewing the project.
     * @param string $projectowner
     *            The username of the project's owner.
     * @param string $projectname
     *            The name of the project.
     */
    function logProjectUsage($user, $project, $comment = '')
    {
        if (is_string($project)) {
            $project = Project::Get($project);
        }
        
        if (is_array($user)) {
            
            $username = $user['username'];
            $userid = $user['id'];
        } elseif (is_string($user)) {
            
            $username = $user;
            $userid = null;
        } elseif (get_class($user) == "Person") {
            $username = $user->username;
            $userid = $user->id;
        }
        
        $projectowner = $project->owner->username;
        $projectownerid = $project->owner->id;
        $projectname = $project->name;
        $projectid = $project->id;
        if ($comment === null)
            $comment = '';
        $this->db->Execute('INSERT INTO _usagelog (datetime,account,owner,project,comment,owner_id,project_id,account_id) VALUES (NOW(),?,?,?,?,?,?,?)', array(
            $username,
            $projectowner,
            $projectname,
            $comment,
            $projectownerid,
            $projectid,
            $userid
        ));
    }

    /**
     * Make a log entry about the user having logged in.
     *
     * @param string $username
     *            The username who's viewing the project.
     * @param string $ipaddress
     *            The IP address from which they came.
     */
    function logUserLogin($username, $ipaddress)
    {
        $this->db->Execute('INSERT INTO _logins (datetime,username,ipaddress) VALUES (NOW(),?,?)', array(
            $username,
            $ipaddress
        ));
    }

    /**
     * Fetch the most recent entries in the account activity log.
     *
     * @see logAccountActivity()
     * @param integer $howmany
     *            How many log entries to bring up; Default is 100.
     * @return array Array of associative arrays, each one representing a log entry.
     */
    function fetchRecentLogins($howmany = 100, $username = null)
    {
        if (! (int) $howmany)
            $howmany = 100;
        if ($username) {
            $query = "SELECT * FROM _logins WHERE username=? ORDER BY datetime DESC LIMIT $howmany";
            $args = array(
                $username
            );
        } else {
            $query = "SELECT * FROM _logins ORDER BY datetime DESC LIMIT $howmany";
            $args = array();
        }
        return $this->db->Execute($query, $args)->getRows();
    }

    /**
     * Fetch the most recent entries in the account activity log.
     *
     * @see logAccountActivity()
     * @param integer $howmany
     *            How many log entries to bring up; Default is 100.
     * @return array Array of associative arrays, each one representing a log entry.
     */
    function fetchAccountActivity($howmany = 100)
    {
        if (! (int) $howmany)
            $howmany = 100;
        return $this->db->Execute("SELECT * FROM _accountlog ORDER BY datetime DESC LIMIT $howmany")->getRows();
    }

    /**
     * Fetch the most recent entries in the project usage log.
     *
     * @see logProjectUsage()
     * @param integer $howmany
     *            How many log entries to bring up; Default is 100.
     * @return array Array of associative arrays, each one representing a log entry.
     */
    function fetchProjectUsage($howmany = 100, $user = null, $map = null)
    {
        if (! (int) $howmany)
            $howmany = 100;
        $where = "";
        $data = Array();
        if (! is_null($user)) {
            $where .= " WHERE owner_id = ?";
            $data[] = $user;
            if (! is_null($map)) {
                $where .= " AND project_id = ?";
                $data[] = $map;
            }
        }
       #$this->db->debug=true;
        return $this->db->Execute("SELECT * FROM _usagelog $where ORDER BY datetime DESC LIMIT $howmany", $data)->getRows();
    }
    
    // ///
    // /// functions to search for people, layers, and projects
    // ///
    /**
     * Fetch a person by their username.
     *
     * @param string $username
     *            A username.
     * @return Person A Person object.
     */
    function getPersonByUsername($username = null)
    {
        $ini = System::GetIni();
        if (is_null($username))
            $username = $ini->visitor_account;
        $username = strtolower($username);
        $id = $this->db->GetOne('SELECT id FROM people WHERE username=?', array(
            $username
        ));
        if($id === false) {
            return false;
        }
        return $this->getPersonById($id);
    }

    /**
     * Same as getPersonByUsername() except that it takes a person's ID# instead of their username.
     */
    function getPersonById($id)
    {
        try {
            return new Person($this, $id);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *
     * @param integer $id
     *            The unique ID# of the Layer to fetch.
     * @return Layer A Layer object, or else false if the specified Layer does not exist.
     */
    function getLayerById($id)
    {
        try {
            return new Layer($this, $id);
        } catch (Exception $e) {
            
            return false;
        }
    }

    /**
     *
     * @param integer $id
     *            The unique ID# of the Project to fetch.
     * @return Project A Project object, or else false if the specified Project does not exist.
     */
    function getProjectById($id)
    {
        try {
            return new Project($this, $id);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Search for people who match your search key.
     * This function does not perform filtering or sorting; that's up to the caller.
     *
     * @param string $searchstring
     *            The search tag. May be false, in which case all available people are returned.
     * @return array An array of Person objects.
     */
    function searchPeople($searchstring = false)
    {
        if ($searchstring === '')
            return array();
        $query = 'SELECT id FROM people WHERE id!=0 ';
        $args = array();
        if ($searchstring !== false) {
            $searchstring = strtolower($searchstring);
            $query .= 'AND (username LIKE ? OR lower(realname) LIKE ? OR lower(tags) LIKE ? OR lower(description) LIKE ?)';
            $args = array(
                "%$searchstring%",
                "%$searchstring%",
                "%$searchstring%",
                "%$searchstring%"
            );
        }
        $people = $this->db->Execute($query, $args)->getRows();
        
        $people = array_map(create_function('$a', 'return $a["id"];'), $people);
        $people = array_map(array(
            $this,
            'getPersonById'
        ), $people);
        return $people;
    }

    /**
     * Search for layers which match your search key.
     * This function does not perform filtering or sorting; that's up to the caller.
     *
     * @param string $searchstring
     *            The search tag. May be false, in which case all available layers are returned.
     * @return array An array of Layer objects.
     */
    function searchLayers($searchstring = false)
    {
        if ($searchstring === '')
            return array();
        $query = 'SELECT id FROM layers';
        $args = array();
        if ($searchstring !== false) {
            $searchstring = strtolower($searchstring);
            $query .= ' WHERE lower(description) LIKE ? OR lower(tags) LIKE ? OR lower(name) LIKE ?';
            $args = array(
                "%$searchstring%",
                "%$searchstring%",
                "%$searchstring%"
            );
        }
        $layers = $this->db->Execute($query, $args)->getRows();
        
        $layers = array_map(create_function('$a', 'return $a["id"];'), $layers);
        $layers = array_map(array(
            $this,
            'getLayerById'
        ), $layers);
        return $layers;
    }

    /**
     * Search for projects that match your search key.
     * This function does not perform filtering or sorting; that's up to the caller.
     *
     * @param string $searchstring
     *            The search tag. May be false, in which case all available projects are returned.
     * @return array An array of Project objects.
     */
    function searchProjects($searchstring = false)
    {
        if ($searchstring === '')
            return array();
        $query = 'SELECT id FROM projects';
        $args = array();
        if ($searchstring !== false) {
            $searchstring = strtolower($searchstring);
            $query .= ' WHERE lower(description) LIKE ? OR lower(tags) LIKE ? OR lower(name) LIKE ?';
            $args = array(
                "%$searchstring%",
                "%$searchstring%",
                "%$searchstring%"
            );
        }
        $projects = $this->db->Execute($query, $args)->getRows();
        
        $projects = array_map(create_function('$a', 'return $a["id"];'), $projects);
        $projects = array_map(array(
            $this,
            'getProjectById'
        ), $projects);
        return $projects;
    }
    
    // ///
    // /// CAPTCHA stuff. Doesn't pertain to the World per se, but since it uses the tempdir and fontdir
    // /// and other World->config items, it seemed best to put it here
    // ///
    /**
     * Generate a CAPTCHA phrase and image, setting it in the session as a side effect.
     * Note that generating a CAPTCHA overwrites any previous one, so this function is not "thread safe"
     * and should only be used where there's one CAPTCHA happening at a time.
     *
     * @return string The name of the image file containing the CAPTCHA image.
     *         This will be under the tempurl/tempdir and will therefore be web-accessible.
     * @see tempurl
     */
    function generateCaptcha()
    {
        // create the CAPTCHA and a file to receive it, and generate it
        $img = new securimage();
        $filename = md5(microtime()) . '.png';
        $img->outputfilename = $this->config['tempdir'] . '/' . $filename;
        $img->show();
        // return the path to the image file
        return $this->config['tempurl'] . '/' . $filename;
    }

    /**
     * Check the CAPTCHA against the given string.
     *
     * @param string $code
     *            The code entered by the person.
     * @return boolean True or false indicating whether the code matched.
     */
    function checkCaptcha($phrase)
    {
        $img = new securimage();
        return $img->check($phrase);
    }
    
    // ///
    // /// other miscellaneous functions
    // ///
    /**
     * Return a new Mapper object.
     *
     * @see Mapper
     * @return Mapper A Mapper object.
     */
    function getMapper()
    {
        return new Mapper($this);
    }

    /**
     * Connect to the database and return a ADOdb database connection handle.
     * This is called automatically by the World's constructor, and a database handle is already available
     * for use, so you'll probably never need to call this directly.
     *
     * @link http://phplens.com/lens/adodb/docs-adodb.htm
     * @return An ADOdb database handle.
     */
    function connectToDatabase($superuser = false)
    {
        return $superuser ? System::GetDB(System::DB_ACCOUNT_SU) : System::GetDB(System::DB_ACCOUNT_SU);
    }

    /**
     * Connect to a ODBC data source
     *
     * @return array An array of 3 items:
     *         A PHP ODBC database handle, as described in odbc_connect()
     *         The path to the odbc.ini file
     *         The path to the freetds.conf file
     */
    function connectToODBC($odbcinfo, $noconnect = false)
    {
        $filename = md5(microtime());
        $ini = System::GetIni();
        $odbcini_filename = $ini->tempdir . '/' . $filename . '.odbc.ini';
        $freetdsconf_filename = $ini->tempdir . '/' . $filename . '.freetds.conf';
        
        $freetdsconf_content = "[global]\ntds version = 7.0\ntext size = 64512\n";
        $freetdsconf_content .= "[dsn]\n";
        $freetdsconf_content .= sprintf("host = %s\n", $odbcinfo->odbchost);
        $freetdsconf_content .= sprintf("port = %d\n", $odbcinfo->odbcport);
        file_put_contents($freetdsconf_filename, $freetdsconf_content);
        putenv("FREETDSCONF=$freetdsconf_filename");
        
        $odbcini_content = "[dsn]\n";
        $odbcini_content .= sprintf("Driver  = %s\n", $odbcinfo->driver);
        $odbcini_content .= sprintf("%s  = %s\n", $odbcinfo->driver == ODBCUtil::PGSQL ? 'Servername' : 'Server', $odbcinfo->odbchost);
        $odbcini_content .= sprintf("Port    = %d\n", $odbcinfo->odbcport);
        $odbcini_content .= sprintf("Database = %s\n", $odbcinfo->odbcbase);
        if ($odbcinfo->driver == ODBCUtil::MSSQL)
            $odbcini_content .= "TDS_Version = 7.0\n";
        file_put_contents($odbcini_filename, $odbcini_content);
        putenv("ODBCINI=$odbcini_filename");
        
        // and do the connection, now that there's a proper DSN given
        if (! $noconnect) {
            
            try {
                ob_start();
                $odbc = odbc_connect('dsn', $odbcinfo->odbcuser, $odbcinfo->odbcpass, SQL_CUR_USE_ODBC);
                ob_end_clean();
            } catch (Exception $e) {
                var_dump($e->getMessage());
                die();
                // do nothing.
            }
        } else {
            
            $odbc = null;
        }
        return array(
            $odbc,
            $odbcini_filename,
            $freetdsconf_filename
        );
    }

    /**
     * Given three criteria arguments (field, operator, value) generate the corresponding SQL.
     * The list of criteria_operator choices is: == > < <= >= contains isnull
     *
     * @param
     *            dbhandle A database handle, e.g. $world->db
     * @param
     *            criteria_field The field for the criterion, e.g. "population"
     * @param
     *            criteria_operator The operator for the criterion, e.g. ">"
     * @param
     *            criteria_value The comparison value for the criterion, e.g. "1000000"
     * @param
     *            database May be any of the ODBCUtil:* constants, defaults to ODBCUtil::PGSQL
     * @return A string of SQL appropriate for inclusion in a WHERE clause. If the criteria are blank, the string 'true' will be returned, which is appropriate for use in WHERE clauses both with and without other comparisons.
     */
    function criteria_to_sql($criteria1, $criteria2, $criteria3, $dbtype = ODBCUtil::PGSQL)
    {
        $criteria1 = preg_replace('/\W/', '', $criteria1); // the field is always a plain old word
        
        if ($criteria1 == '' or $criteria2 == '' or $criteria3 == '') {
            $sql = 'true';
            
            if (($criteria3 == '') and (($criteria1 != '') and ($criteria2 != ''))) {
                $sql = (strtoupper($criteria2) == "ISNULL") ? strtolower($criteria1) . " ISNULL" : true;
            }
        } elseif (in_array($criteria2, array(
            '<',
            '>',
            '<=',
            '>='
        ))) {
            $criteria3 = $this->db->qstr(strtolower(trim($criteria3)));
            if ($dbtype == ODBCUtil::PGSQL)
                $sql = "\"{$criteria1}\" {$criteria2} {$criteria3}";
            elseif ($dbtype == ODBCUtil::MYSQL)
                $sql = "`{$criteria1}` {$criteria2} {$criteria3}";
        } elseif ($criteria2 == '==') {
            $criteria3 = $this->db->qstr((trim($criteria3)));
            if ($dbtype == ODBCUtil::PGSQL) {
                $sql = "lower(\"{$criteria1}\") = lower({$criteria3})";
               
            } elseif ($dbtype == ODBCUtil::MYSQL)
                $sql = "`{$criteria1}` = {$criteria3}";
        } elseif ($criteria2 == 'contains') {
            $criteria3 = $this->db->qstr("%" . strtolower($criteria3) . "%");
            if ($dbtype == ODBCUtil::PGSQL)
                $sql = "lower(\"{$criteria1}\"::text) LIKE $criteria3";
            elseif ($dbtype == ODBCUtil::MYSQL)
                $sql = "`{$criteria1}` LIKE $criteria3";
        } else {
            $sql = "$criteria1 $criteria2 $criteria3";
        }
        
        return $sql;
    }
}

?>