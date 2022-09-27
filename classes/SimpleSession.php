<?php

use auth\Creds;
use auth\Auth;
use model\SeatAssignments;
use model\RolePermissions;
use model\Seats;
use utils\ParamUtil;

// setup a variable
class SimpleSession implements ArrayAccess {

    // test
    const UPDATE_SET = '$set';
    const UPDATE_UNSET = '$unset';
    const COOKIE = 'SLSESSID';
    const METHOD_INVALID = - 1;
    const METHOD_UNKNOWN = 0;
    const METHOD_COOKIE = 1;
    const METHOD_TOKEN = 2;
    const STATE_SESS_OK = 1;
    const STATE_SESS_NONE = 0;
    const STATE_SESS_EXPIRED = -1;
    const STATE_SESS_EXISTS = -2;

    private $mongo = null;
    private $db = null;
    private $collection = null;
    private $sessionId = null;
    public $session = null;
    private $creds = null;
    public $sessionMethod = self::METHOD_UNKNOWN;
    public $sessionState = self::STATE_SESS_NONE;
    public $isEmbedded = false;
    public $context = '';

    function __construct($session = null) {



        $creds = Creds::GetFromRequest();
        $this->creds = $creds;
        $authState = Auth::GetAuthState($creds);
        $ini = System::GetIni();
        $db = $ini->mongo_db;
        $collection = $ini->session_collection;

        $this->isEmbedded = RequestUtil::HasParam('embedded');

        if (RequestUtil::HasParam('context')) {
            $context = RequestUtil::Get('context');
            if ($context == 'embed')
                $this->isEmbedded = true;
        }
        $this->mongo = System::GetMongo();
        $this->db = $this->mongo;

        $this->collection = $this->mongo->selectCollection($db, $collection);

        $return_to = RequestUtil::Get('return_to', null);

        if (!is_null($return_to)) {
            $return_to = base64_encode(json_encode($_GET));
            $this->AddCookie('return_to', $return_to);
        }



        if (is_null($session)) {

            if (isset($_REQUEST ['token'])) {

                $session = $_REQUEST ['token'];
                $this->sessionMethod = self::METHOD_TOKEN;
            } elseif (!is_null($creds->username) && !$this->isEmbedded) {
                if (count($_COOKIE) > 0) {
                    if (isset($_COOKIE [self::COOKIE])) {
                        $session = $_COOKIE [self::COOKIE];
                        if ($authState == Auth::STATE_OK) {
                            $this->EndSession(new \MongoDB\BSON\ObjectId($session), false);
                        }
                    }
                }
            } elseif ((count($_COOKIE) > 0 ) && !$this->isEmbedded) {
                if (isset($_COOKIE [self::COOKIE])) {
                    $session = $_COOKIE [self::COOKIE];
                    $this->sessionMethod = self::METHOD_COOKIE;
                }
            }
        }

        if (isset($GLOBALS ['_SESSION']))
            throw new Exception('SimpleSession - Access error, use global $_SESSION or SimpleSession::Get()');

        if (is_null($session)) {
            // this->CreateSession();
        } else {
            $this->sessionId = new \MongoDB\BSON\ObjectId($session);
            $this->GetSession();
            $this->SetCookie();
        }
        $GLOBALS ['_SESSION'] = $this;
    }

    public function HasSession() {
        //
        return !is_null($this->session);
    }

    public function __get($what) {
        if ($what == 'sessionState')
            return $this->sessionState;
        if ($what == 'sessionMethod')
            return $this->sessionMethod;
        if (is_null($this->session)) {
            return null;
        }
        $this->session = iterator_to_array($this->session);
        return ParamUtil::Get($this->session, $what);
    }

    public function GetID($id = null) {


        if (is_null($id) && !is_null($this->sessionId)) {

            $id = $this->sessionId;
        }
        if (is_null($id))
            return null;
        return "$id";
    }

    public function GetUserInfo() {
        $ini = System::GetIni();
        if (!isset($this->session)) {
            $username = $this->isEmbedded ? $ini->visitor_account : $this->creds->username;
            $person = System::Get()->getPersonByUsername($username);
            if ($person == false) {
                return null;
                throw new Exception('Invalid user:The user provided was not found');
            }
            return $person->getRecord();
            return null;
        } elseif (is_null($this->session['user']) || ( $this->isEmbedded)) {
            return System::Get()->getPersonByUsername($ini->visitor_account)->getRecord();
        }


        if (is_null($this->isEmbedded)) {
            
        }


        return $this->session ['user'];
    }

    /**
     * @return \Person
     */
    public function GetUser() {
        $userInfo = $this->GetUserInfo();
        if (is_null($userInfo))
            return null;
        return System::Get()->getPersonById($userInfo['id']);
    }

    /**
     *
     * @param string $session        	
     * @return SimpleSession
     */
    public static function Get($session = null) {
        if (isset($GLOBALS ['_SESSION'])) {
            return $GLOBALS ['_SESSION'];
        }

        new SimpleSession($session);

        /* $GLOBALS['_SESSION'] SimpleSession */
        return $GLOBALS ['_SESSION'];
    }

