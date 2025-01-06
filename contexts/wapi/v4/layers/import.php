<?php
use utils\ParamUtil;
use utils\PageUtil;

function _exec()
{
    
    $args = WAPI::GetParams();
    
    $world = System::Get();
    $user = SimpleSession::Get()->GetUser();
    
    // TODO implement check for plan's max layer-limit.
    
    $importFmt = ParamUtil::RequiresOne($args,'import_format');
    
    $formatObj = LayerFormats::GetFormatInstance ( $importFmt );
    
    $format = WAPI::GetFormat();
 
    $report = $formatObj->Import($args );
    #$format =   'json';
    switch($format) {
        case 'html':
           PageUtil::RedirectTo('import/reports/layers/report:' . $report['id']);
            break;
        default:
            WAPI::SendSimpleResponse(array('report'=>$report));
            break;
            
    }
}