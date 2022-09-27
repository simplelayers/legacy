<?php

namespace views;

class ContactViews extends UserViews {

    public static function GetViewDisplayList() {
        $views = array();
        $views['Mine'] = 'mine';
        $views['Groups'] = 'groups';
        $views['Community'] = 'everyoneelse';
        return $views;
    }

    public function GetBookMarked($filter = false, $useAnd = true) {
        
    }

    public function GetMine($filter = false, $useAnd = true) {
        
    }

    public function GetOwners($minPermission = 1, $filter = false, $useAnd = true) {
        
    }

    public function GetByOwner($ownerId, $minPermission = 1, $filter = false, $useAnd = true) {
        
    }

    public function GetMine2($permissionsFor, $filter = false, $useAnd = true) {
        $permissionQuery = "";
        $reportLvlQuery = '';
        if ($permissionsFor !== false) {
            if ($permissionsFor[0] === 'shareproject') {
                $permissionQuery = ",CASE WHEN (SELECT  permission FROM projectsharing WHERE project = " . $permissionsFor[1] . " AND who = people.id) IS NULL THEN 0 ELSE (SELECT  permission FROM projectsharing WHERE project = " . $permissionsFor[1] . " AND who = people.id) END AS permission";
                $reportLvlQuery = '';
            }
            if ($permissionsFor[0] === 'sharelayer') {
                $permissionQuery = ",CASE WHEN (SELECT  permission FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) IS NULL THEN 0 ELSE (SELECT  permission FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) END AS permission";
                $reportLvlQuery = ",CASE WHEN (SELECT  reporting_level FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) IS NULL THEN 0 ELSE (SELECT  reporting_level FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) END AS reporting";
            }
        }
        $query = <<<QUERY
SELECT
people.id AS id,
people.username,
people.realname,
'true' AS added,
visible
$permissionQuery
$reportLvlQuery
FROM buddylist

LEFT OUTER JOIN people ON buddy = people.id
WHERE owner = ?
QUERY;
        if ($filter !== false)
            $this->AddFilters($query, $filter, $useAnd);
        $query .= " ORDER BY UPPER(people.username)";
        
        return $this->GetResults($query);
    }

    public function GetOthers($permissionsFor, $filter = false, $useAnd = true) {
        $sharingQuery = "";
        $reportLvlQuery = '';
        if ($permissionsFor !== false) {
            if ($permissionsFor[0] === 'shareproject') {
                $sharingQuery = ",CASE WHEN (SELECT  permission FROM projectsharing WHERE project = " . $permissionsFor[1] . " AND who = people.id) IS NULL THEN 0 ELSE (SELECT  permission FROM projectsharing WHERE project = " . $permissionsFor[1] . " AND who = people.id) END AS permission";
                $reportLvlQuery = '';
            }
            if ($permissionsFor[0] === 'sharelayer') {
                $sharingQuery = ",CASE WHEN (SELECT  permission FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) IS NULL THEN 0 ELSE (SELECT  permission FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) END AS permission";
                $reportLvlQuery = ",CASE WHEN (SELECT  reporting_level FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) IS NULL THEN 0 ELSE (SELECT  reporting_level FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) END AS reporting";
            }
        }
        $query = <<<QUERY
SELECT 
people.id AS id,
people.username,
people.realname,
visible,
CASE
WHEN (SELECT DISTINCT
        bl.id
        FROM buddylist AS bl
        WHERE bl.owner=?
        AND bl.buddy=people.id) IS NULL THEN 'false'
ELSE 'true'
END AS added
$sharingQuery
$reportLvlQuery
FROM buddylist
LEFT OUTER JOIN people ON owner = people.id
WHERE buddy = ?
QUERY;
        if ($filter !== false) {
            $this->AddFilters($query, $filter, $useAnd);
        }
        $query .= ' ORDER BY UPPER(people.username)';
        
        return $this->GetResults($query, array($this->userid, $this->userid));
    }

