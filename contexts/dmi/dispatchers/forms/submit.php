<?
function _config_submit() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_submit($template, $args) {
	$error = Array();
	$form = $args["world"]->getForm($_REQUEST["id"]);
	$attributes = $form->layer->getAttributesVerbose(false,true);
	foreach($form->fields as $name=>$info){
		if (isset($_REQUEST[$name])){ $val = $_REQUEST[$name]; }
		switch($info->dataType){
			case 1:
				if(strlen($val) < $info->min) $error[] = Array($name, $info->display . " requires a minimum of " . $info->min . " characters.");
				if(strlen($val) > $info->max && $info->max != 0) $error[] = Array($name, $info->display . " requires a maximum of " . $info->max . " characters.");
				//ToDo Regex
				break;
			case 2:
				if(!is_numeric($val)){
					$error[] = Array($name, $info->display . " must be a number.");
				}else{
					$val = round($val, $info->precision);
					if($val < $info->min) $error[] = Array($name, $info->display . " requires a minimum value of " . $info->min . ".");
					if($val < $info->max) $error[] = Array($name, $info->display . " requires a maximum value of " . $info->max . ".");
				}
				break;
			case 3:
				$dateRegex = "/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/";
				if(preg_match($dateRegex, $val) == 0) $error[] = Array($name, $info->display . " requires a date in ISO 8601 date format (YYYY-MM-DD).");
			case 4:
				if(isset($_REQUEST[$name])){
					if($attributes[$name]["requires"] == "boolean") $val = 1;
					else $val = $info->checked;
				}else{
					if($attributes[$name]["requires"] == "boolean") $val = 0;
					else $val = $info->unchecked;
				}
				break;
			case 7:
				$val = $info->value;
				break;
		}
		$_REQUEST[$name] = $val;
	}
	if(empty($error)){
		$changes = Array();
		foreach($form->fields as $name=>$info){
			if($info->dataType == 8) $_REQUEST[$name] = doMathBlock($form, (array)$info);
			$changes[$name] = $_REQUEST[$name];
		}
		$form->layer->insertRecord($changes);
	}else{
		print_r($error);
	}
}
function doMathBlock(&$form, $info){
	$attrs = (array)$info["mathAttr"];
	$operators = array_values((array)$info["mathOperator"]);
	$fields = (array)$form->fields;
	$attr = reset($attrs);
	$field = (array)$fields[$attr];
	$value = ($field["dataType"] == 8 ? doMathBlock($form, $field) : $_REQUEST[$attr]);
	foreach($info["mathAttr"] as $key=>$attr){
		if($key != 0){
			$field = (array)$fields[$attr];
			$valOfCurrent = ($field["dataType"] == 8 ? doMathBlock($form, $field) : $_REQUEST[$attr]);
			switch($operators[((int)$key)-1]){
				case "+":
					$value += $valOfCurrent;
					break;
				case "-":
					$value -= $valOfCurrent;
					break;
				case "*":
					$value *= $valOfCurrent;
					break;
				case "/":
					$value /= $valOfCurrent;
					break;
			}
		}
	}
	return $value;
}
?>