<?php
/**
  * See the Person class documentation.
  * @package ClassHierarchy
  */
use model\CRUD;
use model\SeatAssignments;
use model\RolePermissions;
use model\organizations\OrgMedia;
use model\License;

/**
 *
 * @ignore
 *
 *
 */
require_once 'BuddyList.class.php';
/**
 *
 * @ignore
 *
 *
 */
require_once 'Layer.class.php';
/**
 *
 * @ignore
 *
 *
 */
require_once 'Project.class.php';

/**
 * The class representing a Person.
 * A person is a user account on the system, and is also a container for layers and projects.
 *
 * Public attributes:
 * - id -- The person's unique ID#. Read-only.
 * - username -- Their string username, which is also guaranteed unique. Read-only.
 * - databaseusername -- The string username which they would use to login to the PgSQL database directly. Guaranteed unique. Read-only.
 * - admin -- Boolean, indicating whether they're the admin account. Read-only.
 * - realname -- Text, their "real name"
 * - email -- Text, their email address.
 * - accounttype -- Their account-type, one of the AccountTypes::* constants.
 * - buddylist -- A BuddyList object, representing the Person's buddy list. Read-only.
 * - password -- Their encrypted password. This attribute can be set to a new (unencrypted) password and it
 * will automagically be encrypted
 * - visible -- Boolean, indicating whether their account should be visible in the Search People listing.
 * If false, they will only be visible to people on their buddy list.
 * - email_public -- Boolean, indicating whether to show their email address on the peopleinfo page.
 * - description -- Free-form text, a description of themselves.
 * - tags -- Free-form text, additional search tags.
 * - contactinfo -- Free-form text, additional contact information.
 * - comment1 -- Free-form text, an administrative comment.
 * - comment2 -- Free-form text, an administrative comment.
 *
 * @package ClassHierarchy
 */
class Person extends CRUD {
	
	/**
	 *
	 * @ignore
	 *
	 *
	 */
	protected $world; // a link to the World we live in
	/**
	 *
	 * @ignore
	 *
	 *
	 */
	public $id; // the user's unique ID#
	protected $idField = 'id';
	protected $table = 'people';
	protected $hasModified = true;
	protected $_permissions;
	protected $_seat;
	protected $_roleId;
	
	
	private $unsettable =array('seatname','username','databaseusername','buddylist','admin');
	
	/**
	 *
	 * @ignore
	 *
	 *
	 */
	public $buddylist; // their BuddyList object
	function __construct(&$world, $id) {
		
		$this->world = $world;
		$this->id = $id;
		$this->buddylist = new BuddyList ( $this->world, $this );
		
		// verify that we exist. If not, commit noisy suicide
		if (! $this->username)
			throw new Exception ( "No such userid: $id." );
	}
	
	// /// make attributes directly fetchable and editable
	/**
	 *
	 * @ignore
	 *
	 *
	 */
	function __get($name) {
		$val = parent::__get ( $name );
		if ($val)
			return $val;
			// simple sanity check
		if (preg_match ( '/\W/', $name ))
			return false;
			
			// false attributes, e.g. "admin" is a wrapper to check their ID# being 0
		$admins = Array (
				0,
				744 
		);
		if ($name == 'admin')
			return in_array ( $this->id, $admins );
		if ($name == "organization")
			return $this->getOrganization ();
		if ($name == 'orgid') {
			return $this->getOrganization(true);
		}
		if( $name == 'roleId') {
			if(!$this->_roleId) $this->_roleId = SeatAssignments::GetUserRole ( $this->id );
			return $this->_roleId;
		}
		if ($name == 'permissions') {
			if($this->_permissions) return $this->_permissions;
			$roleId = $this->roleId;
			
			if ($roleId) {
				$rolePermissions = new RolePermissions ();
				$permissions = $rolePermissions->GetPermissionsByIds ( null, $roleId );
				$permissions = $rolePermissions->ListPermissions ( $permissions, true, null, true );
				$this->_permissions = $permissions;
				return $this->_permissions;
			}
			return null;
		} 
		
		
		
		// the username they use to access their raw PgSQL database
		if ($name == 'databaseusername')
			return $this->is_db_user ? $this->username : 'simplelayers';
		if ($name == 'oldpassword')
			$name = 'password_old';
			// -- Seats BEGIN
		if ($name == 'seatname') {
			$seat = SeatAssignments::GetUserSeat($this->id,true);
			if($seat) return $seat['seatName'];
			return null;
			/*if ($this->seat == 1)
				return 'Staff';
			if ($this->seat == 2)
				return 'Executive';
			if ($this->seat == 3)
				return 'Power User';
			return 'Community';
			*/
		}
		if ($name == 'seat') {
			if(!$this->_seat)$this->_seat = SeatAssignments::GetUserSeat($this->id);
			if(!$this->_seat) return null;
			return $this->_seat['id'];
			/*if ($this->pre_social == 't' || $this->admin)
				return 3;
			return $this->getBestSeat ();
			*/
		}
		if ($name == 'community') {
			if ($this->admin)
				return false;
			if ($this->seat)
				return false;
			else
				return true;
		}
		/*if ($name == 'staff') {
			if ($this->admin)
				return false;
			if ($this->seat == 1)
				return true;
			else
				return false;
		}
		if ($name == 'executive') {
			if ($this->admin)
				return false;
			if ($this->seat == 2)
				return true;
			else
				return false;
		}
		if ($name == 'poweruser') {
			if ($this->admin)
				return true;
			if ($this->seat == 3)
				return true;
			else
				return false;
		}*/
		// -- Seats END
		
		// if we got here, it must be a direct attribute
		$value = $this->world->db->Execute ( "SELECT $name AS value FROM people WHERE id=?", array (
				$this->id 
		) );
		if (!$value)
			return null;
			// ADOdb's handling of booleans is not right; true and false are strings 't' and 'f', so this fudges it
		if ($name == 'visible' or $name == 'email_public')
			return $value->fields ['value'] == 't' ? true : false;
			
			// f ($name=='icon' && $value->fields['value'] == "") return "v2.5/user_media/usericon.png";
		if ($name == 'settings')
			return json_decode ( $value->fields ['value'], true );
		
		if ($name == "diskquota")
			return Units::MEGABYTE * ( int ) $value->fields ['value'];
		if(!$value->fields) {
			return null;
		}
		return $value->fields['value'];
	}
	private $_cachedRecord = null;
	private function cacheRecord() {
		$db = System::GetDB ( System::DB_ACCOUNT_SU );
		$this->_cachedRecord = $db->GetRow ( 'select * from people where id=?', array (
				$this->id 
		) );
		return $this->_cachedRecord;
	}
	public function getRecord() {
		if ($this->_cachedRecord)
			return $this->_cachedRecord;
		return $this->cacheRecord ();
	}
	public function setCachedRecord($record) {
		$this->_cachedRecord = $record;
	}
	
