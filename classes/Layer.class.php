<?php

use auth\Auth;
use auth\Context;
use custom_types\CustomTypeFactory;
use layer_utils\LayerUtils;
use model\logging\Log;
use model\SL_Query;
use utils\ParamUtil;
use utils\SQLUtil;
use model\viewdefs\SLAttributeInfoView;

/**
 * See the Layer class documentation.
 *
 * @package ClassHierarchy
 */
/**
 *
 * @ignore
 *
 *
 */
require_once 'ColorScheme.class.php';

class Layer {

    /**
     *
     * @ignore
     *
     *
     *
     */
    private $world; // a link to the World we live in

    /**
     *
     * @ignore
     *
     *
     *
     */
    public $id; // the layer's unique ID#

    /**
     *
     * @ignore
     *
     *
     *
     */
    public $colorscheme = array(); // the ColorScheme object for this layer

    /**
     *
     * @ignore
     *
     *
     *
     */
    const TABLE = 'layers';

    public $filter_gids;
    public $filter_color = "FFFF00";
    public $filter_field = "gid";
    protected $layer_record = array();

    function __construct(&$world, $id = null, $layer_record = null) {
        if (is_null($id) && is_null($layer_record))
            throw new Exception('Invalid layer construction');
        $this->world = System::Get();
        $this->id = is_null($layer_record) ? $id : (int) $layer_record ['id'];

        $this->RefreshLayerRecord($layer_record);

        $this->colorscheme = false;

// fetch the layer type. this is used to verify that we exist, and also for the colorscheme
        $type = (int) $this->type;


        if (!$type) {
            $this->type = LayerTypes::VECTOR;
        }
//throw new Exception ( "No such layerid: $id." );
        if (in_array($type, array(
                    LayerTypes::VECTOR,
                    LayerTypes::ODBC,
                    LayerTypes::RELATIONAL
                ))) {
            $this->colorscheme = new ColorScheme($this->world, $this);
        }
    }

    /**
     * Change the type on a given field
     */
    public function ChangeFieldType($field, $newType) {
        $db = $this->world->admindb;
        $table = $this->url;
        $allowedTypes = DataTypes::GetAliases(); // CustomTypeFactory::GetTypes();

        /* array_push($allowedTypes, "text");
          if (!in_array(strtolower(trim($newType)), $allowedTypes)) {
          return false;
          } */
        $type = false;
        $isURL = in_array($newType,['url','cg_url']);
        if ($isURL ) {
            $type = 'cg_url';
        } else {
            $type = $allowedTypes[$newType];
        }
        if (!$type) {
            return false;
        }
       
        # $db->debug = true;
        $query = "ALTER TABLE $table alter column $field TYPE $type USING $field::$type";
        $result = $db->GetRow($query);
        if (!$result) {
            $err = $db->ErrorMsg();
            if ($type === 'int') {
                if (stripos(strtolower($err), 'is out of range for type integer') > -1) {
                    $query = "ALTER TABLE $table alter column $field TYPE bigint USING ($field::numeric)";
                    $result = $db->GetRow($query);
                    if(!$result) $type = 'int or bigint';
                }
            }
        }
        if ($result !== false) {
            return "ok";
        } else {
            throw new \Exception($field . ' could not be converted to ' . $type);
            error_log("Attempting to convert " . $this->id . " field $field to $type:" . $db->ErrorMsg());
        }
    }

    public static function GetHasRecordsQuery() {
        $query = new SL_Query();
        $query->newTableQuery(self::TABLE);
        $query->AddInCriteria('type', array(LayerTypes::VECTOR, LayerTypes::RELATABLE, LayerTypes::RELATIONAL));
        $query->AddSort('id');
        return $query;
    }

    public static function GetTypeQuery($type) {
        $query = new SL_Query();
        $query->newTableQuery(self::TABLE);
        $query->AddEqualsCriteria('type', $type);
        $query->AddSort('id');
        return $query;
    }

    function getHasRecords() {
        if (in_array($this->type, array(LayerTypes::VECTOR, LayerTypes::RELATABLE, LayerTypes::RELATIONAL)))
            return true;
        $geom_type = $this->geom_type;
        $record_types = array(GeomTypes::LINE, GeomTypes::POINT, GeomTypes::POLYGON, GeomTypes::RELATABLE);

        if (in_array($geom_type, $record_types))
            return true;
        return false;
    }

    function getHasEditableRecords() {

        return in_array(+$this->type, array(LayerTypes::VECTOR, LayerTypes::RELATABLE));
    }

// TODO Rename

    function setLayerType($typeId = null) {
        $db = System::GetDB(System::DB_ACCOUNT_SU);
        if ($typeId) {

            if (LayerTypes::IsValidType($typeId)) {

                $db->Execute("update " . self::TABLE . " set type=$typeId where id=$this->id");
                $this->touch();
            }
        }
        return $this->type;
    }

    function setLayerGeomType($typeId = null) {
        if ($typeId) {
            $this->world->db->Execute("update " . self::TABLE . " set geom_type=$typeId where id=$this->id");
            return $typeId;
        }

        if (stripos($this->url, 'vectordata_') !== 0)
            return GeomTypes::UNKNOWN;

        $stats = $this->world->db->GetRow("select position('POINT' in geom) as isPoint, position('POLY' in geom) as isPolygon, position('LINE' in geom) as isLine from (SELECT ST_ASTEXT(the_geom) as geom from {$this->url} where the_geom <> '' limit 1) as q1 ");
        if (!$stats) {
            return GeomTypes::UNKNOWN;
        }
        if (count($stats) == 0) {
            return GeomTypes::UNKNOWN;
        }
        $geom_type = "";
        $layerId = '';

        foreach ($stats as $type => $is) {

            if ((int) $is > 0) {
                $geoms = GeomTypes::GetEnum();
                $geom_type = $geoms [substr($type, 2)];
// $geom_type = GeomTypes:: substr($type,2);//strip off 'is';
                break;
            }
        }
        $res = $this->world->db->Execute("update " . self::TABLE . " set geom_type=$geom_type where id=$this->id");

        if (!$res) {

            Log::Error($this->world->db->ErrorMsg());
            ob_flush();
            throw new Exception('Unable to set layer geometry');
            die();
        }

        ob_flush();
        return ($geom_type == '') ? GeomTypes::UNKNOWN : $geom_type;
    }

// /// make attributes directly fetchable and editable

    /**
     *
     * @ignore
     *
     *
     *
     */
    function __get($name) {
// TODO: Optimize this function using case statments and removing redundant if's.
        $getter = '__get_' . $name;

        if (method_exists($this, $getter)) {
            return call_user_func([$this, $getter]);
        }
        $ini = System::GetIni();
// simple sanity check
        if (preg_match('/\W/', $name))
            return false;
        if ($name == 'classification')
            return $this->colorscheme;
// if they ask for the URL attribute, vectors and rasters are special cases, returning their datafile names
        if ($name == 'url') {
            if ($this->type == LayerTypes::VECTOR)
                return "vectordata_{$this->id}";
            if ($this->type == LayerTypes::RELATIONAL)
                return "vectordata_{$this->id}";
            if ($this->type == LayerTypes::RASTER)
                return "{$ini->rasterdir}{$this->id}.tif";
            if ($this->type == LayerTypes::ODBC) {
                $value = $this->world->db->Execute("SELECT url FROM " . self::TABLE . " WHERE id=?", array(
                    $this->id
                ));
                $value = $value->fields ['url'];
                $value = json_decode($value);
                return $value;
            }
            if ($this->type == LayerTypes::WMS) {
                $value = $this->world->db->Execute("SELECT url FROM " . self::TABLE . " WHERE id=?", array(
                    $this->id
                ));
                return $value->fields ['url'];
            }
            if ($this->type == LayerTypes::RELATABLE)
                return "vectordata_{$this->id}";
        }

// make the artificial "geomtype" attribute, which says point, line, or polygon
        if ($name == 'geomtype') {
            if ($this->type == LayerTypes::RASTER)
                return GeomTypes::RASTER;
            if ($this->type == LayerTypes::WMS)
                return GeomTypes::WMS;
            if ($this->type == LayerTypes::ODBC)
                return GeomTypes::POINT;
            if ($this->type == LayerTypes::COLLECTION)
                return GeomTypes::COLLECTION;


            if (in_array($this->geom_type, ['', null])) {

                return $this->setLayerGeomType();
            } else {

                return $this->geom_type;
            }
// must be VECTOR or RELATIONAL to have gotten this far
            /*
             * $value = $this->world->db->Execute ( 'SELECT type FROM geometry_columns WHERE f_table_name=?', array ( $this->url ) ); if (preg_match ( '/POINT/', $value->fields ['type'] )) { return GeomTypes::POINT; } elseif (preg_match ( '/POLYGON/', $value->fields ['type'] )) { return GeomTypes::POLYGON; } elseif (preg_match ( '/LINE/', $value->fields ['type'] )) { return GeomTypes::LINE; } else { return GeomTypes::UNKNOWN; }
             */
        }
        if ($name == 'geomtypestring') {

            $geomTypes = GeomTypes::GetEnum();
            $layerTypes = LayerTypes::GetEnum();
            $isFeatureSource = LayerTypes::IsFeatureSource($this->type);

            if ($isFeatureSource) {
                $gType = intval($this->geom_type);
                if (is_int($gType)) {
                    return LayerUtils::ToGeomTypeString($this);
                }
            }
            return $layerTypes [$this->type];
        }
        if ($name == 'geomtyperaw') {
            if ($this->type == LayerTypes::RASTER)
                return GeomTypes::RASTER;
            if ($this->type == LayerTypes::WMS)
                return GeomTypes::WMS;
            if ($this->type == LayerTypes::ODBC)
                return GeomTypes::POINT;
            if ($this->type == LayerTypes::COLLECTION)
                return GeomTypes::COLLECTION;
            $value = $this->world->db->Execute('SELECT type FROM geometry_columns WHERE f_table_name=?', array(
                $this->url
            ));
            return $value->fields ['type'];
        }
// convert last_modified to seconds
        if ($name == 'last_modified_seconds') {
            $value = $this->world->db->Execute("SELECT DATE_PART('epoch',now()-last_modified) AS seconds FROM " . self::TABLE . " WHERE id=?", array(
                $this->id
            ));
            return $value->fields ['seconds'];
        }
        if ($name == 'last_modified_unix') {
            $value = $this->world->db->Execute("SELECT DATE_PART('epoch',last_modified) AS unixtime FROM " . self::TABLE . " WHERE id=?", array(
                $this->id
            ));
            return $value->fields ['unixtime'];
        }

// make the artificial "diskusage" attribute, being the bytes of disk space occupied by this layer
        if ($name == 'diskusage') {
            $type = (int) $this->type;

            if ($type == LayerTypes::WMS)
                return 0;
            if ($type == LayerTypes::ODBC)
                return 0;
            if ($type == LayerTypes::RELATIONAL)
                return 0;
            if ($type == LayerTypes::COLLECTION)
                return 0;
            if ($type == LayerTypes::RASTER)
                return file_exists($this->url) ? filesize($this->url) : null;

// else it must be a vector
            $s = $this->world->db->GetOne("SELECT pg_relation_size('{$this->url}') AS size");
            return $s; // $s->fields['size'] / Units::MEGABYTE;
        }
        if ($name == 'area') {
            /*
             * if($this->geomtype == GeomTypes::POLYGON){ $value = $this->world->db->Execute("SELECT sum(ST_Area(ST_Transform(the_geom,900913))*0.000000386102) AS miles, sum(ST_Area(ST_Transform(the_geom,900913))*0.000001) AS kilometers FROM {$this->url}"); return $value->fields; }else
             */
            return false;
        }
        if ($name == "backuptime") {
            $value = $this->world->db->Execute("SELECT to_char(\"$name\", 'MM/DD/YY @ HH12:MI am') AS value FROM " . self::TABLE . " WHERE id=?", array(
                $this->id
            ));
            return $value->fields['value'];
        }
// if we got here, it must be a direct attribute
// a little fancy: return an object for some calls, e.g. a Person instead of a username or id
        if ($name == 'ownerid') {
            return $this->layer_record ['owner'];
        }


        $realName = $name;
        if ($realName == 'label_style_string')
            $name = 'label_style';

        $value = isset($this->layer_record[$name]) ? $this->layer_record[$name] : null;

        if ($name === 'field_info') {
            $jsonValue = json_decode($value, true);
            if ($jsonValue) {
                if (count($jsonValue) === 0) {
                    $value = null;
                } else {
                    $value = $jsonValue;
                }
            } else {
                $value = null;
            }

            if (is_null($value) || ($value === '')) {
                $info = array_values($this->getAttributesVerbose(false));
                $this->field_info = $info;
                $value = $info;
            }
            return $value;
        }
        if ($name === 'rich_tooltip') {
            if (in_array($value, array(
                        false,
                        '',
                        null
                    )) > - 1) {
                return false;
            } else {
                if (substr($value, 0, 4) === 'b64:') {
                    $value = base64_decode(substr($value, 4));
                }
                return $value;
            }
        }

        if ($realName == 'label_style_string')
            return $value;
        if ($name == 'label_style')
            return json_decode($value, true);


        /*
         * $this->world->db->Execute ( "SELECT \"$name\" AS value FROM layers WHERE id=?", array ( $this->id ) );
         */

// value = $value ['value'];
// error_log($name);

        if ($name == 'owner') {

            if (is_null($value))
                $value = 0;

            return System::Get()->getPersonById((int) $value);
        }

        if (!$value)
            return null;

        if ($name == 'metadata' && $value != "") {
            return unserialize(gzuncompress(base64_decode($value)));
        }
        if ($name == 'field_info') {
            return json_decode($value, true);
        }
        if ($name == 'custom_data') {
            return json_decode($value, true);
        }
        if ($name == 'import_info') {
            return json_decode($value, true);
        }
        return $value;
    }

