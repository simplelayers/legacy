<?php

function _config_save() {
	$config = Array ();
	// Start config
	$config ["header"] = false;
	$config ["footer"] = false;
	$config ["customHeaders"] = true;
	$config ['sendUser'] = true;
	$config ['sendWorld'] = true;
	// Stop config
	return $config;
}

function _headers_save() {
	header ( 'Content-Type: text/xml' );
	/*	if (! isset ( $_REQUEST ['format'] ))
		$_REQUEST ['format'] = 'json';
	switch ($_REQUEST ['format']) {
		case "json" :
		case "ajax" :
			header ( 'Content-Type: application/json' );
			break;
		case "xml" :
			header ( 'Content-Type: text/xml' );
			break;
		case "phpserial" :
			header ( 'Content-Type: text/plain' );
			break;
	}
		*/
}

function _dispatch_save($template, $args) {
	/* @var $world World */
	$world = $args ['world'];
	
	/* @var $wapi WAPI */
	$wapi = $world->wapi;
	
	/* @var $project Project */
	$project = $wapi->RequireProject ();
	
	$layer = $wapi->RequireALayer ();
	if ($wapi->layerType == WAPI::ALAYER_PROJECTLAYER)
		$layer = $layer->layer;
	
	if ($layer->type != LayerTypes::VECTOR and $layer->type != LayerTypes::ODBC)
		return denied ( DENIED_WRONGGEOM );
	
	$changes = array ();
	$gid = null;
	$timestamps = RequestUtil::GetList ( 'timestamps', ',', array () );
	
	$featureInfo = $layer->getAttributes ();
	
	$featureSet = $wapi->GetInputXML();// RequestUtil::Get('XMLInput')->children ();
	
	$records = array ();
	foreach ( $featureSet as $feature ) {
	    /* @var $feature SimpleXMLElement */
		$columns = array();
		foreach ( $feature->attributes () as $column => $value ) $columns[] = $column;
		$isQuery = count($columns)>0;
		$src = !$isQuery ? $feature->children() : $feature->attributes();
		 
		foreach ( $src as $i=>$item ) {
			if(!$isQuery) {
			    /* @var $item SimpleXMLElement */
			    $column = $item->getName();
			    $value = (string) $item;
			} else {
			    $column= $i;
			    $value = $item;			    
			}
			if ($column == 'gid') {
				$gid = ( int ) $value;
				continue;
			}
			if($column=="search_on") {
				$column="searchable";
				if($value == "0") $value=false;
			}
			if (in_array ( $column, $timestamps ))
				continue;
		
			$v = $value;
			
			switch ($featureInfo [$column]) {
				case DataTypes::TEXT :
					$v = ($value != "") ? ( string ) htmlentities ( $value ) : NULL;
					break;
				case DataTypes::FLOAT :
					$v = ($value != "") ? ( float ) $value : NULL;
					break;
				case DataTypes::INTEGER :
					$v = ($value != "") ? ( int ) $value : NULL;
					break;
				case DataTypes::BOOLEAN :
					$value = strtolower ( substr ( trim ( $value ), 0, 1 ) );
					switch ("" . $value) {
						case 't' :
							$value = 't';
							break;
						case 'f' :
							$value = 'f';
							break;
						case '1' :
							$value = 't';
						case '0' :
							$value = 'f';
						default :
							if (strlen ( $value ) == 1)
								$value = 'f';
							else
								$value = NULL;
							break;
					}
					
					$v = $value;
					break;
			}
			$changes [$column] = $v;
		
		}
		$fieldtypes = array ('C' => DataTypes::TEXT, 'X' => DataTypes::TEXT, 'D' => DataTypes::TEXT, 'T' => DataTypes::TEXT, 'B' => DataTypes::TEXT, 'N' => DataTypes::FLOAT, 'I' => DataTypes::INTEGER, 'R' => DataTypes::INTEGER, 'L' => DataTypes::BOOLEAN );
		// if a gid is provided update the record, otherwise add a new one.
		$isUpdating = (! is_null ( $gid ));
		
		unset ( $changes ['the_geom'] );
		unset ( $changes ['wkt_geom'] );
		unset ( $changes ['gid'] );
		if ($isUpdating) {
			$newRecord = $layer->updateRecordById ( $gid, $changes, $timestamps );
		} else {
			$newRecord = $layer->insertRecord ( $changes, $timestamps );
		}
		array_push ( $records, $newRecord );
	
	}
	if(!count($records)) {
		/* @var $template Smarty */
		$template->assign('message','no records found');
		$template->assign('sql','');
		$template->display('wapi/error.tpl');
		return;
	}
	$template->assign('ok','OK');
	$template->assign('message',count($records).' records affected');
	$template->display('wapi/okno.tpl');

}
