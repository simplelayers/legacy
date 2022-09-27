<?php

use auth\Context;
use auth\Creds;
use auth\Auth;
use utils\ParamUtil;

/**
 * See the Project class documentation.
 *
 * @package ClassHierarchy
 */
/**
 *
 * @ignore
 *
 */
require_once 'ProjectLayer.class.php';

/**
 * The class representing a Project.
 *
 * Public attributes:
 * - owner -- A Person object, the Person who owns this Project.
 * - name -- String, the name of the project. Read-only.
 * - category -- The ID# of the category that this project is in. Defaults to 0 (no category).
 * - categoryname -- The string name of the category that this layer is in. Read-only.
 * - description -- Free-form text description of the project.
 * - tags -- Free form text, additional search tags.
 * - last_modified -- The date+time when the Layer (or its constituents) was last modified. Read-only; use the touch() method to update it.
 * - last_modified_seconds -- The last_modified time, expressed as seconds in the past from right now.
 * - last_modified_unix -- The last_modified time, expressed as a Unix timestamp, e.g. seconds since the epoch.
 * - private -- Boolean; true if the project has private status; false if the project does not (e.g. is public status)
 * - allowlpa -- Boolean; true if the project should allow Limited Public Access (LPA) to non-logged-in people/browsers
 * - bbox -- Used by the viewer; the initial spatial extent (zoom region) when the project loads. A comma-joined string of the format "123.45,67.8,90.123,45.6"
 * - windowsize -- Used by the viewer; the size of the display window. A comma-joined width and height, e.g. "720,720"
 *
 * @package ClassHierarchy
 */
class Project {
    /* @var string $bbox Initial spatial extents */

    /**
     *
     * @ignore
     *
     */
    private $world; // a link to the World we live in

    /**
     *
     * @ignore
     *
     */
    public $id; // the project's unique ID#

    /**
     *
     * @ignore
     *
     */
    function __construct(&$world, $id) {
        $this->world = $world;
        $this->id = $id;

        // verify that we exist. If not, commit noisy suicide
        if (!$this->owner) {
            throw new Exception("No such projectid: $id.");
        }
    }

    // /// make attributes directly fetchable and editable

    /**
     *
     * @ignore
     *
     */
    function __get($name) {
        // simple sanity check
        if (preg_match('/\W/', $name))
            return false;
        $as = 'value';
        // make the artificial "categoryname" attribute, which returns the string name of the layer's category
        // convert last_modified to seconds
        switch ($name) {
            case 'last_modified_seconds':
                $c = $this->world->db->Execute("SELECT DATE_PART('epoch',now()-last_modified) AS seconds FROM projects WHERE id=?", array(
                    $this->id
                ));
                return $c->fields['seconds'];
            case 'last_modified_unix':
                $value = $this->world->db->Execute("SELECT DATE_PART('epoch',last_modified) AS unixtime FROM projects WHERE id=?", array(
                    $this->id
                ));
                return $value->fields['unixtime'];
            case 'projectionSRID':
                $projection = $this->projection;
                return ($projection == null) ? $world->projections->defaultSRID : $projection;
            case 'maxZ':
                $value = System::GetDB()->GetOne('SELECT max(abs(z)) from project_layers where project=?', array(
                    $this->id
                ));
                return $value;
            case 'nextZ':
                $value = - ($this->maxZ + 1);
                return $value;
            case "ownerid":
                $name = "owner";
                $as = 'ownerid';
                break;
        }
        // if we got here, it must be a direct attribute. do apprpriate type conversion and return it

        $selection = (stripos($name, ' as ') > - 1) ? $name : $name . ' AS value ';
        $value = $this->world->db->Execute("SELECT $selection FROM projects WHERE id=?", array(
            $this->id
        ));
        if (!$value)
            return null;

        $value = $value->fields['value'];

        if ($name == 'owner') {
            if ($as == 'ownerid')
                return $value;
            $value = System::Get()->getPersonById($value);
        } elseif (in_array($name, array(
                    'private',
                    'allowlpa'
                )))
            $value = $value == 't' ? true : false;
        return $value;
    }

