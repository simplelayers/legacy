<?php

namespace views;

class ProjectUserViews
extends UserViews
{
	public function GetMine($filter=false,$useAnd=true) {
		$query = "SELECT
					f.*,
					people.realname AS owner_name,
					CASE
					WHEN (SELECT DISTINCT
						project_bookmarks.id
						FROM project_bookmarks
						WHERE project_bookmarks.owner=?
						AND project_bookmarks.project=f.id) IS NULL THEN 'false'
					ELSE 'true'
					END AS bookmarked
				FROM projects AS f
				JOIN people ON f.owner=people.id
				WHERE f.owner=? OR f.adder=?
				ORDER BY UPPER(f.name)";
		if($filter !== false)$this->AddFilters($query,$filter,$useAnd);
		return $this->GetResults($query, array($this->userid, $this->userid,$this->userid));
	}

	public function GetBookMarked($filter=false,$useAnd=true) {
		$query = "SELECT
					f.*,
					people.realname AS owner_name,
					people.id AS owner,
					f.id AS projectid,
					'true' AS bookmarked
				FROM projects AS f
				JOIN project_bookmarks ON f.id=project_bookmarks.project
				LEFT OUTER JOIN projectsharing ON f.id = projectsharing.project AND (projectsharing.who=? OR projectsharing.who IS NULL)
				JOIN people ON f.owner = people.id
				WHERE project_bookmarks.owner=? AND (f.private = false OR
					(projectsharing.permission > 0) OR f.owner=project_bookmarks.owner)
				ORDER BY UPPER(f.name)";
		if($filter !== false)$this->AddFilters($query,$filter,$useAnd);
		return $this->GetResults($query, array($this->userid, $this->userid));
	}

	public function GetOwners($minPermission=1,$filter=false,$useAnd=true) {
		
		$minPermission -= 1;
		$query = "SELECT
					people.realname AS username,
					people.id AS userid,
					people.realname AS name,
					people.id AS id
				FROM people
				WHERE
				people.id!=? AND
				id IN (SELECT DISTINCT
							owner
						FROM projects AS f
						WHERE f.private = false OR f.id IN (SELECT DISTINCT
											project
										FROM projectsharing
										WHERE
											who=?
											AND permission >$minPermission)";
		if($filter !== false)$this->AddFilters($query,$filter,$useAnd);
		$query .= ") ORDER BY UPPER(people.realname)";
		return $this->GetResults($query,array($this->userid,$this->userid));
	}
	
	public function GetByOwner($ownerId,$minPermission=1,$filter=false,$useAnd=true) {
		$minPermission-=1;
		$query = "SELECT
					f.*,
					people.realname AS owner_name,
					projectsharing.permission,
					CASE
					WHEN (SELECT DISTINCT
						project_bookmarks.id
						FROM project_bookmarks
						WHERE project_bookmarks.owner=?
						AND project_bookmarks.project=f.id) IS NULL THEN 'false'
					ELSE 'true'
					END AS bookmarked
				FROM projects AS f
				LEFT OUTER JOIN projectsharing ON f.id = projectsharing.project AND (projectsharing.who=? OR projectsharing.who IS NULL)
				JOIN people ON f.owner = people.id
				WHERE f.owner!=? AND ".(($ownerId < 0)? "" : " f.owner=? AND")." (f.private = false OR
					(projectsharing.permission > $minPermission))";
		if($filter !== false)$this->AddFilters($query,$filter,$useAnd);
		$query .= " ORDER BY UPPER(f.name), UPPER(people.realname)";
		return $this->GetResults($query,(($ownerId < 0) ? array($this->userid,$this->userid,$this->userid) : array($this->userid,$this->userid,$this->userid,$ownerId)));
	}
	
	public function GetByGroup($id,$filter=false,$useAnd=true) {
		$query = "SELECT
					f.*,
					people.realname AS owner_name,
					projectsharing_socialgroups.permission,
					CASE
					WHEN (SELECT DISTINCT
						project_bookmarks.id
						FROM project_bookmarks
						WHERE project_bookmarks.owner=?
						AND project_bookmarks.project=f.id) IS NULL THEN 'false'
					ELSE 'true'
					END AS bookmarked
				FROM projects AS f
				LEFT OUTER JOIN projectsharing_socialgroups ON f.id = projectsharing_socialgroups.project_id
				JOIN people ON f.owner = people.id
				WHERE projectsharing_socialgroups.permission > 0
				AND projectsharing_socialgroups.group_id = ?";
		if($filter !== false)$this->AddFilters($query,$filter,$useAnd);
		$query .= " ORDER BY UPPER(f.name), UPPER(people.realname)";
		return $this->GetResults($query, array($this->userid,$id));
	}
	public function GetGroups($filter=false,$useAnd=true) {
		$query = "SELECT
				groups.id,
				title AS name
				FROM groups
				WHERE id = ANY(SELECT group_id FROM groups_members WHERE person_id = ? AND (actor = 1 OR actor = 5)) AND
				id = ANY(SELECT group_id FROM projectsharing_socialgroups WHERE permission != 0)";
		if($filter !== false) $this->AddFilters($query,$filter,$useAnd);
		$query .= " ORDER BY UPPER(title)";
		
		$return = $this->GetResults($query,array($this->userid));
		return $return;
	}
	public function GetByTag($id,$filter=false,$useAnd=true) {
		$addToQuery = "";
		$query = "SELECT
					f.*,
					people.realname AS owner_name,
					projectsharing.permission,
					CASE
					WHEN (SELECT DISTINCT
						project_bookmarks.id
						FROM project_bookmarks
						WHERE project_bookmarks.owner=?
						AND project_bookmarks.project=f.id) IS NULL THEN 'false'
					ELSE 'true'
					END AS bookmarked
				FROM projects AS f
				LEFT OUTER JOIN projectsharing ON f.id = projectsharing.project AND (projectsharing.who=? OR projectsharing.who IS NULL)
				JOIN people ON f.owner = people.id
				WHERE LOWER(?) = ANY(string_to_array(LOWER(f.tags), ',')) AND (f.private = false OR
					(projectsharing.permission > 0) OR f.owner = ?)";
		if($filter !== false)$this->AddFilters($query,$filter,$useAnd);
		return $this->GetResults($query,array($this->userid,$this->userid,$id,$this->userid));
	}
}

?>