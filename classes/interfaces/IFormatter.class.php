<?php

interface IFormatter {

	public function WriteXML($target, $preferences=null );
	public function WriteJSON($target, $preferences=null);

}


?>