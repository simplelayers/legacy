<?php

namespace views;

class GroupViews extends UserViews {

    public function GetMine($filter = false, $useAnd = true) {

        $query = "SELECT *
		FROM groups_members
		JOIN groups ON groups_members.group_id = groups.id
		WHERE person_id = ? AND actor IN (1 ,5)";
        if ($filter !== false)
            $this->AddFilters($query, $filter, $useAnd);
        $query .= "";
        return $this->GetResults($query);
    }

    public function GetBookMarked($filter = false, $useAnd = true) {
        
    }

    public function GetOwners($minPermission = 1, $filter = false, $useAnd = true) {
        
    }

    public function GetByOwner($ownerId, $minPermission = 1, $filter = false, $useAnd = true) {
        
    }

    public function GetIModerate($permissionsFor, $filter = false, $useAnd = true) {
        $sharingQuery = "";
        if ($permissionsFor !== false) {
            if ($permissionsFor[0] === 'shareproject') {
                $sharingQuery = ",CASE WHEN (SELECT  permission FROM projectsharing_socialgroups WHERE project_id = " . $permissionsFor[1] . " AND group_id = groups.id) IS NULL THEN 0 ELSE (SELECT  permission FROM projectsharing_socialgroups WHERE project_id = " . $permissionsFor[1] . " AND group_id = groups.id) END AS permission";
                $reportingLvlQuery = '';
            }
            if ($permissionsFor[0] === 'sharelayer') {
                $sharingQuery = ",CASE WHEN (SELECT  permission FROM layersharing_socialgroups WHERE layer_id = " . $permissionsFor[1] . " AND group_id = groups.id) IS NULL THEN 0 ELSE (SELECT  permission FROM layersharing_socialgroups WHERE layer_id = " . $permissionsFor[1] . " AND group_id = groups.id) END AS permission";
                $reportingLvlQuery = ", CASE WHEN (SELECT  reporting_level FROM layersharing_socialgroups WHERE layer_id = " . $permissionsFor[1] . " AND group_id = groups.id) IS NULL THEN 0 ELSE (SELECT  permission FROM layersharing_socialgroups WHERE layer_id = " . $permissionsFor[1] . " AND group_id = groups.id) END AS reporting";
            }
        }
        $query = <<<QUERY
SELECT
    *
    $sharingQuery
    $reportingLvlQuery
    FROM groups_members
    JOIN groups ON groups_members.group_id = groups.id
    WHERE person_id = ? AND actor = 5
QUERY;
        if ($filter !== false)
            $this->AddFilters($query, $filter, $useAnd);
        $query .= "";
        return $this->GetResults($query);
    }

    public function GetIAmIn($permissionsFor, $filter = false, $useAnd = true) {
        $reportingLvlQuery = '';
        $sharingQuery = '';
        if ($permissionsFor !== false) {
            if ($permissionsFor[0] === 'shareproject') {
                $sharingQuery = ",CASE WHEN (SELECT  permission FROM projectsharing_socialgroups WHERE project_id = " . $permissionsFor[1] . " AND group_id = groups.id) IS NULL THEN 0 ELSE (SELECT  permission FROM projectsharing_socialgroups WHERE project_id = " . $permissionsFor[1] . " AND group_id = groups.id) END AS permission";
                $reportingLvlQuery = '';
            }
            if ($permissionsFor[0] === 'sharelayer') {
                $sharingQuery = ",CASE WHEN (SELECT  permission FROM layersharing_socialgroups WHERE layer_id = " . $permissionsFor[1] . " AND group_id = groups.id) IS NULL THEN 0 ELSE (SELECT  permission FROM layersharing_socialgroups WHERE layer_id = " . $permissionsFor[1] . " AND group_id = groups.id) END AS permission";
                $reportingLvlQuery = ", CASE WHEN (SELECT  reporting_level FROM layersharing_socialgroups WHERE layer_id = " . $permissionsFor[1] . " AND group_id = groups.id) IS NULL THEN 0 ELSE (SELECT  permission FROM layersharing_socialgroups WHERE layer_id = " . $permissionsFor[1] . " AND group_id = groups.id) END AS reporting";
            }
        }
        $query = <<<QUERY
SELECT *
    $sharingQuery
    $reportingLvlQuery
    FROM groups_members
    JOIN groups ON groups_members.group_id = groups.id
    WHERE person_id = ? AND actor = 1
QUERY;
        if ($filter !== false) {
            $this->AddFilters($query, $filter, $useAnd);
        }
        $query .= "";
        return $this->GetResults($query);
    }

