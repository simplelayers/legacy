<?php

use utils\ParamUtil;

/**
 * See the ProjectLayer class documentation.
 * 
 * @package ClassHierarchy
 */
/**
 *
 * @ignore
 *
 */
require_once 'ColorScheme.class.php';

/**
 * The class representing a ProjectLayer; that is, a layer in the context of a project.
 *
 * - id -- The unique ID# for this ProjectLayer. Read-only.
 * - project -- A Project object, the Project that contains this ProjectLayer. Read-only.
 * - layer -- A Layer object, the original Layer outside the project context. Read-only.
 * - colorscheme -- The ColorScheme object that will be used for this ProjectLayer. Read-only.
 * - colorschemetype -- A string indicating what type of color scheme is in use. One of the COLORSCHEME_* constants.
 * - whoadded -- A Person object, being the Person who added this layer to the project. This attribute
 * can be set; when doing so, use the Person's unique ID#.
 * - z -- The z-index for this layer in the project. Lower Zs are towards the bottom, "further away from the user"
 * - opacity -- The opacity at which this layer will be displayed; a float from 0 to 1.
 * - on_by_default -- Boolean, indicating whether this layer should be on when the project is first loaded in the viewer.
 * - labelitem -- The name of the field/attribute/column which will be used as the label for this layer. Vector, Relational, ODBC layers only.
 * - labels_on -- Boolean, indicating whether labels should be requested by default when the project is first loaded in the viewer.
 * Only effective for Vector, Relational, ODBC layers.
 * - tooltip -- String, the name of the tooltip item used by the viewer.
 * - labels_on -- Boolean, whether labels should be displayed by default when the ProjectLayer is drawn.
 * Only effective for Vector, Relational, ODBC layers.
 * - tooltip_on -- Boolean, whether tooltips should be enabled by default when the Projectlayer is used in the Viewer.
 * Only effective for Vector, Relational, ODBC layers.
 * - searchable -- Boolean, whether this ProjectLayer should be considered selectable for searching purposes in the Viewer.
 * Only effective for Vector, Relational, ODBC layers.
 *
 * @package ClassHierarchy
 */
class ProjectLayer {

    /**
     *
     * @ignore
     *
     */
    private $world; // a link to the World we live in

    /**
     * @var Project
     * 
     */
    public $project; // a link to the project that contains this layer

    /**
     *
     * @ignore
     *
     */
    public $id; // the layer's unique ID#

    /**
     *
     * @ignore
     *
     */
    public $colorscheme; // the ColorScheme object for this layer

    /**
     *
     * @ignore
     *
     */
    public $filter_gids;
    public $filter_color = "FFFF00";
    public $filter_field = "gid";

    function __construct(&$world, &$project, $id) {
        $this->world = $world;
        $this->project = $project;
        $this->id = $id;
        $this->colorscheme = new ColorScheme($this->world, $this);
        
        // verify that we exist. If not, commit noisy suicide
        if (!$this->layer)
            throw new Exception("No such projectlayerid: $id.");
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

        // if they ask for the URL attribute but it's a vector layer, return the table where their data is stored
        if ($name == 'url' and $this->layer->type == LayerTypes::VECTOR)
            return "vectordata_{$this->layer->id}";
        if ($name == "layerid")
            $name = "layer as layerid";

        $realName = $name;
        if ($name == 'label_style_string') {
            $name = 'label_style';
        }

        // if we got here, it must be a direct attribute
        // a bit fancy: whoadded and layer return Layer and Person objects, not just the ID#
        $value = $this->world->db->GetOne("SELECT $name AS value FROM project_layers WHERE id=?", array(
            $this->id
        ));

        if ($name === 'parentLayer') {
            return ProjectLayer::Get(intval($this->parent));
        }

        if ($realName == 'label_style_string') {
            if (is_null($value))
                return $this->layer->label_style_string;
            return $value;
        }

        if ($name == 'labelitem') {
            if (in_array($value, array(false, null, 'null', ''))) {
                return $this->layer->labelitem;
            }
        } elseif ($name == 'label_style') {
            if (in_array($value, array(false, null, 'null', ''))) {
                return $this->layer->label_style;
            }
            return json_decode($value, true);
        } elseif ($name == 'tooltip') {
            if (in_array($value, array(false, null, 'null', ''))) {
                return $this->layer->tooltip;
            } else {
                return $value;
            }
        } elseif ($name === 'rich_tooltip') {
            if (in_array($value, array(false, null, 'null', ''))) {
                $value = $this->layer->rich_tooltip;
                if (!$value) {
                    $value = $this->tooltip;
                    if (in_array($value, array(false, null, 'null', ''))) {
                        $value = $this->layer->tooltip;
                    }
                }
            } else
            if (substr($value, 0, 4) === 'b64:') {
                $value = base64_decode(substr($value, 4));
            }
            return $value;
        }

        if ($name == 'on_by_default')
            return $value == 't' ? 1 : 0;
        elseif ($name == 'layer')
            return $this->world->getLayerById($value);
        elseif ($name == 'whoadded') {
            if (is_null($value))
                return System::GetSystemOwner();
            return $this->world->getPersonById($value);
        } elseif ($name == 'labels_on')
            return $value == 't' ? 1 : 0;
        elseif ($name == 'tooltip_on')
            return $value == 't' ? 1 : 0;
        elseif ($name == 'searchable')
            return $value == 't' ? 1 : 0;
        if (!$value)
            return $this->layer->$name;

        return $value;
    }