    /**
     *
     * @ignore
     *
     *
     *
     */
    function __set($name, $value) {
// simple sanity check

        if (preg_match('/\W/', $name))
            return false;

// a few items cannot be set
        switch ($name) {

            case 'id' :
            case 'type' :
            case 'owner' :
            case 'colorscheme' :
            case 'geomtype' :
            case 'diskusage' :
            case 'last_modified' :
                return false;
                break;
            case 'url' :
                if ($this->type != LayerTypes::WMS and $this->type != LayerTypes::ODBC)
                    return false;
                break;
            case 'metadata' :
                if (!is_null($value)) {
//$this->deep_ksort ( $value );
                    $value = base64_encode(gzcompress(serialize($value), 9));

                    $this->world->db->Execute("UPDATE " . self::TABLE . " SET metadata=? WHERE id=?", array(
                        $value,
                        $this->id
                    ));
                    $this->touch();
                    return;
                }
                break;
            case 'field_info' :
            case 'custom_data' :
            case 'import_info' :
            case 'label_style':
                if (is_string($value)) {
                    if ((stripos($value, '{"json":') === 0) || (stripos($value, "{'json':") == 0)) {
                        $value = ParamUtil::GetJSON(array('arg' => $value), 'arg');
                    }
                }
                if (!is_string($value)) {
                    $value = is_null($value) ? null : json_encode($value);
                } else {
                    if (!json_decode($value)) {
                        $value = null;
                    }
                }
                break;
        }
// if we got here, we're making a change; so flag us as having been modified
        $this->touch();
#$this->world->db->debug = true;	
// if we got here, we must be setting a direct attribute
        $this->world->db->Execute("UPDATE " . self::TABLE . " SET \"$name\"=? WHERE id=?", array(
            $value,
            $this->id
        ));
    }

    /**
     * Update the Layer's last_modified to be the current date+time.
     */
    public function touch() {
        $this->world->db->Execute('UPDATE ' . self::TABLE . ' SET last_modified=NOW() WHERE id=?', array(
            $this->id
        ));

        if (LayerTypes::IsFeatureSource($this->type)) {
#$this->GenerateThumbnail(true,false);
        }

        $this->RefreshLayerRecord();
    }

    function RefreshLayerRecord($record = null) {

        $this->layer_record = is_null($record) ? $this->world->db->GetRow("select * from " . self::TABLE . " where id=" . $this->id) : $record;
    }

    function GetLayerRecord() {
        return $this->layer_record;
    }

// /// the self-destruct button: clean up files/tables

    /**
     * Delete the layer from the system, handling dependencies e.g.
     * its presence in projects.
     */
    function delete() {

// vector: drop the table hosting our info
        if ($this->type == LayerTypes::VECTOR) {
            $this->setDBOwnerToDatabase();
            $this->world->db->Execute("DROP TABLE {$this->url} CASCADE");
            /* $this->world->db->Execute ( 'DELETE FROM geometry_columns WHERE f_table_name=?', array (
              $this->url
              ) ); */
        }  // vector: drop the table hosting our info
        else if ($this->type == LayerTypes::RELATIONAL) {
            $this->setDBOwnerToDatabase();
            $this->world->db->Execute("DROP VIEW {$this->url}");
            /* $this->world->db->Execute ( 'DELETE FROM geometry_columns WHERE f_table_name=?', array (
              $this->url
              ) ); */
        }  // raster: delete the fileset
        elseif ($this->type == LayerTypes::RASTER) {
            unlink($this->url);
        }
// delete the layer's entry in the DB
        $this->world->db->Execute('DELETE FROM ' . self::TABLE . ' WHERE id=?', array(
            $this->id
        ));
    }

    function DropData() {
        switch ($this->type) {
            case LayerTypes::VECTOR:
                $this->setDBOwnerToDatabase();
// $this->world->db->debug=true;

                /* $oid = $this->world->db->GetOne("select c.oid as oid from pg_class  c join pg_namespace n on n.oid=c.relnamespace where c.relname = '{$this->url}' and n.nspname='public'");
                  if($oid) {
                  $this->world->db->Execute("delete from pg_constraint where conrelid = $oid");
                  } */
                $this->world->db->Execute("DROP TABLE IF EXISTS {$this->url}");
                break;
            case LayerTypes::RELATIONAL:
                $this->setDBOwnerToDatabase();
                /* $oid = $this->world->db->GetOne("select c.oid as oid from pg_class  c join pg_namespace n on n.oid=c.relnamespace where c.relname = '{$this->url}' and n.nspname='public'");
                  if($oid) {
                  $this->world->db->Execute("delete from pg_constraint where conrelid = $oid");
                  } */
                $this->world->db->Execute("DROP VIEW {$this->url}");
                break;
            case LayerTypes::RASTER:
                unlink($this->url);
                break;
        }
    }

// ///
// /// optimization techniques
// ///

    /**
     * Optimize the layer for performance.
     * For vector layers, this runs a VACUUM and ANALZYE.
     * For other layer types, this returns false.
     *
     * @return boolean True if optimization was successful, false if not.
     */
    function optimize() {
        switch ($this->type) {
            case LayerTypes::VECTOR :
                $this->setDBOwnerToDatabase();
                $this->world->db->Execute("VACUUM FULL ANALYZE {$this->url}");
                $this->setDBOwnerToOwner();
                break;
        }
        return true;
    }

    /**
     * Purge all data from the table.
     * For other layer types, this returns false.
     *
     * @return boolean True if purge was successful, false if not.
     */
    function truncate() {
        if ($this->type == LayerTypes::VECTOR) {
            $table = $this->url;
            $seq = $this->url . '_gid_seq';
            $this->setDBOwnerToDatabase();
            $this->world->db->Execute("TRUNCATE TABLE $table");
            $this->world->db->Execute("SELECT SETVAL ('$seq',1)");
            $this->setDBOwnerToOwner();
        } else if ($this->type == LayerTypes::ODBC) {
            $odbcinfo = $this->url;
            switch ($odbcinfo->driver) {
                case ODBCUtil::MYSQL :
                    $db = NewADOConnection("mysql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $db->Execute("TRUNCATE TABLE `{$odbcinfo->table}`");
                    break;
                case ODBCUtil::PGSQL :
                    $db = NewADOConnection("postgres://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $db->Execute("TRUNCATE TABLE \"{$odbcinfo->table}\"");
                    $db->Execute("SELECT SETVAL ('{$odbcinfo->table}_id_seq',1)");
                    break;
                case ODBCUtil::MSSQL :
                    list ( $odbc, $odbcini, $freetdsconf ) = $this->world->connectToODBC($odbcinfo, 'NOCONNECT');
                    $db = NewADOConnection("mssql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@dsn/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $db->Execute("DELETE FROM \"{$odbcinfo->table}\"");
                    break;
            }
        } else {
            return false;
        }

        return true;
    }

    function getPermissionByUsername($username) {
        $id = $this->world->getUserIdFromUsername($username);
        return $this->getPermissionById($id);
    }

    function getPermissionById($userId) {
        $id = (int) $userId;

        $ownerId = (int) $this->owner->id;


        if ($id === $ownerId) {
            $res = AccessLevels::EDIT;
            return $res;
        }

        if ($id === 0) {
            $context = Context::Get();
            $sharelevel = is_null($this->sharelevel) ? AccessLevels::READ : $this->sharelevel;
            $permission = ($context->authState == Auth::STATE_OK) ? AccessLevels::EDIT : $sharelevel;
            return $permission;
        }

        if ($id === false or $id === null)
            return $this->sharelevel;
        $table = self::TABLE;
        $query = "SELECT MAX(permission) FROM
			(SELECT permission FROM layersharing WHERE layer=? AND who=?
			UNION 
			SELECT permission FROM layersharing_socialgroups WHERE layer_id=? AND group_id=ANY(
			SELECT group_id FROM groups_members WHERE person_id=? AND (actor=1 OR actor=5))
			UNION
			SELECT sharelevel AS permission FROM $table WHERE id=?
			UNION 
			SELECT null AS permission
			) AS temp";
        return $this->world->db->GetOne($query, Array(
                    $this->id,
                    $id,
                    $this->id,
                    $id,
                    $this->id
        ));
    }

    function getRptLvlById($userId) {
        $id = (int) $userId;

        $ownerId = (int) $this->owner->id;


        if ($id === $ownerId) {
            $res = ReportingLevels::GEOEXPORT;
            return $res;
        }

        if ($id === 0) {
            $context = Context::Get();
            $reportingLevel = is_null($this->reporting_level) ? ReportingLevels::VIEW : $this->reporting_level;
            $permission = ($context->authState == Auth::STATE_OK) ? AccessLevels::EDIT : AcessLevels::READ;
            return $permission;
        }

        if ($id === false or $id === null) {
            return $this->reporting_level;
        }
        $table = self::TABLE;
        $query = "SELECT MAX(reporting_level) as reporting_level FROM
			(SELECT reporting_level FROM layersharing WHERE layer=? AND who=?
			UNION 
			SELECT reporting_level FROM layersharing_socialgroups WHERE layer_id=? AND group_id=ANY(
			SELECT group_id FROM groups_members WHERE person_id=? AND (actor=1 OR actor=5))
			UNION
			SELECT reporting_level AS permission FROM $table WHERE id=?
			UNION 
			SELECT null AS permission
			) AS temp";
        return (double) $this->world->db->GetOne($query, Array(
                    $this->id,
                    $id,
                    $this->id,
                    $id,
                    $this->id
        ));
    }

    /**
     * Set the permission for a specific person.
     * Certain cleanup is intentionally left out, and it is advised
     * that setGlobalPermission() be called after this to do the cleanup.
     * Example: $layer->setPermissionByUsername('bob',AccessLevels::EDIT);
     *
     * @param string $username
     * 		A username.
     * @param integer $level
     * 		A permission level from the AccessLevels::* defines.
     */
    function setPermissionByUsername($username, $level) {
// set the permission
        $id = $this->world->getUserIdFromUsername($username);
        return $this->setPermissionById($id, $level);
    }

    function setPermissionById($id, $level) {
        if ($id === $this->owner->id)
            return; // an owner setting their own permission would be silly
        if ($id == 0)
            return; // the admin always has access to everything
// just set a permission entry for them
        $this->world->db->Execute('INSERT INTO layersharing (layer,who,permission) VALUES (?,?,?)', array(
            $this->id,
            $id,
            $level
        ));
        $this->world->db->Execute('UPDATE layersharing SET permission=? WHERE layer=? AND who=?', array(
            $level,
            $this->id,
            $id
        ));
    }

    function setGlobalPermission($level) {
        $this->world->db->Execute('UPDATE ' . self::TABLE . ' SET sharelevel=? WHERE id=?', array(
            $level,
            $this->id
        ));
    }

    function setContactPermissionById($id, $level) {
        if ($id === $this->owner->id)
            return;
        if ($id == 0)
            return;
        $result = $this->world->db->Execute('SELECT id FROM layersharing WHERE layer=? AND who=?', array(
                    $this->id,
                    $id
                ))->FetchRow();
        if ($result ["id"]) {
            if ($level > 0) {
                $this->world->db->Execute('UPDATE layersharing SET permission=? WHERE layer=? AND who=?', array(
                    $level,
                    $this->id,
                    $id
                ));
            } else {
                $this->world->db->Execute('DELETE FROM layersharing WHERE layer=? AND who=?', array(
                    $this->id,
                    $id
                ));
            }
        } else {
            $this->world->db->Execute('INSERT INTO layersharing (layer,who,permission) VALUES (?,?,?)', array(
                $this->id,
                $id,
                $level
            ));
            $this->world->getPersonById($id)->notify($this->owner->id, "shared the layer:", $this->name, $this->id, "./?do=layer.info&id=" . $this->id, 3);
        }
    }

    function setContactRptLvlById($id, $level) {
        if ($id === $this->owner->id) {
            return;
        }
        if ($id == 0) {
            return;
        }
        $result = $this->world->db->Execute('SELECT id FROM layersharing WHERE layer=? AND who=?', array(
                    $this->id,
                    $id
                ))->FetchRow();
        if ($result ["id"]) {
            if ((double) $level > 0) {
                $this->world->db->Execute('UPDATE layersharing SET reporting_level=? WHERE layer=? AND who=?', array(
                    $level,
                    $this->id,
                    $id
                ));
            } else {
                $this->world->db->Execute('DELETE FROM layersharing WHERE layer=? AND who=?', array(
                    $this->id,
                    $id
                ));
            }
        } else {
            $this->world->db->Execute('INSERT INTO layersharing (layer,who,reporting_level) VALUES (?,?,?)', array(
                $this->id,
                $id,
                $level
            ));
            $this->world->getPersonById($id)->notify($this->owner->id, "shared the layer:", $this->name, $this->id, "./?do=layer.info&id=" . $this->id, 3);
        }
    }

    function setGroupPermissionById($id, $level) {
        $result = $this->world->db->Execute('SELECT id FROM layersharing_socialgroups WHERE layer_id=? AND group_id=?', array(
                    $this->id,
                    $id
                ))->FetchRow();
        if ($result ["id"]) {
            if ($level > 0) {
                $this->world->db->Execute('UPDATE layersharing_socialgroups SET permission=? WHERE layer_id=? AND group_id=?', array(
                    $level,
                    $this->id,
                    $id
                ));
            } else {
                $this->world->db->Execute('DELETE FROM layersharing_socialgroups WHERE layer_id=? AND group_id=?', array(
                    $this->id,
                    $id
                ));
            }
        } else {
            $this->world->db->Execute('INSERT INTO layersharing_socialgroups (layer_id,group_id,permission) VALUES (?,?,?)', array(
                $this->id,
                $id,
                $level
            ));
        }
        $results = $this->world->db->Execute('SELECT person_id FROM groups_members WHERE group_id=?', array(
                    $id
                ))->GetRows();
        foreach ($results as $result) {
            $this->world->getPersonById($result ["person_id"])->notify($this->owner->id, "shared the layer:", $this->name, $this->id, "./?do=layer.info&id=" . $this->id, 3);
        }
    }

    function setGroupRptLvlById($id, $level) {
        $result = $this->world->db->Execute('SELECT id FROM layersharing_socialgroups WHERE layer_id=? AND group_id=?', array(
                    $this->id,
                    $id
                ))->FetchRow();
        if ($result ["id"]) {
            if ($level > 0) {
                $this->world->db->Execute('UPDATE layersharing_socialgroups SET reporting_level=? WHERE layer_id=? AND group_id=?', array(
                    $level,
                    $this->id,
                    $id
                ));
            } else {
                $this->world->db->Execute('DELETE FROM layersharing_socialgroups WHERE layer_id=? AND group_id=?', array(
                    $this->id,
                    $id
                ));
            }
        } else {
            $this->world->db->Execute('INSERT INTO layersharing_socialgroups (layer_id,group_id,reporting_level) VALUES (?,?,?)', array(
                $this->id,
                $id,
                $level
            ));
        }
        $results = $this->world->db->Execute('SELECT person_id FROM groups_members WHERE group_id=?', array(
                    $id
                ))->GetRows();
        foreach ($results as $result) {
            $this->world->getPersonById($result ["person_id"])->notify($this->owner->id, "shared the layer:", $this->name, $this->id, "./?do=layer.info&id=" . $this->id, 3);
        }
    }

    function fixDBPermissions() {
        if (!$this->getHasRecords())
            return false;

        $db = $this->world->admindb; // "avoid the dot" to prevent repeated object lookups
// set the table's ownership back to its proper owner

        $this->setDBOwnerToOwner();
// rescind all permissions from the table; this is sloppy and slow, but necessary since
// PgSQL provides no usable mechanism for finding out existing permissions
        $db->Execute("REVOKE all ON TABLE {$this->url} FROM public");
        foreach ($this->world->getAllPeople() as $u) {
// if($db->Execute("SELECT 1 FROM pg_user WHERE username = '{$u->databaseusername}' LIMIT 1"))
            $db->Execute("REVOKE all ON {$this->url} FROM \"{$u->databaseusername}\"");
        }
        $db->Execute("GRANT all ON TABLE {$this->url} TO " . WORLD_NAME);
// based on the Layer's overall sharelevel, grant permission to it
        $sharelevel = $this->sharelevel;
        if ($sharelevel == AccessLevels::READ)
            $db->Execute("GRANT select ON {$this->url} TO public");
        elseif ($sharelevel == AccessLevels::COPY)
            $db->Execute("GRANT select ON {$this->url} TO public");
        elseif ($sharelevel == AccessLevels::EDIT)
            $db->Execute("GRANT all ON {$this->url} TO public");
// now go through the per-user permissions and grant those as well
        $permissions = $db->Execute("SELECT layersharing.permission,people.id FROM layersharing,people WHERE people.id=layersharing.who AND layersharing.layer=?", array(
                    $this->id
                ))->getRows();
        foreach ($permissions as $p) {
            $p = $this->world->getPersonById($p ['id']);
            if (!$p)
                continue;
            $p = $p->databaseusername;
            if ($p ['permission'] == AccessLevels::READ)
                $db->Execute("GRANT select ON {$this->url} TO \"$p\"");
            if ($p ['permission'] == AccessLevels::COPY)
                $db->Execute("GRANT select ON {$this->url} TO \"$p\"");
            if ($p ['permission'] == AccessLevels::EDIT)
                $db->Execute("GRANT all ON {$this->url} TO \"$p\"");
        }
// all set!
        return;
    }

    /**
     * Vector layers only; set the underlying DB table's ownership to the user account
     * This allows the owneruser (via raw DB) to do owner-only operations such as adding/dropping columns or indexes
     */
    function setDBOwnerToOwner() {
        if (!$this->getHasRecords())
            return false;
        try {
            $this->world->admindb->Execute("ALTER TABLE {$this->url} OWNER TO \"{$this->owner->databaseusername}\"");
            $this->world->admindb->Execute("ALTER SEQUENCE {$this->url}_gid_seq OWNER TO \"{$this->owner->databaseusername}\"");
            $this->world->admindb->Execute("GRANT all ON {$this->url} TO \"" . WORLD_NAME . "\"");
            $this->world->admindb->Execute("GRANT all ON {$this->url}_gid_seq TO \"" . WORLD_NAME . "\"");
        } catch (Exception $e) {
            
        }
    }

    /**
     * Vector layers only; set the underlying DB table's ownership to the DMI account
     * This allows the DMI to do owner-only operations such as adding/dropping columns or indexes
     */
    function setDBOwnerToDatabase() {

        if (!$this->getHasRecords())
            return false;

        $this->world->admindb->Execute("ALTER TABLE {$this->url} OWNER TO \"" . WORLD_NAME . "\"");
        try {
            $this->world->admindb->Execute("GRANT all ON {$this->url} TO \"{$this->owner->databaseusername}\"");
            $this->world->admindb->Execute("GRANT all ON {$this->url}_gid_seq TO \"{$this->owner->databaseusername}\"");
            $this->world->admindb->Execute("ALTER SEQUENCE {$this->url}_gid_seq OWNER TO \"" . WORLD_NAME . "\"");
        } catch (Exception $e) {
// do nothing
        }
    }

// ////
// //// methods for fetching and updating records
// ////

    /**
     * Vector, Relational, ODBC layers only; How many records/features are in this Layer?
     *
     * @return integer Number of records found in the layer's PostGIS table.
     */
    function getRecordCount() {

        if (!LayerTypes::IsTabular($this->type))
            return false;
        $count = $this->world->db->Execute("SELECT count(*) AS count FROM {$this->url}");

        return ($count === false) ? 0 : $count->fields ['count'];
    }

    /**
     * Vector, Relational layers only; fetch a list of all records in the layer.
     *
     * @param string $orderby
     * 		The name of the field/column to sort them by. Optional.
     * @return array An array of associative arrays, each one representing a feature/record. Note that altering
     * 		 the associative array does not update the feature in the layer; use updateRecordById() for that.
     * 		 WARNING: This method can be very memory intensive and is not particularly fast. Don't use it.
     */
    function getRecords($orderby = 'gid', $geomformat = null, &$paging = null) {
        if ($this->type != LayerTypes::VECTOR and $this->type != LayerTypes::RELATIONAL)
            return false;
        if (is_null($paging))
            $paging = new Paging();
// order by what field?
        if (preg_match('/\W/', $orderby))
            $orderby = 'gid';
        $limit = isset($paging) ? $paging->toQueryString() : "";
        $count = null;
        $fields = '*,st_asText(the_geom) as wkt_geom';
        $groupBy = '';


        if ($geomformat == 'GML') {
            $fields = '*,st_asGML(the_geom) as gml_geom, st_extent(the_geom)::text as bbox';
            $groupBy = 'GROUP BY gid';
        }


        if (is_null($paging->count)) {
            $r = $this->world->db->GetRow("SELECT count(*) from (Select $fields FROM \"{$this->url}\" ORDER BY \"$orderby\") as q1");
            $count = $r ['count'];
        }

        if ($orderby != '')
            $orderby = 'ORDER BY ' . $orderby;

        $r = $this->world->db->Execute("SELECT $fields FROM \"{$this->url}\" $groupBy $orderby  $limit");

// rows = (! $r) ? array () : $r->getRows ();
// paging->setResults ( $rows, $count );

        return $r;
    }

    /**
     * Vector, Relational layers only; fetch a specific record/feature from the layer.
     *
     * @param integer $id
     * 		The unique ID# (the gid) of the feature.
     * @return array An associative array representing the feature. Note that altering the associative array
     * 		 does not update the feature in the layer; use updateRecordById() for that.
     */
    function getRecordById($id, $ignoreGeom = true, $ignoreWKT = false) {
        if ($this->getHasRecords()) {

            $geom = $ignoreGeom ? "" : "the_geom::box2d as box_geom";
            $wkt = $ignoreWKT ? "" : ",ST_AsText(the_geom) AS wkt_geom";

            $geomChecks = $wkt;
            if (!$ignoreGeom)
                $geomChecks .= ",$geom";
//$geomChecks = $ignoreWKT ? "" : (",$wkt". $ignoreGeom? "":",$geom");

            $record = $this->world->db->Execute("SELECT * $geomChecks FROM \"{$this->url}\" WHERE gid=?", array(
                $id
            ));
//$this->world->db->debug=false;
            if (!$record)
                die();
            if (!$record)
                return null;
            $record = $record->fields;
            if (!$ignoreGeom) {
                $record ['box_geom'] = str_replace('BOX(', '', $record ['box_geom']);
                $record ['box_geom'] = str_replace(')', '', $record ['box_geom']);
                $record ['box_geom'] = str_replace(' ', ',', $record ['box_geom']);
            } else {

//unset($record['box_geom']);
            }

            if ($ignoreWKT) {
//unset($record['wkt_geom']);
            }

//unset ( $record ['the_geom'] );
            unset($record ['field_info']);
        } else if ($this->type == LayerTypes::ODBC) {
            $odbcinfo = $this->url;
            switch ($odbcinfo->driver) {
                case ODBCUtil::MYSQL :
                    $db = NewADOConnection("mysql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $record = $db->Execute("SELECT *, id AS gid FROM `{$odbcinfo->table}` WHERE id=?", array(
                                $id
                            ))->fields;
                    break;
                case ODBCUtil::PGSQL :
                    $db = NewADOConnection("postgres://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $record = $db->Execute("SELECT *, id AS gid FROM \"{$odbcinfo->table}\" WHERE id=?", array(
                                $id
                            ))->fields;
                    break;
                case ODBCUtil::MSSQL :
                    list ( $odbc, $odbcini, $freetdsconf ) = $this->world->connectToODBC($odbcinfo, 'NOCONNECT');
                    $db = NewADOConnection("mssql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@dsn/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $record = $db->Execute("SELECT *, id AS gid FROM \"{$odbcinfo->table}\" WHERE id=?", array(
                                $id
                            ))->fields;
                    break;
            }
        } else {
            return false;
        }
        unset($record ['field_info']);
// done
        return $record;
    }