	/**
	 *
	 * @ignore
	 *
	 *
	 */
	function __set($name, $value) {
		
		// simple sanity check
		if (preg_match ( '/\W/', $name ))
			return false;
			// a few items cannot be set
		if ($name == 'id')
			return false;
		if($name == 'seat') {
			$seatAssignments = new SeatAssignments();
			$assignment = $seatAssignments->AssignSeat(array('userId'=>$this->id,'seatId'=>$value));
			return(!is_null($assignment));
		} 
		if(in_array($name,$this->unsettable)) return false;
		if ($name == 'settings')
			$value = json_encode ( $value );
			// sanitize things
		if ($name == 'visible' or $name == 'email_public')
			$value = $value ? 't' : 'f';
			// if they're setting a password, also do it for their underlying PostgreSQL account
		if ($name == 'password') {
			// update their DB account; it may not exst and this may fail
			// this->world->admindb->Execute(sprintf('ALTER USER "%s" PASSWORD %s', $this->databaseusername, $this->world->admindb->quote($value) ));
			// encrypt the password, then let the value be updated in the usual fashion
			$value = \Security::Encode_1way( $value, ( int ) $this->id );// System::Get()->encryptPasswordNew ( $value, ( int ) $this->id );
		}
		if ($name == 'oldpassword')
			$name = 'password_old';
			// if they're setting the accounttype (paid feature level) it's necessary to enable/disable their PostgreSQL account
		/*if ($name == 'accounttype') {
			// create/drop their PGDB account; it may or may not exist
			$username = $this->databaseusername;
			$worldname = WORLD_NAME;
			if ($value >= AccountTypes::PLATINUM) {
				// adding access: create account and grant login access
				$this->world->admindb->Execute ( "CREATE USER \"$username\" login nocreatedb nocreateuser" );
				$this->world->admindb->Execute ( "GRANT CONNECT ON database $worldname TO \"$username\"" );
				$this->world->admindb->Execute ( "GRANT USAGE ON schema public TO \"$username\"" );
				// adding access: change table ownership
				foreach ( $this->listLayers () as $layer )
					$layer->setDBOwnerToOwner ();
			} else {
				// dropping access: change table ownership
				foreach ( $this->listLayers () as $layer )
					$layer->setDBOwnerToDatabase ();
					// dropping access: revoke login access and delete account
				$this->world->admindb->Execute ( "REVOKE CONNECT ON database $worldname FROM \"$username\"" );
				$this->world->admindb->Execute ( "REVOKE USAGE ON schema public FROM \"$username\"" );
				$this->world->admindb->Execute ( "DROP USER \"$username\"" );
			}
			// then let the value be updated below in the usual fashion
			$this->world->db->Execute ( "UPDATE people SET $name=? WHERE id=?", array (
					$value,
					$this->id 
			) );
		}*/
		
		// expiration date, should also be reflected in the DB so they can't login to the DB after their DMI account expires
		if ($name == 'expirationdate') {
			if ($value) {
				$this->world->db->Execute ( "UPDATE people SET expirationdate=? WHERE id=?", array (
						$value,
						$this->id 
				) );
				$this->world->admindb->Execute ( "ALTER ROLE \"{$this->databaseusername}\" VALID UNTIL '$value'" );
			} else {
				$this->world->db->Execute ( "UPDATE people SET expirationdate=null WHERE id=?", array (
						$this->id 
				) );
				$this->world->admindb->Execute ( "ALTER ROLE \"{$this->databaseusername}\" VALID UNTIL 'infinity'" );
			}
			return;
		}
		
		// if we got here, we must be setting a more mundane attribute
		$this->world->db->Execute ( "UPDATE people SET $name=? WHERE id=?", array (
				$value,
				$this->id 
		) );
	}
	function SetLayerOwnership($id = null) {
		$username = $this->databaseusername;
		$worldname = WORLD_NAME;
		$this->world->admindb->Execute ( "CREATE USER \"$username\" login nocreatedb nocreateuser" );
		$this->world->admindb->Execute ( "GRANT CONNECT ON database $worldname TO \"$username\"" );
		$this->world->admindb->Execute ( "GRANT USAGE ON schema public TO \"$username\"" );
		// adding access: change table ownership
		if ($id) {
			$this->getLayerById ( $id )->setDBOwnerToOwner ();
			return;
		}
		foreach ( $this->listLayers () as $layer )
			$layer->setDBOwnerToOwner ();
	}
	
	/**
	 * The self-destruct button, to have the user gracefully delete themselves.
	 *
	 * @param string $comment
	 *        	Required. A comment about the user's deletion.
	 */
	function delete($comment=null) {
		// there must be a comment, or else we bail. this prevents mistakes
		if (is_null($comment))
			return false;
			// no deleting the admin, of course
		if ($this->admin)
			return false;

		$orgId = $this->organization->id;
		
			// delete our projects and our layers
		foreach ( $this->listProjects () as $project ) {
			$project->delete ();
			$this->removeProjectBookmarkById ( $project->id );
		}
		foreach ( $this->listLayers () as $layer ) {
			$layer->delete ();
			$this->removeLayerBookmarkById ( $layer->id );
		}
		
		// delete the PgSQL account's ability to login
		//$this->acounttype = AccountTypes::MIN;
		
		// delete our Person password entry
		$this->world->db->Execute ( 'DELETE FROM people WHERE id=?', array (
				$this->id 
		) );
		
		$orgMedia = new OrgMedia();
		$orgMedia->RemoveEmployee($orgId,$this->id);
		
		// log the event and we're done
		$this->world->logAccountActivity ( $this->username, 'delete', $comment );
		return true;
	}
	
	// ///
	// /// functions pertaining to their community and visibility
	// ///
	/**
	 * Check whether this Person can see a specified person.
	 *
	 * @param string $username
	 *        	A username.
	 * @return boolean True/false indicating whether the Person can see the target user.
	 */
	function canSeeUserByUsername($username) {
		$id = $this->world->getUserIdFromUsername ( $username );
		return $this->canSeeUserById ( $id );
	}
	
	/**
	 * Same as canSeeUserByUsername() except that it takes a target user's ID# instead of their username.
	 *
	 * @param integer $id
	 *        	A user's unique ID#.
	 * @return boolean True/false indicating whether the Person can see the target user.
	 */
	function canSeeUserById($id) {
		$p = $this->world->getPersonById ( $id );
		if ($p->visible)
			return true;
		if ($p->buddylist->isOnListById ( $this->id ))
			return true;
		return false;
	}
	