    /**
     *
     * @ignore
     *
     */
    function __set($name, $value) {
        // simple sanity check
        if (preg_match('/\W/', $name))
            return false;
        // a few items cannot be set
        if ($name == 'id')
            return false;
        if ($name == 'owner') {
            return false;
        }
        if ($name == 'last_modified')
            return false;

        // if we got this far, we're making a change; so flag us as having been modified
        $this->touch();

        // sanitize text fields, boolean fields, and some specifically-formatted fields
        if (in_array($name, array(
                    'private',
                    'allowlpa'
                )))
            $value = $value ? 't' : 'f';
        else
        if ($name == 'windowsize' and!preg_match('/^\d+,\d+$/', $value))
            return false;
        else
        if ($name == 'bbox' and!preg_match('/^[\d\.\-]+,[\d\.\-]+,[\d\.\-]+,[\d\.\-]+$/', $value))
            return false;
        // if we got here, we must be setting a direct attribute
        $this->world->db->Execute("UPDATE projects SET $name=? WHERE id=?", array(
            $value,
            $this->id
        ));
    }

    /**
     * Update the Project's last_modified to be the current date+time.
     */
    function touch() {
        $this->world->db->Execute('UPDATE projects SET last_modified=NOW() WHERE id=?', array(
            $this->id
        ));
    }

    /**
     * Have the Project delete itself.
     */
    function delete() {
        // unlike layers, projects don't have an external storage, so cleanup is simple...
        // delete the layer's entry in the DB
        $this->world->db->Execute('DELETE FROM projects WHERE id=?', array(
            $this->id
        ));
    }

    function getPermissionByUsername($username) {
        $id = $this->world->getUserIdFromUsername($username);
        return $this->getPermissionById($id);
    }

    function setPermissionByUsername($username, $level) {
        $id = $this->world->getUserIdFromUsername($username);
        return $this->setPermissionById($id, $level);
    }

    function setContactPermissionById($id, $level) {
        if ($id === $this->owner->id)
            return;
        if ($id == 0)
            return;
        $result = $this->world->db->Execute('SELECT id FROM projectsharing WHERE project=? AND who=?', array(
                    $this->id,
                    $id
                ))->FetchRow();
        if ($result["id"]) {
            if ($level > 0) {
                $this->world->db->Execute('UPDATE projectsharing SET permission=? WHERE project=? AND who=?', array(
                    $level,
                    $this->id,
                    $id
                ));
            } else {
                $this->world->db->Execute('DELETE FROM projectsharing WHERE project=? AND who=?', array(
                    $this->id,
                    $id
                ));
            }
        } else {
            $this->world->db->Execute('INSERT INTO projectsharing (project,who,permission) VALUES (?,?,?)', array(
                $this->id,
                $id,
                $level
            ));
            $this->world->getPersonById($id)->notify($this->owner->id, "shared project:", $this->name, $this->id, "./?do=project.info&id=" . $this->id, 2);
        }
    }

    function setGroupPermissionById($id, $level) {
        $result = $this->world->db->Execute('SELECT id FROM projectsharing_socialgroups WHERE project_id=? AND group_id=?', array(
                    $this->id,
                    $id
                ))->FetchRow();
        if ($result["id"]) {
            if ($level > 0) {
                $this->world->db->Execute('UPDATE projectsharing_socialgroups SET permission=? WHERE project_id=? AND group_id=?', array(
                    $level,
                    $this->id,
                    $id
                ));
            } else {
                $this->world->db->Execute('DELETE FROM projectsharing_socialgroups WHERE project_id=? AND group_id=?', array(
                    $this->id,
                    $id
                ));
            }
        } else {
            $this->world->db->Execute('INSERT INTO projectsharing_socialgroups (project_id,group_id,permission) VALUES (?,?,?)', array(
                $this->id,
                $id,
                $level
            ));
        }
        $results = $this->world->db->Execute('SELECT person_id FROM groups_members WHERE group_id=?', array(
                    $id
                ))->GetRows();
        foreach ($results as $result) {
            $this->world->getPersonById($result["person_id"])->notify($this->owner->id, "shared project:", $this->name, $this->id, "./?do=project.info&id=" . $this->id, 2);
        }
    }

