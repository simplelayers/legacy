<?php
function _config_message() {
	$config = Array ();
	// Start config
	// Stop config
	return $config;
}
function _dispatch_message($template, $args) {
	$user = $args ['user'];
	$world = $args ['world'];
	$messages = $user->getMessages ();
	$unread = $user->countUnreadMessages ();
	$total = $user->countMessages ();
	
	$open = $user->getPData ( "openMessages", false, Array () );
	foreach ( $unread as $row ) {
		if ($row ["messages"] != 0) {
			if (($key = array_search ( $row ["from"], $open )) === false) {
				$open [] = $row ["from"];
			}
		}
	}
	$user->setPData ( "openMessages", $open );
	
	$organizedMessages = Array ();
	$userInfo = Array ();
	foreach ( $open as $uid ) {
		if (is_array ( $organizedMessages ) && (! isset ( $organizedMessages [$uid] ) || ! is_array ( $organizedMessages [$uid] ))) {
			$organizedMessages [$uid] = Array ();
			$person = $world->getPersonById ( $uid );
			$userInfo [$uid] = Array (
					$person->realname,
					$person->username,
					$person->icon,
					true 
			);
		}
	}
	foreach ( $messages as $message ) {
		$handleId = $message ["from"];
		if ($handleId == $user->id)
			$handleId = $message ["to"];
		if (is_array ( $organizedMessages ) && (! isset ( $organizedMessages [$handleId] ) || ! is_array ( $organizedMessages [$handleId] ))) {
			$organizedMessages [$handleId] = Array ();
			$person = $world->getPersonById ( $handleId );
			$userInfo [$handleId] = Array (
					$person->realname,
					$person->username,
					$person->icon,
					false 
			);
		}
		$organizedMessages [$handleId] [] = $message;
	}
	
	$template->assign ( 'user', $user );
	$template->assign ( 'messages', json_encode ( $organizedMessages ) );
	$template->assign ( 'unread', json_encode ( $unread ) );
	$template->assign ( 'total', json_encode ( $total ) );
	$template->assign ( 'userInfo', json_encode ( $userInfo ) );
	$template->display ( 'contact/message.tpl' );
}
?>
