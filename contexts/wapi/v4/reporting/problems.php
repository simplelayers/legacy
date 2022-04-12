<?php

use reporting\Problem;


function getData() {
	$data = RequestUtil::GetJSONParam('data',null,true);
	if(!$data) throw new Exception('Bad request no arguments provided');
	return $data;
}

function _exec() {
	
	$sys = System::Get();
	$wapi = $sys->wapi;
	$format = RequestUtil::Get('format',WAPI::FORMAT_JSON);
	
	$user = SimpleSession::Get()->GetUser();
	
	$crud = RequestUtil::Get('crud');
	
	switch($crud) {
		case 'c':
		case 'create':
			$data = getData();
			error_log(var_export($data,true));
			$problem = new Problem();
			$problem->victem =$data['victem'];
			$problem->reporter = $data['reporter'];
			$problem->notes = $data['notes'];
			$problem->subject = $data['subject'];
			$problem->Save(true);
			
			WAPI::SetWapiHeaders($format);
			switch($format ) {
				case WAPI::FORMAT_JSON:
					die( $problem->ToJSON() );
				case WAPI::FORMAT_XML:
					die(WAPI::ArrayToXML('problem', $problem));
					break;
			}		
			break;
		case 'r':
		case 'retrieve':
			RequestUtil::Required('by');
			$by = RequestUtil::Get('by');
			switch($by) {
				case Problem::VIEW_MINE:
					$id = $user->id;
					break;
				case Problem::VIEW_USER:
				case Problem::VIEW_GROUP:
				case Problem::VIEW_ORG:
					$id = RequestUtil::Get('id');
					break;
			}
			WAPI::SetWapiHeaders($format);
			$document = new Problem();
			$probType = RequestUtil::Get('probType',null);
			$results = $document->GetView($by,$id,$probType);
			switch($format) {
				case WAPI::FORMAT_JSON;
					die(WAPI::MongoResultsToJSON($results));
				case WAPI::FORMAT_XML;
					die(WAPI::MongoResultsToJSON($results));
			}
			break;
		case 'u':
		case 'update':
			break;
		case 'd':
		case 'delete':
			break;
	}
	throw new Exception('No action to taken:Either the request was not supported or has not yet been implemented.');
	
}



?>