    /**
     *
     * @ignore
     *
     */
    function __set($name, $value) {
        // $this->world->db->debug = true;

        // simple sanity check
        if (preg_match('/\W/', $name))
            return false;
        // a few items cannot be set
        if ($name == 'id')
            return false;
        if ($name == 'project')
            return false;
        if ($name == 'layer')
            return false;
        if ($name == 'colorscheme')
            return false;

        // if we got this far, we're making a change. so flag the parent Project as having been flaggd
        $this->touch();
        // sanitize...
        $nullable = false;
        if ($name == 'on_by_default') {
            $value = (int) $value ? 't' : 'f';
        } elseif ($name == 'labels_on') {
            $value = (int) $value ? 't' : 'f';
        } elseif ($name == 'tooltip_on') {
            $value = (int) $value ? 't' : 'f';
        } elseif ($name == 'searchable') {
            $value = (int) $value ? 't' : 'f';
        } elseif ($name == 'label_style') {
           
            if (!is_null($value)) {
                if (is_string($value)) {
                    if ((stripos($value, '{"json":') === 0) || (stripos($value, "{'json':") == 0)) {
                        // $value = json_decode($value,true);
                    }
                }
                if (!is_string($value)) {
                    $value = is_null($value) ? null : json_encode($value);
                } else {
                    if (!json_decode($value)) {
                        $value = null;
                    }
                    $nullable = true;
                }
            } else {
                $nullable = true;
            }
        } elseif (in_array($name, array('tooltip', 'rich_tooltip', 'labelitem', 'label_style'))) {
            $nullable = true;
        }
        // if we got here, we must be setting a direct attribute
        if ($value === null) {
            if (!$nullable)
                return false;
        }
        $this->world->db->Execute("UPDATE project_layers SET $name=? WHERE id=?", array(
            $value,
            $this->id
        ));
    }

    function SaveZ($z, $recursive = true) {
        $this->z = $z;
        $z -= 1;
        if ($recursive === true) {
            if ($this->layer->type == LayerTypes::COLLECTION) {
                return $this->UpdateSubLayerZs($z);
            }
        }
        return $z;
    }

    function GetSubs() {
        $subsRes = $this->world->db->Execute('select id from project_layers where parent=? order by abs(z)', $this->id);

        $subs = array();
        foreach ($subsRes as $subId) {
            $subs[] = $this->project->getLayerById($subId['id']);
        }
        return $subs;
    }

    function GetSubIds() {
        $subIds = System::GetDB()->Execute('select id from project_layers where parent=? order by abs(z)', $this->id);
        if ($subIds === false) {
            throw new Exception('db problem:' . System::GetDB()->ErrorMsg());
        }
        $subIds = ParamUtil::GetSubValues($subIds, 'id');
        return $subIds;
    }

    function HasSubs() {
        $subCount = System::GetDB()->GetOne('select count(id) from project_layers where parent=? order by abs(z)', $this->id);
        if ($subCount === false) {
            throw new Exception('db problem:' . System::GetDB()->ErrorMsg());
        }
        return $subCount;
    }

    /**
     * For a consistent programming interface, the ColoScheme calls $parent->touch() and that will sometimes refer to a ProjectLayer
     * instead of a plain Layer.
     * Fair enough; just pass it on to our containing Project.
     */
    function touch() {
        $this->project->touch();
    }

    function getExtent() {
        return $this->layer->getExtent();
    }

    /**
     * Remove the ProjectLayer from its project.
     */
    function delete() {
        $this->world->db->Execute('DELETE FROM project_layers WHERE id=?', array(
            $this->id
        ));
        $this->world->db->Execute('DELETE FROM project_layers WHERE parent=?', array(
            $this->id
        ));
        $this->project->UpdateProjLayerZs();
        $this->project->touch();
    }

