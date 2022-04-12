<?php
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

function _config_message() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	$config['sendUser'] = true;
	// Stop config
	return $config;
}

function _headers_message() {
	header('Content-Type: application/json');
	$hash = "";
	switch(strtolower($_REQUEST['action'])){
		case "send":
		case "openmessage":
			if(isset($_REQUEST['recipient'])) $hash = "#U".$_REQUEST['recipient'];
		case "closemessage":
			header('Location: ./?do=contact.message'.$hash);
			break;
	}
}

function _dispatch_message($template, $args) {
	$world = $args['world'];
	$user = $args['user'];
	
	switch(strtolower($_REQUEST['action'])){
		case "send":
			if(isset($_REQUEST['recipient']) and isset($_REQUEST['message']) and $_REQUEST['message']) $user->sendMessage($_REQUEST['message'],$_REQUEST['recipient']);
		case "openmessage":
			if(isset($_REQUEST['recipient'])){
				$open = $user->getPData("openMessages", false, Array());
				if(!is_array($open)) $open = Array();
				if(array_search($_REQUEST['recipient'], $open) === false) $open[] = $_REQUEST['recipient'];
				$user->setPData("openMessages", $open);
			}
			break;
		case "mark":
			if(isset($_REQUEST['recipient'])) echo json_encode($user->markRead($_REQUEST['recipient'], $_REQUEST['upto']));
			break;
		case "markall":
			$user->markRead();
			break;
		case "closemessage":
			if(isset($_REQUEST['recipient'])){
				$user->markRead($_REQUEST['recipient']);
				$open = $user->getPData("openMessages", false, Array());
				$key = array_search($_REQUEST['recipient'], $open);
				if($key !== false) unset($open[$key]);
				$user->setPData("openMessages", $open);
			}
			break;
		case "more":
			if(isset($_REQUEST['recipient'])){
				$messages = $user->getMessages($_REQUEST['recipient'], 30, $_REQUEST['offset']);
				echo json_encode($messages);
			}
			break;
	}
}

?>