    private function GetSession($force = false, $isDMI = true) {
        #if (! is_null ( $this->session ))
        #	return $this->session;

        $this->session = $this->collection->findOne(array(
            '_id' => $this->sessionId
        ));

        $userInfo = $this->GetUserInfo();
        if (!is_null($userInfo)) {
            if ($isDMI) {
                $criteria = array(
                    'user.id' => $userInfo ['id'],
                    'application' => 'dmi'
                );
                $sessions = $this->collection->find($criteria, array('sort' => array(
                        'created' => - 1
                )));

                $numSessions = $this->collection->countDocuments($criteria);

                if ($numSessions > 1) {
                    $ctr = 0;

                    foreach ($sessions as $sess) {
                        // ar_dump($sess);
                        $sess['deleteme'] = 1;
                        $this->collection->replaceOne(array('_id' => $sess['_id']), $sess);
                        $this->EndSession($sess['_id']);

                        $ctr++;
                        if ($ctr == $numSessions)
                            break;
                    }
                }
            }
        }
        if ($this->sessionId) {
            $this->session = $this->collection->findOne(array(
                '_id' => $this->sessionId
            ));
        }

        if (is_null($this->session) && !$force) {

            $this->sessionState = self::STATE_SESS_NONE;
            return $this->session;
        }
        $expires = 0;
        $fingerprint;
        $currentFingerprint;
        if (!is_null($this->session)) {
            $expires = $this->session ['expires'];
            $created = $this->session ['created'];
            $fingerprint = $this->session ['fingerprint'];
        }


        if ($expires === 0) {
            $expires = PHP_INT_MAX;
        }
        $this->sessionState = self::STATE_SESS_OK;

        if ($expires < time()) {
            //$this->sessionState = self::STATE_SESS_EXPIRED;
            //}elseif ($currentFingerprint != $fingerprint) {
            //$this->sessionState = self::STATE_FINGERPRINT_MISMATCH;
        } else {
            $this->sessionState = self::STATE_SESS_OK;
        }


        $this->isEmbedded = ($this->session) ? $this->session['isEmbedded'] : false;

        return $this->session;
    }

    public function CreateSession(Person $user = null, $data = null, $useCookie = false, $override = false, $expires = '1 hour') {


        if ($override) {
            $this->EndSession();
            unset($this->session);
        }


        if (is_null($data))
            $data = array();
        $mid = new \MongoDB\BSON\ObjectId();

        $this->sessionId = $mid;
        $time = time();
        $expiry = ($expires == 0) ? 0 : strtotime($expires, $time);
        $orgId = null;
        $seatId = null;
        $permissions = null;
        if ($user) {
            $userId = $user->id;

            $seatAssignments = new SeatAssignments();


            $seatAssignment = $seatAssignments->GetAssignment($userId);

            if ($seatAssignment) {
                $orgId = $seatAssignment['data']['orgId'];
                $seatId = $seatAssignment['data']['seatId'];


                $roleId = SeatAssignments::GetUserRole($userId, $orgId);


                if ($roleId) {
                    $rolePermissions = new RolePermissions();
                    $permissions = $rolePermissions->GetPermissionsByIds(null, $roleId);

                    $permissions = $rolePermissions->ListPermissions($permissions, true, null, true);
                }
            }
        }


        $fingerprint = $this->GetFingerprint($time);
        $sessionInfo = array(
            '_id' => $mid,
            'id' => $this->GetID($mid),
            'created' => $time,
            'updated' => $time,
            'expires' => $expiry,
            'fingerprint' => $fingerprint,
            'user' => ($user) ? $user->getRecord() : null,
            'isEmbedded' => $this->isEmbedded,
            'orgId' => $orgId,
            'seatId' => $seatId,
            'permissions' => $permissions,
            'return_to' => '',
        );

        if (isset($data['context'])) {
            $data['context'] = 1;
        }
        $sessionInfo = array_merge($sessionInfo, $data);

        // sessionInfo['session'] = new stdClass();
        $res = $this->mongo->simplelayers->sessions->insertOne($sessionInfo);



        // res = $this->collection->insert((object)$sessionInfo,array('w',1));

        $this->GetSession(true);

        $this->sessionState = self::STATE_SESS_OK;

        if ($useCookie) {

            $this->SetCookie();
        }
        return $this;
    }