    function copy($project, $parent = null) {
        $newid = $this->world->db->Execute("INSERT INTO project_layers (project,layer,z,whoadded,opacity,on_by_default,labels_on,labelitem,label_style,tooltip,searchable,tooltip_on,colorschemetype,colorschemestroke,colorschemefill,colorschemesymbol,colorschemesymbolsize,colorschemecolumn,parent
															) SELECT ?,layer,z,whoadded,opacity,on_by_default,labels_on,labelitem,label_style,tooltip,searchable,tooltip_on,colorschemetype,colorschemestroke,colorschemefill,colorschemesymbol,colorschemesymbolsize,colorschemecolumn,? FROM project_layers WHERE id=? RETURNING id", array(
            $project->id,
            $parent,
            $this->id
        ));
        $newid = $newid->fields['id'];
        if ($this->layer->type == LayerTypes::COLLECTION) {
            $children = $this->world->db->Execute("SELECT id FROM project_layers WHERE parent=?", array(
                        $this->id
                    ))->getRows();
            $children = array_map(create_function('$a', 'return $a["id"];'), $children);
            foreach ($children as $childId) {
                $child = new ProjectLayer($this->world, $project, $childId);
                $child->copy($project, $newid);
            }
        }
        $newProjectLayer = new ProjectLayer($this->world, $project, $newid);
        $this->colorscheme->copy($newProjectLayer);
        return $newProjectLayer;
    }

    public function UpdateSubLayerZs($startZ) {
        $z = $startZ;
        $subLayers = $this->GetSubs();
        foreach ($subLayers as $subLayer) {
            $subLayer->SaveZ($z);
            $z -= 1;
        }
        return $z;
    }

    function ResetSubs($who = null) {
        $layerSubs = $this->layer->GetSubsAsIdPairs();
        $projLayerSubs = $this->GetSubs();
        foreach ($projLayerSubs as $subProjLayer) {
            $subProjLayer->delete();
        }
        $who = is_null($who) ? SimpleSession::Get()->GetUser()->id : $who;
        foreach ($layerSubs as $layerSub) {
            $z = null;

            $projLayer = $this->project->AddLayerById($layerSub['layerid'], $this->id, $who, $z);
            $projLayer->layer_coll_entry_id = $layerSub['id'];
        }
        //$this->project->UpdateProjLayerZs();
        return $this->GetSubs();
    }

    function UpdateSubs($resetOrder = false, $who = null) {
        $layerSubs = $this->layer->GetSubsAsIdPairs();
        $layerSubIds = ParamUtil::GetColumn($layerSubs, 'id');
        $layerSubLayers = ParamUtil::GetColumn($layerSubs, 'layerId');
        $projLayerSubs = $this->GetSubs();
        $matchedValues = array();
        $remainingProjSubs = array();

        foreach ($projLayerSubs as $projLayerSub) {

            if (in_array($projLayerSub->layer_coll_entry_id, $layerSubIds)) {
                $matchedValues[] = $projLayerSub->layer_coll_entry_id;
                $remainingProjSubs[] = $projLayerSub;
            } else {

                $projLayerSub->delete();
            }
        }

        $layerSubCollIds = ParamUtil::GetColumn($layerSubs, 'id');
        $added = array();
        foreach ($layerSubs as $layerSubIds) {
            if (in_array($layerSubIds['id'], $matchedValues)) {
                continue;
            }
            $z = null;
            $who = is_null($who) ? SimpleSession::Get()->GetUser()->id : $who;
            $projLayer = $this->project->AddLayerById($layerSubIds['layerid'], $this->id, $who, $z);
            $projLayer->SaveZ(-count($added));
            $projLayer->layer_coll_entry_id = $layerSubIds['id'];
            $added[] = $projLayer->id;
            $remainingProjSubs[] = $projLayer;
        }


        if ($resetOrder) {
            foreach ($remainingProjSubs as $subProj) {

                $collEntryId = $subProj->layer_coll_entry_id;
                $index = array_search($collEntryId, $layerSubCollIds);
                $subProj->z = -$index;
            }
        }
    }

    function CopyColorScheme(Layer $layer = null) {
        if (is_null($layer)) {
            $layer = $this->layer;
        }
        if ($layer->type != LayerTypes::VECTOR and $layer->type != LayerTypes::ODBC and $layer->type != LayerTypes::RELATIONAL)
            return;
        $this->colorscheme->clearScheme();
       
        $colorschemeEntries = $layer->colorscheme->getAllEntries(false);

        foreach ($colorschemeEntries as $oldColorSchemeEntry) {
            $newColorSchemeEntry = $this->colorscheme->addEntry();
            $newColorSchemeEntry->MergeEntry($oldColorSchemeEntry);
        }
    }

    public static function Get($playerId) {
        $db = System::GetDB();

        $projectId = $db->GetOne('select project from project_layers where id=' . $playerId);

        $project = Project::Get($projectId);
        $sys = System::Get();
        return new ProjectLayer($sys, $project, $playerId);
    }

}

?>