    function getPermissionById($id) {
        $permission = AccessLevels::NONE;
        if ($this->allowlpa)
            $permission = AccessLevels::READ;
        if (!$this->private)
            $permission = AccessLevels::READ;

        if ($id === $this->owner->id) {
            $permission = AccessLevels::EDIT;
        }
        if ($id === System::GetSystemOwner(true)) {
            $context = Context::Get(Creds::GetFromRequest());
            $permission = ($context->authState == Auth::STATE_OK) ? AccessLevels::EDIT : AccessLevels::READ;
        }

        if ($id === false or $id === null)
            return $permission;
        $query = "SELECT MAX(permission) FROM
				(SELECT permission FROM projectsharing WHERE project=? AND who=?
				UNION 
				SELECT permission FROM projectsharing_socialgroups WHERE project_id=? AND group_id=ANY(
				SELECT group_id FROM groups_members WHERE person_id=? AND (actor=1 OR actor=5))
				UNION SELECT 0 AS permission
				) AS temp";
        $databaseperm = $this->world->db->GetOne($query, Array(
            $this->id,
            $id,
            $this->id,
            $id
        ));

        if ($databaseperm > $permission)
            $permission = $databaseperm;
        return $permission;
    }

    /**
     * This is a wrapper used by viewer dispatchers, to figure out whether the project is embedded
     * and how the various convoluted permisisons should be applied.
     * It distills all the info
     * into a 2-item array which dispatchers can use to figure their permissions, with decent granularity.
     *
     * @return array A two-item array: ($embedded,$permission) The first is a bool indicating whether the project is being requested in an embedded context. The second is one of the AccessLevels::* constants indicating the viewer's permission.
     */
    function checkBrowserPermission($user, $request, $server) {
        $context = Context::Get(Creds::GetFromRequest());

        $userid = (!is_object($user)) ? $user : $user->id;
        if (isset($request['embedded']))
            $request['embed'] = $request['embedded'];
        // are they using embed mode? this affects the later decisions...
        $embedded = isset($request['embed']);
        // What is their normal base permission? This takes into account LPA and Private modes.

        $permission = $this->getPermissionById($userid);

        // all set!
        return array(
            $embedded,
            $permission
        );
    }

    // ///
    // /// functions pertaining to layer membership
    // ///

    /**
     * Fetch the list of ProjectLayers that are in this project.
     *
     * @return array A list of ProjectLayer objects.
     */
    function getLayers($onlyParentless = true, $orderDir = 'DESC') {
        return array_map(array(
            $this,
            'getLayerById'
                ), $this->getLayerIds($onlyParentless, $orderDir));
    }

    function getSubLayers($layerId) {
        $ids = $this->world->db->Execute('SELECT id FROM project_layers WHERE parent=? ORDER BY z DESC', array(
                    $layerId
                ))->getRows();
        $ids = array_map(function($a) {
            return $a["id"];
        }, $ids);
        $allSubLayers = Array();
        foreach ($ids as $id) {
            $allSubLayers[] = $this->getLayerById($id, $layerId);
        }
        return $allSubLayers;
    }

    function getSearchableLayers() {
        $allowedTypes = implode(',', array(
            LayerTypes::VECTOR,
            LayerTypes::ODBC
        ));

        $result = $this->world->db->Execute("SELECT project_layers.id as id from project_layers join layers on project_layers.layer = layers.id where project=? AND searchable=? AND layers.type IN($allowedTypes) ORDER BY project_layers.z DESC", array(
            $this->id,
            true
        ));
        $ids = array();
        if (!$result) {
            // ar_dump($this->world->db->ErrorMsg());
        } else {
            $ids = $result->GetRows();
        }
        $ids = array_map(create_function('$a', 'return (int)$a["id"];'), $ids);
        $ids = array_unique($ids);

        return array_map(array(
            $this,
            'getLayerById'
                ), $ids);
    }