    public function GetEveryoneElse($permissionsFor, $filter = false, $useAnd = true, $visCheck = true) {
        $sharingQuery = '';
        $reportLvlQuery = '';
        if ($permissionsFor !== false) {
            if ($permissionsFor[0] === 'shareproject') {
                $sharingQuery = ",CASE WHEN (SELECT  permission FROM projectsharing WHERE project = " . $permissionsFor[1] . " AND who = people.id) IS NULL THEN 0 ELSE (SELECT  permission FROM projectsharing WHERE project = " . $permissionsFor[1] . " AND who = people.id) END AS permission";
                $reportLvlQuery = '';
            }
            if ($permissionsFor[0] === 'sharelayer') {
                $sharingQuery = ",CASE WHEN (SELECT  permission FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) IS NULL THEN 0 ELSE (SELECT  permission FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) END AS permission";
                $reportLvlQuery = ",CASE WHEN (SELECT  reporting_level FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) IS NULL THEN 0 ELSE (SELECT  reporting_level FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) END AS reporting";
            }
        }
        $visAnd = ($visCheck) ? "people.visible = 't' AND " : "";
        $query = <<<QUERY
SELECT 
    people.id AS id,
    people.username,
    people.realname,
    visible,
    'false' AS added
    $sharingQuery
    $reportLvlQuery
    FROM people
    WHERE $visAnd people.id NOT IN (SELECT buddy FROM buddylist WHERE owner = ?) AND people.id != ?
QUERY;
        if ($filter !== false)
            $this->AddFilters($query, $filter, $useAnd);
        $query .= " ORDER BY UPPER(people.username)";
        return $this->GetResults($query, array($this->userid, $this->userid));
    }

    public function GetGroups($filter = false, $useAnd = true) {
        $query = "SELECT
		groups.id,
		title AS name
		FROM groups
		WHERE id = ANY(SELECT group_id FROM groups_members WHERE person_id = ? AND (actor = 1 OR actor = 5))";
        if ($filter !== false)
            $this->AddFilters($query, $filter, $useAnd);
        $query .= " ORDER BY UPPER(title)";
        return $this->GetResults($query, array($this->userid));
    }

    public function GetGroup($id, $me = false, $permissionsFor, $filter = false, $useAnd = true) {
        if ($id === '') {
            return (object) ['_numOfRows' => 0];
        }
        $sharingQuery = "";
        $reportLvlQuery = '';

        if ($permissionsFor !== false) {
            if ($permissionsFor[0] === 'shareproject') {
                $sharingQuery = ",CASE WHEN (SELECT  permission FROM projectsharing WHERE project = " . $permissionsFor[1] . " AND who = people.id) IS NULL THEN 0 ELSE (SELECT  permission FROM projectsharing WHERE project = " . $permissionsFor[1] . " AND who = people.id) END AS permission";
                $reportLvlQuery = '';
            }
            if ($permissionsFor[0] === 'sharelayer') {
                $sharingQuery = ",CASE WHEN (SELECT  permission FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) IS NULL THEN 0 ELSE (SELECT  permission FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) END AS permission";
                $reportLvlQuery = ",CASE WHEN (SELECT  reporting_level FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) IS NULL THEN 0 ELSE (SELECT  reporting_level FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) END AS reporting";
            }
        }
        $qroupQuery = ($id === '') ? '' : 'group_id = ? AND';
        $meQuery = ($me) ? '' : 'AND people.id != ?';
        $query = <<<QUERY
SELECT 
    people.id AS id,
    people.username,
    people.realname,
    groups_members.actor AS status,
    groups_members.actor,
    groups_members.seat,
    visible,
    CASE
    WHEN (SELECT DISTINCT
            bl.id
            FROM buddylist AS bl
            WHERE bl.owner=?
            AND bl.buddy=people.id) IS NULL THEN 'false'
    ELSE 'true'
    END AS added
    $sharingQuery
    $reportLvlQuery
    FROM groups_members
    JOIN people ON person_id = people.id WHERE $qroupQuery (actor = 1 OR actor = 5) $meQuery
QUERY;
        if ($filter !== false)
            $this->AddFilters($query, $filter, $useAnd);
        $query .= " ORDER BY UPPER(username)";
        $queryParams = null;
        if ($me) {
            $queryParams = [$this->userid, $id];
        } else {
            $queryParams = [$this->userid, $id, $this->userid];
        }
        return $this->GetResults($query, $queryParams);
        // return $this->GetResults($query,($me ? array($this->userid,$id) : array($this->userid,$id,$this->userid)));
    }