    private function GetFingerprint($time) {
        $agent = isset($_SERVER ['HTTP_USER_AGENT']) ? $_SERVER ['HTTP_USER_AGENT'] : '';

        $ip = isset($_SERVER ['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        $port = isset($_SERVER ['REMOTE_PORT']) ? $_SERVER['REMOTE_PORT'] : '22';

        $fingerprint = Security::Encode_1way($agent . $ip . $port, $time);
    }

    public function EndSession($id = null, $removeCookie = true) {

        if (is_null($id))
            $id = $this->sessionId;

        if (is_null($id))
            return null;

        $past = 0;

        if ($id == $this->sessionId && $removeCookie) {
            unset($_COOKIE[self::COOKIE]);
            $c = setcookie(self::COOKIE, null, -1, '/', FALSE, FALSE);
        }

        $res = $this->collection->deleteOne(array('_id' => $id));

        $this->sessionState = self::STATE_SESS_NONE;
        $this->session = null;
        unset($GLOBALS ['_SESSION']);


        // context = Context::Get(Creds::GetFromRequest());
    }

    public function EndAllSessions($userid) {

        $res = $this->collection->remove(array('user.id' => "" . $userid), array('safe' => true));
        $currentUserInfo = $this->GetUserInfo();
        if ($currentUserInfo['userId'] == $userid) {
            $this->EndSession(null, true);
        }
    }

    public function SetCookie() {

        if (!isset($_SERVER ['REQUEST_URI']))
            return;

        if (!in_array($this->sessionMethod, array(
                    self::METHOD_COOKIE,
                    self::METHOD_UNKNOWN
                )))
            return;


        if (is_null($this->GetID()))
            return null;
        $c = $this->MakeCookie(self::COOKIE, $this->GetID());
        //$c = setcookie(self::COOKIE, $this->GetID(), strtotime('+1 day', time()), '/', '.simplelayers.com', true, true);
    }

    

    public static function MakeCookie($name, $value, $duration = '+1 day') {
        $c = setcookie($name, $value,
                ['expires' => strtotime($duration, time()),
                    'path' => '/',
                    'domain' => '.simplelayers.com',
                    'secure' => true,
                    'httponly' => false,
                    'samesite' => 'Strict']);
        return $c;
    }

    public static function UnsetCookie($name, $value, $duration = '+1 days') {
        $c = setcookie($name, $value,
                ['expires' => strtotime($duration, time()),
                    'path' => '/',
                    'domain' => '.simplelayers.com',
                    'secure' => true,
                    'httponly' => false,
                    'samesite' => 'Strict']);
        return $c;
    }

    public function AddCookie($name, $value, $duration = '+1 day') {
        self::MakeCookie($name, $value, strtotime($duration, time()));
    }

    public function RemoveCookie($name) {
        self::UnsetCookie($name, null, -1);
    }

    public function UpdateSession($mode = self::UPDATE_SET, $updateCookie = false) {

        $time = strtotime('now');
        $setData ['updated'] = $time;
        $setData ['expires'] = strtotime('1 hour', $time);


        if (isset($this->session ['session'])) {
            $session = (object) $this->session ['session'];
            $setData ['session'] = $session;
        }
        $res = $this->collection->updateOne(array(
            '_id' => $this->sessionId
                ), array(
            $mode => $setData
        ));

        // f($updateCookie) $this->SetCookie ();
        $this->GetSession();
    }

    public function offsetExists($offset) {
        if (is_null($this->session))
            return false;

        return isset($this->session[$offset]);
    }

    public function offsetGet($offset) {
        if ($offset == '_id') {
            $_id = '_id';
            return $this->sessionId->$_id;
        }

        if (!$this->offsetExists($offset))
            return false;
        return $this->session[$offset];
    }

    public function offsetSet($offset, $value) {
        $this->session ['session'] [$offset] = $value;
        $this->UpdateSession(self::UPDATE_SET);
    }

    public function offsetUnset($offset) {
        unset($this->session ['session'] [$offset]);
        $this->UpdateSession(self::UPDATE_SET);
    }

    public function GetOrg() {
        if (!isset($this['context']))
            return Organization::GetOrg(1);
        return Organization::GetOrg($this['context']);
    }

    public function GetPermission($permPath = null) {
        if (is_null($this->session))
            return array();
        if (is_null($permPath))
            return $this->session['permissions'];
        return $this->session['permissions'][$permPath];
    }

    public static function UpdateSessionsByRole($roleId) {
        $ini = System::GetIni();
        $db = $ini->mongo_db;
        $collection = $ini->session_collection;

        $mongo = System::GetMongo();
        $db = $mongo->$db;
        $collection = $mongo->selectCollection($db, $collection);

        $seats = new Seats();
        $seatAssignments = new SeatAssignments();
        $seatList = $seats->FindByCriteria(Comparisons::ToMongoCriteria('data.roleId', Comparisons::COMPARE_EQUALS, $roleId));
        foreach ($seatList as $seat) {
            $seatId = $seat['id'];
            $sessions = $collection->find(Comparisons::ToMongoCriteria('seatId', Comparisons::COMPARE_EQUALS, $seatId));
            foreach ($sessions as $session) {
                $userId = $session['user']['id'];
                $rolePermissions = new RolePermissions();
                $permissions = $rolePermissions->GetPermissionsByIds(null, $roleId);
                $permissions = $rolePermissions->ListPermissions($permissions, true, null, true);
                $session['permissions'] = $permissions;
                $session['updated'] = mktime();
                $collection->update(array('id' => $session['id']), $session);
            }
        }
    }

}

?>