    /**
     * Like getLayers() except it only returns the unique ID#s of the ProjectLayers.
     *
     * @return array A list of integers, being unique ID#s of ProjectLayer objects.
     */
    function getLayerIds($onlyParentless = true, $orderDir = 'ASC') {
        // ar_dump('SELECT layer FROM project_layers WHERE project=?'.($onlyParentless ? ' and parent IS NULL' : '').' ORDER BY z '.$orderDir);
        $ids = $this->world->db->Execute('SELECT id FROM project_layers WHERE project=?' . ($onlyParentless ? ' and parent IS NULL' : '') . ' ORDER BY z ' . $orderDir . ', parent ' . $orderDir, array(
                    $this->id
                ))->getRows();
        $ids = array_map(function($a) {
            return $a["id"];
        }, $ids);
        return $ids;
    }

    /**
     * Fetch a ProjectLayer from this Project, using the Layer owner's username and the Layer's name.
     *
     * @param string $ownername
     *            The name of the Layer's owner.
     * @param string $layername
     *            The name of the Layer.
     * @return ProjectLayer A ProjectLayer object.
     */
    function getLayerByName($ownername, $layername) {
        $p = $this->world->getPersonByUsername($ownername);
        if (!$p)
            return false;
        $l = $p->getLayerByName($layername);
        if (!$l)
            return false;
        return $this->getLayerById($l->id);
    }

    /**
     * Fetch a ProjectLayer from this Project, using the Layer's unique proect layer ID#.
     *
     * @param integer $id
     *            The unique ID# of the project layer.
     * @return ProjectLayer A ProjectLayer object.
     */
    function getLayerById($id, $parentId = null) {

        // record = $this->world->db->Execute('SELECT id FROM project_layers WHERE id=? AND parent'.($parentId===null ? ' IS NULL' : '='.$parentId), array($id) );
        try {
            return new ProjectLayer($this->world, $this, $id);
        } catch (Exception $e) {
            // ar_dump($e->getMessage().$id);
            return false;
        }
    }

    /**
     * Is the specified Layer in this Project?
     *
     * @param string $ownername
     *            The name of the Layer's owner.
     * @param string $layername
     *            The name of the Layer.
     * @return boolean True/false indicating whether the Layer is in this Project.
     */
    function hasLayerByName($ownername, $layername) {
        $p = $this->world->getPersonByUsername($ownername);
        if (!$p)
            return false;
        $l = $p->getLayerByName($layername);
        if (!$l)
            return false;
        return $this->hasLayerById($l->id);
    }

    /**
     * Is the specified Layer in this Project?
     *
     * @param integer $id
     *            The unique ID# of the Layer.
     * @return boolean True/false indicating whether the Layer is in this Project.
     */
    function hasLayerById($id) {
        $x = $this->world->db->Execute('SELECT id FROM project_layers WHERE project=? AND layer=?', array(
            $this->id,
            $id
        ));
        return (bool) !$x->EOF;
    }

    /**
     * Add a Layer to this Project.
     *
     * @param string $ownername
     *            The name of the Layer's owner.
     * @param string $layername
     *            The name of the Layer.
     * @return ProjectLayer A ProjectLayer object.
     */
    function addLayerByName($ownername, $layername) {
        $p = $this->world->getPersonByUsername($ownername);
        if (!$p)
            return false;
        $l = $p->getLayerByName($layername);
        if (!$l)
            return false;
        return $this->addLayerById($l->id);
    }

