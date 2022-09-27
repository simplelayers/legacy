<?php
use reporting\Reporting;
use utils\ParamUtil;
define('REPORT_ACTIVITY_CREATE', 1);
define('REPORT_ACTIVITY_RETRIEVE', 2);
define('REPORT_ACTIVITY_UPDATE', 3);
define('REPORT_ACTIVITY_DELETE', 4);
define('REPORT_ACTIVITY_EMBED', 5);
define('REPORT_ACTIVITY_OPEN', 6);
define('REPORT_ACTIVITY_SHARE', 7);
define('REPORT_ACTIVITY_GIVE', 8);

define('REPORT_ENVIRONMENT_DMI', 1);
define('REPORT_ENVIRONMENT_VIEWER', 2);
define('REPORT_ENVIRONMENT_UTILITY', 3);
define('REPORT_ENVIRONMENT_EXTERNAL', 4);

define('REPORT_TARGET_MAP', 1);
define('REPORT_TARGET_LAYER', 2);
define('REPORT_TARGET_PROJECT_LAYER', 3);
define('REPORT_TARGET_PERSON', 4);
define('REPORT_TARGET_GROUP', 5);

define('REPORT_RECIPIENT_TYPE_PERSON', 1);
define('REPORT_RECIPIENT_TYPE_GROUP', 2);

class Report {
	public $activity;
	public $environment;
	public $target;
	public $target_id;
	public $target_name;
	public $actor;
	public $actor_id;
	public $actor_ip;
	public $recipient;
	public $recipient_id;
	public $recipient_type;

	const ACTIVITY_CREATE = 1;
	const ACTIVITY_RETRIEVE = 2;
	const ACTIVITY_UPDATE = 3;
	const ACTIVITY_DELETE = 4;
	const ACTIVITY_EMBED = 5;
	const ACTIVITY_OPEN = 6;
	const ACTIVITY_SHARE = 7;
	const ACTIVITY_GIVE = 8;
	
	const ENVIRONMENT_DMI = 1;
	const ENVIRONMENT_VIEWER = 1;
	const ENVIRONMENT_EXTERNAL=3;
	
	const TARGET_MAP=1;
	const TARGET_LAYER=2;
	const TARGET_PROJECT_LAYER=3;
	const TARGET_PERSON=4;
	const TARGET_GROUP=5;
	
	const RECIPIENT_TYPE_PERSON=1;
	const RECIPIENT_TYPE_GROUP=2;
	const RECIPIENT_TYPE_ORG=3;

	protected $db;
	
	function __construct($world,$data) {
	    //$activity=null,$environment=null,$target=null,$target_id=null,$target_name=null,$actor=null,$actor_id=null,$actor_ip=null,$recipient=null,$recipient_id=null,$recipient_type=null) {
	
		$this->db = $world->db;

		$this->activity = ParamUtil::Get($data,'activity');
		$this->environment = ParamUtil::Get($data,'environment');
		$this->target = ParamUtil::Get($data,'target');
		$this->target_id = ParamUtil::Get($data,'target_id');
		$this->target_name = ParamUtil::Get($data,'target_name');
		$actor = ParamUtil::Get($data,'actor');
		if(is_array($actor)) {
			$user = $actor;
			$actor = $user['username'];
			$actor_id = $user['id'];
		} elseif(gettype($actor) === 'object' && get_class($actor) === 'Person'){
			$user = $actor;
			$actor = $user->username;
			$actor_id = $user->id;
		}
		$actor_ip = ParamUtil::Get($data,'actor_ip', $_SERVER['REMOTE_ADDR']);
		$this->actor = $actor;
		$this->actor_id = $actor_id;
		$this->actor_ip = $actor_ip;
		
		$recipient = ParamUtil::Get($data,'recipient');
		$recipient_id = ParamUtil::Get($data,'recipient_id');
		$recipient_type = ParamUtil::Get($data,'recipient_type');
		
		//ToDo if recipient is person set recipient id and name. Type = user
		if(gettype($actor) === 'object'){
			if(get_class($actor) === 'Person'){
				$user = $recipient;
				$recipient = $user->username;
				$recipient_id = $user->id;
				$recipient_type = REPORT_RECIPIENT_TYPE_PERSON;
			}elseif(get_class($actor) === 'Group'){
				$group = $recipient;
				$recipient = $group->name;
				$recipient_id = $group->id;
				$recipient_type = REPORT_RECIPIENT_TYPE_GROUP;
			}
		}
		//ToDo if recipient is group set recipient id and name as groups. Type = group
		$this->recipient = $recipient;
		$this->recipient_id = $recipient_id;
		$this->recipient_type = $recipient_type;
	}
	
	public static function MakeEntry($activity=null,$environment=null,$target=null,$target_id=null,$target_name=null,$actor=null,$actor_id=null,$actor_ip=null,$recipient=null,$recipient_id=null,$recipient_type=null) {
            
            $data = array();
	    $data['activity']=$activity;
	    $data['environment'] = $environment;
	    $data['target'] = $target;
	    $data['target_id'] = $target_id;
	    $data['target_name'] = $target_name;
	    $data['actor'] = $actor;
	    $data['actor_id'] = $actor_id;
	    $data['actor_ip'] = $actor_ip;
	    $data['recipient_id'] = $recipient_id;
	    $data['recipient_type'] = $recipient_type;
            return $data;
	}  
	
	function toString(){
		return 	'Activity: '.Reporting::activityToSting($this->activity).
				' Environment: '.Reporting::environmentToSting($this->environment).
				' Target: '.Reporting::environmentToSting($this->target).
				' Target_id: '.$this->target_id.
				' Target_name: '.$this->target_name.
				' Actor: '.$this->actor.
				' Actor_id: '.$this->actor_id.
				' Actor_ip: '.$this->actor_ip.
				' Recipient: '.$this->recipient.
				' Recipient_id: '.$this->recipient_id.
				' Recipient_type: '.Reporting::recipientTypeToSting($this->recipient_type);
	}
	
	function commit(){
            System::GetDB()->Execute('INSERT INTO public._reporting (activity,environment,target,target_id,target_name,actor,actor_id,actor_ip,recipient_name,recipient_id,recipient_type) VALUES (?,?,?,?,?,?,?,?,?,?,?)', array($this->activity, $this->environment, $this->target, $this->target_id, $this->target_name, $this->actor, $this->actor_id, $this->actor_ip, $this->recipient, $this->recipient_id, $this->recipient_type));
		
	}
}


?>