<?php
namespace model\reporting;

use utils\ParamUtil;
class Reports
{
    
    const ORG_LAYER_COUNTS='org_layer_counts';
    const ORG_LAYER_TYPE_COUNTS='org_layer_type_counts';
    const ORG_MAP_COUNTS='org_map_counts';
    
    public static function Get($reportName,$args) {
        
        $orgId = ParamUtil::Get($args,'orgId');
        $db = \System::GetDB(\System::DB_ACCOUNT_SU);
         
        switch($reportName) {
            case self::ORG_LAYER_COUNTS:
                $query = 'select * from _report_orgTotalLayerCount_view';
                if($orgId) $query.=" where org_id=$orgId";
                $result = $db->Execute($query);
                break;
            case self::ORG_LAYER_TYPE_COUNTS:
                $query = 'select * from _report_orglayercountbytype_view';
                if($orgId) $query.=" where org_id=$orgId";
                break;
            case self::ORG_MAP_COUNTS:
                $query = 'select * from _report_orgTotalMapCount_view';
                if($orgId) $query.=" where org_id=$orgId";
                break;                        
        }
        if(is_null($query)) throw new \Exception('Report not recognized: '.$reportName.' not recognized');
        
        $result = $db->Execute($query);
        return $result;
        
    }
    
    
    
    
}

?>