    /**
     * Add a Layer to this Project.
     *
     * @param integer $id
     *            The unique ID# of the Layer.
     * @return ProjectLayer A ProjectLayer object
     */
    function addLayerById($id, $parentId = null, $adder = 0, &$z) {
        $layer = Layer::GetLayer($id, true);

        $values = array(
            (int) $this->id,
            (int) $id,
            $this->nextZ,
            (int) $adder
        );
        $column = "";
        $val = "";
        if ($parentId) {
            $column = ",parent";
            $val = ",?";
            $values[] = $parentId;
        }

        $res = $this->world->admindb->Execute("INSERT INTO project_layers (project,layer,z,whoadded $column) VALUES (?,?,?,? $val) returning id", $values);

        $projectLayer = $this->getLayerById($res->fields['id']);
        $projectLayer->toolitp = '';
        $projectLayer->rich_toolitp = '';
        $projectLayer->labelitem = $projectLayer->layer->labelitem;
        $projectLayer->label_style = $projectLayer->layer->label_style;
        if (isset($z)) {
            $this->MoveLayerToZ($id, $z);
        }

        // id=$record['id'];
        $this->touch();

        $layer = $projectLayer->layer;

        if ($layer->type == LayerTypes::COLLECTION) {
            $subs = LayerCollection::GetSubs($this->world, $layer->id);
            $layerCollEntryId = System::GetDB()->GetOne('select id from layer_collections where layer_id=? and parent_id=?',
                    array($sub->id, $layer->id));
            foreach ($subs as $sub) {
                $subProjLayer = $this->addLayerById((int) $sub->id, $projectLayer->id, $adder, $z);
                $subProjLayer->layer_coll_entry_id = $layerCollEntryId;
            }
        }
        $projectLayer->opacity = ($layer->type == LayerTypes::VECTOR) ? 1.0 : 0.5;

        $projectLayer->colorschemetype = $layer->colorschemetype;
        $projectLayer->labelitem = $layer->labelitem;
        $projectLayer->CopyColorScheme($layer);

        return $projectLayer;
    }

    function copy($newProjectName = false, $ownerForNewProject = false) {
        if ($newProjectName === false) {
            $newProjectName = "Copy of " . $this->name;
        }
        if ($ownerForNewProject === false) {
            $ownerForNewProject = $this->owner;
        } else {
            
        }

        $idOfNewProject = $this->world->db->Execute("INSERT INTO projects (owner,category,description,tags,bbox,windowsize,private,allowlpa,projection,name) SELECT ?,category,description,tags,bbox,windowsize,private,allowlpa,projection,? FROM projects WHERE id=? RETURNING id", array(
            $ownerForNewProject->id,
            $newProjectName,
            $this->id
        ));

        if (!$idOfNewProject) {
            $err = trim($this->world->db->ErrorMsg() . '');
            $comp = trim('duplicate key value violates unique constraint');
            if (stripos($err, $comp) !== false) {
                echo "<script>alert('The name $newProjectName is already taken');window.history.back();</script>";
            }
            return;
        }
        $idOfNewProject = $idOfNewProject->fields['id'];
        $newProject = $ownerForNewProject->getProjectById($idOfNewProject);

        $this->world->db->Execute("INSERT INTO projectsharing (project,who,permission) SELECT ?,who,permission FROM projectsharing WHERE project=?", array(
            $idOfNewProject,
            $this->id
        ));
        $this->world->db->Execute("INSERT INTO projectsharing_socialgroups (project_id,group_id,permission) SELECT ?,group_id,permission FROM projectsharing_socialgroups WHERE project_id=?", array(
            $idOfNewProject,
            $this->id
        ));
        $arrayOfProjectLayers = Array();
        foreach ($this->getLayers() as $projectLayer) {
            if ($projectLayer->parent !== null)
                continue;
            $arrayOfProjectLayers[] = $projectLayer;
            $projectLayer->copy($newProject);
        }
        return $newProject;
    }

    function RemoveAllLayers() {
        $this->world->db->Execute('delete from project_layers where id=' . $this->id);
    }

