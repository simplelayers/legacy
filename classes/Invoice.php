<?php
use model\CRUD;
/*
 * Created on Sep 14, 2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
class Invoice extends CRUD{
	protected $myOrg;
	function __construct(&$world, $id) {
		$this->world = $world;
		$this->table = "invoices";
		$this->idField = 'id';
		$this->objectId = $id;
		$this->arrayOfObjectsRetrivedRowData = $this->getAllFieldsAsArray();
		if($this->arrayOfObjectsRetrivedRowData == null) throw new Exception("No such invoice: $id.");
		$this->myOrg = $this->world->getOrganizationById($this->org_id);
		$this->isReadyOnly = false;
	}
	public function Update($updates) {
		parent::Update($updates);
		$this->arrayOfObjectsRetrivedRowData = $this->getAllFieldsAsArray();
	}
	public function tos(){
		return date('n/j/Y', strtotime($this->world->db->Execute("SELECT created+paymentterm AS tos FROM invoices WHERE id=?", Array($this->id))->fields["tos"]));
	}
	public function __get($name) {
		if($name == "organization" or $name == "org") return $this->myOrg;
		if($name == "created" or $name == "paid") return date('n/j/Y', strtotime($this->arrayOfObjectsRetrivedRowData[$name]));
		if(isset($this->arrayOfObjectsRetrivedRowData[$name])) return $this->arrayOfObjectsRetrivedRowData[$name];
	}
	public function poPrice(){return $this->po_subsequent_price*($this->po_seats-1);}
	public function exPrice(){return $this->ex_subsequent_price*($this->ex_seats-1);}
	public function stPrice(){return $this->st_subsequent_price*($this->st_seats-1);}
	public function subTotal(){
		$return = 0;
		if($this->po_seats){
			$return += $this->po_initial_price;
			$return += $this->poPrice();
		}
		if($this->ex_seats){
			$return += $this->ex_initial_price;
			$return += $this->exPrice();
		}
		if($this->st_seats){
			$return += $this->st_initial_price;
			$return += $this->stPrice();
		}
		$return += $this->extra;
		return $return;
	}
	public function taxTotal(){
		return $this->subTotal()*($this->sales_tax/100);
	}
	public function total(){
		return $this->subTotal()+$this->taxTotal();
	}
	/*function notify($address, $code){
		$fromUser  = $this->owner;
		$from     = sprintf("From: %s <%s>\r\n", $fromUser->realname, $fromUser->email );
		$from .= "Content-type: text/html\r\n"; 
		$to       = sprintf("%s", $address );
		$email = new Templater();
		$message = '';
		$devpath = '';
		//$devpath = '~doug/cartograph/';
		
		$subject  = sprintf("[%s] Invitation to Cartograph", $this->name );
		$message .= sprintf("You have been invited to join cartograph by %s as a part of %s.<br/><br/>", $fromUser->realname, $this->name);
		$message .= sprintf("You can use the following registration code ot join, or simply use the link below.<br/>");
		$message .= sprintf("%s<br/><br/>", $code);
		$message .= sprintf("<a style=\"text-decoration:none;\" href=\"https://www.cartograph.com/%s?do=organization.join&code=%s\">Accept</a>", $devpath, $code);
		
		$email->assign('group', $this);
		$email->assign('subject', $subject);
		$email->assign('message', $message);
		$email->assign('devpath', $devpath);
		mail($to,$subject,$email->fetch('group/email.tpl'),$from);
	}*/
}
?>