    public function GetOpen($permissionsFor, $filter = false, $useAnd = true) {
        $sharingQuery = '';
        $reportingLvlQuery = '';
        if ($permissionsFor !== false) {
            if ($permissionsFor[0] === 'shareproject') {
                $sharingQuery = ",CASE WHEN (SELECT  permission FROM projectsharing_socialgroups WHERE project_id = " . $permissionsFor[1] . " AND group_id = groups.id) IS NULL THEN 0 ELSE (SELECT  permission FROM projectsharing_socialgroups WHERE project_id = " . $permissionsFor[1] . " AND group_id = groups.id) END AS permission";
                $reportingLvlQuery = '';
            }
            if ($permissionsFor[0] === 'sharelayer') {
                $sharingQuery = ",CASE WHEN (SELECT  permission FROM layersharing_socialgroups WHERE layer_id = " . $permissionsFor[1] . " AND group_id = groups.id) IS NULL THEN 0 ELSE (SELECT  permission FROM layersharing_socialgroups WHERE layer_id = " . $permissionsFor[1] . " AND group_id = groups.id) END AS permission";
                $reportingLvlQuery = ", CASE WHEN (SELECT  reporting_level FROM layersharing_socialgroups WHERE layer_id = " . $permissionsFor[1] . " AND group_id = groups.id) IS NULL THEN 0 ELSE (SELECT  permission FROM layersharing_socialgroups WHERE layer_id = " . $permissionsFor[1] . " AND group_id = groups.id) END AS reporting";
            }
        }
        $query = <<<QUERY
SELECT
    *
    $sharingQuery
    $reportingLvlQuery
    FROM groups
    WHERE invite = FALSE AND hidden = FALSE
QUERY;
        if ($filter !== false) {
            $this->AddFilters($query, $filter, $useAnd);
        }
        $query .= '';
        return $this->GetResults($query, Array());
    }

    public function GetInvite($permissionsFor, $filter = false, $useAnd = true) {
        $sharingQuery = '';
        $reportingLvlQuery = '';
        if ($permissionsFor !== false) {
            if ($permissionsFor[0] === 'shareproject') {
                $sharingQuery = ",CASE WHEN (SELECT  permission FROM projectsharing_socialgroups WHERE project_id = " . $permissionsFor[1] . " AND group_id = groups.id) IS NULL THEN 0 ELSE (SELECT  permission FROM projectsharing_socialgroups WHERE project_id = " . $permissionsFor[1] . " AND group_id = groups.id) END AS permission";
                $reportingLvlQuery = '';
            }
            if ($permissionsFor[0] === 'sharelayer') {
                $sharingQuery = ",CASE WHEN (SELECT  permission FROM layersharing_socialgroups WHERE layer_id = " . $permissionsFor[1] . " AND group_id = groups.id) IS NULL THEN 0 ELSE (SELECT  permission FROM layersharing_socialgroups WHERE layer_id = " . $permissionsFor[1] . " AND group_id = groups.id) END AS permission";
                $reportingLvlQuery = ", CASE WHEN (SELECT  reporting_level FROM layersharing_socialgroups WHERE layer_id = " . $permissionsFor[1] . " AND group_id = groups.id) IS NULL THEN 0 ELSE (SELECT  permission FROM layersharing_socialgroups WHERE layer_id = " . $permissionsFor[1] . " AND group_id = groups.id) END AS reporting";
            }
        }
        $query = <<<QUERY
SELECT
    *
    $sharingQuery
    $reportingLvlQuery
    FROM groups
    WHERE invite = TRUE AND hidden = FALSE
QUERY;
        if ($filter !== false) {
            $this->AddFilters($query, $filter, $useAnd);
        }
        $query .= "";
        return $this->GetResults($query, Array());
    }

    public function GetMembers($id) {
        $query = "SELECT
		count(id) FROM groups_members
		WHERE group_id = ? AND actor = 1";
        return $this->db->GetOne($query, Array($id));
    }

    public function GetStatus($gid, $uid = null) {
        if ($uid === null) {
            $uid = $this->userid;
        }
        $query = <<<QUERY
        SELECT 
        actor AS status
        FROM groups_members
        WHERE person_id=? AND group_id = ?
QUERY;
        $result = $this->GetResults($query, Array($uid, $gid));
        return (($result->_numOfRows != 0) ? $result->fields["status"] : 0);
    }

    public function GetByTag($permissionsFor, $id, $filter = false, $useAnd = true) {
        $sharingQuery = '';
        $reportingLvlQuery = '';
        if ($permissionsFor !== false) {
            if ($permissionsFor[0] === 'shareproject') {
                $sharingQuery = ",CASE WHEN (SELECT  permission FROM projectsharing_socialgroups WHERE project_id = " . $permissionsFor[1] . " AND group_id = groups.id) IS NULL THEN 0 ELSE (SELECT  permission FROM projectsharing_socialgroups WHERE project_id = " . $permissionsFor[1] . " AND group_id = groups.id) END AS permission";
            }
            if ( $permissionsFor[0] === 'sharelayer') {
                $sharingQuery = ",CASE WHEN (SELECT  permission FROM layersharing_socialgroups WHERE layer_id = " . $permissionsFor[1] . " AND group_id = groups.id) IS NULL THEN 0 ELSE (SELECT  permission FROM layersharing_socialgroups WHERE layer_id = " . $permissionsFor[1] . " AND group_id = groups.id) END AS permission";
                $reportingLvlQuery = ", CASE WHEN (SELECT  reporting_level FROM layersharing_socialgroups WHERE layer_id = " . $permissionsFor[1] . " AND group_id = groups.id) IS NULL THEN 0 ELSE (SELECT  permission FROM layersharing_socialgroups WHERE layer_id = " . $permissionsFor[1] . " AND group_id = groups.id) END AS reporting";
            }
        }
        $query = <<<QUERY
SELECT
    *
    $sharingQuery
    $reportingLvlQuery
    FROM groups
    WHERE LOWER(?) = ANY(string_to_array(LOWER(tags), ','))
QUERY;
        if ($filter !== false) {
            $this->AddFilters($query, $filter, $useAnd);
        }
        return $this->GetResults($query, array($id));
    }

}

?>