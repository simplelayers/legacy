<?

namespace subnav;

use auth\Context;
use model\Permissions;
class AdminSubnav extends Subnav {
	
	function makeDefault($user, $name,$org,$pageArgs){		
		$permissions = $pageArgs['permissions'];
		$context = Context::Get();
		$this->assign("objectData", 'Admin');
		$this->assign("ownerData", htmlspecialchars ($name));
		if(!$user->admin) return false;
		//if(!is_null($right)) $this->assign("rightbar", $right);
		
		if($permissions[':SysAdmin:UserAccounts:'] & (Permissions::VIEW | Permissions::EDIT)) $this->add("Users", "List", "admin.userlist");
		if($permissions[':SysAdmin:UserAccounts:'] & (Permissions::CREATE)) $this->add("Users", "New", "admin.adduser1");
		if($permissions[':SysAdmin:Organizations:'] & (Permissions::VIEW))$this->add("Organizations", "List", "admin.organization.list");
		if($permissions[':SysAdmin:Organizations:'] & (Permissions::CREATE)) $this->add("Organizations", "New", "admin.organization.new1");
		//if($permissions[':SysAdmin:Organizations:'] & (Permissions::EDIT))$this->add("Organizations", "Invoices", "admin.organization.invoice.list");
		if($permissions[':SysAdmin:Logs:MapUsage:'] & (Permissions::VIEW)) $this->add("Logs", "Map Activity", "admin.projectlog");
		if($permissions[':SysAdmin:Logs:AccountLogins:'] & (Permissions::VIEW)) $this->add("Logs", "Logins", "admin.loginlog");
		if($permissions[':SysAdmin:Logs:AccountChanges:'] & Permissions::VIEW) $this->add("Logs", "Account Changes", "admin.accountlog");
		if($permissions[':SysAdmin:Logs:LayerTransactions:'] & Permissions::VIEW) $this->add("Logs", "Transactions", "admin.report.transactions");
		if($permissions[':SysAdmin:Defaults:Bookmarks:'] &(Permissions::VIEW | Permissions::EDIT))$this->add("Defaults", "Bookmarks", "admin.usersetupbookmarks1");
		if($permissions[':SysAdmin:Defaults:Contacts:'] &(Permissions::VIEW | Permissions::EDIT)) $this->add("Defaults", "Friends", "admin.usersetupfriends1");
		if($permissions[':SysAdmin:Defaults:Layers:'] &(Permissions::VIEW | Permissions::EDIT))$this->add("Defaults", "Layers", "admin.usersetuplayers1");
		if($permissions[':SysAdmin:SystemIdentification:'] &(Permissions::VIEW | Permissions::EDIT)) $this->add("Configuration", "System", "admin.configidentification1");
		//$this->add("Configuration", "Prices", "admin.configquotas1");
		if($permissions[':SysAdmin:Signups:'] &(Permissions::VIEW | Permissions::EDIT))	$this->add("Configuration", "Messages", "admin.configsignups1");
		
	}
}
?>