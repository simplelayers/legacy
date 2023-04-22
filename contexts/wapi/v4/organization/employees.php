<?php
use utils\ParamUtil;
use model\Seats;
use model\SeatAssignments;
use enums\AccountTypes;
use model\License;
function _exec() {
	$wapi = System::GetWapi ();
	$sys = System::Get();
	$args = $_REQUEST;
	$action = strtolower ( ParamUtil::Get ( $args, 'action' ) );
	$what = strtolower ( ParamUtil::Get ( $args, 'what' ) );
	$format = WAPI::GetFormat ();
	$orgId = ParamUtil::Get ( $_REQUEST, 'orgId' );
	$session = SimpleSession::Get();
	if(!$session) throw new Exception('A valid session is required.');
	$user = $session->GetUserInfo ();
	$org = is_null ( $orgId ) ? Organization::GetOrgByUserId ( $user ['id'] ) : Organization::GetOrg ( $orgId );
	$seats  = new Seats();
	$seatAssignments = new SeatAssignments();
	switch ($action) {
		case WAPI::ACTION_LIST :
			/*@var $org Organization */
			/*@var $group Group */
			$group = $org->group;
			$members = $group->getMembers (false);
			$memberLookup = array ();
			
			foreach ( $members as $member ) {
				$key = ''.$member->realname;
				if($key=='') $key= $member->username; 
				$memberLookup [$key] = array (
						'id' => $member->id,
						'realname' => $member->realname,
						'username' => $member->username, 
						'email' => $member->email
				);
				
			}
			$keys = array_keys($memberLookup);
			
			ksort($keys);
			$memberLookup = array_values($memberLookup);
			$license = new License();
			$licenseSeats = $license->GetLimitLookup($org->id);
			
			$results ['org'] = $org->id;
			
			$results ['members'] = $memberLookup;
			$results ['seatAssignments'] = iterator_to_array( $seatAssignments->ListSeats(array('orgId'=>$org->id) ));
			$results ['license_lookup'] = $licenseSeats;
			$incSysAdmin = $org->name == 'SimpleLayers';
			$results['seats_lookup'] = $seats->GetLookup('int',false,$incSysAdmin);
			WAPI::SendSimpleResults ( $results );
			die ();
			break;
			
		case WAPI::ACTION_ADD:
			$assignment  =  ParamUtil::Get($args,'seatId',$seats->GetUnassigned());
			$seats = new Seats();
		
			list($username,$password,$realname,$email) = ParamUtil::Requires($args,'username','password','realname','email');
			//$type = AccountTypes::GOLD;
			$sys = System::Get();
			
			$person = $sys->getPersonByUsername($username);
			if($person) {
				throw new Exception("Creation Problem: User with username $username already exists.");
			}
			
			$person = $sys->createPerson($username, $password);
			
			if($person) {
				$person->email = $email;
				$person->realname = $realname;
				$person->accounttype = AccountTypes::MAX;
				$person->expirationdate = null;
			} else {
				throw new Exception("Creation Problem: Unknown prolbem trying to create user.");
			}
			
			$org->group->joinById($person->id);
						
			$params = array();
			$params['orgId'] = $org->id;
			$params['userId'] = $person->id;
			$params['seatId'] = $assignment;
			
			$seatAssignment = $seatAssignments->AssignSeat($params);
			$orgShort = $org->short;
                        $nameParts = explode('@',$username);
			$username = array_shift($nameParts);
			$username.='@'.$orgShort;
			
			$newPerson = array('person'=>array('id'=>$person->id, 'username'=>$username,'realname'=>$realname,'email'=>$email),'seatAssignment'=>$seatAssignment,'status'=>'added');
			
			WAPI::SendSimpleResults($newPerson);
			die();
			/*try {
				list($name,$role) = ParamUtil::Requires($_REQUEST,'seatName','roleId');
				$seat = $seats->AddSeat(array('seatName'=>$name,'roleId'=>$role));
				if (! $seat)
					throw new Exception ( 'Creation Problem:There was an unknown problem creating a seat for <i>$name</i>' );
				WAPI::SendSimpleResults(array('document'=>$seat,'action_status'=>'added'));
			} catch ( Exception $e ) {
				if( stripos($e->getMessage(),'duplicate key error')>=0) {
					throw new Exception("Creation Problem: Seat with name $name already exists.");	
				} 	
			}*/		
			break;
		case WAPI::ACTION_SAVE :
			list ( $changes ) = ParamUtil::Requires ( $args, 'changeset' );
			$assignments = $changes['assignments'];
			foreach($assignments as $assignment) {
			   $seatAssignments->UpdateSeatAssignment($assignment);
			}
			
			$employees = $changes['employees'];
			$owner = $org->owner;
			
			/*@var $employee Person */
			foreach($employees as  $employee) {
				if(!(isset($employee['isChanged']) || isset($employee['isDeleted']))) continue;
				$person =  $sys->getPersonById($employee['id']);
				if(!$person) continue;
				if($owner) {
    				if($person->id == $owner->id) {
    				    if($person->id > 0) { 
    				        $org->Update(array('short'=>$person->username));
    				    }				    		
    				}
				}
				
				
				$password = ParamUtil::Get($employee,'password');
				
				unset($employee['password']);
				if(isset($employee['isDeleted'])) {
					$person->delete('Account deleted from employees screen');
					
					continue;
				}
				if(!in_array($password,array('',null))) $person->password = $password;
				if($person->id != $owner->id) {
    				if(isset($employee['username'])) {
        				$orgShort = $org->short;
                                        $nameParts = explode('@',$employee['username']);
        				$username = array_shift($nameParts);
        				$username.='@'.$orgShort;
        				$employee['username'] = $username;
    				}
				}
				$person->UpdateDoc($employee);
			}
			WAPI::SendSimpleResults(array('message'=>'updates complete'));
			break;
	}
}

?>