	/**
	 * Check whether this Person can be seen by the specified person.
	 *
	 * @param string $username
	 *        	A username.
	 * @return boolean True/false indicating whether the Person can be seen by the target user.
	 */
	function canBeSeenByUsername($username) {
		$id = $this->world->getUserIdFromUsername ( $username );
		return $this->canBeSeenById ( $p->id );
	}
	/**
	 * Same as canBeSeenByUsername() except that it takes a target user's ID# instead of their username.
	 *
	 * @param integer $id
	 *        	A user's unique ID#.
	 * @return boolean True/false indicating whether the Person can be seen by the target user.
	 */
	function canBeSeenById($id) {
		if ($this->visible)
			return true;
		if ($this->buddylist->isOnListById ( $id ))
			return true;
		return false;
	}
	
	// ///
	// /// functions pertaining to account expiration and account type
	// ///
	/**
	 * How many days left until this Person's account expires?
	 *
	 * @return integer, number of days left until the account expires. This will be negative if their account
	 *         has already expired. It will be false if the account does not have an expiration date.
	 */
	function daysUntilExpiration() {
		
		if (! $this->expirationdate)
			return false;
		$days = $this->world->db->Execute ( 'SELECT expirationdate-NOW()::DATE AS days FROM people WHERE id=?', array (
				$this->id 
		) );
		return $days->fields ['days'];
	}
	/**
	 * Extend an account's expiration date by X years.
	 *
	 * @param integer $years
	 *        	The number of years to add to their account's membership.
	 */
	function addYears($years) {
		$this->world->db->Execute ( 'UPDATE people SET expirationdate=expirationdate+(365.25*?)::int WHERE id=?', array (
				$years,
				$this->id 
		) );
	}
	/**
	 * Find out how much it would cost to upgrade this account to a new level.
	 *
	 * @param integer $newlevel
	 *        	The account level, one of the AccountTypes::* constants.
	 * @return string A float-like string, being the price of the upgrade.
	 */
	/*function priceUpgradeAccount($newlevel) {
		if ($newlevel <= $this->accounttype)
			return 0;
		$years = $this->daysUntilExpiration () / 365.0;
		$oldpriceperyear = $this->world->getAccountPrice ( $this->accountlevel );
		$newpriceperyear = $this->world->getAccountPrice ( $newlevel );
		$price = ($newpriceperyear - $oldpriceperyear) * $years;
		return sprintf ( "%.2f", $price );
	}*/
	/**
	 * Find out how much it would cost to add time to their account.
	 * Unlike simply multiplying the price per year times years, this one takes into account extra services such as disk storage.
	 *
	 * @param integer $years
	 *        	The number of years to add to their account's membership.
	 * @return string A float-like string, being the price of the upgrade.
	 */
	/*function priceAddTime($years) {
		// find the base price: their account price times the years
		$price_membership = $this->world->getAccountPrice ( $this->accounttype ) * $years;
		// how much extra disk space are they using?
		$price_diskusage = $years * $this->world->config ['storagepergb'] * ($this->diskquota - $this->world->config ['diskquota']) / KILOBYTE;
		// just add 'em up
		$price = $price_membership + $price_diskusage;
		return sprintf ( "%.2f", $price );
	}*/
	
	// ///
	// /// functions pertaining to database usage and disk usage
	// ///
	/**
	 * How much disk space is used by the user's vector layers in the database?
	 *
	 * @return integer The disk usage, in MiB.
	 */
	function diskUsageDB() {
		$b = 0.0;
		foreach ( $this->listLayers () as $layer )
			if ($layer->type == LayerTypes::VECTOR)
				$b += $layer->diskusage;
		return $b;
	}
	/**
	 * How much disk space is used by the user's raster layers?
	 *
	 * @return integer The disk usage, in MiB.
	 */
	function diskUsageFiles() {
		$b = 0.0;
		foreach ( $this->listLayers () as $layer )
			if ($layer->type == LayerTypes::RASTER)
				$b += $layer->diskusage;
		return $b;
	}
	/**
	 * What is this Person's maximum allowed storage?
	 *
	 * @return integer The Person's maximum allowed storage, in MiB.
	 */
	function diskUsageAllowed() {
		if ($this->admin)
			return 1099511627776;
		return $this->diskquota;
	}
	/**
	 * How much disk space does the Person have left?
	 *
	 * @return integer Their remaining disk quota, in MiB.
	 */
	function diskUsageRemaining() {
		if ($this->admin)
			return INF;
		$orgId = $this->getOrganization(true);
		$license = new License();
		$license = $license->Get($orgId);
		if($license['data']['max_space']=='') {
		    return  INF; 
		}
		$orgSpaceRemaining = $license['data']['max_space']*pow(1024,3);
		$orgSpaceRemaining-= ($this->diskUsageDB () + $this->diskUsageFiles ());
		return $orgSpaceRemaining;
		//return $this->diskUsageAllowed () - ;
	}
	/**
	 * Calculate the price to up their disk quota.
	 *
	 * @param integer $gigabytes
	 *        	Number of gigabytes to add to their quota.
	 * @return float Price, in dollars, to upgrade their disk storage for the rest of their membership.
	 */
	function priceStorageUpgrade($gigabytes) {
		if ($gigabytes <= 0)
			return 0.0;
		$pricepergigabyteperyear = $this->world->config ['storagepergb'];
		$years = $this->daysUntilExpiration () / 365.0;
		$price = $pricepergigabyteperyear * $years * $gigabytes;
		return sprintf ( "%.2f", $price );
	}
	
	// ///
	// /// methods pertaining to layers and projects
	// ///
	/**
	 * Get a list of all the Layers that this person owns.
	 *
	 * @param string $orderby
	 *        	The attribute to sort the list by. Optional, defaults to "name".
	 * @return array A list of Layer objects.
	 */
	function listLayers($orderby = 'name') {
		if (preg_match ( '/\W/', $orderby ))
			$orderby = 'name';
		$layers = $this->world->db->Execute ( "SELECT id FROM layers WHERE owner=? ORDER BY $orderby", array (
				$this->id 
		) )->getRows ();
                $layers = array_map ( function($a) { return $a["id"];}, $layers );
		$layers = array_map ( array (
				$this->world,
				'getLayerById' 
		), $layers );
		return $layers;
	}
	
	function countLayers() {
		$db = System::GetDB(System::DB_ACCOUNT_SU);
		return $db->GetOne( "SELECT COUNT (*) as count FROM layers WHERE owner=?",array(intval($this->id)));
	}
	
	
	/**
	 * Fetch a Layer that this Person owns, using the Layer's name.
	 *
	 * @param string $name
	 *        	The name of the layer to fetch.
	 * @return Layer A Layer object, or else false if there was no such Layer.
	 */
	function getLayerByName($name, $type = null) {
		$query = 'SELECT id FROM layers WHERE name=? AND owner=?';
		if (! is_null ( $type ))
			$query .= ' AND type=?';
		$queryData = array (
				$name,
				$this->id 
		);
		if (! is_null ( $type ))
			array_push ( $queryData, $type );
		
		$id = $this->world->db->GetOne ( $query, $queryData );
		return System::Get ()->getLayerById ( $id );
	}
	
