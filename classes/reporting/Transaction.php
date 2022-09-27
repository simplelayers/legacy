<?php

namespace reporting;

class Transaction {
	public static function add($world, $layer, $project, $actor, $transaction)
	{
		$world->db->Execute("SELECT UPSERT_TRANSACTIONS(?, ?, ?, ?);", Array($layer->id, $project->id, $actor->id, $transaction));
	}
	function getTransactions($db, $date=null, $id=null){
		$filter = Transaction::makeFilter($date, $id);
		
		if($id === null){
			$case = "
				CASE WHEN transaction=0 THEN 'Load/Refresh'
					WHEN transaction=1 THEN 'Center'
					WHEN transaction=2 THEN 'Pan'
					WHEN transaction=3 THEN 'Pan Zoom'
					WHEN transaction=4 THEN 'Zoom By'
					WHEN transaction=5 THEN 'Zoom To'
					WHEN transaction=6 THEN 'Feature Zoom'
					WHEN transaction=7 THEN 'Layer Zoom'
					WHEN transaction=8 THEN 'Zoom Level'
					WHEN transaction=9 THEN 'Resize'
					ELSE 'other'
				END AS transaction_name
			";
		}else{
			$case = "
				CASE WHEN transaction=0 THEN 'Load/Refresh'
					WHEN transaction=1 THEN 'Pan'
					WHEN transaction=2 THEN 'Pan'
					WHEN transaction=3 THEN 'Pan'
					WHEN transaction=4 THEN 'Zoom'
					WHEN transaction=5 THEN 'Zoom'
					WHEN transaction=6 THEN 'Zoom'
					WHEN transaction=7 THEN 'Zoom'
					WHEN transaction=8 THEN 'Zoom'
					WHEN transaction=9 THEN 'Resize'
					ELSE 'other'
				END AS transaction_name
			";
		}
		
		$query= "SELECT *,(SELECT name FROM layers AS l WHERE l.id = layer_id) AS layer_name, 
		(SELECT username FROM people AS p WHERE p.id = actor_id) AS actor_name, 
		(SELECT name FROM projects AS pr WHERE pr.id = project_id) AS project_name, $case
		FROM _transactions $filter ORDER BY id DESC";
		
		$results = $db->Execute($query);
		if(!$results) return json_encode(array());
		//foreach($rows as &$row){}
		return json_encode($results->getRows());
	}
	function makeFilter($date, $id=null){
		$str = " WHERE ";
		$isFirst = ($str==' WHERE ');
		if($id !== null)  {
		    if(!$isFirst) $str.=' AND ';
		    $str .= " layer_id = ".$id;
		}
		$isFirst = ($str==' WHERE ');
		
		if(!is_null($date)){
		    if(!$isFirst) $str.= (!$date['year'] && !$date['month'] && !$date['day']) ? '' : ' AND ';
			if($date["range"] == false){
			    $firstDate = true;
				foreach($date as $column => $value){
					if($column == "range") continue;
					if($value === false) continue;
					if($column == "year") $type = "YEAR";
					if($column == "month") $type = "MONTH";
					if($column == "day") $type = "DAY";
					if(!$firstDate) $str.=' AND ';
					$str .= "EXTRACT(".$type." FROM date) = ".$value;
					$firstDate = false;
				}
			}else{
				$start = "TIMESTAMP '".$date["year"]."-".$date["month"]."-".$date["day"]." 00:00:00'";
				$end = "TIMESTAMP '".$date["year2"]."-".$date["month2"]."-".(int)$date["day2"]." 24:00:00'";
				$str .= "date >= ".$start." AND date < ".$end;
			}
		}
		
		if($str == ' WHERE ') return '';
		return $str;
	}
}

class Reporting {
	function getReports($db, $full, $filter=null, $date=null){
		//$rows = $db->Execute("SELECT activity, EXTRACT(YEAR FROM timestamp) AS year, EXTRACT(MONTH FROM timestamp) AS month, EXTRACT(DAY FROM timestamp) AS day, 1 AS sum FROM _reporting ORDER BY id DESC")->getRows();
		$filter = Reporting::makeFilter($filter, $date);
		if($full){
			$rows = $db->Execute("SELECT * FROM _reporting".$filter." ORDER BY id DESC")->getRows();
		}else{
			$rows = $db->Execute("SELECT activity, target, target_name FROM _reporting".$filter." ORDER BY id DESC")->getRows();
		}
		foreach($rows as &$row){
			$row["activity"] = Reporting::activityToString($row["activity"]);
			$row["target"] = Reporting::targetToString($row["target"]);
			$row["sum"] = 1;
		}
		return json_encode($rows);
	}