    public function newReply($user, $parent, $post) {
        $new = $this->world->db->Execute("INSERT INTO project_discussions (id, text, owner, project_id, parent) VALUES (DEFAULT, ?, ? ,?, ?) RETURNING id;", array(
                    nl2br(htmlentities($post)),
                    $user->id,
                    $this->id,
                    $parent
                ))->fields["id"];
        $results = $this->world->db->Execute("WITH RECURSIVE parentTree(id, parent, owner) AS(
				SELECT id, parent, owner FROM project_discussions WHERE id = ?
			  UNION ALL
				SELECT
				t.id,
				t.parent,
				t.owner
				FROM project_discussions t
				JOIN parentTree rt ON rt.parent = t.id
			)
			SELECT owner FROM parentTree GROUP BY owner", array(
                    $parent
                ))->getRows();
        foreach ($results as $result) {
            $this->world->getPersonById($result["owner"])->notify($user->id, "commented on project:", $this->name, $this->id, "./?do=project.discussion&id=" . $this->id . "#" . $new, 10);
        }
        $this->owner->notify($user->id, "Commented On Project", $this->name, $this->id, "./?do=project.discussion&id=" . $this->id . "#" . $new, 10);
        return $new;
    }

    public function getReply($id = false) {
        $retrive = Array(
            $this->id
        );
        if ($id)
            $retrive[] = $id;
        $query = "SELECT * FROM project_discussions AS d WHERE project_id = ?" . (($id) ? " AND id=?" : "") . " ORDER BY created";
        $results = $this->world->db->Execute($query, $retrive);
        return $results->getRows();
    }

    private function nestResults($results, $id) {
        $return = Array();
        foreach ($results as $result) {
            if ($result["parent"] == $id) {
                $result["fromnow"] = timeToHowLongAgo(time() - strtotime($result["created"]));
                $return[$result["id"]] = Array(
                    "data" => $result
                );
            }
        }

        foreach ($return as $key => &$result) {
            $result["children"] = $this->nestResults($results, $key);
        }

        return $return;
    }

    public function getNestedReplies() {
        $query = "SELECT * FROM project_discussions AS d WHERE project_id = ? ORDER BY created";
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
                    "project_id" => $this->id,
                    "parent" => 0
                ),
                "children" => $return
            )
        );
        return $return;
    }

    public function deleteReply($id) {
        $this->world->db->Execute("UPDATE project_discussions SET text='Comment Removed' WHERE id = ?", array(
            $id
        ));
    }

    public function UpdateProjLayerZs() {
        $projectParentLayerIds = System::GetDB()->Execute('select id from project_layers where project=? and parent is null order by abs(z)', $this->id);
        $z = -1;
        $parentProj = null;
        foreach ($projectParentLayerIds as $projectParentLayerId) {

            $projectLayer = ProjectLayer::Get($projectParentLayerId['id']);
            if ($z == -1) {
                $parentProj = $projectLayer->parent;
            }
            $z = $projectLayer->SaveZ($z);
        }
        if (!is_null($parentProj)) {
            $parentProj->SaveZ(0);
        }
    }

    public function MoveLayerToZ($projectLayerId, $z) {
        $results = System::GetDb()->Execute('select id from project_layers where project=? order by abs(z)', $this->id);
        $ids = ParamUtil::GetSubValues($results, 'id');
        $fromIndex = array_search($projectLayerId, $ids);
        if ($z === $fromIndex)
            return;
        $newOrder = array_splice($ids, $fromIndex, 1);
        array_splice($ids, abs($z), 0, $newOrder);
        $z = 0;
        foreach ($newOrder as $playerId) {
            $pLayer = ProjectLayer::Get($playerId);
            $z = $pLayer->SaveZ(-$z, false);
        }
        $this->UpdateProjLayerZs();
    }

    public function DropMissingLayers($projectLayerIds) {
        if (!is_array($projectLayerIds)) {
            return false;
        }
        if (count($projectLayerIds) === 0) {
            return 0;
        }
        $projectLayerIds = implode(',', $projectLayerIds);
        $query = <<<QUERY
            delete from project_layers where project=? and id NOT IN($projectLayerIds) 
QUERY;
        $db = System::GetDB();
        $db->Execute($query, $this->id);
        return $db->affected_rows();
        $this->UpdateProjLayerZs();
    }

    public function GetDefaultConfig() {
        $config = [];
        $config['styleSelResults'] = '#ffff00;0.20;0.60';
        $config['styleUnselResults'] = '#ff0000;0.10;0.40';
        $config['resultInput'] = '#0000ff;0.15;0.25';
        return $config;
    }

    public function SetDefaults() {
        $db = \System::GetDB();
        $this->config = json_encode($this->GetDefaultConfig());
        $this->bbox = '-180,-90,180,90';
    }

    public static function Get($id) {
        $world = System::Get();
        return new Project($world, $id);
    }

}

?>
