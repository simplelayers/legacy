<?php

namespace utils;

use System;

class SQLUtil
{

    public static function InsertToObj($insertQuery = null)
    {
        \System::RequireSQLParser();
        $insertQuery = <<<QUERY
INSERT INTO "vectordata_5695" ("objectid","recordtype","hyperlink","id","popup","apn","descript","dt_record","surveyor","lic_type","surveyed","cr_type","corner","street","cross_st","ref_case","lic_number","projectno","pagesuffix",the_geom) VALUES ('786','Corner Record','http://countyofsb.org/pwd/surveyor/cr_pdfs/700_799/CR0769.pdf','CR0769','<a href="http://countyofsb.org/pwd/surveyor/cr_pdfs/700_799/CR0769.pdf" target=_blank>CR0769</a>','069-261-015','Tract 11184 BK 77/84','20000412','Edmund R. Villa','PLS','19990803','Other','Left As Found','Coralino Road','Cambridge Drive',NULL,'6232','769',NULL,'0101000020E6100000BB310C2782F45DC024A09A1071394140');
QUERY;
        $parser = new \PHPSQLParser($insertQuery);
        $parsed = $parser->parsed;
        $columns = array();
        $record = array();
        foreach ($parsed["INSERT"]['columns'] as $column) {
            $columns[] = trim($column['base_expr'], " \"");
        }

        $i = 0;
        $values = array_shift($parsed["VALUES"]);
        $values = $values['data'];

        foreach ($values as $data) {
            $data = trim($data['base_expr'], " \t\n\r'");

            if ((strpos($data, '.'))) {
                if (filter_var($data, FILTER_VALIDATE_FLOAT)) {

                    $data = (float) $data;
                }
            } elseif (filter_var($data, FILTER_VALIDATE_INT)) {
                $data = (int) $data;
            }
            $record[$columns[$i]] = $data;
            $i++;
        }
        return $record;
    }

    public static function GetDistance($lat1, $lon1, $lat2, $lon2)
    {
        $db = \System::GetDB();
        // use the PosTGIS function distance_spheroid() to fetch the linear distance in meters
        $geom1 = "ST_GeometryFromText('POINT($lon1 $lat1)',4326)";
        $geom2 = "ST_GeometryFromText('POINT($lon2 $lat2)',4326)";
        $spheroid = 'SPHEROID["WGS 84",6378137,298.257223563]';
        //$db->debug = true;
        $distance = $db->Execute("SELECT ST_DistanceSpheroid($geom1,$geom2,'$spheroid') AS meters");

        $distance = $distance->fields['meters'];

        // make up the conversions
        $meters = $distance;
        $kilometers = $meters / 1000;
        $feet = $meters * 3.2808399;
        $miles = $feet / 5280;

        // done!
        return array($feet, $miles, $meters, $kilometers);
    }

