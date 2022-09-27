<?php

namespace utils;

class ParamUtil {

    public static function Has($src, $what) {
        return isset($src[$what]);
    }

    public static function Get($src, $what, $default = null, $detectJSON = false) {
        if (is_null($src)) {
            throw new Exception('attempting to get property of null value');
        }
        if (count($src) == 0)
            return $default;
        if (!is_array($src))
            throw new \Exception($src . ' is not an array');
        $keys = implode(',', array_keys($src));
        $keys = strtolower($keys);
        $keys = explode(',', $keys);
        
        $index = array_search(strtolower($what), $keys);
        $keys = array_keys($src);
        if ($index === false)
            return $default;

        if (!isset($src[$keys[$index]]))
            return $default;

        $val = $src[$keys[$index]];


        if ($detectJSON) {
            // TODO: Verify stripos check still works. 
            if ((stripos($val, '{"json":') === 0) || (stripos($val, "{'json':") == 0)) {
                $val = self::GetJSON($src, $keys[$index], $default);
                return $val['json'];
            }
        }
        return $val;
    }

    public static function ForceOne($src, $what, $default) {
        $value = self::Get($src, $what, $default);
        $src[$what] = $default;
    }

    public static function GetJSON($src, $what, $default = array()) {

        if (!isset($src [$what]))
            return $default;

        $content = $src[$what];
        $content = json_decode($content, true);
        if (!$content)
            return $src[$what];
        return $content;
    }

    public static function GetBoolean($src) {
        $args = func_get_args();
        array_shift($args);
        $vals = array();
        foreach ($args as $arg) {
            if (isset($src[$arg])) {
                $val = $src[$arg];
                if (intval($val) == '1') {
                    $vals[$arg] = true;
                } elseif ($val == 'true' || $val == 't') {
                    $vals[$arg] = true;
                } else {
                    $vals[$arg] = false;
                }
            } else {
                $vals[$arg] = null;
            }
        }
        return array_values($vals);
    }

    public static function GetList($src, $delim, $param) {
        if (!isset($src[$param]))
            return array();
        return explode($delim, $src[$param]);
    }

    public static function RequireJSON($src, $what, $default = array()) {
        if (!isset($src [$what]))
            throw new \Exception('Missing required parameters: ' . $what);
        $content = $src[$what];
        return json_decode($content, true);
    }

    public static function Requires(array $src) {
        $args = func_get_args();
        array_shift($args);

        $keys = array_keys($src);
        $missing = array();
        $values = array();
        foreach ($args as $arg) {
            if (!in_array($arg, $keys)) {
                $missing [] = $arg;
                continue;
            }
            $val = trim($src[$arg]);
            if (stripos(substr($val, 0, 6), 'json')) {
                $val = json_decode($val, true);
                $val = $val['json'];
            }
            $values[] = $val;
        }
        if (count($missing))
            throw new \Exception('Missing required parameters: ' . implode(',', $missing));
        return $values;
    }

    public static function RequiresOne(array $src) {
        $args = func_get_args();
        array_shift($args);

        $keys = array_keys($src);


        $matches = 0;
        foreach ($keys as $key) {

            if (in_array($key, $args)) {
                $matches++;
                break;
            }
        }

        if ($matches == 0)
            throw new \Exception('Missing required parameters: requires one of ' . implode(',', $args));
        return $src[$key];
    }

    public static function Prune(&$src) {
        $args = func_get_args();
        array_shift($args);

        foreach ($args as $key) {
            if (isset($src [$key]))
                unset($src [$key]);
        }
    }

    public static function GetOne($src) {
        $args = func_get_args();
        array_shift($args);
        $keys = array_keys($src);

        foreach ($args as $key) {
            if (in_array($key, $keys)) {
                return $src [$key];
            }
        }
    }

    public static function GetAlLValues(array $src) {
        $args = func_get_args();
        array_shift($args);
        $values = array();

        foreach ($args as $arg) {
            if (isset($src[$arg])) {
                $values[$arg] = $src[$arg];
            } else {
                
            }
        }

        return $values;
    }

    public static function GetValues($src) {

        $args = func_get_args();
        array_shift($args);
        $values = array();
        $missing = array();
        foreach ($args as $arg) {

            $arg = explode(':', $arg);
            $as = '';
            if (count($arg) == 2) {
                list($arg, $as) = $arg;
            } else {
                $arg = array_shift($arg);
                $as = $arg;
            }
            if (isset($src[$arg])) {

                $values[$as] = $src[$arg];
            } else {
                //$values[$as] = null;
            }
        }

        return $values;
    }