	/**
	 * Fetch a layer record by its id, a wrapper for the World equivalent function.
	 *
	 * @param Mixed $id
	 *        	int or string
	 * @return Layer
	 *
	 */
	function getLayerById($id) {
		// $id = $this->world->db->GetOne('SELECT id FROM layers WHERE id=? AND owner=?', array($id,$this->id) );
		return $this->world->getLayerById ( $id );
	}
	/**
	 * Get a list of all the Projects that this person owns.
	 *
	 * @param string $orderby
	 *        	The attribute to sort the list by. Optional, defaults to "name".
	 * @return array A list of Pproject objects.
	 */
	function listProjects($orderby = 'name') {
		if (preg_match ( '/\W/', $orderby ))
			$orderby = 'name';
		$projects = $this->world->db->Execute ( "SELECT id FROM projects WHERE owner=?  ORDER BY $orderby", array (
				$this->id 
		) );
		$projects = $projects->getRows ();
                $projects = array_map ( function($a){ return $a["id"]; }, $projects );
		$projects = array_map ( array (
				$this->world,
				'getprojectById' 
		), $projects );
		return $projects;
	}
	/**
	 * Fetch a Project that this Person owns, using the Project's name.
	 *
	 * @param string $name
	 *        	The name of the Project to fetch.
	 * @return Project A Project object, or else false if there was no such Project.
	 */
	function getProjectByName($name) {
		$id = $this->world->db->Execute ( 'SELECT id FROM projects WHERE name=? AND owner=?', array (
				$name,
				$this->id 
		) );
		return $this->world->getprojectById ( $id->fields ['id'] );
	}
	function getProjectById($id) {
		$id = $this->world->db->Execute ( 'SELECT id FROM projects WHERE id=? AND owner=?', array (
				$id,
				$this->id 
		) );
		return $this->world->getprojectById ( $id->fields ['id'] );
	}
	/**
	 * Create a new Layer owned by this Person.
	 *
	 * @param
	 *        	string The name of the new Layer. The user must not already have a Layer by this same name.
	 * @param
	 *        	integer The layer's type, one of the LayerTypes::* defines.
	 * @return Layer A Layer object.
	 */
	function createLayer($name, $type, $restrictName = false, $ownerId = null) {
		
		$owner = is_null ( $ownerId ) ? $this->id : $ownerId;
		$adder = $this->id;
		$includeClass = in_array($this->type, array(LayerTypes::VECTOR,LayerTypes::RELATIONAL,LayerTypes::ODBC ));
                $newName = $name;
                if(strlen($name) > 30) {
                    $newName = substr($newName,0,30);
                }
                $layer = Layer::CreateLayer($newName, $type, $owner, $adder,$includeClass);
		$layer->description = $name;
		/*// f($this->community && count($this->listLayers()) >= 3) return false;
		$extant = $this->world->db->Execute ( "select * from layers where owner=? and name=? and type=?", array (
				$this->id,
				$name,
				$type 
		) );
		
		$owner = is_null ( $ownerId ) ? $this->id : $ownerId;
		$adder = $this->id;
		
		if (($extant->RecordCount () == 0) || ! $restrictName) {
			$id = $this->world->db->GetOne ( 'INSERT INTO layers (owner,name,type,adder) VALUES (?,?,?,?) RETURNING id', array (
					$owner,
					$name,
					$type,
					$adder 
			) );
		}
		
		$layer = $this->getLayerById ( $id );
		*/
		
		
		
		return $layer;
	}
	
