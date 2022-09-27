<?php

namespace model\viewdefs;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class SLAttributeInfoView {

    static function MakeView() {
        $db = System::GetDB();
        $query = <<<QUERY
CREATE OR REPLACE VIEW sl_attribute_info as 
with layerInfo as (select * from layers),
table_names as (select Concat('vectordata_', "name") as table_name from layerInfo)
select table_name,column_name,data_type,domain_name,
	case 
		when data_type='integer' THEN 'integer'
		when column_name='the_geom' THEN 'geometry'
		when domain_name='cg_url' THEN 'url'
		when data_type='character varying' then 'string'
		when data_type='character' then 'string'
		when data_type='char' then 'string'
		when data_type='varchar' then 'text'
		when data_type='text' then 'text'
		when data_type='date' then 'date'
		when data_type='timestamp with timezone' then 'timestamp'
		when data_type='timestamp without timezone' then 'timestamp'
		when data_type='time' then 'time'
		when data_type='time with timezone' then 'time'
		when data_type='integer' then 'integer'
		when data_type='double precision' then 'float'
		when data_type='numeric' then 'float'
		when data_type='smallint' then 'integer'
		when data_type='bigint' then 'integer'
		when data_type='decimal' then 'float'
		when data_type='real' then 'integer'
		when data_type='smallserial' then 'integer'
		when data_type='serial' then 'integer'
		when data_type='bigserial' then 'integer'
                ELSE data_type::text
	END as type,
ordinal_position,column_default,is_nullable,character_maximum_length,character_octet_length, numeric_precision,numeric_precision_radix,numeric_scale,datetime_precision,
	case when data_type like '%with timezone%' then true ELSE false END as with_timezone from information_schema.columns 
	where table_catalog='simplelayers' 
	AND table_schema='public'
 	AND table_name like 'vectordata_%'
	
QUERY;
        $db->Execute('DROP VIEW sl_attribute_info IF Exists');
        return $db->Execute($query);
    }

    static function GetAttributeLookup(\Layer $layer) {
        $db = \System::GetDB();
        $query = <<<QUERY
with srcdata as (
    select
    column_name as "name",
    type,
    ordinal_position as "table_z",
    is_nullable as "nullable",
    character_maximum_length as "max_chars",
    numeric_precision as "precision",
    numeric_scale as "scale",
    numeric_precision_radix as "radix",
    datetime_precision as "precisiond",
    with_timezone as "has_tz"
    from sl_attribute_info
    where table_name=?
    order by table_z)
    select name, row_to_json(srcdata.*) as val from srcdata;
QUERY;
        $response = [];
        $results = $db->Execute($query, [$layer->url]);
        if ($results) {
            foreach ($results as $row) {
                $response[$row['name']] = json_decode($row['val'], true);
            }
        }
        return $response;
    }

    static function GetAttributeInfo($layer, $attName) {
        if(!$layer->HasTable()) {
            return false;
        }
        $db = \System::GetDB();
        $query = <<<QUERY
select
column_name as "name",
type,
ordinal_position as "table_z",
is_nullable as "nullable",
character_maximum_length as "max_chars",
numeric_precision as "precision",
numeric_scale as "scale",
numeric_precision_radix as "radix",
datetime_precision as "precisiond",
with_timezone as "has_tz"
from sl_attribute_info
where table_name=? and column_name=?
order by table_z
QUERY;
        
        $result = $db->GetRow($query, [$layer->url, $attName]);
        
        return $result;
    }

}