    public static function GetDistances($points, $order = 'latlon')
    {
        $segments = array();
        $total = array('feet' => 0, 'miles' => 0, 'meters' => 0, 'kilometers' => 0);
        if (sizeof($points) < 2)
            throw new \Exception('Too few points for distance calculation');
        for ($i = 1; $i < sizeof($points); $i++) {

            // fetch the point-pair and split into ordinates. then fetch the distance
            switch ($order) {
                case 'latlon':
                    $p1 = $points[$i - 1];
                    list($p1lat, $p1lon) = explode(' ', trim($p1));
                    $p2 = $points[$i];
                    list($p2lat, $p2lon) = explode(' ', trim($p2));
                    break;
                case 'lonlat':
                    $p1 = trim($points[$i - 1]);
                    list($p1lon, $p1lat) = explode(' ', trim($p1));
                    $p2 = trim($points[$i]);
                    list($p2lon, $p2lat) = explode(' ', trim($p2));
                    break;
            }

            list($feet, $miles, $meters, $kilometers) = SQLUtil::GetDistance($p1lat, $p1lon, $p2lat, $p2lon);

            // increment the total
            $total['feet'] += $feet;
            $total['miles'] += $miles;
            $total['meters'] += $meters;
            $total['kilometers'] += $kilometers;

            // log info about this segment
            $segment = array('from' => $p1, 'to' => $p2, 'feet' => $feet, 'miles' => $miles, 'meters' => $meters, 'kilometers' => $kilometers);
            array_push($segments, $segment);
        }
        return array('total' => $total, 'segments' => $segments);
    }
    public static function GetColumnsSans($table, $except = 'id')
    {
        $query = <<<QUERY
SELECT FIRST_VALUE(SELECT string_agg(quote_ident(attname), ', ' ORDER BY attnum)
FROM   pg_attribute
WHERE  attrelid = 'public.$table'::regclass
AND    NOT attisdropped  -- no dropped (dead) columns
AND    attnum > 0        -- no system columns
QUERY;
        if ($except !== '') {
            $exceptions = explode(',', $except);
            $excepted = self::StringifyValues($exceptions, ',');
            $query .= " AND    attname not ilike any(array[$excepted]))";
        }

        $db = System::GetDB();
        return $db->GetOne($query);
    }

    public static function StringifyValues(array $vals, $joinChar = false, $quote = '"')
    {
        $stringified = [];
        while (count($vals) > 0) {
            $stringified[] = $quote . array_shift($vals) . $quote;
        }
        if ($joinChar !== false) {
            return implode($joinChar, $stringified);
        }
        return $stringified;
    }

    public static function DuplicateRow($table, $targetId, $idField, $excludeId = true)
    {
        $except = ($excludeId === true) ? $idField : false;
        $columns = self::GetColumnsSans($table, $except);
        $query = <<<QUERY
SELECT INTO $table($columns) SELECT $columns from $table where id=$targetId
QUERY;
        $db = System::GetDB();
        $result = $db->Execute($query);
        if (!$result) {
            return false;
        } else {
            return $db->insert_Id();
        }
    }

    public static function SetIDSequence($table, $idField)
    {
        $seqName = $table . '_' . $idField . '_seq';
        $db = System::GetDB();

        $ok = $db->Execute('create sequence $seqName') !== false;
        if (!$ok) {
            return false;
        }
        $ok = $db->Execute("select setval($seqName,(select max($idField) as $idField from $table));") !== false;
        if (!$ok) {
            return false;
        }
        $ok = $db->Execute("alter table $table.$idField set not null;") !== false;
        if (!$ok) {
            return false;
        }
        $ok = $db->Execute('$query', "alter table $table.$idField set default nextval('$seqName'::regclass);") !== false;
        return $ok;
    }

    public function Withify($sql, $with = false, $as = null)
    {
        $prefix = is_null($as) ? '' : (($with === false) ? ", $as as(" : "with $as as(");
        $postFix = is_null($as) ? "" : ")";
        return $prefix . $sql . $postFix;
    }

    public static function ReplaceTable($table, $withGeom = true, $xD = 2, $wName = true)
    {
        $db = \System::GetDB();
        if (stripos($table, 'vectordata_') !== 0) throw new \Exception('SLQUtil::ReplaceTable - only allows deletion of tables with a vectordata_ prefix');
        $db->Execute("DROP TABLE  if exists {$table}");
        self::MakeNewTable($table, $withGeom, $xD, $wName);
    }
    public static function MakeNewTable($table, $withGeom = true)
    {
        $db = \System::GetDB();
        $db->Execute("CREATE TABLE {$table} (gid serial,name text)");
        if ($withGeom === true) {
            $db->Execute("SELECT AddGeometryColumn('','{$table}','the_geom',4326,'GEOMETRY',2)");
        }
        $db->Execute("CREATE INDEX {$table}_index_the_geom ON $table USING GIST (the_geom)");
    }

    public static function MakeTempTable($table, $withGeom = true)
    {
        $db = \System::GetDB();
        $db->Execute("CREATE TEMP TABLE {$table} (gid serial,name text)");
        $db->Execute("SELECT AddGeometryColumn('','{$table}','the_geom',4326,'GEOMETRY',2)");
        $db->Execute("CREATE INDEX {$table}_index_the_geom ON $table USING GIST (the_geom)");
    }

    public static function SpatializeTable($table)
    {
        $db = \System::GetDB();

        $info = $db->Execute("select * from {$table} where 1=0");
        $fields = $info->fieldTypesArray();
        foreach ($fields as $field) {
            if ($field->name === 'the_geom') {
                return true;
            }
        }
        $db->debug = true;
        $res = $db->Execute("SELECT AddGeometryColumn('','{$table}','the_geom',4326,'GEOMETRY',2)");
        if (!$res) {
            throw new \Exception('Problem adding geometry column:' . $db->ErrorMsg());
        }
        return true;
    }
}
