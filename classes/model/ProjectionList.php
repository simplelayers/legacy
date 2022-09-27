<?php

namespace model;

use utils\ParamUtil;
use model\CRUD;

/**
 * See the Mapper class documentation.
 * @package ClassHierarchy
 */

/**
 * A wrapper class for manipulating the spatial_ref_sys table.
 * This allows one to search, define and delete, etc. the defined projections.
 *
 * @package ClassHierarchy
 */
class ProjectionList extends CRUD {

    public $defaultSRID = 4326; //lat lon (unprojected).
    public $defaultProj4;

    /**
     * @ignore
     */
    function __construct() {
        $this->defaultProj4 = $this->getProj4BySRID($this->defaultSRID);

        $this->table = 'spatial_ref_lookup';
    }

    /////
    ///// finding existing projections
    /////

    /**
     * Fetch a projection, by its unique ID.
     * @param integer $srid The spatial reference identifier (SRID) uniquely identifying the projection.
     * @return array An assocarray describing a projection.
     */
    function getProjectionBySRID($srid = null) {
        $db = \System::GetDB(\System::DB_ACCOUNT_SU);
        if (is_null($srid))
            $srid = $this->defaultSRID;
        $result = $db->GetOne('SELECT proj4 FROM spatial_ref_lookup WHERE srid=?', array($srid));
        return $result;
    }

    /////
    ///// create, update, delete
    /////

    /**
     * Add a new projection to the table.
     * @param array $info An assocarray of information about the projection.
     * @param integer $srid Optional; a unique ID# (SRID) for the new projection. If specified, it must not already exist.
     * @return integer The numeric (integer) SRID of the newly-generated projection.
     */
    function createProjection($info, $srid = null) {
        /* $rs = $this->world->db->Execute('SELECT * FROM spatial_ref_sys WHERE srid=0');
          if ($srid) $info['srid'] = $srid;
          $sql = $world->db->getInsertSql($rs,$info);
          $world->db->Execute($sql,$info);
          // now fetch and return the newly-generated SRID
          $srid = $world->db->Execute('SELECT srid FROM spatial_ref_sys WHERE oid=?', array($world->db->Insert_ID()) );
          return $srid;
         */
    }

    /**
     * Update a projection, using its SRID as an identifier and passing an assocarray of changes.
     * @param integer $srid The unique SRID for the projectin.
     * @param array $updates An assocarray of info, to be used to update the projection.
     */
    function updateProjectionBySRID($srid, $updates) {
        /* $rs = $this->world->db->Execute('SELECT * FROM spatial_ref_sys WHERE srid=?', array($srid) );
          if (!$rs) return false;
          $sql = $world->db->getUpdateSql($rs,$updates);
          $sql = preg_replace('/\?\s*$/',$srid,$sql);
          $world->db->Execute($sql);
         */
    }

    /**
     * Delete a projection from the list.
     * DO NOT USE THIS, EVER!
     * @param integer $srid The SRID of the projection to delete.
     */
    function deleteProjectionBySRID($srid) {
        // $this->world->db->Execute('DELETE FROM spatial_ref_sys WHERE srid=?', array($srid) );
    }

    /////
    ///// convenience wrappers
    /////

    /**
     * Fetch just the PROJ4 text, given a SRID. This is basically a subset of getProjctionBySRID()
     * @param integer $srid The spatial reference identifier (SRID) uniquely identifying the projection.
     * @return string The PROJ4 text for the given projection, if it exists.
     */
    function getProj4BySRID($srid) {
        $proj4 = $this->getProjectionBySRID($srid);

        return $proj4;
    }

    /**
     * Generate a unique SRID.
     * @param boolean $temporary If true, the generated SRID is chosen within the "temporary" number space. If false (the default) is it generated in the "site specific" number space.
     * @return integer A SRID known to be unique at the time generateSRID() was called.
     *
     * Note: The numeric ranges for site-specific SRIDs and for temporary SRIDs are defined as SRID_* in projections.php
     *
     * Note: This is not thread-safe and the number selection is random.
     *
     * Note: This function does not in fact create the projection nor does it delete them; it just generates numbers.
     */
    function generateSRID($temporary = false) {
        // figure out the min/max range
        global $SRID_TEMPORARY_MIN;
        global $SRID_TEMPORARY_MAX;
        global $SRID_SITESPECIFIC_MIN;
        global $SRID_SITESPECIFIC_MAX;
        $min = $temporary ? $SRID_TEMPORARY_MIN : $SRID_SITESPECIFIC_MIN;
        $max = $temporary ? $SRID_TEMPORARY_MAX : $SRID_SITESPECIFIC_MAX;
        // pick random numbers and if it's available then give it
        // to avoikd infinite loops, this gives up after 100 tries
        $srid = null;
        for ($i = 0; $i < 100; $i++) {
            $random = rand($min, $max);
            if (!$this->getProjectionBySRID($random)) {
                $srid = $random;
                break;
            }
        }
        return $srid;
    }

    /////
    ///// search projections and return matches
    /////