	/**
	 * Create a new Project owned by this Person.
	 *
	 * @param
	 *        	string The name of the new Project.
	 * @return Project A Project object.
	 */
	function createProject($name, $ownerId = null) {
		$owner = is_null ( $ownerId ) ? $this->id : $ownerId;
		$adder = $this->id;
		$db = \System::GetDB();
		$this->world->db->Execute ( 'INSERT INTO projects (owner,name,adder) VALUES (?,?,?)', array (
				$owner,
				$name,
				$adder 
		) );
                $lastId = $db->insert_Id();
		$project = Project::Get($lastId);
                $project->SetDefaults();
		return $project;
	}
	/**
	 * Create a new Project owned by this Person, copying the layer content from an existing Project.
	 *
	 * @param
	 *        	string The name of the new Project; Optional, defaults to the original Project's name.
	 * @return Project A Project object, or null if the original was not found.
	 */
	function createCopyOfProject($projectid, $name = null) {
		// fetch the old Project, create the new one and fill in the basic attributes
		$old = $this->world->getProjectById ( $projectid );
		if (! $old)
			return null;
		if (! $name)
			$name = $old->name;
		$new = $this->createProject ( $name );
		$new->bbox = $old->bbox;
		$new->windowsize = $old->windowsize;
		$new->description = $old->description;
		$new->tags = $old->tags;
		// go through the old one's Layer entries and copy them into the new one
		foreach ( $old->getLayers () as $oldprojectlayer ) {
			$newprojectlayer = $new->addLayerById ( $oldprojectlayer->layer->id );
			$newprojectlayer->whoadded = $this->id;
			$newprojectlayer->opacity = $oldprojectlayer->opacity;
			$newprojectlayer->z = $oldprojectlayer->z;
			$newprojectlayer->colorschemetype = $oldprojectlayer->colorschemetype;
			$newprojectlayer->labelitem = $oldprojectlayer->labelitem;
			$newprojectlayer->on_by_default = $oldprojectlayer->on_by_default;
			$newprojectlayer->labels_on = $oldprojectlayer->labels_on;
			$newprojectlayer->tooltip = $oldprojectlayer->tooltip;
			$oldprojectlayer->colorscheme->copy($newprojectlayer,'project_layer_colors');
			/* // if this is a vector layer, copy the color scheme
			if ($oldprojectlayer->layer->type == LayerTypes::VECTOR) {
				foreach ( $oldprojectlayer->colorscheme->getAllEntries () as $oldcolorschemeentry ) {
					$newcolorschemeentry = $newprojectlayer->colorscheme->addEntry ();
					$newcolorschemeentry->priority = $oldcolorschemeentry->priority;
					$newcolorschemeentry->criteria1 = $oldcolorschemeentry->criteria1;
					$newcolorschemeentry->criteria2 = $oldcolorschemeentry->criteria2;
					$newcolorschemeentry->criteria3 = $oldcolorschemeentry->criteria3;
					$newcolorschemeentry->fill_color = $oldcolorschemeentry->fill_color;
					$newcolorschemeentry->stroke_color = $oldcolorschemeentry->stroke_color;
					$newcolorschemeentry->description = $oldcolorschemeentry->description;
					$newcolorschemeentry->symbol = $oldcolorschemeentry->symbol;
					$newcolorschemeentry->symbol_size = $oldcolorschemeentry->symbol_size;
				}
			}
			*/
		}
		// done
		return $new;
	}
	/**
	 * Create a new Layer owned by this Person, copying the content from an existing Layer.
	 *
	 * @param
	 *        	string The name of the new Layer; Optional, defaults to the original Layer's name.
	 * @return Layer A Layer object, or null if the original was not found.
	 */
	function createCopyOfLayer($layerid, $name = null) {
		// fetch the old Layer and its name
		$old = $this->world->getLayerById ( $layerid );
		if (!$old || is_null($old)) {
			return null;
                }
		if (!$name) {
                    $name = $old->name;
                }
			// depending on the data type, create it and then populate the data
		switch ($old->type) {
			case LayerTypes::WMS :
				// copying WMS data is simple; just copy the URL of the WMS server
				$new = $this->createLayer ( $name, LayerTypes::WMS );
				$new->url = $old->url;
				break;
			case LayerTypes::RASTER :
				// copying raster data is also easy; just copy the image file
				$new = $this->createLayer ( $name, LayerTypes::RASTER );
				copy ( $old->url, $new->url );
				break;
			case LayerTypes::RELATIONAL :
				$new = $this->createLayer ( $name, LayerTypes::VECTOR );
				$filename = md5 ( mt_rand () . mt_rand () );
				$shapefile = pgsql2shp ( $this->world, $old->url, $filename, true );
				$shapefile = $shapefile [0];
				shp2pgsql ( $this->world, $shapefile, $new->url, true );
				break;
			case LayerTypes::VECTOR :
				$new = $this->createLayer ( $name, LayerTypes::VECTOR );
				pgsql2pgsql ( $this->world, $old, $new, true );
				break;
			case LayerTypes::ODBC :
				// create the new vector layer, and set up its indexes etc.
				$new = $this->createLayer ( $name, LayerTypes::VECTOR );
				$this->world->db->Execute ( "CREATE TABLE {$new->url} (gid serial)" );
				$this->world->db->Execute ( "SELECT AddGeometryColumn('','{$new->url}','the_geom',4326,'POINT',2)" );
				$this->world->db->Execute ( "CREATE INDEX {$new->url}_index_the_geom ON $new->url USING GIST (the_geom)" );
				$this->world->db->Execute ( "CREATE INDEX {$new->url}_index_oid ON $new->url (oid)" );
				
				// populate the vector layer's columns
				foreach ( $old->getAttributes () as $colname => $type ) {
					if ($colname == 'gid')
						continue;
					if ($colname == 'id')
						continue;
					if ($colname == 'the_geom')
						continue;
					$new->addAttribute ( $colname, $type );
				}
				
				// connect to the ODBC...
				$odbcinfo = $old->url;
				switch ($odbcinfo->driver) {
					case ODBCUtil::MYSQL :
						$db = NewADOConnection ( "mysql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC );
						$records = $db->Execute ( "SELECT * FROM `{$odbcinfo->table}`" );
						break;
					case ODBCUtil::PGSQL :
						$db = NewADOConnection ( "postgres://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC );
						$records = $db->Execute ( "SELECT * FROM \"{$odbcinfo->table}\"" );
						break;
					case ODBCUtil::MSSQL :
						list ( $odbc, $odbcini, $freetdsconf ) = $this->world->connectToODBC ( $odbcinfo, 'NOCONNECT' );
						$db = NewADOConnection ( "mssql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@dsn/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC );
						$records = $db->Execute ( "SELECT * FROM {$odbcinfo->table}" );
						break;
				}
				
				// iterate over the ODBC records, copying them into the new vector table with a point geometry
				while ( ! $records->EOF ) {
					$record = $records->fields;
					$record ['wkt_geom'] = sprintf ( "POINT(%f %f)", $record [$odbcinfo->loncolumn], $record [$odbcinfo->latcolumn] );
					$new->insertRecord ( $record );
					$records->MoveNext ();
				}
				
				// done copying the ODBC layer to a new Vector layer
				$new->setDBOwnerToOwner ();
				break;
		}
		
		// populate some more simple attributes
		@$description = $old->description;
                @$new->description = is_null($description) ? '' : $old->description;
		@$tags = $old->tags;
                @$new->tags = is_null($tags) ? '' : $old->tags;
		if(!!$old->colorscheme) {
                    $old->colorscheme->copy($new,'layer_default_colors');
                }
                // if this is a vector layer, copy the color scheme
		/* if ($old->type == LayerTypes::VECTOR) {
			$new->colorschemetype = $old->colorschemetype;
			foreach ( $old->colorscheme->getAllEntries () as $oldcolorschemeentry ) {
				$newcolorschemeentry = $new->colorscheme->addEntry ();
				$newcolorschemeentry->priority = $oldcolorschemeentry->priority;
				$newcolorschemeentry->criteria1 = $oldcolorschemeentry->criteria1;
				$newcolorschemeentry->criteria2 = $oldcolorschemeentry->criteria2;
				$newcolorschemeentry->criteria3 = $oldcolorschemeentry->criteria3;
				$newcolorschemeentry->fill_color = $oldcolorschemeentry->fill_color;
				$newcolorschemeentry->stroke_color = $oldcolorschemeentry->stroke_color;
				$newcolorschemeentry->description = $oldcolorschemeentry->description;
				$newcolorschemeentry->symbol = $oldcolorschemeentry->symbol;
				$newcolorschemeentry->symbol_size = $oldcolorschemeentry->symbol_size;
			}
		}
		*/
		// done
		return $new;
	}
	/**
	 * Given a desired name for a layer (be it existing or just-created), find out whether the name is
	 * already in use, and mangle the name to ensure that it is unique.
	 * This method does not actually rename
	 * the Project; it just checks the name's uniqueness.
	 *
	 * @param string $name
	 *        	The desired name for the Layer.
	 * @param
	 *        	Project The Project that would be renamed; this is so the name isn't mistaken as "already in use" if it's in use by this selfsame Project. Optional; defeaults to no Project at all (useful in case where a brand-new layer will be created).
	 * @return string A unique name for the Layer, as close as possible to the desired name.
	 */
	function uniqueProjectName($name, $project = null) {
		$already = $this->getProjectByName ( $name );
		if (! $already)
			true; // no such project? easy, the name's available
		elseif ($project and $already->id == $project->id)
			true; // the project is this selfsame project? no problem keeping the name then!
		else
			$name .= ' ' . md5 ( microtime () . mt_rand () ); // okay, we had to mangle the filename
				                                                  // truncate it and return it
		$name = substr ( $name, 0, 50 );
		return $name;
	}
	/**
	 * Given a desired name for a layer (be it existing or just-created), find out whether the name is
	 * already in use, and mangle the name to ensure that it is unique.
	 * This method does not actually rename
	 * the Layer; it just checks the name's uniqueness.
	 *
	 * @param string $name
	 *        	The desired name for the Project.
	 * @param
	 *        	Layer The Layer that would be renamed; this is so the name isn't mistaken as "already in use" if it's in use by this selfsame Layer. Optional; defeaults to no Layer at all (useful in case where a brand-new layer will be created).
	 * @return string A unique name for the Project, as close as possible to the desired name.
	 */
	function uniqueLayerName($name, $layer = null) {
		// $already = $this->getLayerByName($name);
		// if (!$already) true; // no such layer? easy, the name's available
		// elseif ($layer and $already->id==$layer->id) true; // the layer is this selfsame layer? no problem keeping the name then!
		// else $name .= ' '.md5(microtime().mt_rand()); // okay, we had to mangle the filename
		// truncate it and return it
		// $name = substr($name,0,50);
		return $name;
	}
	
	/**
	 * See if layer name exists already or not.
	 *
	 * Return true if exists, false if not.
	 * Will be used in layer format import statements to determine if user wants to overwrite an existing layer.
	 */
	function layerExists($name, $layer = null) {
		// print javascriptalert("$name");
		$already = $this->getLayerByName ( $name );
		if ($already) {
			$layerexists = true;
		} elseif (! $already) {
			$layerexists = false;
		}
		return $layerexists;
	}
	// ///
	// /// methods for manipulating layer bookmarks
	// ///
	/**
	 * Return a list of bookmarked layers.
	 *
	 * @return array A list of Layer objects.
	 */
	function getLayerBookmarks() {
		$bms = $this->getLayerBookmarkIds ();
		$bms = array_map ( array (
				$this->world,
				'getLayerById' 
		), $bms );
		return $bms;
	}
	/**
	 * Like getLayerBookmarks() except that it only returns the ID#s of the layers.
	 *
	 * @return array A list of integers, ID#s of layers that are bookmarked.
	 */
	function getLayerBookmarkIds() {
		$bms = $this->world->db->Execute ( 'SELECT layer FROM layer_bookmarks WHERE owner=?', array (
				$this->id 
		) )->getRows ();
                $bms = array_map ( function ( $a){ return $a["layer"]; }, $bms );
		return $bms;
	}
	/**
	 * Is the specified Layer bookmarked by this Person?
	 *
	 * @param integer $id
	 *        	The unique ID# of the Layer.
	 * @return boolean True/false indicating whether the Person has the specified Layer bookmarked.
	 */
	function isLayerBookmarkedById($id) {
		$id = $this->world->db->Execute ( 'SELECT id FROM layer_bookmarks WHERE layer=? AND owner=?', array (
				$id,
				$this->id 
		) );
		return ! $id->EOF;
	}
	/**
	 * Same as isLayerBookmarkedById(), except that it takes the layer owner's username and the layer's name.
	 */
	function isLayerBookmarkedByName($layerowner, $layername) {
		$p = $this->world->getPersonByUsername ( $layerowner );
		if (! $p)
			return false;
		$l = $p->getLayerByName ( $layername );
		if (! $l)
			return false;
		return $this->isLayerBookmarkedById ( $l->id );
	}
	/**
	 * Add a new Layer to the Person's bookmark list, using the Layer's unique ID#.
	 *
	 * @param integer $id
	 *        	The unique ID# of the Layer to add.
	 */
	function addLayerBookmarkById($id) {
		$this->world->db->Execute ( 'INSERT INTO layer_bookmarks (owner,layer) VALUES (?,?)', array (
				$this->id,
				$id 
		) );
	}
	/**
	 * Add a new Layer to the Person's bookmark list, using the Layer's owner and name.
	 *
	 * @param string $layerowner
	 *        	The layer owner's username.
	 * @param string $layername
	 *        	The layer's name.
	 */
	function addLayerBookmarkByName($layerowner, $layername) {
		$p = $this->world->getPersonByUsername ( $layerowner );
		if (! $p)
			return false;
		$l = $p->getLayerByName ( $layername );
		if (! $l)
			return false;
		return $this->addLayerBookmarkById ( $l->id );
	}
	/**
	 * Remove a Project from the Person's bookmark list, using the Project's unique ID#.
	 *
	 * @param integer $id
	 *        	The unique ID# of the Project.
	 */
	function removeLayerBookmarkById($id) {
		$this->world->db->Execute ( 'DELETE FROM layer_bookmarks WHERE owner=? AND layer=?', array (
				$this->id,
				$id 
		) );
	}
	/**
	 * Remove a Project from the Person's bookmark list, using the Project owner's username and the Project's name.
	 *
	 * @param string $projectowner
	 *        	The project owner's username.
	 * @param string $projectname
	 *        	The project's name.
	 */
	function removeLayerBookmarkByName($layerowner, $layername) {
		$p = $this->world->getPersonByUsername ( $layerowner );
		if (! $p)
			return false;
		$l = $p->getLayerByName ( $layername );
		return $this->removeLayerBookmarkId ( $l->id );
	}
	
	// ///
	// /// methods for manipulating project bookmarks
	// ///
	/**
	 * Return a list of bookmarked projects.
	 *
	 * @return array A list of Project objects.
	 */
	function getProjectBookmarks() {
		$bms = $this->getProjectBookmarkIds ();
		$bms = array_map ( array (
				$this->world,
				'getProjectById' 
		), $bms );
		return $bms;
	}
	/**
	 * Like getProjectBookmarks() except that it only returns the ID#s of the projects.
	 *
	 * @return array A list of integers, ID#s of projects that are bookmarked.
	 */
	function getProjectBookmarkIds() {
		$bms = $this->world->db->Execute ( 'SELECT project FROM project_bookmarks WHERE owner=?', array (
				$this->id 
		) )->getRows ();
                $bms = array_map( function($a) {return $a["project"];}, $bms );
		return $bms;
	}
	/**
	 * Is the specified Project bookmarked by this Person?
	 *
	 * @param integer $id
	 *        	The unique ID# of the Project.
	 * @return boolean True/false indicating whether the Person has the specified Project bookmarked.
	 */
	function isProjectBookmarkedById($id) {
		$id = $this->world->db->Execute ( 'SELECT id FROM project_bookmarks WHERE project=? AND owner=?', array (
				$id,
				$this->id 
		) );
		return !$id->EOF;
	}
	/**
	 * Same as isProjectBookmarkedById(), except that it takes the project owner's username and the project's name.
	 */
	function isProjectBookmarkedByName($projectowner, $projectname) {
		$p = $this->world->getPersonByUsername ( $projectowner );
		if (! $p)
			return false;
		$l = $p->getprojectByName ( $projectname );
		if (! $l)
			return false;
		return $this->isprojectBookmarkedById ( $l->id );
	}
	/**
	 * Add a new Project to the Person's bookmark list, using the Project's unique ID#.
	 *
	 * @param integer $id
	 *        	The unique ID# of the Project to add.
	 */
	function addProjectBookmarkById($id) {
		$this->world->db->Execute ( 'INSERT INTO project_bookmarks (owner,project) VALUES (?,?)', array (
				$this->id,
				$id 
		) );
	}
	/**
	 * Add a new Project to the Person's bookmark list, using the Project's owner and name.
	 *
	 * @param string $projectowner
	 *        	The project owner's username.
	 * @param string $projectname
	 *        	The project's name.
	 */
	function addProjectBookmarkByName($projectowner, $projectname) {
		$p = $this->world->getPersonByUsername ( $projectowner );
		if (! $p)
			return false;
		$l = $p->getProjectByName ( $projectname );
		if (! $l)
			return false;
		return $this->addProjectBookmarkById ( $l->id );
	}
	/**
	 * Remove a Project from the Person's bookmark list, using the Project's unique ID#.
	 *
	 * @param integer $id
	 *        	The unique ID# of the Project.
	 */
	function removeProjectBookmarkById($id) {
		$this->world->db->Execute ( 'DELETE FROM project_bookmarks WHERE owner=? AND project=?', array (
				$this->id,
				$id 
		) );
	}
	/**
	 * Remove a Project from the Person's bookmark list, using the Project owner's username and the Project's name.
	 *
	 * @param string $projectowner
	 *        	The project owner's username.
	 * @param string $projectname
	 *        	The project's name.
	 */
	function removeProjectBookmarkByName($projectowner, $projectname) {
		$p = $this->world->getPersonByUsername ( $projectowner );
		if (! $p)
			return false;
		$l = $p->getProjectByName ( $projectname );
		if (! $l)
			return false;
		return $this->removeProjectBookmarkById ( $l->id );
	}
	
	// ///
	// /// methods pertaining to SocialGroup memberships
	// ///
	/**
	 * Fetch a list of SocialGroups where this Person is a member or moderator.
	 *
	 * @param bool/null $moderator
	 *        	Filter the results: groups where they're the moderator (true),
	 *        	groups where they're not the moderator (false), or groups where they're member or moderator (null).
	 *        	Default is null, to not filter by moderatorship.
	 * @return array An array of SocialGroup objects, all of which this person is a member.
	 */
	function listGroups($moderator = null) {
		// fetch groups where they're a member
		$groups1 = $this->world->db->Execute ( 'SELECT group_id FROM groups_members JOIN groups ON group_id=groups.id WHERE person_id=? AND actor=1 ORDER BY title', array (
				$this->id 
		) )->getRows ();
                $groups1 = array_map ( function($a) { return $a['group_id'];}, $groups1 );
		// fetch groups where they're a moderator
		$groups2 = $this->world->db->Execute ( 'SELECT group_id FROM groups_members JOIN groups ON group_id=groups.id WHERE person_id=? AND actor=5 ORDER BY title', array (
				$this->id 
		) )->getRows ();
                $groups2 = array_map ( function ($a) {return $a["id"];}, $groups2 );
		// compile the IDs together for whichever set is requested
		if ($moderator === null)
			$groups = array_merge ( $groups1, $groups2 );
		elseif ($moderator)
			$groups = $groups2;
		else
			$groups = $groups1;
		if (! sizeof ( $groups ))
			return $groups;
		$groups = array_map ( array (
				$this->world,
				'getGroupById' 
		), $groups );
		return $groups;
	}
	public function getOrganization($idOnly = false) {
		$query = "SELECT org_id FROM groups WHERE id = ANY(SELECT group_id FROM groups_members WHERE person_id = $this->id)";
		
		$orgid = $this->world->db->GetOne ( 'SELECT MIN(id) FROM organizations WHERE owner=?', array (
				$this->id 
		) );
		
		if (! $orgid) {
			$orgid = $this->world->db->GetOne ( 'SELECT org_id FROM groups WHERE id = ANY(SELECT group_id FROM groups_members WHERE person_id = ?) AND org_id IS NOT NULL LIMIT 1', array (
					$this->id 
			) );
		}
		
		if ($orgid) {
			return ( $idOnly === true) ? $orgid : $this->world->getOrganizationById ( $orgid );
		}
		return false;
	}
	function sendMessage($message, $to) {
		$this->world->db->Execute ( 'INSERT INTO messages ("from", "to", "message") VALUES (?,?,?)', array (
				$this->id,
				$to,
				$message 
		) );
	}
	function getMessages($from = false, $limit = 30, $offset = 0) {
		if ($from)
			$details = array (
					$this->id,
					$this->id,
					$offset,
					$from,
					$from,
					$limit 
			);
		else
			$details = array (
					$this->id,
					$this->id,
					$offset,
					$limit 
			);
		$messages = $this->world->db->Execute ( 'SELECT * FROM messages WHERE ("to"=? OR "from"=?) AND id' . ($offset > 0 ? '<' : '>') . '?' . ($from ? ' AND ("to"=? OR "from"=?)' : '') . ' ORDER BY "sent" DESC LIMIT ?', $details )->getRows ();
		foreach ( $messages as &$message ) {
			$message ["sent"] = date ( 'D, M jS, g:ia', strtotime ( $message ["sent"] ) );
			if ($message ["from"] == $this->id) {
				if ($message ["read"] == 'f')
					$message ["read"] = "Not Yet Viewed";
				else
					$message ["read"] = "Viewed";
			} else {
				if ($message ["read"] == 'f')
					$message ["read"] = "New";
				else
					$message ["read"] = "";
			}
		}
		return $messages;
	}
	function markRead($from = false, $latest = 0) {
		$return = Array ();
		$details = array (
				$this->id 
		);
		if ($from) {
			$details = array (
					$this->id,
					$from,
					$latest 
			);
			$return = $this->world->db->Execute ( 'SELECT * FROM messages WHERE ("to"=? AND "from"=?) AND id>? AND "read"=false ORDER BY "sent" ASC', $details )->getRows ();
			$details = array (
					$this->id,
					$from 
			);
		}
		$this->world->db->Execute ( 'UPDATE messages SET "read"=true WHERE "read"=false AND "to"=?' . ($from ? ' AND "from"=?' : ''), $details );
		foreach ( $return as &$message ) {
			$message ["sent"] = date ( 'D, M jS, g:ia', strtotime ( $message ["sent"] ) );
			if ($message ["from"] == $this->id) {
				if ($message ["read"] == 'f')
					$message ["read"] = "Not Yet Viewed";
				else
					$message ["read"] = "Viewed";
			} else {
				if ($message ["read"] == 'f')
					$message ["read"] = "New";
				else
					$message ["read"] = "";
			}
		}
		return $return;
	}
	function getUnreadMessages() {
		$messages = $this->world->db->Execute ( 'SELECT count("from"), "from", (SELECT sent FROM messages WHERE m."from"="from" AND "to"=? ORDER BY "sent" DESC LIMIT 1) AS sent, (SELECT message FROM messages WHERE m."from"="from" AND "to"=? ORDER BY "sent" DESC LIMIT 1) AS message FROM messages AS m WHERE "to"=? AND read=false GROUP BY "from"', array (
				$this->id,
				$this->id,
				$this->id 
		) )->getRows ();
		foreach ( $messages as &$message ) {
			$message ["sent"] = date ( 'D, M jS, g:ia', strtotime ( $message ["sent"] ) );
			$message ["from"] = $this->world->getPersonById ( $message ["from"] );
			if (strlen ( $message ["message"] ) > 20)
				$message ["message"] = substr ( $message ["message"], 0, 17 ) . "...";
			$message ["message"] = htmlspecialchars ( '"' . $message ["message"] . '"' );
		}
		return $messages;
	}
	function countAllUnreadMessages() {
		return $this->world->db->Execute ( 'SELECT count(id) AS messages FROM messages WHERE "to"=? AND "read"=?', array (
				$this->id,
				'f' 
		) )->fields ['messages'];
	}
	function countUnreadMessages($from = false) {
		return $this->world->db->Execute ( 'SELECT "from", count(id) AS messages FROM messages WHERE "to"=? AND "read"=?' . ($from ? ' AND "from"=?' : '') . ' GROUP BY "from"', ($from ? array (
				$this->id,
				'f',
				$from 
		) : array (
				$this->id,
				'f' 
		)) )->getRows ();
	}
	function countMessages($from = false) {
		return $this->world->db->Execute ( 'SELECT "from", count(id)+(SELECT count(id) FROM messages WHERE "from"=? AND "to"=m."from") AS messages FROM messages AS m WHERE "to"=?' . ($from ? ' AND "from"=?' : '') . ' GROUP BY "from"', ($from ? array (
				$this->id,
				$this->id,
				$from 
		) : array (
				$this->id,
				$this->id 
		)) )->getRows ();
	}
	/*
	 * 1 - Discussion Reply 2 - Map Shared 3 - Layer Shared 4 - Map Edited 5 - Layer Edited 6 - New Discussion 7 - Contact Follows 8 - Updated Layer Data 9 - Group Invites/Joins 10 - Map Comment 11 - Layer Comment 12 - Layer Ownership Transfer
	 */
	function notify($actor, $action, $subjectName, $subjectId, $redirect, $subjectType) {
		if ($actor == $this->id)
			return false;
		$results = $this->world->db->GetRow ( 'SELECT * FROM notifications WHERE "user"=? AND action=? AND subject_id=? AND subject_type=?', array (
				$this->id,
				$action,
				$subjectId,
				$subjectType 
		) );
		if ($results) {
			$actors = json_decode ( $results ["actors"] );
			if (! in_array ( $actor, $actors ))
				$actors [] = $actor;
			$this->world->db->Execute ( 'UPDATE notifications SET last_access=now(), actors=? WHERE "user"=? AND action=? AND subject_id=? AND subject_type=?', array (
					json_encode ( $actors ),
					$this->id,
					$action,
					$subjectId,
					$subjectType 
			) );
		} else {
			$this->world->db->Execute ( 'INSERT INTO notifications ("user",actors,action,subject,subject_id,subject_redirect,subject_type) VALUES (?,?,?,?,?,?,?)', Array (
					$this->id,
					json_encode ( Array (
							$actor 
					) ),
					$action,
					$subjectName,
					$subjectId,
					$redirect,
					$subjectType 
			) );
		}
		
	}
	function clearNotifications($notification = false) {
		$this->world->db->Execute ( 'DELETE FROM notifications WHERE "user"=?' . ($notification ? ' AND id=?' : ''), ($notification ? array (
				$this->id,
				$notification 
		) : array (
				$this->id 
		)) );
	}
	function getNotifications($after = false) {
		$return = $this->world->db->Execute ( 'SELECT * FROM notifications WHERE "user"=?' . ($after ? ' AND id>?' : '') . ' ORDER BY last_access', ($after ? array (
				$this->id,
				$after 
		) : array (
				$this->id 
		)) )->getRows ();
		foreach ( $return as &$row ) {
			$actors = json_decode ( $row ["actors"] );
			$row ["actors"] = Array ();
			foreach ( $actors as $id ) {
				$row ["actors"] [] = $this->world->getPersonById ( $id );
			}
		}
		return $return;
	}
	function getNotification($id) {
		return $this->world->db->GetRow ( 'SELECT * FROM notifications WHERE "id"=? AND "user"=?', array (
				$id,
				$this->id 
		) );
	}
	function getAllNotificationsJson($after = false) {
		$notifications = $this->getNotifications ( $after );
		$format = Array ();
		foreach ( $notifications as $notify ) {
			$temp = Array (
					"id" => $notify ["id"],
					"action" => $notify ["action"],
					"subject" => $notify ["subject"],
					"subject_id" => $notify ["subject_id"],
					"subject_redirect" => $notify ["subject_redirect"],
					"last_access" => $notify ["last_access"],
					"subject_type" => $notify ["subject_type"] 
			);
			$temp ["actors"] = Array ();
			foreach ( $notify ["actors"] as $actor ) {
				$temp ["actors"] [] = Array (
						"realname" => $actor->realname,
						"id" => $actor->id 
				);
			}
			$format [] = $temp;
		}
		return json_encode ( $format );
	}
	function getPData($name, $isBoolean = false, $default = null) {
		$settings = $this->settings;
		if (isset ( $settings [$name] )) {
			if ($isBoolean) {
				if ($settings [$name] == 'f')
					$settings [$name] = false;
				elseif ($settings [$name])
					$settings [$name] = true;
				else
					$settings [$name] = false;
			}
			return $settings [$name];
		}
		return $default;
	}
	function setPData($name, $value) {
		$settings = $this->settings;
		$settings [$name] = $value;
		$this->settings = $settings;
		return $value;
	}
	function getBestSeat() {
		$return = $this->world->db->GetOne ( 'SELECT seat FROM groups_members WHERE person_id=? ORDER BY seat LIMIT 1', array (
				$this->id 
		) );
		if ($return == null)
			$return = 0;
		return $return;
	}
	
	public static function Get($id) {
            $sys = System::Get();
	    return new Person($sys,$id);
	}
}
?>