    /**
     * Vector layers only; delete a specific record/feature from the layer.
     *
     * @param integer $id
     * 		The unique ID# (the gid) of the feature.
     */
    function deleteRecordById($id) {
        if ($this->type == LayerTypes::VECTOR) {
            $this->setDBOwnerToDatabase();
            $this->world->db->Execute("DELETE FROM \"{$this->url}\" WHERE gid=?", array(
                $id
            ));
            $this->setDBOwnerToOwner();
            $this->touch();
        } else if ($this->type == LayerTypes::ODBC) {
            $odbcinfo = $this->url;
            switch ($odbcinfo->driver) {
                case ODBCUtil::MYSQL :
                    $db = NewADOConnection("mysql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $db->Execute("DELETE FROM `{$odbcinfo->table}` WHERE id=?", array(
                        $id
                    ));
                    break;
                case ODBCUtil::PGSQL :
                    $db = NewADOConnection("postgres://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $db->Execute("DELETE FROM \"{$odbcinfo->table}\" WHERE id=?", array(
                        $id
                    ));
                    break;
                case ODBCUtil::MSSQL :
                    list ( $odbc, $odbcini, $freetdsconf ) = $this->world->connectToODBC($odbcinfo, 'NOCONNECT');
                    $db = NewADOConnection("mssql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@dsn/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $db->Execute("DELETE FROM \"{$odbcinfo->table}\" WHERE id=?", array(
                        $id
                    ));
                    break;
            }
        } else {
            return false;
        }
    }

    function getRecordsExtent($layerId) {
        
    }

    /**
     * Vector layers only; update a record/feature in the layer, given its unique ID# (gid) and an array of changes.
     * Example: $layer->updateRecordById(123, array('authority'=>'CA Dept Trans','private'=>1) );
     *
     * @param integer $id
     * 		The unique ID# (the gid) of the feature.
     * @param array $changes
     * 		An associative array of fieldname=>value updates to be applied to the record.
     * @return array An associative array, being the result of getRecordById(id)
     */
    function updateRecordById($gid, $changes) {

        $gid = (int) $gid;
        unset($changes ['gid']);
        unset($changes ['id']);
        if ($this->type == LayerTypes::VECTOR) {
            $sql = "UPDATE {$this->url} SET ";
            $updates = Array();
            foreach ($changes as $column => $value) {
                if (!in_array($column, Array(
                            "wkt_geom",
                            "box_geom"
                        ))) {
                    $sql .= '"' . $column . '"=?, ';
                    $updates [] = $value;
                }
                if ($column == 'wkt_geom') {
                    if (is_null($value) || ($value == '') || ($value == 'null')) {
                        $sql .= '"the_geom"=NULL';
                        $updates[] = null;
                    } else {
                        $sql .= '"the_geom"=ST_GeomFromText(\'' . $value . '\',4326) ,';
//$updates[] = $value;
                    }
                }
            }

            $sql = substr($sql, 0, strlen($sql) - 2);
            $sql .= " WHERE gid={$gid}";

            $this->setDBOwnerToDatabase();

            $this->world->db->Execute($sql, $updates);

            $this->setDBOwnerToOwner();
        } else if ($this->type == LayerTypes::ODBC) {
            $odbcinfo = $this->url;
            switch ($odbcinfo->driver) {
                case ODBCUtil::MYSQL :
                    $db = NewADOConnection("mysql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $rs = $db->Execute("SELECT * FROM `{$odbcinfo->table}` WHERE id=?", array(
                        $gid
                    ));
                    break;
                case ODBCUtil::PGSQL :
                    $db = NewADOConnection("postgres://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $rs = $db->Execute("SELECT * FROM \"{$odbcinfo->table}\" WHERE id=?", array(
                        $gid
                    ));
                    break;
                case ODBCUtil::MSSQL :
                    list ( $odbc, $odbcini, $freetdsconf ) = $this->world->connectToODBC($odbcinfo, 'NOCONNECT');
                    $db = NewADOConnection("mssql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@dsn/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $rs = $db->Execute("SELECT * FROM \"{$odbcinfo->table}\" WHERE id=?", array(
                        $gid
                    ));
                    break;
            }

            $sql = $db->GetUpdateSQL($rs, $changes, true);
            $sql = preg_replace('/id=\?$/', "id=$gid", $sql);
            $db->Execute($sql);
        } else {
            return false;
        }

// if a geometry was submitted, that has to be handled separately, as both the fieldname and the quoting on it
// are very delicate
        if (isset($changes ['wkt_geom']) and $this->type == LayerTypes::VECTOR) {

//$changes ['wkt_geom'] = preg_replace ( '/[^\(\)\d\.\-\w\,]/', '', $changes ['wkt_geom'] );
            $query = "UPDATE \"{$this->url}\" SET the_geom=st_GeometryFromText('{$changes['wkt_geom']}',4326) WHERE gid=$gid";
// $this->setDBOwnerToDatabase();
            $this->world->db->Execute($query);
// $this->setDBOwnerToOwner();
        }

