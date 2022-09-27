<?php

namespace views;

use utils\ParamUtil;
use auth\Context;
use auth\WAPIContext;
class LayerUserViews
extends UserViews
{
	const SUBMODE_INCLUDESUBS = true;
	const SUBMODE_NOSUBS = false;
	
	const MINE = 'mine';
	const BOOKMARKED = 'bookmarked';
	const SHARED = 'shared';
	const GROUPS = 'groups';
	const GROUP = 'group';
	const TAGS = 'tags';
	const OWNERS ='owners';
	const OWNER = 'owner';
	
	protected $subMode = self::SUBMODE_NOSUBS;

	public function __construct($userid,$db=null,$subMode = null) {
		if(is_null($db) ) $db= \System::GetDB(\System::DB_ACCOUNT_SU);
		if(is_null($subMode)) $subMode = self::SUBMODE_NOSUBS;
		
		parent::__construct($userid, $db);
		$this->subMode = $subMode;
	}
	
	protected function addSubLayers($results){
		if($results->RecordCount() <= 0) return $results;
		$return = Array();
		foreach($results as $result) {
			$temp = Array();
			foreach($result as $key=>$val) {
				$temp[$key] = $val;
			}
			$temp["children"] = Array();
			if($temp["type"] == 6){
				$subs = $this->db->Execute( "select * from layer_collections where parent_id=? order by z",array($temp["id"]));
				foreach ($subs as $sub){
					$temp["children"][] = $this->GetSub($sub["layer_id"]);
				}
			}
			$return[] = $temp;
		}
		return $return;
	}
	protected function GetSub($id){
		$query = "SELECT 
					f.*,
					geometry_columns.type AS geom,
					people.realname AS owner_name,
					CASE
					WHEN (SELECT DISTINCT
						layer_bookmarks.id
						FROM layer_bookmarks
						WHERE layer_bookmarks.owner=?
						AND layer_bookmarks.layer=f.id) IS NULL THEN 'false'
					ELSE 'true'
					END AS bookmarked
				FROM layers AS f
				JOIN people ON f.owner = people.id
				LEFT OUTER JOIN geometry_columns ON geometry_columns.f_table_name = TEXTCAT('vectordata_', CAST(f.id AS text))
				WHERE f.id=? ORDER BY UPPER(f.name), UPPER(people.realname)";
				$return = $this->GetResults($query, array($this->userid,(int)$id));
				
				foreach($return as $result) {
					$toSend = Array();
					foreach($result as $key=>$val) {
						$toSend[$key] = $val;
					}
				}
				return $toSend;
	}
	
	public function ListViews() {
		$views = array();
		$context = Context::Get();
		$isV4 = false;
		if(is_a($context,'auth\WAPIContext')) {
		   /* @var auth\WAPIContext $context */
		    $isV4 = ($context->version == WAPIContext::VERSION_CURRENT);  
		}
		$name = self::MINE;
		$views[$name]['label'] = 'My Layers';
		$wapiTarget = $isV4 ? 'layers/views/action:get' : 'wapi/layer/views';
		$views[$name]['wapi'] = "wapi/$wapiTarget/type:mine/userId:".$this->userid;
		$views[$name]['name'] = $name;
		
		$name = self::BOOKMARKED;
		$views[$name]['label'] = 'Bookmarked';
		$wapiTarget = $isV4? 'layers/views/action:get' : 'layer/views';
		$views[$name]['wapi'] = "wapi/$wapiTarget/type:bookmarked/userId:".$this->userid;
		$views[$name]['name'] = $name;
		
		$name = self::SHARED;
		$views[$name]['label'] = 'Available to me';
		$wapiTarget = $isV4 ? 'layers/views/action:get' : 'layer/views';
		$views[$name]['wapi'] = "wapi/$wapiTarget/type:shared/userId:".$this->userid."/owner:[userid]";
		$views[$name]['options'] = ParamUtil::ResultsToArray($this->GetOwners());
		array_unshift($views[$name]['options'],array('name'=>'Anybody','userid'=>''));
		$views[$name]['name'] = $name;
		
		$name = self::GROUPS;
		$views[$name]['label'] = "Group's layers";
		$wapiTarget = $isV4 ? 'layers/views/action:get' : 'layer/views';
		$views[$name]['wapi'] = "wapi/$wapiTarget/type:group/userId:".$this->userid.'/groupId:[id]';
		$views[$name]['options'] =  ParamUtil::ResultsToArray($this->GetGroups());
		array_unshift($views[$name]['options'],array('name'=>'Select a group','groupId'=>''));
		$views[$name]['name'] = $name;
		
		$name = self::TAGS;
		$views[$name]['label'] = "tags";
		$wapiTarget = $isV4 ? 'layers/views/action:get' : 'layer/views';
		$views[$name]['wapi'] = "wapi/$wapiTarget/type:tags/userId:".$this->userid.'/tag:[tag]';
		$views[$name]['name'] = $name;
		
		
		return $views;
	}
	
	public function GetMine($filter=false,$useAnd=true) {
	    $shareLevel = "'".\AccessLevels::EDIT."' as sharelevel";
		$query = "SELECT 
					f.id,
                    f.name,
                    f.type,
					f.description,
                    f.geom_type as geom,
                    f.sharelevel,
                    people.realname AS owner_name,
                    f.owner,
                    f.last_modified,
					CASE
					WHEN (SELECT DISTINCT
						layer_bookmarks.id
						FROM layer_bookmarks
						WHERE layer_bookmarks.owner=?
						AND layer_bookmarks.layer=f.id) IS NULL THEN 'false'
					ELSE 'true'
					END AS bookmarked
				FROM layers AS f
				JOIN people ON f.owner = people.id
				WHERE f.owner=?";
		if($filter !== false) $this->AddFilters($query,$filter,$useAnd);
		$query .= " ORDER BY UPPER(f.name), UPPER(people.realname),f.id";
		
		$return = $this->GetResults($query, array($this->userid,$this->userid));
		if($this->subMode == self::SUBMODE_NOSUBS )return $return;
		return $this->addSubLayers($return);
	}
	
	public function GetBookMarked($filter=false,$useAnd=true) {
		$query = "SELECT
					f.id,
                    f.name,
                    f.type,
					f.description,
                    f.geom_type as geom,
                    f.sharelevel,
                    f.owner,
		            f.last_modified,
                    f.created,
                    f.geom_type AS geom,
					people.realname AS owner_name,
					people.id AS owner,
					f.name AS layername,
					f.id AS layerid,
					layersharing.permission,
					'true' AS bookmarked,
		              f.owner,
				FROM layers AS f
				JOIN layer_bookmarks ON f.id=layer_bookmarks.layer
				LEFT OUTER JOIN layersharing ON f.id  = layersharing.layer AND (layersharing.who=? OR layersharing.who IS NULL)
				JOIN people ON f.owner = people.id
				WHERE 
					layer_bookmarks.owner=?
					AND ((layersharing.permission > 0) OR f.sharelevel > 0
					OR f.owner=?)";
		
		if($filter !== false) $this->AddFilters($query,$filter,$useAnd);
		$query .= " ORDER BY UPPER(f.name), UPPER(people.realname),f.id";
		
		$return = $this->GetResults($query, array($this->userid,$this->userid,$this->userid));
		if($this->subMode == self::SUBMODE_NOSUBS )return $return;
		return $this->addSubLayers($return);
	}

	public function GetOwners($minPermission=1,$filter=false,$useAnd=true) {
		
		$minPermission-=1;
		
		$query = "SELECT people.username AS username, people.id AS userid, people.realname AS name, people.id AS id FROM people 
		          join (SELECT distinct(layers.owner)as owner FROM layersharing
                        join layers on layersharing.layer=layers.id
                        WHERE (layersharing.permission > 0 and who=?) or layers.sharelevel >0) as qWho on people.id=qWho.owner";
		
		/*$query = "SELECT
					people.realname AS username,
					people.id AS userid,
					people.realname AS name,
					people.id AS id
				FROM people join layersharing on people.id=owner.id
				WHERE layersharing.permission > $minPermission";
		*/	
		
		if($filter !== false)$this->AddFilters($query,$filter,$useAnd);
		$query .= " ORDER BY UPPER(people.realname)";

		return $this->GetResults($query,array($this->userid));
	}

	
	public function GetByOwner($ownerId,$minPermission=1,$filter=false,$useAnd=true) {
		
		$minPermission-=1;
		$query = "SELECT
					f.id,
                    f.name,
                    f.type,
					f.description,
                    f.geom_type as geom,
                    f.sharelevel,
                    f.owner,
		            f.last_modified,
                    people.realname AS owner_name,
					layersharing.permission,
					CASE
					WHEN (SELECT DISTINCT
						layer_bookmarks.id
						FROM layer_bookmarks
						WHERE layer_bookmarks.owner=?
						AND layer_bookmarks.layer=f.id) IS NULL THEN 'false'
					ELSE 'true'
					END AS bookmarked
				FROM layers AS f
				LEFT OUTER JOIN layersharing ON f.id  = layersharing.layer AND (layersharing.who=? OR layersharing.who IS NULL)
				JOIN people ON f.owner = people.id
				WHERE f.owner!=? AND ".(($ownerId < 0)? "" : " f.owner=? AND")."
					(layersharing.permission > $minPermission
					OR f.sharelevel > $minPermission)";
		if($filter !== false) $this->AddFilters($query,$filter,$useAnd);
		$query .= " ORDER BY UPPER(f.name), UPPER(people.realname),f.id";
	 	$return = $this->GetResults($query,(($ownerId < 0) ? array($this->userid,$this->userid,$this->userid) : array($this->userid,$this->userid,$this->userid,$ownerId)));
		if($this->subMode == self::SUBMODE_NOSUBS )return $return;
		return $this->addSubLayers($return);
	}
	
	public function GetByGroup($id,$filter=false,$useAnd=true) {
		$query = "SELECT
					f.id,
                    f.name,
                    f.type,
					f.description,
                    f.geom_type as geom,
                    f.sharelevel,
                    f.owner,
		            f.last_modified,
                    people.realname AS owner_name,
					layersharing_socialgroups.permission,
					CASE
					WHEN (SELECT DISTINCT
						layer_bookmarks.id
						FROM layer_bookmarks
						WHERE layer_bookmarks.owner=?
						AND layer_bookmarks.layer=f.id) IS NULL THEN 'false'
					ELSE 'true'
					END AS bookmarked
				FROM layersharing_socialgroups 
				LEFT OUTER JOIN layers AS f ON layersharing_socialgroups.layer_id = f.id
				JOIN people ON f.owner=people.id
				WHERE layersharing_socialgroups.permission > 0 AND layersharing_socialgroups.group_id=?
				";
		if($filter !== false) $this->AddFilters($query,$filter,$useAnd);
		$query .= " ORDER BY UPPER(f.name), UPPER(people.realname),f.id";
		
		$return = $this->GetResults($query,array($this->userid,$id));
		if($this->subMode == self::SUBMODE_NOSUBS )return $return;
		return $this->addSubLayers($return);
	}
	public function GetGroups($filter=false,$useAnd=true) {
		$query = "SELECT
				groups.id,
				title AS name
				FROM groups
				WHERE id = ANY(SELECT group_id FROM groups_members WHERE person_id = ? AND (actor = 1 OR actor = 5)) AND
				id = ANY(SELECT group_id FROM layersharing_socialgroups WHERE permission != 0)";
		if($filter !== false) $this->AddFilters($query,$filter,$useAnd);
		$query .= " ORDER BY UPPER(title)";
		
		$return = $this->GetResults($query,array($this->userid));
	
		return $return;
	}
	public function GetByTag($id,$filter=false,$useAnd=true) {
		$addToQuery = "";
		$query = "SELECT
					f.id,
                    f.name,
                    f.type,
					f.description,
                    f.geom_type as geom,
                    f.sharelevel,
                    f.owner,
		            f.last_modified,
                   people.realname AS owner_name,
					layersharing.permission,
					CASE
					WHEN (SELECT DISTINCT
						layer_bookmarks.id
						FROM layer_bookmarks
						WHERE layer_bookmarks.owner=?
						AND layer_bookmarks.layer=f.id) IS NULL THEN 'false'
					ELSE 'true'
					END AS bookmarked
				FROM layers AS f
				LEFT OUTER JOIN layersharing ON f.id = layersharing.layer AND (layersharing.who=? OR layersharing.who IS NULL)
				JOIN people ON f.owner = people.id
				WHERE LOWER(?) = ANY(string_to_array(LOWER(f.tags), ',')) AND (f.sharelevel > 0 OR
					(layersharing.permission > 0) OR f.owner = ?)";
		if($filter !== false)$this->AddFilters($query,$filter,$useAnd);
		return $this->GetResults($query,array($this->userid,$this->userid,$id,$this->userid));
	
	}
}
?>