    public static function GetRequiredValues(array $src) {
        $args = func_get_args();
        array_shift($args);
        $values = array();
        $missing = array();
        foreach ($args as $arg) {
            $arg = explode(':', $arg);
            $as = '';
            if (count($arg) == 2) {
                list($arg, $as) = $arg;
            } else {
                $arg = array_shift($arg);
                $as = $arg;
            }
            if (isset($src[$arg])) {

                $values[$as] = $src[$arg];
            } else {
                $missing[] = $arg;
            }
        }
        if (count($missing))
            throw new \Exception('Missing required parameters: ' . implode(',', $missing));
        return $values;
    }

    public static function ListValues(array $src) {
        $args = func_get_args();
        array_shift($args);

        $values = array();
        foreach ($args as $arg) {
            if (isset($src[$arg])) {
                $values[] = $src[$arg];
            } else {
                $values[] = null;
            }
        }
        return $values;
    }

    public static function ParseParams($src) {
        $params = "{";
        $firstVal = true;

        foreach ($src as $data) {
            $params .= $firstVal ? '' : ',';
            if (strpos($data, ':')) {
                list ( $key, $val ) = explode(':', $data);
                $key = "\"$key\"";
                if (preg_match('/\D/', $val)) {

                    $val = "\"$val\"";
                }
                $data = "$key:$val";
                $params .= $data;
            } else {
                if (preg_match('/\D/', $data)) {

                    $params .= "\"$data\"";
                } else {
                    $params .= $data;
                }
            }

            $firstVal = false;
        }
        $params .= '}';

        $params = json_decode($params, true);
        return $params;
    }

    public static function IsAssoc($parameter) {
        if (!is_array($parameter))
            return false;
        $keys = preg_grep('/[^\d]/', array_keys($parameter));
        return count($keys) > 0;
    }

    public static function MinMergeLists($id = 'id', $val = 'value') {
        $args = func_get_args();
        array_shift($args);
        array_shift($args);
        if (count($args) <= 1)
            return $args;
        $firstArg = $args [0];
        $result = array();

        foreach ($firstArg as $item) {
            $values = array();
            $itemId = $item[$id];
            foreach ($args as $arg) {
                if ($arg[$id] == $itemId)
                    $values[] = $arg[$val];
            }
            $result[$itemId] = min($values);
        }
        return $result;
    }

    public static function GetSubValues($params, $subId) {
        $subs = array();
        $subId = explode('.', $subId);
        foreach ($params as $key => $item) {
            $subItem = $item;
            foreach ($subId as $id) {
                $subItem = $subItem[$id];
            }

            if (isset($subItem))
                $subs[] = $subItem;
        }
        return $subs;
    }

    public static function GetColumn($recordSet, $id) {
        $column = array();
        $id = strtolower($id);
        foreach ($recordSet as $record) {
            $column[] = $record[$id];
        }
        return $column;
    }

    public static function SubsetAssocArray($array, $keys) {
        $subset = array();
        foreach ($keys as $key) {
            //if(!in_array($key,$keys)) continue;
            $val = $array[$key];
            ;
            $subset[$key] = $val;
        }

        return $subset;
    }

    public static function ResultsToKeyVal(\ADORecordSet_assoc_postgres9 $dbResults, $keyField, $valField) {
        $data = array();
        foreach ($dbResults as $record) {
            $data[$record[$keyField]] = $record[$valField];
        }
        return $data;
    }

    public static function ResultsToKeyVals(\ADORecordSet_assoc_postgres9 $dbResults, $keyField) {
        $data = array();
        $keys = array_slice(func_get_args(), 2);
        foreach ($dbResults as $record) {
            $val = self::SubsetAssocArray($record, $keys);
            $data[$record[$keyField]] = $val;
        }
        return $data;
    }

    public static function ResultsToArray(\ADORecordSet_assoc_postgres9 $dbResults) {
        return $dbResults->GetRows();
    }

    public static function BoolToTF($boolVar = null) {
        return ($boolVar) ? 't' : 'f';
    }

    public static function SanitizeInts($intArray, $joinOn = null) {

        $resultArray = [];

        foreach ($intArray as $i => $val) {
            $intVal = intval($val);

            if ($intVal === NAN) {
                continue;
            }
            $resultArray[] = $val;
        }
        if (!is_null($joinOn)) {
            return implode($joinOn, $resultArray);
        }
        return $resultArray;
    }

}

?>