// flag the layer's modtime and return the new record
        $this->touch();
        return $this->getRecordById($gid);
    }

    function GetBounds($recordIds = null, $ptBuffer = 0) {
        $records = is_null($recordIds) ? null : $recordIds;
//if(is_null($records)) return $this->bbox;
        if (is_array($records))
            $records = implode(',', $records);

        $db = System::GetDB(System::DB_ACCOUNT_SU);
        $the_geom = "ST_BUFFER(the_geom,$ptBuffer)";
        $the_geom = 'the_geom';
        $where = is_null($records) ? "" : "where gid in ($records)";



//$query = "select ST_MemUnion(  ST_BUFFER(the_geom,$ptBuffer)::box2d )::box2d as box_geom from {$this->url} $where";
        $query = "SELECT min(x0) as x0,min(y0) as y0, max(x1) as x1, max(y1) as y1 from (select ST_XMin($the_geom) as x0,ST_YMin($the_geom) as y0,ST_XMax($the_geom) as x1,ST_YMax($the_geom) as y1 from {$this->url} $where) as q1";

        /* if($this->geomtypestring == 'point') {
          //$query = "SELECT ST_XMin(the_geom) as x0, ST_YMIN(the_geom) as y0, ST_XMax(the_geom) as x1, ST_YMax(the_geom) from (select $the_geom as the_geom from {$this->url} where gid in ({$records})) as q1";
          $query = "SELECT MIN(ST_XMin($the_geom)) as x0,MIN(ST_YMin($the_geom)) as y0,MAX(ST_XMax($the_geom)) as x1,MAX(ST_YMax($the_geom)) as y1 from ( select ST_BUFFER(the_geom,$ptBuffer) as the_geom from {$this->url} $where) as q1";

          $bounds = $db->GetRow($query);
          return $bounds;

          } */
        $bounds = $db->GetRow($query);

        return $bounds;

        $bounds = $db->GetOne($query);
        $bounds = str_replace('BOX(', '', $bounds);
        $bounds = str_replace(')', '', $bounds);
        $bounds = str_replace(' ', ',', $bounds);
        return explode(',', $bounds);
    }

    /**
     * @return int id number for the new record.
     */
    function MakeRecord() {
        $this->setDBOwnerToDatabase();
        $gid = $this->world->db->GetOne("INSERT INTO \"{$this->url}\" (gid) VALUES (DEFAULT) RETURNING gid");
        if ($gid === false)
            return false;
        return intval($gid);
    }

    /**
     * Vector layers only; add a new feature/record into the layer.
     *
     * @param array $content
     * 		An associative array of fieldname=>value updates to be applied to the record.
     * @return array An associative array representing the newly-created record. Note that altering the associative array
     * 		 does not update the feature in the layer; use updateRecordById() for that.
     */
    function insertRecord($content) {
        if ($this->type == LayerTypes::VECTOR) {
// insert a perfectly blank record with nothing but a gid, then call updateRecordById() to do the updates
            $this->setDBOwnerToDatabase();
            $gid = $this->world->db->Execute("INSERT INTO \"{$this->url}\" (gid) VALUES (DEFAULT) RETURNING gid")->fields ['gid'];
            $this->setDBOwnerToOwner();
        } else if ($this->type == LayerTypes::ODBC) {
            $odbcinfo = $this->url;
            switch ($odbcinfo->driver) {
                case ODBCUtil::MYSQL :
                    $db = NewADOConnection("mysql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $db->Execute("INSERT INTO `{$odbcinfo->table}` (id) VALUES (NULL)");
                    $gid = $db->Execute("SELECT max(id) AS id FROM `{$odbcinfo->table}`")->fields ['id'];
                    break;
                case ODBCUtil::PGSQL :
                    $db = NewADOConnection("postgres://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $db->Execute("INSERT INTO \"{$odbcinfo->table}\" (id) VALUES (nextval('{$odbcinfo->table}_id_seq'))");
                    $gid = $db->Execute("SELECT max(id) AS id FROM \"{$odbcinfo->table}\"")->fields ['id'];
                    break;
                case ODBCUtil::MSSQL :
                    list ( $odbc, $odbcini, $freetdsconf ) = $this->world->connectToODBC($odbcinfo, 'NOCONNECT');
                    $db = NewADOConnection("mssql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@dsn/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $db->Execute("INSERT INTO \"{$odbcinfo->table}\" (id) VALUES (NULL)"); // no diea here
                    $gid = $db->Execute("SELECT max(id) FROM \"{$odbcinfo->table}\"")->fields ['id']; // no idea here
                    break;
            }
        } else {
            return false;
        }

// flag the Layer as having been modified, and return that newly-created record
        $newrecord = $content ? $this->updateRecordById($gid, $content) : array(
            'gid' => $gid
        );

        $this->touch();

        return $newrecord;
    }

    public static function SanitizeColumnName($colName) {
        $colName = strtolower(preg_replace('/\W/', '_', $colName));
        return $colName;
    }

    /**
     * Vector, Relational, ODBC layers only; fetch an array of the columns/fields/attributes for this layer.
     *
     * @return array An associative array; keys are fieldnames, values are the data type for that field.
     * 		 Data types are represented by one of the DataTypes::* defines, and are strings.
     */
    function getAttributes($quoteAttributes = false, $excludeGid = false) {
// not a valid layer type: it has no attributes
        $cg_urls = array();
        if (!$this->getHasRecords())
            return array();
        $attributes = array();

// connect to the database, be it local or ODBC, and fetch the raw column info
        $layertype = $this->type;

        $odbcinfo = $this->url;

        if ($this->getHasRecords()) {
            $cg_urlQuery = <<<QUERY
SELECT column_name as field from (
	SELECT column_name,
		CASE WHEN domain_name is null then udt_name
			 WHEN domain_name is not null then domain_name
			 ELSE ''
			 END AS column_type	 
			 FROM information_schema.columns WHERE 
			 table_name = '$this->url' ) as q1 
WHERE column_type = 'cg_url' 
	ORDER BY column_name
QUERY;
            $results = $this->world->db->Execute($cg_urlQuery);
            if ($results->RecordCount() > 0) {
                foreach ($results as $result) {
                    array_push($cg_urls, $result ['field']);
                }
            }
            $rs = $this->world->db->Execute("SELECT * FROM {$this->url} LIMIT 1");
        } else if ($layertype == LayerTypes::ODBC and $odbcinfo->driver == ODBCUtil::MYSQL) {
            $db = NewADOConnection("mysql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
            $rs = $db->Execute("SELECT * FROM {$odbcinfo->table} WHERE 1=0");
        } else if ($layertype == LayerTypes::ODBC and $odbcinfo->driver == ODBCUtil::PGSQL) {
            $db = NewADOConnection("postgres://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
            $rs = $db->Execute("SELECT * FROM {$odbcinfo->table} WHERE 1=0");
        } else if ($layertype == LayerTypes::ODBC and $odbcinfo->driver == ODBCUtil::MSSQL) {
            list ( $odbc, $odbcini, $freetdsconf ) = $this->world->connectToODBC($odbcinfo, 'NOCONNECT');
            $db = NewADOConnection("mssql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@dsn/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
            $rs = $db->Execute("SELECT * FROM {$odbcinfo->table} WHERE 1=0");
        }

// iterate through the recordset, populating the $attributes to be fieldname->datatype
        $fieldtypes = array(
            'C' => DataTypes::TEXT,
            'X' => DataTypes::TEXT,
            'D' => DataTypes::TEXT,
            'T' => DataTypes::TEXT,
            'B' => DataTypes::TEXT,
            'N' => DataTypes::FLOAT,
            'I' => DataTypes::INTEGER,
            'R' => DataTypes::INTEGER,
            'L' => DataTypes::BOOLEAN
        );
        if (!$rs)
            return $attributes;
        $info = $this->field_info;

        $j = 0;

        for ($i = 0; $i < $rs->FieldCount(); $i++) {
            $field = $rs->FetchField($i);
            $fieldname = $field->name;
            if ($fieldname == 'gid' && $excludeGid)
                continue;
            if ($quoteAttributes)
                $fieldname = '"' . $fieldname . '"';
            $fieldtype = "";
            if (($fieldname == "url") || in_array($fieldname, $cg_urls)) {
                $fieldtype = "url";
            } else {
                $fieldtype = $fieldtypes [$rs->MetaType($field->type)];
            }
            $attributes [$fieldname] = $fieldtype;
        }


// prune out a few "invisible" columns, then sort 'em and return 'em
        unset($attributes ['the_geom']);
        return $attributes;
    }

    function cmpZ($a, $b) {
        return ($a ['z'] < $b ['z']) ? 1 : - 1;
    }

    function getAttributesVerbose($includeGeom = false, $new = false, $includeMeta = false, $includeGid = false) {
// var_dump($includeGeom,$new,$includeMeta,$includeGid);
// not a valid layer type: it has no attributes
        $layertype = $this->type;

        /* if (!$this->getHasRecords())
          return array(); */
        $attributes = array();

// these type codes indicate what type of field should be drawn
        $fieldrequirements = array(
            'C' => 'text input',
            'X' => 'text area',
            'D' => 'date',
            'T' => 'time',
            'L' => 'boolean',
            'B' => 'binary',
            'N' => 'float',
            'I' => 'int',
            'R' => 'int'
        );
        $fieldMeta = array(
            'C' => array(
                'category' => 'alphanum',
                'sort_type' => 'alphanum',
                'desc' => 'Character'
            ),
            'X' => array(
                'category' => 'alphanum',
                'sort_type' => 'alphanum',
                'desc' => 'Text'
            ),
            'B' => array(
                'category' => 'binary',
                'sort_type' => 'none',
                'desc' => 'BLOb'
            ),
            'D' => array(
                'category' => 'date',
                'sort_type' => 'date',
                'desc' => 'Date'
            ),
            'T' => array(
                'category' => 'time',
                'sort_type' => 'time',
                'desc' => 'Time'
            ),
            'I' => array(
                'category' => 'numeric',
                'sort_type' => 'numeric',
                'desc' => 'Integer'
            ),
            'N' => array(
                'category' => 'numeric',
                'sort_type' => 'numeric',
                'desc' => 'Numeric'
            ),
            'R' => array(
                'category' => 'numeric',
                'sort_type' => 'numeric',
                'desc' => 'Serial'
            ),
            'L' => array(
                'category' => 'boolean',
                'sort_type' => 'numeric',
                'desc' => 'Boolean'
            )
        );

// generate a recordset object, depending on the back-end data being used
// connect to the database, be it local or ODBC, and fetch the raw column info
        $odbcinfo = $this->url;
        $cg_urls = array();
        $info = null;

        if ($new)
            $info = null;

        if ($this->getHasRecords()) {
            $cg_urlQuery = <<<QUERY
SELECT column_name as field, CASE WHEN domain_name is null then udt_name
			 WHEN domain_name is not null then domain_name
			 ELSE ''
			 END AS column_type
FROM information_schema.columns WHERE 
			 table_name = '$this->url' AND (domain_name = 'cg_url' or udt_name = 'cg_url')
	ORDER BY column_name /*Internal: determining url attributes*/
QUERY;
            $db = System::GetDB(System::DB_ACCOUNT_SU);

            $results = $db->Execute($cg_urlQuery);

            if ($results->RecordCount() > 0) {
                foreach ($results as $result) {
                    array_push($cg_urls, $result['field']);
                }
            }

            $this->setDBOwnerToDatabase();

            $rs = $db->Execute("SELECT * FROM {$this->url}  where 1=0"); // ;LIMIT 1" /* Internal */);
        } elseif ($layertype == LayerTypes::ODBC and $odbcinfo->driver == ODBCUtil::MYSQL) {
            $db = NewADOConnection("mysql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
            $rs = $db->Execute("SELECT * FROM {$odbcinfo->table} WHERE 1=0");
        } elseif ($layertype == LayerTypes::ODBC and $odbcinfo->driver == ODBCUtil::PGSQL) {
            $db = NewADOConnection("postgres://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
            $rs = $db->Execute("SELECT * FROM {$odbcinfo->table} WHERE 1=0");
        } else
        if ($layertype == LayerTypes::ODBC and $odbcinfo->driver == ODBCUtil::MSSQL) {
            list ($odbc, $odbcini, $freetdsconf) = $this->world->connectToODBC($odbcinfo, 'NOCONNECT');
            $db = NewADOConnection("mssql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@dsn/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
            $rs = $db->Execute("SELECT * FROM {$odbcinfo->table} WHERE 1=0");
        }
        if (!$rs)
            return (!$attributes) ? array() : $attributes;

// iterate over the list of fields and collect the data type and field requirements

        $j = 0;
        for ($i = 0; $i < $rs->FieldCount(); $i++) {
            $field = $rs->FetchField($i);

            $fieldname = $field->name;
            $fieldType = $fieldrequirements[$rs->MetaType($field->type)];
            $type = $fieldType;

            if (stripos($type, 'text') !== false)
                $type = 'string';

            if (($fieldname == "url") || in_array($fieldname, $cg_urls)) {
                $type = 'cg_url';
                $fieldType = "url";
            }

            if ($info == '')
                $info = null;

            if ($new && !is_null($info)) {

                foreach ($info as $row) {

                    if ($row["name"] == $fieldname) {
                        if (!isset($row['searchable']))
                            $row['searchable'] = false;
                        $row['display'] = ($row['display'] == '') ? $fieldname : $row['display'];
                        $fieldData = Array(
                            'requires' => $fieldType,
                            'type' => $fieldType,
                            'display' => $row["display"],
                            'searchable' => $row["searchable"],
                            'visible' => $row["visible"],
                            'z' => $row["z"],
                            'maxlength' => $field->max_length
                        );
                        if ($includeMeta) {
                            $fieldData['meta_type'] = $rs->MetaType($field->type);
                            $fieldData['meta_info'] = $fieldMeta[$rs->MetaType($field->type)];
                        }
                        break;
                    }
                }
            } elseif (!is_null($info)) {
// var_dump('check2');
// var_dump($info);
                foreach ($info as $row) {
                    if ($row["name"] == $fieldname) {
                        if (!isset($row['searchable']))
                            $row['searchable'] = false;
                        $row['display'] = ($row['display'] == '') ? $fieldname : $row['display'];
                        $fieldData = Array(
                            'requires' => $fieldType,
                            'type' => $type,
                            'display' => $row["display"],
                            'searchable' => $row["searchable"],
                            'visible' => $row["visible"],
                            'z' => $row["z"],
                            'maxlength' => $field->max_length
                        );
                        if ($includeMeta) {
                            $fieldData['meta_type'] = $rs->MetaType($field->type);
                            $fieldData['meta_info'] = $fieldMeta[$rs->MetaType($field->type)];
                        }
                        break;
                    }
                }
            } else {
// var_dump($fieldname);
                $fieldData = Array(
                    "name" => $fieldname,
                    "requires" => $fieldType,
                    'type' => $type,
                    'display' => $fieldname,
                    'searchable' => true,
                    'visible' => true,
                    'z' => $j--,
                    'maxlength' => $field->max_length
                );
// var_dump($fieldData);

                if ($includeMeta) {
                    $fieldData['meta_type'] = $rs->MetaType($field->type);

                    $fieldData['meta_info'] = $fieldMeta[$rs->MetaType($field->type)];
                }
            }

            if (isset($fieldData)) {
                $this->MergeAttributeInfo($fieldData);
                $attributes[$fieldname] = $fieldData;
            }
        }

        uasort($attributes, array(
            $this,
            "cmpZ"
        ));
// prune out a few "invisible" columns, then sort 'em and return 'em
        if (!$includeGeom)
            unset($attributes['the_geom']);

        if (!$includeGid) {
            unset($attributes['gid']);
        } elseif (isset($attributes['gid'])) {
            $atts = array();
            $atts['gid'] = $attributes['gid'];
            unset($attributes['gid']);
            $atts = array_merge($atts, $attributes);
            return $atts;
        }
        return $attributes;
    }

    /**
     * Vector, Relational, ODBC layers only; return true/false indicating whether the Layer has an attribute/field.
     *
     * @param string $colname
     * 		The name of the column/field to check.
     * @return boolean True/false indicating whether the layer has the specified field.
     */
    function hasAttribute($colname) {
        if (!in_array($this->type, array(
                    LayerTypes::VECTOR,
                    LayerTypes::RELATIONAL,
                    LayerTypes::ODBC
                ))) {
            return false;
        }
        $attribs = $this->getAttributes();

        return isset($attribs [$colname]);
    }

    /**
     * Vector & ODBC layers only; Drop an attribute/field from the layer.
     *
     * @param string $colname
     * 		The name of the column/field to drop.
     */
    function dropAttribute($colname) {
        if (preg_match('/\W/', $colname))
            return;

        if ($this->type == LayerTypes::VECTOR) {
            $info = $this->field_info;
            if (isset($info[$colname])) {
                unset($info[$colname]);
                $this->field_info = $info;
            }
            $this->setDBOwnerToDatabase();
            $this->world->db->Execute("ALTER TABLE \"{$this->url}\" DROP COLUMN \"$colname\"");
            $this->setDBOwnerToOwner();
        } else if ($this->type == LayerTypes::ODBC) {
            $odbcinfo = $this->url;
            switch ($odbcinfo->driver) {
                case ODBCUtil::MYSQL :
                    $db = NewADOConnection("mysql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $db->Execute("ALTER TABLE `{$odbcinfo->table}` DROP COLUMN `{$colname}`");
                    break;
                case ODBCUtil::PGSQL :
                    $db = NewADOConnection("postgres://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $db->Execute("ALTER TABLE \"{$odbcinfo->table}\" DROP COLUMN \"{$colname}\"");
                    break;
                case ODBCUtil::MSSQL :
                    list ( $odbc, $odbcini, $freetdsconf ) = $this->world->connectToODBC($odbcinfo, 'NOCONNECT');
                    $db = NewADOConnection("mssql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@dsn/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $db->Execute("ALTER TABLE \"{$odbcinfo->table}\" DROP COLUMN \"{$colname}\"");
                    break;
            }
        } else {
            return false;
        }

        $this->touch();
        return true;
    }

    function ValidateColumnNames() {
        $db = System::GETDB(System::DB_ACCOUNT_SU);
        $layertype = $this->type;

        if ($layertype == LayerTypes::VECTOR or $layertype == LayerTypes::RELATIONAL) {
            $rs = $this->world->db->Execute("SELECT * FROM {$this->url} LIMIT 1");
        } elseif ($layertype == LayerTypes::ODBC and $odbcinfo->driver == ODBCUtil::MYSQL) {
            $db = NewADOConnection("mysql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
            $rs = $db->Execute("SELECT * FROM {$odbcinfo->table} WHERE 1=0");
        } elseif ($layertype == LayerTypes::ODBC and $odbcinfo->driver == ODBCUtil::PGSQL) {
            $db = NewADOConnection("postgres://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
            $rs = $db->Execute("SELECT * FROM {$odbcinfo->table} WHERE 1=0");
        } elseif ($layertype == LayerTypes::ODBC and $odbcinfo->driver == ODBCUtil::MSSQL) {
            list ( $odbc, $odbcini, $freetdsconf ) = $this->world->connectToODBC($odbcinfo, 'NOCONNECT');
            $db = NewADOConnection("mssql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@dsn/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
            $rs = $db->Execute("SELECT * FROM {$odbcinfo->table} WHERE 1=0");
        }

        if (!$rs)
            return;


        $changed_fields = array();
        $fields = array();
        for ($i = 0; $i < $rs->FieldCount(); $i++) {
            $field = $rs->FetchField($i)->name;
            $changedFields[] = $field;
            if (preg_match('/\W/', $field)) {
                $this->renameAttribute($field, $field);
            }
        }

        $this->ValidateFieldInfo();
    }

    public function ValidateFieldInfo() {
        $info = $this->field_info;

        if (!$info)
            return;

        $newInfo = array();

        foreach ($info as $item) {

            if (preg_match('/\W/', $item['name'])) {
                $item['name'] = preg_replace('/\W/', '_', $item['name']);
            }
            $newInfo[] = $item;
        }

        $this->field_info = $newInfo;
    }

    /**
     * Vector & ODBC layers only; add a new attribute/field/column to the layer.
     *
     * @param string $colname
     * 		The name of the column/field to add.
     * @param string $type
     * 		The data type of the column, one of the DataTypes::* defines.
     */
    function addAttribute($colname, $type, $alias = null) {
        if (is_null($alias))
            $alias = $colname;
        $colname = substr(strtolower(preg_replace('/\W/', '_', $colname)), 0, 30);
        $info = $this->field_info;

        if (!DataTypes::IsValidType($type)) {
            return false;
        }

        if ($this->hasAttribute($colname)) {
            return false;
        }

        if ($this->type == LayerTypes::VECTOR) {
            $this->setDBOwnerToDatabase();
            $query = "ALTER TABLE \"{$this->url}\" ADD COLUMN \"$colname\" $type";
            $this->world->db->Execute($query);
            $info = $this->getAttributesVerbose();
            $fInfo = $this->field_info;
            $fInfo[$colname] = $info[$colname];
            $this->field_info = $fInfo;
            $this->setDBOwnerToOwner();
        } elseif ($this->type == LayerTypes::ODBC) {
            $odbcinfo = $this->url;
            switch ($odbcinfo->driver) {
                case ODBCUtil::MYSQL :
                    $db = NewADOConnection("mysql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $db->Execute("ALTER TABLE `{$odbcinfo->table}` ADD COLUMN `{$colname}` $type");
                    break;
                case ODBCUtil::PGSQL :
                    $db = NewADOConnection("postgres://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $db->Execute("ALTER TABLE \"{$odbcinfo->table}\" ADD COLUMN \"{$colname}\" $type");
                    break;
                case ODBCUtil::MSSQL :
                    list ( $odbc, $odbcini, $freetdsconf ) = $this->world->connectToODBC($odbcinfo, 'NOCONNECT');
                    $db = NewADOConnection("mssql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@dsn/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $db->Execute("ALTER TABLE \"{$odbcinfo->table}\" ADD COLUMN \"{$colname}\" $type");
                    break;
            }
        } else {
            return false;
        }

        $this->touch();
        return $colname;
    }

    /**
     * Vector & ODBC layers only; rename an attribute/field/column
     *
     * @param string $oldname
     * 		The name of the column/field to be renamed.
     * @param string $newname
     * 		The new name of the column.
     */
    function renameAttribute($oldname, $newname) {
//$oldname = substr ( strtolower ( preg_replace ( '/\W/', '_', $oldname ) ), 0, 30 );
        $newname = substr(strtolower(preg_replace('/\W/', '_', $newname)), 0, 30);
        if (in_array($newname, array(
                    'gid',
                    'the_geom',
                    'wkt_geom'
                )))
            return false;
        if (in_array($oldname, array(
                    'gid',
                    'the_geom',
                    'wkt_geom'
                )))
            return false;
        if ($this->hasAttribute($newname))
            return false;


        if ($this->getHasRecords()) {
            $this->setDBOwnerToDatabase();
            if ($this->type == LayerTypes::RELATIONAL) {
                /* $query = "UPDATE information_schema.columns set column_name='$newname'
                  where table_name = '{$this->url}' and table_schema = 'public' and column_name = '$oldname'";
                 */
            } else {
                $query = "ALTER TABLE \"{$this->url}\" RENAME COLUMN \"$oldname\" to \"$newname\"";
            }
            $info = $this->fieldInfo;
            $info[$newname] = $info[$oldname];
            unset($info[$oldname]);
            $this->field_info = $this->getAttributesVerbose();
            $this->world->db->Execute($query);
            $this->setDBOwnerToOwner();
        } elseif ($this->type == LayerTypes::ODBC) {
            $odbcinfo = $this->url;
            switch ($odbcinfo->driver) {
                case ODBCUtil::MYSQL :
                    $type = $this->getAttributes();
                    $type = $type [$oldname];
                    $db = NewADOConnection("mysql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $db->Execute("ALTER TABLE `{$odbcinfo->table}` CHANGE COLUMN `$oldname` `$newname` $type");
                    break;
                case ODBCUtil::PGSQL :
                    $db = NewADOConnection("postgres://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $db->Execute("ALTER TABLE \"{$odbcinfo->table}\" RENAME COLUMN \"$oldname\" TO \"$newname\"");
                    break;
                case ODBCUtil::MSSQL :
                    list ( $odbc, $odbcini, $freetdsconf ) = $this->world->connectToODBC($odbcinfo, 'NOCONNECT');
                    $db = NewADOConnection("mssql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@dsn/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $db->Execute("ALTER TABLE \"{$odbcinfo->table}\" RENAME COLUMN \"$oldname\" TO \"$newname\"");
                    break;
            }
        } else {
            return false;
        }

        $this->touch();
        return true;
    }

// ///
// /// methods for fetching the layer's spatial extent
// ///
    public function MergeAttributeInfo(&$attVerbose) {
        $record = SLAttributeInfoView::GetAttributeInfo($this, $attVerbose['name']);

        if (!$record) {
            return false;
        }

        foreach ($record as $key => $value) {

            switch ($key) {
                case 'name':
                    $attVerbose['name'] = $value;
                    if (!isset($attVerbose['display'])) {
                        $attVerbose['display'] = $value;
                    }
                    break;
                case 'type':
                    $attVerbose['type'] = $value;
                    $attVerbose['requires'] = $value;
                    break;
                case 'max_chars':
                    $attVerbose['maxlength'] = $value;
                case 'table_z':
                case 'nullable':
                case 'precision':
                case 'scale':
                case 'radix':
                case 'precisiond':
                case 'has_tz':
                    if (!is_null($value)) {
                        $attVerbose[$key] = $value;
                    }
                    break;
            }
        }
    }

    /**
     * Fetch the layer's extent as an aligned table for display.
     *
     * @return string Some HTML showing the layer's extent in a table.
     */
    function getExtentPretty() {
        $extent = $this->getExtent();
// $area = $this->area;
        $retstr = "<table style=\"border-collapse:collapse;border-style:none;\">\n";
        $retstr .= "	<tr><td>Lat:</td><td>{$extent[1]}</td><td> to </td><td>{$extent[3]}</td></tr>\n";
        $retstr .= "	<tr><td>Lon:</td><td>{$extent[0]}</td><td> to </td><td>{$extent[2]}</td></tr>\n";
// if($area !== false) $retstr .= " <tr><td>Area:</td><td colspan=\"3\">".round($area["miles"], 4)." Sq. Miles&nbsp;&nbsp;".round($area["kilometers"], 4)." Sq. Kilometers</td></tr>\n";
        $retstr .= "</table>\n";
        return $retstr;
    }

    /**
     * Fetch the layer's extent as a space-joined string of values.
     *
     * @return string An extent in space-joined format, e.g. "12.34 56.78 9.012 3.45"
     */
    function getSpaceExtent() {
        return implode(" ", $this->getExtent());
    }

    /**
     * Fetch the layer's extent as a comma-joined string of values.
     *
     * @return string An extent in comma-joined format, e.g. "12.34,56.78,9.012,3.45"
     */
    function getCommaExtent() {
        return implode(",", $this->getExtent());
    }

    /**
     * Fetch a 4-tuple representing the layer's spatial extent.
     * Note that all coordinates are transformed into latlong (EPSG:4326).
     *
     * @return array An array of 4 values, being the bounding box coordinates for the layer's coverage.
     */
    function getExtent() {
        if ($this->type == LayerTypes::COLLECTION) {
            $subs = LayerCollection::GetSubs($this->world, $this->id);
            if (count($subs) == 0) {
                return array(-180, -90, 180, 90);
            }
            $maxX = -1000;
            $minX = 1000;
            $maxY = - 1000;
            $minY = 1000;

            foreach ($subs as $sub) {
                list ( $xmin, $ymin, $xmax, $ymax ) = $sub->getExtent();

                $maxX = max($maxX, $xmax);
                $maxY = max($maxY, $ymax);
                $minX = min($minX, $xmin);
                $minY = min($minY, $ymin);
            }
            return array(
                $minX,
                $minY,
                $maxX,
                $maxY
            );
        }

// WMS layers are simple:we call the value from the bbox field in the
// Layers table, and if it doesn't exist, then we set it to zoom out to
// a world view.
        if ($this->type == LayerTypes::WMS) {
            $bboxval = $this->bbox;
            if ($bboxval) {
                return array(
                    $bboxval
                );
            } else {
// if the bbox field has no value, revert to world view extent
                return array(
                    - 180,
                    - 90,
                    180,
                    90
                );
            }
        }  // vector layers are easy, thanks to PostGIS
        elseif ($this->type == LayerTypes::VECTOR or $this->type == LayerTypes::RELATIONAL) {
            $extent = $this->world->db->GetOne("SELECT st_extent(the_geom) AS extent FROM {$this->url} where the_geom is not null");

            if (!$extent)
                $extent = 'BOX(-180 -90,180 90)';
            $extent = trim(preg_replace('/[^\d\.\s\-]/', ' ', $extent));
            list ( $x1, $y1, $x2, $y2 ) = explode(' ', $extent);

            if (($x1 == $x2) && ($y1 == $y2)) {

                $extent = $this->world->db->GetOne("SELECT st_extent(ST_Buffer(ST_GeomFromText('POINT($x1 $y1)'),.025))");

                if (!$extent)
                    $extent = 'BOX(-180 -90,180 90)';
                $extent = trim(preg_replace('/[^\d\.\s\-]/', ' ', $extent));
                list ( $x1, $y1, $x2, $y2 ) = explode(' ', $extent);
            }

            $minx = min($x1, $x2);
            $miny = min($y1, $y2);
            $maxx = max($x1, $x2);
            $maxy = max($y1, $y2);


            return array(
                (float) $minx,
                (float) $miny,
                (float) $maxx,
                (float) $maxy
            );
        }

// a raster, call gdalinfo and parse the string output
        elseif ($this->type == LayerTypes::RASTER) {
            $output = `gdalinfo {$this->url}`;
            @preg_match('/Lower Left\s+\(\s*([\d\.\-]+)\s*,\s*([\d\.\-]+)\s*\)/', $output, $extent1);
            @preg_match('/Upper Right\s+\(\s*([\d\.\-]+)\s*,\s*([\d\.\-]+)\s*\)/', $output, $extent2);
            return array(
                $extent1 [1],
                $extent1 [2],
                $extent2 [1],
                $extent2 [2]
            );
        }

// ODBC layers are easy, albeit slow and a bit barbaric since not truly spatial
        elseif ($this->type == LayerTypes::ODBC) {
            $odbcinfo = $this->url;
            list ( $odbc, $odbcini, $freetdsconf ) = $this->world->connectToODBC($odbcinfo);

            if (!$odbc)
                return array();
            $bbox = odbc_exec($odbc, "SELECT min({$odbcinfo->latcolumn}) AS s, max({$odbcinfo->latcolumn}) AS n, min({$odbcinfo->loncolumn}) AS w, max({$odbcinfo->loncolumn}) AS e FROM {$odbcinfo->table}");
            $n = odbc_result($bbox, 'n');
            $s = odbc_result($bbox, 's');
            $e = odbc_result($bbox, 'e');
            $w = odbc_result($bbox, 'w');

            return array(
                $w,
                $s,
                $e,
                $n
            );
        }

// huh? well, return null and they'll probably barf and bring attention to whatever went wrong
        else {
            return null;
        }
    }

// ///
// /// methods for searching the dataset (vector only)
// ///

    /**
     * Given a bounding box, return a list of rows which are in this Layer and inside that box.
     * Coordinates are assumed to be in latlong (EPSG:4326).
     *
     * @param float $llx
     * 		The bounding box's west side.
     * @param float $lly
     * 		The bounding box's south side.
     * @param float $urx
     * 		The bounding box's east side.
     * @param float $ury
     * 		The bounding box's north side.
     * @param string $geometry
     * 		Optional, whether to include the geometry with each returned row. Defaults to false to not return geometry. Can be true to include geometry as WKT. Can be 'GML' to include geometry in GML.
     */
    function searchFeaturesByBbox($llx, $lly, $urx, $ury, $geom = false, $projection = 4326, $order = 'gid', $orderDir = "DESC") {
        if ($this->type != LayerTypes::VECTOR and $this->type != LayerTypes::RELATIONAL)
            return array();

// construct the WKT for finding the intersection		 
        $llx = (float) $llx;
        $lly = (float) $lly;
        $urx = (float) $urx;
        $ury = (float) $ury;

        $geometry = "st_GeometryFromText('POLYGON(($llx $lly, $llx $ury, $urx $ury, $urx $lly, $llx $lly))',4326)";

// fetch all fields, plus any geometry frields based on the $geom parameter
        $atts = $this->getAttributes(true);
        $atts = array_keys($atts);
        $fields = implode(',', $atts);

        if ($geom == 'GML')
            $fields .= ',st_asGML(the_geom) as gml_geom';
        else if ($geom)
            $fields .= ',st_asText(the_geom) as wkt_geom';

// do the fetch, easy
        ;

        $orderBy = ' order by ' . $order . ' ' . $orderDir;
        $query = "SELECT $fields FROM {$this->url} WHERE the_geom && $geometry AND ST_Intersects($geometry,the_geom) $orderBy";


        $features = $this->world->db->Execute($query);

        if (!$features)
            return array();
        return $features; // array_map ( create_function ( '$a', 'unset($a["the_geom"]);return $a;' ), $features->getRows () );
    }

    function searchFeaturesWithinBBox($bbox, $projection, $criteria, $paging, $geom = true, $method = "OR", $order = "gid", $joinLayers = array()) {
        if ($this->type != LayerTypes::VECTOR and $this->type != LayerTypes::RELATIONAL and $this->type != LayerTypes::ODBC)
            return array();

        $atts = $this->getAttributes(true);
        $fields = implode(',', array_keys($atts));

        list ( $llx, $lly, $urx, $ury ) = explode(",", $bbox);
        $llx = (float) $llx;
        $lly = (float) $lly;
        $urx = (float) $urx;
        $ury = (float) $ury;

        $databasedriver = ODBCUtil::PGSQL;
        if ($this->type == LayerTypes::ODBC) {
            $odbcinfo = $this->url;
            $databasedriver = $odbcinfo->driver;
            $order = '';
        } else {
            $geometry = "st_GeometryFromText('POLYGON(($llx $lly, $llx $ury, $urx $ury, $urx $lly, $llx $lly))',$projection)";
            $order = "ORDER BY $order";
        }

        if ($paging == null)
            $paging = new Paging ();

        $myfields = $this->getAttributes();
        $compareStrings = array();
        foreach ($criteria as &$item) {
            list ( $field, $compareOp, $value ) = $item;
            if ($compareOp == "=") {
                $compareOp = "contains";
            }
            $isnumber = $myfields [$field] != DataTypes::TEXT;
            $item = $this->world->criteria_to_sql($field, $compareOp, $value, $databasedriver, $isnumber);
            array_push($compareStrings, $item);
        }

// $fields = join(",",$fields);
        $compareString = join(" $method ", $compareStrings);
        $compareClause = ($compareString == "") ? "" : "AND ($compareString)";

        $count = $paging->count;
        $limit = $paging->toQueryString();

        if ($this->type != LayerTypes::ODBC) {
            if ($count == null) {
                $countQuery = "SELECT COUNT(*) FROM {$this->url} WHERE st_intersects(st_transform( the_geom, $projection),$geometry) $compareClause";

                $count = $this->world->db->Execute($countQuery)->getRows();
                $count = $count [0] ['count'];
            }
            $query = "SELECT $fields FROM {$this->url} WHERE st_intersects( st_transform( the_geom, $projection),$geometry) $compareClause $order $limit";

            $features = $this->world->db->Execute($query);
// features = ($features) ? $features->getRows () : array ();
// if they wanted geometry, fix the bbox to be llx,lly,urx,ury format
            if ($geom) {
                foreach ($features as $f) {
                    if (!isset($f ['box_geom']))
                        continue;
                    $f ['box_geom'] = str_replace('BOX(', '', $f ['box_geom']);
                    $f ['box_geom'] = str_replace(')', '', $f ['box_geom']);
                    $f ['box_geom'] = str_replace(' ', ',', $f ['box_geom']);
                }
            }
// finally! run the SQL and prune out the the_geom fields
            $returnFeatures = $features; // array_map ( create_function ( '$a', 'unset($a["the_geom"]);return $a;' ), $features );
        } else { // must be a ODBC layer, a whole monster unto itself
// make the connection
            if ($odbcinfo->driver == ODBCUtil::MYSQL) {
                $db = NewADOConnection("mysql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
            } else if ($odbcinfo->driver == ODBCUtil::PGSQL) {
                $db = NewADOConnection("postgres://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
            } else if ($odbcinfo->driver == ODBCUtil::MSSQL) {
                list ( $odbc, $odbcini, $freetdsconf ) = $this->world->connectToODBC($odbcinfo, 'NOCONNECT');
                $db = NewADOConnection("mssql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@dsn/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
            }

// since this is NOT a spatial database, we make it up
            $spatialQuery = sprintf("%s >= %d AND %s >= %d AND %s <= %d AND %s <= %d", $odbcinfo->loncolumn, $llx, $odbcinfo->latcolumn, $lly, $odbcinfo->loncolumn, $urx, $odbcinfo->latcolumn, $ury);

// fetch a count for paging purposes
            $count = $paging->count;
            if ($count == null) {
                $countQuery = "SELECT count(*) AS howmany FROM {$odbcinfo->table} WHERE $spatialQuery $compareClause $order $limit";
                $count = $db->Execute($countQuery);
                $count = $count->fields ['howmany'];
            }

// do the fetch
            $features = $db->Execute("SELECT $fields FROM {$odbcinfo->table} WHERE $spatialQuery $compareClause $order $limit")->getRows();

// make up the gid attribute, being the same as an 'id' attribute; THIS STILL MAY NOT EXIST!
            for ($i = 0; $i < sizeof($features); $i++) {
                $features [$i] ['gid'] = $features [$i] ['id'];
            }

// if they wanted geometry, fix the bbox to be llx,lly,urx,ury format
            if ($geom) {
                for ($i = 0; $i < sizeof($features); $i++) {
                    $features [$i] ['box_geom'] = sprintf('%f,%f,%f,%f', $features [$i] ['loncolumn'] - 1, $features [$i] ['latcolumn'] - 1, $features [$i] ['loncolumn'] + 1, $features [$i] ['latcolumn'] + 1);
                }
            }
        }

        $paging->setResults($features, $count);
        return $features;
    }

    function SearchByCriteria(SearchCriteria $criteria, $countOnly = false, $pxBox = null, $gids = null, $intersectionMode = 0) {

        $where = $criteria->GetQuery(false, $pxBox, $gids, $intersectionMode);

        $db = System::GetDB(System::DB_ACCOUNT_SU);

        $results = $db->Execute($where);

        $paging = $criteria->paging;
        $pagingData = array();
        if (!$paging->isNull()) {
            $numRecords = $db->GetOne($criteria->GetCountQuery(false));
            if ($countOnly) {
                return array('num_matches' => $numRecords);
            }
            $paging->setResults($results, $numRecords);
            $paging->mergeData($pagingData);
            return array('results' => $results, 'pagingData' => $pagingData);
        }
        return array('results' => $results);
    }

    /**
     *
     * @param
     * 		criteria assoc array of attribute=value pairs.
     */
    function searchFeatures($criteria, $paging, $geom = true, $method = "OR", $order = "gid", $joinLayers = array()) {
        $atts = $this->getAttributes(true);
        $fields = implode(',', array_keys($atts));
        if ($this->type != LayerTypes::VECTOR and $this->type != LayerTypes::RELATIONAL and $this->type != LayerTypes::ODBC)
            return array();
        $databasedriver = ODBCUtil::PGSQL;
        if ($this->type == LayerTypes::ODBC) {
            $odbcinfo = $this->url;
            $databasedriver = $odbcinfo->driver;
            $order = '';
        } else {
            if ($order == 'default')
                $order = 'gid';
            $order = "ORDER BY $order";
        }

        $myfields = $this->getAttributes();

        $compareStrings = array();
        $i = 0;
        foreach ($criteria as $item) {

            if (count($item) > 3) {
                list ( $field, $compareOp, $value, $logic ) = $item;
            } else {
                list ( $field, $compareOp, $value ) = $item;
                $logic = ($i > 0) ? 'or' : null;
            }

            if ($field == "")
                continue;
            if (is_null($field))
                continue;
            if (($compareOp == "=") || ($compareOp == '==')) {
//$compareOp = "contains";
            }

            $isnumber = $myfields [$field] != DataTypes::TEXT;
            $item = $this->world->criteria_to_sql($field, $compareOp, $value, $databasedriver, $isnumber);

            switch ($logic) {
                case 'and' :
                case 'or' :
                    $item = "$logic ($item)";
                    break;
                case '!and' :
                    $item = "AND NOT ($item)";
                    break;
                case '!or' :
                    $item = "OR NOT ($item)";
                    break;
                case 'not' :
                    $item = "NOT ($item)";
                    break;
            }
            array_push($compareStrings, $item);
            $i++;
        }

// $fields = join(",",$fields);
        $compareStrings = join(" ", $compareStrings);
        if ($paging == null)
            $paging = new Paging ();
        $limit = $paging->toQueryString();

        if ($this->type != LayerTypes::ODBC) {
            $count = $paging->count;
            if ($count == null) {
                $countQuery = "SELECT count(*) FROM {$this->url} WHERE $compareStrings";
                $count = $this->world->db->Execute($countQuery);
                $count = ($count) ? $count->getRows() : 0;
                $count = $count [0] ['count'];
            }
            $where = ($compareStrings == "") ? "" : "WHERE $compareStrings";
            $query = "SELECT $fields FROM {$this->url} $where $order $limit";

            $features = $this->world->db->Execute($query);

// $features = ($features) ? $features->getRows () : array ();
// if they wanted geometry, fix the bbox to be llx,lly,urx,ury format
            /*
             * if ($geom) { foreach ( $features as &$f ) { if (! isset ( $f ['box_geom'] )) continue; $f ['box_geom'] = str_replace ( 'BOX(', '', $f ['box_geom'] ); $f ['box_geom'] = str_replace ( ')', '', $f ['box_geom'] ); $f ['box_geom'] = str_replace ( ' ', ',', $f ['box_geom'] ); } }
             */
// finally! run the SQL and prune out the the_geom fields
            $returnFeatures = $features;
// returnFeatures = array_map ( create_function ( '$a', 'unset($a["the_geom"]);return $a;' ), $features );
        } else { // must be a ODBC layer, a whole monster unto itself
// make the connection
            if ($odbcinfo->driver == ODBCUtil::MYSQL) {
                $db = NewADOConnection("mysql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
            } else if ($odbcinfo->driver == ODBCUtil::PGSQL) {
                $db = NewADOConnection("postgres://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
            } else if ($odbcinfo->driver == ODBCUtil::MSSQL) {
                list ( $odbc, $odbcini, $freetdsconf ) = $this->world->connectToODBC($odbcinfo, 'NOCONNECT');
                $db = NewADOConnection("mssql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@dsn/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
            }

// fetch a count for paging purposes
            $count = $paging->count;
            if ($count == null) {
                $countQuery = "SELECT count(*) AS howmany FROM {$odbcinfo->table} WHERE $compareStrings";
                $count = $db->Execute($countQuery);
                $count = $count->fields ['howmany'];
            }

// do the fetch
            $features = $db->Execute("SELECT $fields FROM {$odbcinfo->table} WHERE $compareStrings $order $limit")->getRows();

// make up the gid attribute, being the same as an 'id' attribute; THIS STILL MAY NOT EXIST!
            for ($i = 0; $i < sizeof($features); $i++) {
                foreach ($features as &$f)
                    $features [$i] ['gid'] = $features [$i] ['id'];
            }

// if they wanted geometry, fix the bbox to be llx,lly,urx,ury format
            if ($geom) {
                for ($i = 0; $i < sizeof($features); $i++) {
                    $features [$i] ['box_geom'] = sprintf('%f,%f,%f,%f', $features [$i] ['loncolumn'] - 1, $features [$i] ['latcolumn'] - 1, $features [$i] ['loncolumn'] + 1, $features [$i] ['latcolumn'] + 1);
                }
            }

            $returnFeatures = $features;
        }
        $paging->setResults($returnFeatures, $count);

        return $returnFeatures;
    }

    function searchByFeature($featureId, $layerId, $paging = null, $geom = false, $method = "OR", $order = "gid", $joinLayers = array()) {
        $atts = $this->getAttributes(true);
        $layer = $this->world->getLayerById($layerId);
        $fields = implode(',', array_keys($atts));
        if ($this->type != LayerTypes::VECTOR and $this->type != LayerTypes::RELATIONAL and $this->type)
            return array();
        if ($order == 'default')
            $order = 'gid';
        $order = "ORDER BY $order";

        $myfields = $this->getAttributes();
        $compareStrings = array();

        if ($paging == null)
            $paging = new Paging ();
        $limit = $paging->toQueryString();

        $count = $paging->count;
        if ($count == null) {
            $countQuery = "SELECT count(*) from {$this->url} WHERE ST_Intersects(the_geom, (select the_geom from {$layer->url} where gid={$featureId}))";
            $count = $this->world->db->Execute($countQuery);
            $count = ($count) ? $count->getRows() : 0;
            $count = $count [0] ['count'];
        }
        $atts = $this->getAttributes(true);
        $atts = array_keys($atts);
        $geomIndex = array_search('"the_geom"', $atts);
        if (!$geom) {
            if ($geomIndex > -1)
                array_splice($atts, $geomIndex, 1);
        } else {
            if ($geomIndex > -1)
                array_splice($atts, $geomIndex, 1, 'ST_AsText("the_geom") as the_geom');
        }
        $fields = implode(',', $atts);
        $query = "SELECT $fields from {$this->url} WHERE ST_Intersects(the_geom, (select the_geom from {$layer->url} where gid={$featureId}))";
        $features = $this->world->db->Execute($query);

//$features = ($features) ? $features->getRows () : array ();
// if they wanted geometry, fix the bbox to be llx,lly,urx,ury format
        /*
         * if ($geom) { foreach ( $features as &$f ) { if (! isset ( $f ['box_geom'] )) continue; $f ['box_geom'] = str_replace ( 'BOX(', '', $f ['box_geom'] ); $f ['box_geom'] = str_replace ( ')', '', $f ['box_geom'] ); $f ['box_geom'] = str_replace ( ' ', ',', $f ['box_geom'] ); } }
         */
// finally! run the SQL and prune out the the_geom fields
// returnFeatures = array_map ( create_function ( '$a', 'unset($a["the_geom"]);return $a;' ), $features );

        $paging->setResults($features, $count);

        return $features;
    }

    /**
     * Do a query for features within a given meter-distance from a target point.
     * bbox is supported as a region of interest for limiting queries to data within a view.
     *
     * Enter description here ...
     *
     * @param unknown_type $ptx
     * 		the x (longitude) coordinate of the target point.
     * @param unknown_type $pty
     * 		the y (lattitude) coordinate of the target point.
     * @param unknown_type $meterDistance
     * 		distance from point in meters.
     * @param unknown_type $bbox
     * 		optional comma separated lower left x, lower right y, upper right x, upper right y values of a region of interest.
     * @param unknown_type $geom
     * 		optional false - no geometry, true-include geometry, 'gml' return geometry as geographic markup language.
     */
    function searchFeaturesByDistance($ptx, $pty, $meterDistance, $geom = false) {
        $atts = $this->getAttributes(true);

        $fields = implode(',', array_keys($atts));

// use the PostGIS function distance_spheroid() to fetch the linear distance in meters
        $target = "ST_GeometryFromText('POINT($ptx $pty)',4326)";
// fetch all fields, plus any geometry frields based on the $geom parameter

        if ($geom == 'GML')
            $fields .= ',st_asGML(the_geom) as gml_geom';
        else if ($geom)
            $fields .= ',ST_asText(the_geom) as wkt_geom';

        $where = "where cg_distance <= $meterDistance and the_geom IS NOT NULL";
// if($roi) $where .= " && $roi AND st_intersects(the_geom,$roi)";
        $spheroid = 'SPHEROID["WGS 84",6378137,298.257223563]';
// features = $this->world->db->Execute("select * from (SELECT *, distance_spheroid($target,the_geom,'$spheroid') AS cg_distance from {$this->url}) as q1 $where");
        $query = "select $fields from {$this->url} where ST_INTERSECTS(CAST(ST_BUFFER(ST_GeographyFromText('POINT($ptx $pty)'),$meterDistance) as GEOMETRY),the_geom) and the_geom IS NOT NULL";

        $features = $this->world->db->Execute($query);

        if (!$features)
            return array();
        return $features; // array_map ( create_function ( '$a', 'unset($a["the_geom"]);return $a;' ), $features->getRows () );
    }

// ///
// /// methods for finding who is using this Layer
// ///

    /**
     * Fetch a list of all users who have this layer bookmarked.
     *
     * @return array An array of Person objects.
     */
    function usersBookmarked() {
        $people = $this->world->db->Execute('SELECT owner FROM layer_bookmarks WHERE layer=?', array(
                    $this->id
                ))->getRows();
        $people = array_map(create_function('$a', 'return $a["owner"];'), $people);
        $people = array_map(array(
            $this->world,
            'getPersonById'
                ), $people);
        return $people;
    }

    /**
     * Fetch a list of all users who have this layer bookmarked.
     *
     * @return array An array of Person objects.
     */
    function projectsUsing() {
        $projects = $this->world->db->Execute('SELECT project FROM project_layers WHERE layer=?', array(
                    $this->id
                ))->getRows();
        $projects = array_map(create_function('$a', 'return $a["project"];'), $projects);
        $projects = array_map(array(
            $this->world,
            'getProjectById'
                ), $projects);
        return $projects;
    }

    public function importMetadata($xmlFile, $asString = false) {
        if (!$asString) {
            $xmlFileSize = filesize($xmlFile);

            $xmlFileStream = fopen($xmlFile, 'r');
            if (!$xmlFileStream) {
                error_log($xmlFile);
                return;
            }
            $data = '';
            while (!feof($xmlFileStream))
                $data .= fread($xmlFileStream, $xmlFileSize);
            fclose($xmlFileStream);
            unlink($xmlFile);
        }
        $converter = new Convert ();
        $this->metadata = $converter->xmlToPhp($data);
    }

    function hasMetadata() {
        if ($this->metadata != "" && $this->metadata != Array() && $this->metadata != null) {
            return true;
        } else {
            return false;
        }
    }

    function clearMetadata() {
        $this->world->db->Execute("UPDATE " . self::TABLE . " SET \"metadata\"=? WHERE id=?", array(
            null,
            $this->id
        ));
    }

    private function deep_ksort(&$arr) {
        ksort($arr);
        foreach ($arr as &$a) {
            if (is_array($a) && !empty($a)) {
                $this->deep_ksort($a);
            }
        }
    }

    function setOwner($ownerId, $adder = null) {
        $recipient = $this->world->getPersonById($ownerId);
        if ($recipient->id != $this->owner->id) {
            $recipient->notify($this->owner->id, "gave you ownership of layer:", $this->name, $this->id, "./?do=layer.edit1&id=" . $this->id, 12);

            $this->world->db->Execute('UPDATE ' . self::TABLE . ' SET owner=? WHERE id=?', array(
                $recipient->id,
                $this->id
            ));
            $this->fixDBPermissions();
            $this->touch();
        }
    }

    /*
     * function notify($address) { $fromUser = $this->owner; $from = sprintf ( "From: %s <%s>\r\n", $fromUser->realname, $fromUser->email ); $from .= "Content-type: text/html\r\n"; $to = sprintf ( "%s", $address ); $email = new Templater (); $message = ''; $devpath = ''; // $devpath = '~doug/cartograph/'; $subject = sprintf ( "[%s] You have been given a layer on SimpleLayers: ", $this->name ); $message .= sprintf ( "You have been given a map layer by %s named %s.<br/><br/>", $fromUser->realname, $this->name ); if ($this->description != "" || $this->description != " ") $message .= sprintf ( "The layer has the following description:<br/>%s<br/><br/>", $this->description ); $message .= sprintf ( "<a style=\"text-decoration:none;\" href=\"https://www.cartograph.com/%s?do=layer.edit1&id=%s\">View the layer</a>", $devpath, $this->id ); $email->assign ( 'group', $this ); $email->assign ( 'subject', $subject ); $email->assign ( 'message', $message ); $email->assign ( 'devpath', $devpath ); mail ( $to, $subject, $email->fetch ( 'group/email.tpl' ), $from ); }
     */

    function getTransactions() {
        return $this->world->db->Execute('SELECT * FROM _transactions WHERE layer_id = ?', array(
                    $this->id
                ))->GetAssoc();
    }

    function backup() {
        $url = $this->url;
        $this->backup = $url . "_cg_bak";
        pgsql2pgsql($this->world, $url, $url . "_cg_bak");
        $this->world->db->Execute('UPDATE ' . self::TABLE . ' SET backuptime=NOW() WHERE id=?', array(
            $this->id
        ));
    }

    function rollback() {
        if (!$this->backup)
            return false;
        $url = $this->url;
        $this->truncate();
        $this->setDBOwnerToDatabase();
        pgsql2pgsql($this->world, $this->backup, $url);
        $this->fixDBPermissions();
        $this->setDBOwnerToOwner();
        return $this->backup;
    }

    public static function CloneVector($layerId) {

        $layer = self::GetLayer($layerId, false);
        if (!$layer['type'] == LayerTypes::VECTOR)
            return null;

        unset($layer['id']);
        unset($layer['metadata']);
        unset($layer['import_info']);
        unset($layer['last_modified']);
        unset($layer['created']);
        unset($layer['backuptime']);

        $newLayer = self::CreateLayer($layer['name'], LayerTypes::VECTOR, $layer['owner'], $layer['adder']);
        foreach ($layer as $key => $val) {
            $newLayer->$key = $val;
        }
        $newLayer->classification->setSchemeToSingle();

        return $newLayer;
    }

    /**
     * Create a new Layer owned by this Person, copying the content from an existing Layer.
     *
     * @param
     * 		string The name of the new Layer; Optional, defaults to the original Layer's name.
     * @return Layer A Layer object, or null if the original was not found.
     */
    function SaveAs($layerId, $name = null, $userId, $adderId) {
// fetch the old Layer and its name
        $old = self::GetLayer($layerId);
        if (!$old)
            return null;
        if (!$name)
            $name = 'Copy of ' . $old->name;
        $sys = System::Get();
        $db = System::Get(System::DB_ACCOUNT_SU);





// depending on the data type, create it and then populate the data
        switch ($old->type) {
            case LayerTypes::WMS :
// copying WMS data is simple; just copy the URL of the WMS server
                $new = self::CreateLayer($name, LayerTypes::WMS, $userId, $adderId, false);
                $new->url = $old->url;
                break;
            case LayerTypes::RASTER :
// copying raster data is also easy; just copy the image file
                $new = self::CreateLayer($name, LayerTypes::RASTER, $userId, $adderId, false);
                copy($old->url, $new->url);
                break;
            case LayerTypes::RELATIONAL :
                $new = self::CreateLayer($name, LayerTypes::VECTOR, $userId, $adderId, false);
                $filename = md5(mt_rand() . mt_rand());
                $shapefile = pgsql2shp($sys, $old->url, $filename, true);
                $shapefile = $shapefile [0];
                shp2pgsql($sys, $shapefile, $new->url, true);
                break;
            case LayerTypes::VECTOR :
                $new = $this->createLayer($name, LayerTypes::VECTOR, $userId, $adderId, false);
                pgsql2pgsql($sys, $old, $new, true);
                break;
            case LayerTypes::ODBC :
// create the new vector layer, and set up its indexes etc.
                $new = $this->createLayer($name, LayerTypes::VECTOR);
                $db->Execute("CREATE TABLE {$new->url} (gid serial)");
                $db->Execute("SELECT AddGeometryColumn('','{$new->url}','the_geom',4326,'POINT',2)");
                $db->Execute("CREATE INDEX {$new->url}_index_the_geom ON $new->url USING GIST (the_geom)");
                $db->Execute("CREATE INDEX {$new->url}_index_oid ON $new->url (oid)");

// populate the vector layer's columns
                foreach ($old->getAttributes() as $colname => $type) {
                    if ($colname == 'gid')
                        continue;
                    if ($colname == 'id')
                        continue;
                    if ($colname == 'the_geom')
                        continue;
                    $new->addAttribute($colname, $type);
                }

// connect to the ODBC...
                $odbcinfo = $old->url;
                switch ($odbcinfo->driver) {
                    case ODBCUtil::MYSQL :
                        $db = NewADOConnection("mysql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                        $records = $db->Execute("SELECT * FROM `{$odbcinfo->table}`");
                        break;
                    case ODBCUtil::PGSQL :
                        $db = NewADOConnection("postgres://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                        $records = $db->Execute("SELECT * FROM \"{$odbcinfo->table}\"");
                        break;
                    case ODBCUtil::MSSQL :
                        list ( $odbc, $odbcini, $freetdsconf ) = $sys->connectToODBC($odbcinfo, 'NOCONNECT');
                        $db = NewADOConnection("mssql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@dsn/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                        $records = $db->Execute("SELECT * FROM {$odbcinfo->table}");
                        break;
                }

// iterate over the ODBC records, copying them into the new vector table with a point geometry
                while (!$records->EOF) {
                    $record = $records->fields;
                    $record ['wkt_geom'] = sprintf("POINT(%f %f)", $record [$odbcinfo->loncolumn], $record [$odbcinfo->latcolumn]);
                    $new->insertRecord($record);
                    $records->MoveNext();
                }

// done copying the ODBC layer to a new Vector layer
                $new->setDBOwnerToOwner();
                break;
        }
        $new->Merge($old);



// populate some more simple attributes
        $new->originalid = $layerId;
// if this is a vector layer, copy the color scheme
        if ($new->type == LayerTypes::VECTOR) {
//$new->tooltip = $old->tooltip;
// $new->rich_tooltip = $old->rich_tooltip;
//$new->labelitem = $old->labelitem;
//$new->label_style = $old->label_style;
            $colorSchemeEntries = $old->colorscheme->getAllEntries(true);
            $new->name = 'Copy of ' . $new->name;
            $new->colorschemetype = $old->colorschemetype;
            foreach ($colorSchemeEntries as $oldColorSchemeEntry) {
                $newColorSchemeEntry = $new->colorscheme->addEntry();
                $newColorSchemeEntry->MergeEntry($oldColorSchemeEntry);
            }
        }
        $new->touch();
// done
        return $new;
    }

    /**
     * Create a new Layer owned by this Person.
     *
     * @param
     * 		string The name of the new Layer. The user must not already have a Layer by this same name.
     * @param
     * 		integer The layer's type, one of the LayerTypes::* defines.
     * @return Layer A Layer object.
     */
    public static function CreateLayer($name, $type, $ownerId, $adderId, $includeClass = true) {

// f($this->community && count($this->listLayers()) >= 3) return false;
        $db = System::GetDB(System::DB_ACCOUNT_SU);

        $layerId = $db->GetOne('INSERT INTO ' . self::TABLE . ' (owner,name,type,adder) VALUES (?,?,?,?) RETURNING id', array(
            $ownerId,
            $name,
            $type,
            $adderId
        ));
        if (!$layerId) {
            throw new Exception($db->ErrorMsg());
            die();
        }
        $layer = self::GetLayer($layerId);
        if ($includeClass)
            $layer->colorscheme->setSchemeToSingle();
        return $layer;
    }

    public function newReply($user, $parent, $post) {
        $new = $this->world->db->Execute("INSERT INTO layer_discussions (id, text, owner, layer_id, parent) VALUES (DEFAULT, ?, ? ,?, ?) RETURNING id;", array(
                    nl2br(htmlentities($post)),
                    $user->id,
                    $this->id,
                    $parent
                ))->fields ["id"];
        $results = $this->world->db->Execute("WITH RECURSIVE parentTree(id, parent, owner) AS(
				SELECT id, parent, owner FROM layer_discussions WHERE id = ?
				UNION ALL
				SELECT
				t.id,
				t.parent,
				t.owner
				FROM layer_discussions t
				JOIN parentTree rt ON rt.parent = t.id
			)
			SELECT owner FROM parentTree GROUP BY owner", array(
                    $parent
                ))->getRows();
        foreach ($results as $result) {
            $this->world->getPersonById($result ["owner"])->notify($user->id, "commented on layer:", $this->name, $this->id, "./?do=layer.discussion&id=" . $this->id . "#" . $new, 11);
        }
        $this->owner->notify($user->id, "commented on layer:", $this->name, $this->id, "./?do=layer.discussion&id=" . $this->id . "#" . $new, 11);
        return $new;
    }

    public function getReply($id = false) {
        $retrive = Array(
            $this->id
        );
        if ($id)
            $retrive [] = $id;
        $query = "SELECT * FROM layer_discussions AS d WHERE layer_id = ?" . (($id) ? " AND id=?" : "") . " ORDER BY created";
        $results = $this->world->db->Execute($query, $retrive);
        return $results->getRows();
    }

    private function nestResults($results, $id) {
        $return = Array();
        foreach ($results as $result) {
            if ($result ["parent"] == $id) {
                $result ["fromnow"] = timeToHowLongAgo(time() - strtotime($result ["created"]));
                $return [$result ["id"]] = Array(
                    "data" => $result
                );
            }
        }
        foreach ($return as $key => &$result) {
            $result ["children"] = $this->nestResults($results, $key);
        }
        return $return;
    }

    public function getNestedReplies() {
        $query = "SELECT * FROM layer_discussions AS d WHERE layer_id = ? ORDER BY created";
        $results = $this->world->db->Execute($query, array(
            $this->id
        ));
        $return = $this->nestResults($results, 0);
        $return = Array(
            0 => Array(
                "data" => Array(
                    "id" => 0,
                    "text" => $this->description,
                    "owner" => $this->owner->id,
                    "created" => $this->created,
                    "fromnow" => timeToHowLongAgo($this->created),
                    "layer_id" => $this->id,
                    "parent" => 0
                ),
                "children" => $return
            )
        );
        return $return;
    }

    public function deleteReply($id) {
        $this->world->db->Execute("UPDATE layer_discussions SET text='Comment Removed' WHERE id = ?", array(
            $id
        ));
    }

    public function getSharingInfo() {
        $db = System::GetDB(System::DB_ACCOUNT_SU);
        $sharingData = $db->GetAll('select * from layersharing where layer=' . $this->id);
        return $sharingData;
    }

    public function getGroupSharingInfo() {
        $db = System::GetDB(System::DB_ACCOUNT_SU);
        $sharingData = $db->GetAll('select * from layersharing_socialgroups where layer_id=' . $this->id);
        return $sharingData;
    }

    public function getBookmarkInfo() {
        $db = System::GetDB(System::DB_ACCOUNT_SU);
        $data = $db->GetAll('select * from	layer_bookmarks where layer=' . $this->id);
        return $data;
    }

    public function getCollectionInfo($asParent = true) {
        $db = System::GetDB(System::DB_ACCOUNT_SU);

        if ($asParent) {
            return $db->GetAll('select * from layer_collections where layer_id =' . $this->id);
        }
        return $db->GetAll('select * from layer_collections where parent_id =' . $this->id);
    }

    public static function GetLayer($layerId, $asLayer = true) {
        if ($asLayer) {
            $sys = System::Get();
            return new Layer($sys, $layerId);
        }
        $layer = self::GetLayer($layerId, true);
        $record = $layer->layer_record;
        $layer->RefreshLayerRecord();
        $record ['metadata'] = $layer->metadata;
        $record ['custom_data'] = $layer->custom_data;
        $record ['import_info'] = $layer->import_info;
        $record ['field_info'] = $layer->field_info;

        return $record;
    }

    public static function GetLayerByVectorURL($layerURL, $asLayer = true) {
        $layerURL = trim($layerURL);
        $layerId = 0 + substr($layerURL, 11);
        return self::GetLayer($layerId, $asLayer);
    }

    public static function DumpArray($layerId) {
        $layer = self::GetLayer($layerId);
        $colorscheme = $layer->colorscheme->getAllEntries(false);
        $layerInfo = self::GetLayer($layerId, false);
        $layerData = array();
        $layerData ['classification'] = $colorscheme;
        $layerData ['layer'] = $layerInfo;
        $layerData ['sharing'] = $layer->getSharingInfo();
        $layerData ['group_sharing'] = $layer->getGroupSharingInfo();
        $layerData ['collection_info'] ['as_parent'] = $layer->getCollectionInfo();
        $layerData ['collection_info'] ['as_child'] = $layer->getCollectionInfo(false);
        return $layerData;
    }

    public function GetCachefileName() {
        $ini = System::GetIni();
        $cacheFile = "{$ini->thumbdir}{$ini->name}/layer.{$this->id}.png";

        return $cacheFile;
    }

    public function GenerateThumbnail($force = false, $returnImage = true) {
        $ini = System::GetIni();
        $cacheFile = $this->GetCacheFileName();


        if ($force) {
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
        }

        if ($force === false) {
            $force = $this->IsThumbnailStale();
        }

        if ($returnImage && !$force) {

            if (file_exists($cacheFile)) {
                WAPI::SetWapiHeaders(WAPI::FORMAT_PNG);
                readfile($cacheFile);
                return;
            }
        }

// if the extent is all zeroes, then fudge it to at least be valid
        $extent = $this->getExtent();

// create a new Mapper...
        $sys = System::Get();
        $mapper = $sys->getMapper();

// $mapper->debugMapFile = true;
// TODO: Update with PixoSpatial
        $projector = new Projector_MapScript();

        $projector->SetProjection($sys->projections->defaultProj4);
        $projector->SetViewExtents($extent);
        $projector->SetViewSize($ini->thumbnail_width, $ini->thumbnail_height);
        $projector->CenterAt($ini->thumbnail_width / 2, $ini->thumbnail_height / 2, - 1.75);

        $mapper->labels = true;

        $mapper->init(true, $projector->mapObj);

        /*
         * $mapper->width = ( int ) $ini->thumbnail_width;
         * $mapper->height = ( int ) $ini->thumbnail_height;
         * $mapper->extent = $extent;
         */
// add the basemap
        if ($this->type == LayerTypes::VECTOR or $this->type == LayerTypes::RELATIONAL or $this->type == LayerTypes::ODBC or $this->type == LayerTypes::WMS) {
            $public = $sys->getPersonById(0);
            $basemap = $public->getLayerById($ini->basemap);

            if ($basemap)
                $mapper->addLayer($basemap, 1.0, false, null, null, true);
        }

// add the requested layer
        $mapper->addLayer($this, 1.0, false, null, null, true, false);
// $mapper->debugMapFile=true;
// mapper->init();
// render the image, saving its content to the cache file
// file_put_contents($cachefile,

        try {
// WAPI::SetWapiHeaders(WAPI::FORMAT_PNG);
#$mapper->debugMapFile = true;

            $mapper->renderStream($force, $cacheFile, false);
        } catch (Exception $e) {
            if (!$returnImage)
                return;
            WAPI::SetWapiHeaders(WAPI::FORMAT_PNG);
            readfile(BASEURL . "media/empty.png");
        }

        if (!$returnImage)
            return;
        WAPI::SetWapiHeaders(WAPI::FORMAT_PNG);

        readfile($cacheFile);
    }

    public function GetSubs() {
        $subsRes = $this->world->db->Execute('select id from layers where parent=? order by abs(z)', $this->id);
        $subs = array();
        foreach ($subsRes as $subId) {
            $subs[] = $this->GetLayer($subId);
        }
        return $subs;
    }

    public function GetSubsAsIdPairs() {
        $subsRes = System::GetDB()->Execute('select id,layer_id as layerId from layer_collections where parent_id=? order by z', $this->id);
        return $subsRes;
    }

    function Merge(Layer $oldLayer) {
        $record = $oldLayer->GetLayerRecord();

        unset($record['id']);
        if (is_null($record['tags'])) {
            $record['tags'] = '';
        }

        $db = System::GetDB();
//$db->debug = true;
        $result = $db->AutoExecute('layers', $record, 'UPDATE', 'id=' . $this->id);
        $this->layer_record = $db->GetRow('select * from layers where id=' . $this->id);
    }

    function __get_import_info() {
        $db = System::GetDB();
        $info = $db->GetOne('select import_info from layers where id=?', [$this->id]);
        if ($info) {
            return json_decode($info, true);
        }
        return $info;
    }

    public function Spatialize($geomtype) {

        if (intval($this->type) === LayerTypes::RELATABLE) {
            $ok = SQLUtil::SpatializeTable($this->url);
            if ($ok) {
                $db = \System::GetDB();
                $db->Execute('update layers set type=' . intval(LayerTypes::VECTOR) . ' where id=' . $this->id);
                $this->geom_type = $geomtype;
            }
        } else {
            throw new \Exception('Layer not eligible for spatialization');
        }
    }

    public function ProcessFieldInfo($value) {
        if (is_null($value) || ($value == '')) {
            return null;
        }
        if (is_string($value)) {
            if ((stripos($value, '{"json":') === 0) || (stripos($value, "{'json':") == 0)) {
                $value = ParamUtil::GetJSON(array(
                            'arg' => $value
                                ), 'arg');
            } else {
                $value = json_decode($value, true);
            }
        }

        if (is_null($value)) {
            return null;
        }
        if (!is_array($value)) {
            return $value;
        }
        $atts = $this->getAttributes();

        $fieldNames = [];
        foreach ($value as $i => $fieldInfo) {
            if (isset($fieldInfo['newName'])) {
                if ($fieldInfo['newName'] !== $fieldInfo['name']) {
                    $newName = $fieldInfo['newName'];
                    $oldName = $fieldInfo['name'];
                    $this->renameAttribute($oldName, $newName);
                    unsset($fieldInfo['newName']);
                    $fieldInfo['name'] = $newName;
                }
            } else {
                if (!isset($atts[$fieldInfo['name']])) {
                    $this->addAttribute($fieldInfo['name'], $fieldInfo['type']);
                }
            }
            $fieldInfo['z'] = -$i;
            $fieldNames[] = $fieldInfo['name'];
            $value[$i] = $fieldInfo;
        }

        foreach ($atts as $attName => $att) {
            if (in_array($attName, ['gid', 'the_geom'], strict)) {
                continue;
            }
            if (!in_array($attName, $fieldNames, true)) {
                $this->dropAttribute($attName);
            }
        }

        return $value;
    }

    public function HasTable() {
        if (!is_null($this->_hasTable)) {
            return $this->_hasTable;
        }
        $db = System::GetDB();

        $hasTable = false;
        if (is_string($this->url)) {
            $hasTable = $db->GetOne('select true as hasTable from information_schema.tables where table_name=?', [$this->url]);
        }
        $this->_hasTable = $hasTable;
        if ($hasTable === null) {
            return false;
        }
        return $hasTable;
    }

    public function GetViewDef() {
        if (!in_array($this->type, [LayerTypes::RELATIONAL, LayerTypes::SMART_LAYER])) {
            throw Exception("layer definition problem:" . $this->url . ' is not a relational layer');
        }
        $db = System::GetDB();
        $query = "get_view_def('{$this->url}',true)";
        $def = $db->GetOne($query);
        if (!$def) {
            throw Exception("layer definition problem:There was a problem retrieving a view definition for " . $this->url);
            return false;
        }
        if (strpos($def, -1, 1) === ';') {
            $def = substr($def, 0, strlen($def) - 1);
        }
        return $def;
    }

}

?>