    /**
     * For users to select a commonly-used projection, this returns an array of SRID=>title.
     * Only projections tagged with quicklist=true are returned.
     * @return assocarray Keys are numeric SRID; values are titles.
     */
    function getQuickList() {
        $projx = $this->world->db->Execute('SELECT srid,title FROM spatial_ref_sys WHERE quicklist=true ORDER BY title')->getRows();
        $projs = array();
        foreach ($projx as $p)
            $projs[$p['srid']] = $p['title'];
        return $projs;
    }

    public static function GetQuickLookup($by = 'srid') {
        $db = \System::GetDB(\System::DB_ACCOUNT_SU);
        $projs = $db->Execute('SELECT srid,title FROM spatial_ref_sys WHERE quicklist=true ORDER BY title')->getRows();
        switch ($by) {
            case 'srid':
                $projs = ParamUtil::ResultsToKeyVal($projs, 'srid', 'title');
                break;
            case 'title':
                $projs = ParamUtil::ResultsToKeyVal($projs, 'title', 'srid');
                break;
        }
        return $projs;
    }

    public static function GetQuickOptions($asRecordSet = false, $elementName = 'projection', $elementId = 'projection_sel') {
        $db = \System::GetDB();
        $query = <<<QUERY
select name|| case when variant=''  then '' else  ' - '||variant end as label, srid as value from spatial_ref_lookup where quicklist='t' order by "default" DESC,name,variant
QUERY;

        /* @var ADORecordSet $projs  */
        $projs = $db->Execute($query);
        if (!$asRecordSet) {
            echo "<select name=\"$elementName\" id=\"$elementName\" value=\"WGS 84\">";
            foreach ($projs as $proj) {
                echo "<option value=\"{$proj['value']}\">" . $proj["label"] . "</option>";
            }
            echo "</select>";
            return;
        }
        return $projs;
    }

    public static function GetProj4($srid) {
        $db = \System::GetDB(\System::DB_ACCOUNT_SU);
        $query = 'SELECT proj4 from spatial_ref_lookup';
        return $db->GetOne($query);
    }

    public static function GetWKT($srid) {
        $db = \System::GetDB(\System::DB_ACCOUNT_SU);
        $query = 'SELECT srtext from spatial_ref_sys where srid=?';
        return $db->GetOne($query, $srid);
    }

    /**
     * Fetch a list of projection assoc arrays. The list is those matching the text in their title.
     * @param integer $text A search string, used to match the titles of projections.
     * @return array An array of assoc arrays, each describing a projection.
     */
    function searchProjectionsByTitle($text) {
        /* $text = strtolower(trim(preg_replace('/\W/','',$text)));
          $projs = $this->world->db->Execute("SELECT srid FROM spatial_ref_sys WHERE lower(title) LIKE '%$text%' AND quicklist=true ORDER BY title")->getRows();
          $projs = array_map( create_function('$a','return $a["srid"];') , $projs );
          $projs = array_map( array($this,'getProjectionBySRID') , $projs );
          return $projs;
         */
    }

    public static function BuildSpatialRefs() {
        $db = System::GetDB(System::DB_ACCOUNT_SU);

        $this->Setup_spatial_ref_projections();
        $db->Execute('truncate spatial_ref_lookup');

        $projs = $db->Execute('select proj from spatial_ref_projections');
        $projs = ParamUtil::GetSubValues($projs, 'proj');
        foreach ($projs as $proj) {
            $proj .= ' ';
            $query = "select * from (select srid, substr(proj[2],6) as proj,proj4, srtext from (select srid,string_to_array(proj4text,'+') as proj,proj4text as proj4,srtext from spatial_ref_sys) as q1 order by proj,srid) as q2 where proj='$proj'";

            $projQuery = $db->Execute($query);

            foreach ($projQuery as $projInfo) {
                $matches = array();
                preg_match('/("[^"]+)"/', $projInfo['srtext'], $matches);
                $matches = array_pop($matches);
                $matches = explode('/', $matches);
                $name = $variant = "";
                if (count($matches) > 1) {
                    $name = trim($matches[1]);
                    $variant = trim($matches[0]);
                } else {
                    $name = trim($matches[0]);
                }
                switch ($projInfo['srid']) {
                    case 2163:
                        $name = "US National Atlas Equal Area (Lambert)";
                        break;
                }
                $name = str_replace('"', '', $name);
                $variant = str_replace('"', '', $variant);
                $query = "INSERT INTO spatial_ref_lookup (srid,proj,name,variant,proj4) values(?,?,?,?,?)";
                $record = array($projInfo['srid'], trim($proj), $name, $variant, $projInfo['proj4']);
                $db->Execute($query, $record);
            }
        }
    }

    private function Setup_spatial_ref_projections() {
        $db = System::GetDB(System::DB_ACCOUNT_SU);
        $db->Execute('truncate spatial_ref_projections');

        $fields = array(
            'proj',
            'name',
            'ref_url'
        );
        $file = fopen(BASEDIR . 'includes/spatial_ref_projections.csv', 'r');
        while (!feof($file)) {
            $data = fgetcsv($file);
            foreach ($data as $i => $datum) {
                $data[$i] = trim($datum);
            }
            $rs = $db->Execute("INSERT INTO spatial_ref_projections (" . implode(',', $fields) . ") VALUES(?,?,?)", $data);
            if (!$rs)
                var_dump($db->ErrorMsg());
        }
        fclose($file);
    }

}

?>