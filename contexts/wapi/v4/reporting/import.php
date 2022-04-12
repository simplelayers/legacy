<?php

use model\reporting\ImportReport;
use utils\ParamUtil;
function _exec() {
	
	$importReport = new ImportReport();
	
	$args = WAPI::GetParams();
	
	$action =  ParamUtil::RequiresOne($args,'action');
	
	switch($action) {
		case 'get':
			$reportId = ParamUtil::GetOne($args,'report');
			if($reportId) {
				$report = $importReport->GetReport($reportId);
				
				WAPI::SendSimpleResponse($report);
			}
	}
	
	
	
	
}


?>