	function makeFilter($filter, $date){
		$str = " WHERE 1=1";
		if($filter !== null){
			foreach($filter as $column => $value){
				$n = is_numeric($value);
				$str .= " AND ".$column." = ".($n?"":"'").$value.($n?"":"'");
			}
		}
		if($date !== null){
			if($date["range"] == false){
				foreach($date as $column => $value){
					if($column == "range") continue;
					if($value === false) continue;
					if($column == "year") $type = "YEAR";
					if($column == "month") $type = "MONTH";
					if($column == "day") $type = "DAY";
					$str .= " AND EXTRACT(".$type." FROM timestamp) = ".$value;
				}
			}else{
				$start = "TIMESTAMP '".$date["year"]."-".$date["month"]."-".$date["day"]." 00:00:00'";
				$end = "TIMESTAMP '".$date["year2"]."-".$date["month2"]."-".(int)$date["day2"]." 24:00:00'";
				$str .= " AND timestamp >= ".$start." AND timestamp < ".$end;
			}
		}
		return $str;
	}

	function activityToString($activityId){
		switch($activityId){
			case REPORT_ACTIVITY_CREATE: return 'Create'; break;
			case REPORT_ACTIVITY_RETRIEVE: return 'Retrieve'; break;
			case REPORT_ACTIVITY_UPDATE: return 'Update'; break;
			case REPORT_ACTIVITY_DELETE: return 'Delete'; break;
			case REPORT_ACTIVITY_EMBED: return 'Embed'; break;
			case REPORT_ACTIVITY_OPEN: return 'Open'; break;
			case REPORT_ACTIVITY_SHARE: return 'Share'; break;
			case REPORT_ACTIVITY_GIVE: return 'Give'; break;
		}
		return false;
	}

	function stringToActivity($str){
		switch($str){
			case 'Create': return REPORT_ACTIVITY_CREATE; break;
			case 'Retrieve': return REPORT_ACTIVITY_RETRIEVE; break;
			case 'Update': return REPORT_ACTIVITY_UPDATE; break;
			case 'Delete': return REPORT_ACTIVITY_DELETE; break;
			case 'Embed': return REPORT_ACTIVITY_EMBED; break;
			case 'Open': return REPORT_ACTIVITY_OPEN; break;
			case 'Share': return REPORT_ACTIVITY_SHARE; break;
			case 'Give': return REPORT_ACTIVITY_GIVE; break;
		}
		return false;
	}

	function environmentToString($environmentId){
		switch($environmentId){
			case REPORT_ENVIRONMENT_DMI: return 'DMI'; break;
			case REPORT_ENVIRONMENT_VIEWER: return 'Viewer'; break;
			case REPORT_ENVIRONMENT_EXTERNAL: return 'External'; break;
		}
		return false;
	}

	function stringToEnvironment($str){
		switch($str){
			case 'DMI': return REPORT_ENVIRONMENT_DMI; break;
			case 'Viewer': return REPORT_ENVIRONMENT_VIEWER; break;
			case 'External': return REPORT_ENVIRONMENT_EXTERNAL; break;
		}
		return false;
	}

	function targetToString($targetId){
		switch($targetId){
			case REPORT_TARGET_MAP: return 'Map'; break;
			case REPORT_TARGET_LAYER: return 'Layer'; break;
			case REPORT_TARGET_PROJECT_LAYER: return 'Project Layer'; break;
			case REPORT_TARGET_PERSON: return 'Person'; break;
			case REPORT_TARGET_GROUP: return 'Group'; break;
		}
		return false;
	}

	function stringToTarget($str){
		switch($str){
			case 'Map': return REPORT_TARGET_MAP; break;
			case 'Layer': return REPORT_TARGET_LAYER; break;
			case 'Project Layer': return REPORT_TARGET_PROJECT_LAYER; break;
			case 'Person': return REPORT_TARGET_PERSON; break;
			case 'Group': return REPORT_TARGET_GROUP; break;
		}
		return false;
	}

	function recipientTypeToString($recipientTypeId){
		switch($recipientTypeId){
			case REPORT_RECIPIENT_TYPE_PERSON: return 'Person'; break;
			case REPORT_RECIPIENT_TYPE_GROUP: return 'Group'; break;
		}
		return false;
	}

	function stringToRecipientType($str){
		switch($str){
			case 'Person': return REPORT_RECIPIENT_TYPE_PERSON; break;
			case 'Group': return REPORT_RECIPIENT_TYPE_GROUP; break;
		}
		return false;
	}

	function columnAndStringToNumber($column, $str){
		switch($column){
			case 'target': return Reporting::stringToTarget($str); break;
			case 'activity': return Reporting::stringToActivity($str); break;
			case 'environment': return Reporting::stringToEnvironment($str); break;
			case 'recipient_type': return Reporting::stringToRecipientType($str); break;
		}
		return $str;
	}
}
?>