    public function GetTags($id, $permissionsFor, $filter = false, $useAnd = true) {
        $sharingQuery = "";
        $reportLvlQuery = '';
        if ($permissionsFor !== false) {
            if ($permissionsFor[0] === 'shareproject') {
                $sharingQuery = ",CASE WHEN (SELECT  permission FROM projectsharing WHERE project = " . $permissionsFor[1] . " AND who = people.id) IS NULL THEN 0 ELSE (SELECT  permission FROM projectsharing WHERE project = " . $permissionsFor[1] . " AND who = people.id) END AS permission";
                $reportLvlQuery = '';
            }
            if ($permissionsFor[0] === 'sharelayer') {
                $sharingQuery = ",CASE WHEN (SELECT  permission FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) IS NULL THEN 0 ELSE (SELECT  permission FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) END AS permission";
                $reportLvlQuery = ",CASE WHEN (SELECT  reporting_level FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) IS NULL THEN 0 ELSE (SELECT  reporting_level FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) END AS reporting";
            }
        }
        $query = <<<QUERY
SELECT 
    people.id AS id,
    people.username,
    people.realname,
    visible,
    CASE
    WHEN (SELECT DISTINCT
            bl.id
            FROM buddylist AS bl
            WHERE bl.owner=?
            AND bl.buddy=people.id) IS NULL THEN 'false'
    ELSE 'true'
    END AS added
    $sharingQuery
    $reportLvlQuery    
    FROM people
    WHERE LOWER(?) = ANY(string_to_array(LOWER(tags), ',')) AND people.id != ?
QUERY;
        if ($filter !== false)
            $this->AddFilters($query, $filter, $useAnd);
        $query .= " ORDER BY UPPER(username)";
        return $this->GetResults($query, array($this->userid, $id, $this->userid));
    }

    public function GetApplicants($id, $permissionsFor) {
        $sharingQuery = '';
        $reportLvlQuery = '';
        if ($permissionsFor !== false) {
            if ($permissionsFor[0] === 'shareproject') {
                $sharingQuery = ", CASE WHEN (SELECT  permission FROM projectsharing WHERE project = " . $permissionsFor[1] . " AND who = people.id) IS NULL THEN 0 ELSE (SELECT  permission FROM projectsharing WHERE project = " . $permissionsFor[1] . " AND who = people.id) END AS permission";
            }
            if ($permissionsFor[0] === 'sharelayer') {
                $sharingQuery = ", CASE WHEN (SELECT  permission FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) IS NULL THEN 0 ELSE (SELECT  permission FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) END AS permission";
                $reportLvlQuery = ",CASE WHEN (SELECT  reporting_level FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) IS NULL THEN 0 ELSE (SELECT  reporting_level FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) END AS reporting";
            }
        }
        $query = <<<QUERY
SELECT people.id AS id,
        people.username,
        people.realname,
        visible,
        CASE
        WHEN (SELECT DISTINCT
                bl.id
                FROM buddylist AS bl
                WHERE bl.owner=?
                AND bl.buddy=people.id) IS NULL THEN 'false'
        ELSE 'true'
        END AS added,
        groups_members.actor AS status,
        groups_members.actor
        $sharingQuery
        $reportLvlQuery
        FROM groups_members
        JOIN people ON person_id = people.id
        WHERE (actor = 2 OR actor = 3) AND group_id = ?
QUERY;
        $return = $this->GetResults($query, Array($this->userid, $id));
        return $return;
    }

    public function GetDenied($id, $permissionsFor) {
        $sharingQuery = '';
        $reportLvlQuery = '';
        if ($permissionsFor !== false) {
            if ($permissionsFor[0] === 'shareproject') {
                $sharingQuery = ", CASE WHEN (SELECT  permission FROM projectsharing WHERE project = " . $permissionsFor[1] . " AND who = people.id) IS NULL THEN 0 ELSE (SELECT  permission FROM projectsharing WHERE project = " . $permissionsFor[1] . " AND who = people.id) END AS permission";
            }
            if ($permissionsFor[0] === 'sharelayer') {
                $shaingQuery = ", CASE WHEN (SELECT  permission FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) IS NULL THEN 0 ELSE (SELECT  permission FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) END AS permission";
                $reportLvlQuery = ",CASE WHEN (SELECT  reporting_level FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) IS NULL THEN 0 ELSE (SELECT  reporting_level FROM layersharing WHERE layer = " . $permissionsFor[1] . " AND who = people.id) END AS reporting";
            }
        }
        $query = <<<QUERY
SELECT people.id AS id,
    people.username,
    people.realname,
    visible,
    CASE
    WHEN (SELECT DISTINCT
            bl.id
            FROM buddylist AS bl
            WHERE bl.owner=?
            AND bl.buddy=people.id) IS NULL THEN 'false'
    ELSE 'true'
    END AS added,
    groups_members.actor AS status,
    groups_members.actor
    $sharingQuery
    $reportLvlQuery
    FROM groups_members
    JOIN people ON person_id = people.id WHERE group_id = ? AND actor = 4
QUERY;
        $return = $this->GetResults($query, Array($this->userid, $id));
        return $return;
    }

}

?>