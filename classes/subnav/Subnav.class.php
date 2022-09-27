<?php
namespace subnav;
/*require_once 'LayerSubnav.class.php';
require_once 'ProjectSubnav.class.php';
require_once 'ContactSubnav.class.php';
require_once 'GroupSubnav.class.php';
require_once 'OrganizationSubnav.class.php';
require_once 'AdminSubnav.class.php';*/

class Subnav {
	
	protected $arrayOfNavElements = Array();
	protected $arrayOfDisabled = Array();
	protected $id = Array();
	protected $templater;
	protected $params;
	
	public $template = 'subnav/subnav.tpl';
	function __construct($params=null) {
		$this->params = $params;
		$this->templater = \SLSmarty::GetTemplater();
		
	}
	function add($category, $title, $do, $gray=false){
		$this->arrayOfNavElements[$category][$title] = $do;
		$this->arrayOfDisabled[$category][$title] = $gray;
	}
	function remove($category, $title){
		unset($this->arrayOfNavElements[$category][$title]);
		unset($this->arrayOfDisabled[$category][$title]);
	}
	function removeCategory($category){
		unset($this->arrayOfNavElements[$category]);
		unset($this->arrayOfDisabled[$category]);
	}
	function clear(){
		$this->arrayOfNavElements = Array();
		$this->arrayOfDisabled = Array();
	}
	function get($category, $title){
		return $this->arrayOfNavElements[$category][$title];
	}
	function getCategory($category){
		return $this->arrayOfNavElements[$category];
	}
	function getAll($category){
		return $this->arrayOfNavElements;
	}
	function makeDefault($obj, $user){
	
	}
	function assign($key, $val){
		$this->templater->assign($key,$val);
	}
	function fetch(){
		$this->templater->assign('navArray',$this->arrayOfNavElements);
		$this->templater->assign('disArray',$this->arrayOfDisabled);
		
		return $this->templater->fetch($this->template);
	}
	function display(){
		return null;
		echo($this->fetch());
	}
}
?>
