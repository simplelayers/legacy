<?php

/**
 * Process the layereditvector1 form, to save their changes to the layer information.
 * @package Dispatchers
 */
/**
 */
function _config_relations2()
{
    $config = Array();
    // Start config
    // Stop config
    return $config;
}

function _dispatch_relations2($template, $args,$layer=null)
{
    $user = SimpleSession::Get()->GetUser();
    $db = System::GetDB(System::DB_ACCOUNT_SU);
    
    // load the layer and verify their access
    $layer = is_null($layer) ? Layer::GetLayer($_REQUEST['id']) : $layer;
    if ($layer->owner->id != $user->id) {
        print javascriptalert('Only the owner can edit this Layer.');
        return print redirect('layer.list');
    }
    
    // fetch the component layers, ensure that they're owned by this user
    $layer1 = Layer::GetLayer($_POST['table1']);
    $layer2 = Layer::GetLayer($_POST['table2']);
    if ($layer2->owner->id != $user->id) {
        print javascriptalert('You must own the supplemental layer.');
        $layer->delete();
        return print redirect('layer.list');
    }
    if ($layer1->getPermissionById($user->id) == AccessLevels::NONE) {
        print javascriptalert('You do not have permission to use the spatial layer.');
        return print redirect('layer.list');
    }
    
    // save their submitted table-and-column data as the table's URL field
    // NOTE: this bypasses the $layer->url assignment (the ORM) for direct storage,
    // because this is really a vector layer and needs 'url' to work properly
    $info = array();
    $info['table1'] = preg_replace('/\W/', '', $_POST['table1']);
    $info['column1'] = preg_replace('/\W/', '', $_POST['column1']);
    $info['table2'] = preg_replace('/\W/', '', $_POST['table2']);
    $info['column2'] = preg_replace('/\W/', '', $_POST['column2']);
    $layer->metadta = $info;
    // $db->Execute('UPDATE layers SET url=? WHERE id=?', array(serialize($info),$layer->id) );
    
    // find the list of unique column names from the two tables
    $columns = array();
    $table1 = $layer1->url;
    $table2 = $layer2->url;
    foreach ($layer1->getAttributes() as $name => $type)
        $columns[$name] = sprintf("%s.\"%s\"", $table1, $name);
    foreach ($layer2->getAttributes() as $name => $type)
        $columns[$name] = sprintf("%s.\"%s\"", $table2, $name);
    $columns['gid'] = sprintf("%s.%s", $table1, 'gid');
    //$columns['oid'] = sprintf("%s.%s", $table1, 'oid');
    if(isset($columns['oid'])) unset($columns['oid']);
    $columns['the_geom'] = sprintf("%s.%s", $table1, 'the_geom');
    $columns = array_values($columns);
    $columns = implode(',', $columns);
    
    // find the geometry type of the resulting table (which means the geometry of table1)
    /*geom = $db->Execute("SELECT st_astext(the_geom) AS geom FROM $table1 LIMIT 1")->fields['geom'];
    $geom = preg_match('/^\s*(\w+)/', $geom, $geoms);
    $geom = $geoms[1];*/
    
    // drop and re-create the view
    // the create depends on the type of JOIN being used
    $db->Execute("DROP VIEW if exists {$layer->url}");
    
    switch ($_POST['relationtype']) {
        case 'inner':
            $sql = "CREATE OR REPLACE VIEW {$layer->url} AS SELECT $columns FROM $table1, $table2 WHERE $table1.{$info['column1']} = $table2.{$info['column2']}";
            break;
        case 'left':
            $sql = "CREATE OR REPLACE VIEW {$layer->url} AS SELECT $columns FROM $table1 LEFT JOIN $table2 ON $table1.{$info['column1']} = $table2.{$info['column2']}";
            break;
        case 'right':
            $sql = "CREATE OR REPLACE VIEW {$layer->url} AS SELECT $columns FROM $table1 RIGHT JOIN $table2 ON $table1.{$info['column1']} = $table2.{$info['column2']}";
            break;
    }
    $db->Execute($sql);
   /* $db->Execute("UPDATE geometry_columns SET type=? WHERE f_table_name=?", array(
        $geom,
        $layer->url
    ));*/
    
    // index the source tables on the match-up columns
    /*
     * $layer1->setDBOwnerToDatabase();
     * $db->Execute("CREATE INDEX {$table1}_{$info['column1']} ON $table1 ({$info['column1']})");
     * $layer1->setDBOwnerToOwner();
     * $layer2->setDBOwnerToDatabase();
     * $db->Execute("CREATE INDEX {$table2}_{$info['column2']} ON $table2 ({$info['column2']})");
     * $layer2->setDBOwnerToOwner();
     */
    // cleanup 1: go over the list of this user's relational tables, and construct a list of
    // columns in table1 and table2, which are used in any relational tables. This will be used to
    // remove any relational indexes no longer in use
    /*$indexes_in_use = array();
    $ix = $db->Execute("SELECT id,url FROM layers WHERE type=? AND owner=?", array(
        LayerTypes::RELATIONAL,
        $user->id
    ))->getRows();
    foreach ($ix as $l) {
        $info = unserialize($l['url']);
        $indexes_in_use[] = sprintf("vectordata_%d_%s", $info['table1'], $info['column1']);
        $indexes_in_use[] = sprintf("vectordata_%d_%s", $info['table2'], $info['column2']);
    }
    
    // cleanup 2: make the list of all vector layer-tables owned by this user
    $vectortables = array();
    foreach ($user->listLayers() as $l) {
        if ($l->type == LayerTypes::VECTOR)
            $vectortables[] = $l->url;
    }
    */
    // cleanup 3: go over all indexes in the system, which correspond to layer-tables owned by this user,
    // which have a name indicating that they're not a oid/gid index, which are not in $indexes_in_use
      /*$query = <<<EOF
SELECT  c.relname as "index",
c2.relname as "table"
FROM pg_catalog.pg_class c
    JOIN pg_catalog.pg_index i ON i.indexrelid = c.oid
    JOIN pg_catalog.pg_class c2 ON i.indrelid = c2.oid
    LEFT JOIN pg_catalog.pg_user u ON u.usesysid = c.relowner
    LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
WHERE c.relkind IN ('i','')
     AND n.nspname NOT IN ('pg_catalog', 'pg_toast')
     AND pg_catalog.pg_table_is_visible(c.oid)
ORDER BY 1,2
EOF;
    $ix = $db->Execute($query);
  while (! $ix->EOF) {
        if (! in_array($ix->fields['table'], $vectortables)) {
            $ix->MoveNext();
            continue;
        }
        if (in_array($ix->fields['index'], $indexes_in_use)) {
            $ix->MoveNext();
            continue;
        }
        if (preg_match('/_index_oid$/', $ix->fields['index'])) {
            $ix->MoveNext();
            continue;
        }
        if (preg_match('/_index_the_geom$/', $ix->fields['index'])) {
            $ix->MoveNext();
            continue;
        }
        if (preg_match('/_pkey$/', $ix->fields['index'])) {
            $ix->MoveNext();
            continue;
        }
        
        // print "Deleting index: {$ix->fields['index']} <br>\n";
        $db->Execute("DROP INDEX {$ix->fields['index']}");
        $ix->MoveNext();
    }*/
    
    // done -- send them to either their layer list or their layerbookmark list, depending
    // on whether they own the layer they just edited
    $layer->setLayerGeomType();
    $layer->touch();
   print redirect("layer.editrelational1&id={$layer->id}");
}
?>
