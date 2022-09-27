<?php
use utils\ParamUtil;
/**
  * See the ColorScheme class documentation.
  * @package ClassHierarchy
  */

/**
 *
 * @ignore
 *
 */
System::RequireColorschemes();

/**
 * The classes representing a layer's color scheme.
 * The same class is used for both the default color scheme
 * of standalone layers (Layer objects), and for layers in the context of a project (ProjectLayer object).
 * A ColorScheme's role is to store ColorSchemeEntry objects, and the class itself is pretty bare.
 * You'll undoubtedly want to refer to the ColorSchemeEntry documentation.
 *
 * A ColorScheme object is automagically present in vector layers, e.g. $layer->colorscheme
 * Other layer types do not have color schemes and will therefore not have this feature.
 *
 * Public Attributes:
 * - parent -- A link to the color scheme's parent; that is, the Layer or ProjectLayer using this scheme.
 *
 * @package ClassHierarchy
 */
class ColorScheme
{

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
    public $parent; // a link to the Layer or ProjectLayer that spawned us

    /**
     *
     * @ignore
     *
     */
    private $table; // the DB table we're accessing

    /**
     *
     * @ignore
     *
     */
    private $idfield; // which column is the layer identifier for this table? see comments in constructor

    private $isProject = false;

    /**
     *
     * @ignore
     *
     */
    function __construct(&$world, &$parent)
    {
        $this->world = $world;
        $this->parent = $parent;
        
        // determine whether our parent is a standalone layer (Layer), or for a layer in a project context (ProjectLayer)
        // The 2 tables are nearly identical, so just define the table and id field and treat them the same!
        if (is_a($parent, 'ProjectLayer')) {
            $this->isProject = true;
            $this->table = 'project_layer_colors';
            $this->idfield = 'projectlayer';
        } else {
            $this->table = 'layer_default_colors';
            $this->idfield = 'layer';
        }
    }

    /**
     * This function "defragments" the scheme entries, forcing them to have sequential priorities starting at 1.
     * This is a workaround for operations that would leave holes in the priority list (deleting an entry),
     * or for any other time the priorities are just plain hosed (legacy color schemes). This function
     * can be called directly from the outside, but that's not necessary since it's already done automatically.
     */
    function sortPriorities()
    {
        // get the list of ID#s, sorted by their (probably non-contiguous) priority...
        $ids = $this->world->db->Execute("SELECT id FROM {$this->table} WHERE {$this->idfield}=? ORDER BY priority", array(
            $this->parent->id
        ));
        $ids = ParamUtil::GetColumn($ids,'id');
        // go through the list, and assign priorities starting with 1
        $index = 1;
        foreach($ids as $id) {
            $this->world->db->Execute("UPDATE {$this->table} SET priority=? WHERE id=?", array(
                $index,
                $id
            ));
            $index+=1;
        }
    }
    
    // ///
    // /// functions to fetch scheme entries, and to add a blank one
    // ///
    /**
     * Return an array of ColorSchemeEntry objects, representing the entire color scheme for this layer.
     * 
     * @return array An array of ColorSchemeEntry objects.
     */
    function getAllEntries($asEntryArray = true)
    {
        $num_records = $this->world->db->GetOne("SELECT count(*) as num_records FROM {$this->table} WHERE {$this->idfield}=?", array(
            $this->parent->id
        ));
        
        // fetch the list of IDs, then run them through getEntryById()
        if (! $asEntryArray) {
            $entries = $this->world->db->Execute("SELECT * FROM {$this->table} WHERE {$this->idfield}=? ORDER BY priority,id", array(
                $this->parent->id
            ))->getRows();
            
            if ($num_records == 0) {
                if (! $this->isProject) {
                    return array();
                    // $this->setSchemeToSingle();
                    // return $this->getAllEntries($asEntryArray);
                } else {
                    return $this->parent->layer->colorscheme->getAllEntries($asEntryArray);
                }
            }
            return $entries;
        }

        $entries = $this->world->db->Execute("SELECT id FROM {$this->table} WHERE {$this->idfield}=? ORDER BY priority,id", array(
            $this->parent->id
        ))->getRows();
        if (count($entries) <= 0) {
            if (! $this->isProject) {
                return array();
                /*
                 * $this->setSchemeToSingle();
                 * return $this->getAllEntries($asEntryArray);
                 */
            } else {
                if (! $this->parent->layer->colorscheme) {
                    return array();
                }
                return $this->parent->layer->colorscheme->getAllEntries($asEntryArray);
            }
        } 
        
        $entries = ParamUtil::GetColumn($entries,'id');
        $entries = array_map(array(
            $this,
            'getEntryById'
        ), $entries);
        return $entries;
        
    }
    
    function getUniqueCriteria()
    {
        $entries = $this->getAllEntries(true);
        $info = array();
        foreach($entries as $entry) {
            list($c1,$c2,$c3) = array($entry->criteria1,$entry->criteria2,$entry->criteria3);
            $c = $c1.$c2.$c3;
            if($c=="") {
                $c1=$c2=$c3="";
                $c="default";
            }
            $cInfo = array('c1'=>$c1,'c2'=>$c2,'c3'=>$c3);
            $info[$c]=$cInfo;
            
        }
        
        return array_values($info);
    }

    /**
     * Fetch the ColorSchemeEntry with the specified ID#.
     * This is mostly for internal use.
     * return ColorSchemeEntry
     */
    function getEntryById($id)
    {
        return new ColorSchemeEntry($this->world, $this, $id, $this->table, $this->idfield);
    }

    /**
     * Create a new, blank color scheme entry; its priority will place it at the bottom of the list.
     * return ColorSchemeEntry The newly-created color scheme entry.
     */
    function addEntry()
    {
        $ini = System::GetIni();
        // see whether we're already at the max allowed number of classes
        $c = $this->world->db->Execute("SELECT count(*) AS count FROM {$this->table} WHERE {$this->idfield}=?", array(
            $this->parent->id
        ));
        if ($c->fields['count'] >= $ini->max_colorclasses)
            return;
            
            // figure out the highest priority, and add 1 to it
        $priority = $this->world->db->Execute("SELECT max(priority) AS pri FROM {$this->table} WHERE {$this->idfield}=?", array(
            $this->parent->id
        ));
        $priority = 1 + (int) $priority->fields['pri'];
        
        // go ahead and insert this new blank rule with the new (lowest) priority
        $this->world->db->Execute("INSERT INTO {$this->table} ({$this->idfield},priority) VALUES (?,?)", array(
            $this->parent->id,
            $priority
        ));
        
        // flag the containing Project or Layer as having been modified
        $this->parent->touch();
        
        // now re-fetch it and hand back the object
        $id = $this->world->db->Execute("SELECT id FROM {$this->table} WHERE {$this->idfield}=? AND priority=?", array(
            $this->parent->id,
            $priority
        ));
        $id = $id->fields['id'];
        return new ColorSchemeEntry($this->world, $this, $id, $this->table, $this->idfield);
    }
    
    
    // ///
    // /// functions to set the color scheme to one of the predefined types
    // ///
    /**
     * Clear the entire color scheme, by removing all entries.
     */
    function clearScheme()
    {
        $this->world->db->Execute("DELETE FROM {$this->table} WHERE {$this->idfield}=?", array(
            $this->parent->id
        ));
        $this->parent->touch();
    }

    /**
     * Clear the color scheme, and replace it with a single-value color scheme.
     * This replaces the color scheme with a single entry.
     * 
     * @param string $fill_color
     *            The fill color, in HTML format, e.g. #FFFFFF Optional.
     * @param string $stroke_color
     *            The stroke color, in HTML format, e.g. #000000 Optional.
     * @param string $symbol
     *            The symbol to use for the new entry. Optional.
     */
    function setSchemeToSingle($fill_color = '#FFFFFF', $stroke_color = '#000000', $symbol = 'default', $symbol_size = SymbolSize::MEDIUM)
    {
        // fetch the geometry type as $geomtype; lines are handled in a special fashion
        $parent = $this->parent;
        
        $geomtype = $this->isProject ? $parent->layer->geomtype : $parent->geomtype;
        
        // purge the colorscheme, fetch the new ruleset
        $this->clearScheme();
        $ruleset = $this->generateSingle($fill_color, $stroke_color, $symbol, $symbol_size);
        
        // generate the new ruleset. For a single, this is simple: 1 entry using $ruleset[0]
        $entry = $this->addEntry();
        $entry->stroke_color = $ruleset[0]['stroke_color'];
        $entry->fill_color = $ruleset[0]['fill_color'];
        $entry->stroke_color = $ruleset[0]['stroke_color'];
        $entry->symbol = $ruleset[0]['symbol'];
        $entry->symbol_size = $ruleset[0]['symbol_size'];
        if ($geomtype == GeomTypes::LINE)
            $entry->stroke_color = $ruleset[0]['fill_color'];
            
            // flag the containing Layer or Project as having been updated
        $parent->colorschemetype = Colorschemes::SINGLE;
        $parent->touch();
    }

    /**
     * Generate the rules for 'single' type colorscheme.
     * This is used internally by setSchemeToSingle()
     * Arguments are exactly the same as for setSchemeToSingle()
     * 
     * @return aray A list of assocarrays, representing a ruleset of rules.
     */
    function generateSingle($fill_color = '#FFFFFF', $stroke_color = '#000000', $symbol = 'default', $symbol_size = SymbolSize::MEDIUM)
    {
        $entry = array(
            'fill_color' => $fill_color,
            'stroke_color' => $stroke_color,
            'symbol' => $symbol,
            'symbol_size' => $symbol_size
        );
        $entries = array(
            $entry
        );
        return $entries;
    }

    /**
     * Clear the color scheme, and replace it with a unique-value color scheme.
     * A unique-value color scheme is one in which a column (aka field or attribute) is examined and all unique values
     * are collected. A color scheme entry is then created for each unique value.
     * 
     * @param string $fieldname
     *            The name of the field to be used to classify the features.
     * @param integer $schemenumber
     *            The number of colors in the scheme. Used with $schemename to determine which scheme to use.
     * @param string $schemename
     *            The name of the scheme to use. Used with $schemenumber to determine which scheme to use.
     * @param string $symbol
     *            The symbol to use for all entries in the new scheme. Optional, defaults to 'default'
     * @param string $symbol_size
     *            The size of the symbol. Optional, defaults to SymbolSize::MEDIUM constant.
     */
    function setSchemeToUnique($fieldname, $schemenumber, $schemename, $stroke_color = '#000000', $symbol = 'default', $symbol_size = SymbolSize::MEDIUM)
    {
        if (preg_match('/\W/', $fieldname))
            return false;
            
            // fetch the geometry type as $geomtype; lines are handled in a special fashion
        $parent = $this->parent;
        $geomtype = is_a($parent, 'ProjectLayer') ? $parent->layer->geomtype : $parent->geomtype;
        
        // purge the existing colorscheme, then fetch the ruleset
        $this->clearScheme();
        
        $ruleset = $this->generateUnique($fieldname, $schemenumber, $schemename, $stroke_color, $symbol, $symbol_size);
        
        $numrules = sizeof($ruleset);
        // apply the ruleset
        foreach ($ruleset as $rule) {
            $entry = $this->addEntry();
            
            if (isset($rule['description']))
                $entry->description = $rule['description'];
            if (isset($rule['criteria1']))
                $entry->criteria1 = $rule['criteria1'];
            if (isset($rule['criteria2']))
                $entry->criteria2 = $rule['criteria2'];
            if (isset($rule['criteria3']))
                $entry->criteria3 = $rule['criteria3'];
            $entry->symbol = $rule['symbol'];
            $entry->symbol_size = $rule['symbol_size'];
            $entry->stroke_color = $rule['stroke_color'];
            $entry->fill_color = $rule['fill_color'];
            if ($geomtype == GeomTypes::LINE)
                $entry->stroke_color = $rule['fill_color'];
        }
        
        // create the catch-all rule, so anything that isn't classified is at least black and white
        $entry = $this->addEntry();
        $entry->symbol = $symbol;
        $entry->symbol_size = $symbol_size;
        
        // flag the containing Layer or Project as having been updated
        $parent->colorschemetype = Colorschemes::UNIQUE;
        $this->parent->touch();
    }

    /**
     * Generate the rules for 'unique' type colorscheme.
     * This is used internally by setSchemeToUnique()
     * Arguments are exactly the same as for setSchemeToUnique()
     * 
     * @return aray A list of assocarrays, representing a ruleset of rules.
     */
    function generateUnique($fieldname, $schemenumber, $schemename, $stroke_color = '#000000', $symbol = 'default', $symbol_size = SymbolSize::MEDIUM)
    {
        $ini = System::GetIni();
        // fetch the info for this color scheme, particularly the list of colors
        // no such scheme? then fetch a single scheme and bail!
        $colors = Colorschemes::GetColorScheme(Colorschemes::UNIQUE, $schemenumber, $schemename);
        
        if (! $colors)
            return $this->generateSingle();
        $numcolors = sizeof($colors);
        
        // fetch the underlying Layer object and then the name of the PostGIS table underlying that
        $parent = $this->parent;
        $layer = $this->isProject ? $parent->layer : $parent;
        $table = $layer->url;
        
        // fetch a list of unique values for the specified column
        if ($layer->type == LayerTypes::ODBC) {
            $odbcinfo = $layer->url;
            list ($odbc, $odbcini, $freetdsconf) = $this->world->connectToODBC($odbcinfo);
            $vx = odbc_exec($odbc, "SELECT DISTINCT({$fieldname}) AS value FROM {$odbcinfo->table} LIMIT " . ($ini->max_colorclasses + 1));
            $values = array();
            while ((($v = odbc_fetch_array($vx)) != false))
                $values[] = $v['value'];
            $numvalues = sizeof($values);
        } else {
            $values = $this->world->db->Execute("SELECT DISTINCT({$fieldname}) AS value FROM {$table} LIMIT " . ($ini->max_colorclasses + 1));
            if ($values) {
                $values = array_map(create_function('$a', 'return @$a["value"];'), $values->getRows());
            } else {
                $values = array();
            }
            $numvalues = sizeof($values);
        }
        
        // do we use the "cycle through colors" method using modular division,
        // or do we use the "stretch the value set out to match the length of the colorset" method?
        // If there are more values than colors we cycle.
        // If there's only 1 value, we have to use modulo to avoid dividing by zero.
        $method_modulo = $numvalues > $numcolors or $numvalues < 2;
        
        // go through the values and the palette, and make up an array of hashes: a ruleset of rules
        $ruleset = array();
        $geomtype = is_a($parent, 'ProjectLayer') ? $parent->layer->geomtype : $parent->geomtype;
        
        for ($i = 0; $i < $numvalues; $i ++) {
            if ($numvalues - 1 == 0) {
                $colorindex = 0;
            } else {
                $colorindex = $method_modulo ? $i % $numcolors : round($numcolors * ($i / ($numvalues - 1)));
            }
            if ($colorindex > $numcolors - 1)
                $colorindex = $numcolors - 1;
            $this_value = $values[$i];
            $this_fill_color = $colors[$colorindex];
            $this_stroke_color = $stroke_color;
            
            $rule = array(
                'description' => $this_value,
                'criteria1' => $fieldname,
                'criteria2' => '==',
                'criteria3' => $this_value,
                'symbol' => $symbol,
                'symbol_size' => $symbol_size,
                'stroke_color' => $this_stroke_color,
                'fill_color' => $this_fill_color,
                'description' => $this_value
            );
            if ($geomtype == GeomTypes::LINE)
                $rule['stroke_color'] = $rule['fill_color'];
            array_push($ruleset, $rule);
        }
        return $ruleset;
    }

    /**
     * Clear the color scheme, and replace it with a quantile color scheme.
     * A quantile scheme is one in which the data are sorted by the given field, and then broken into
     * a set number of classifications with the same number of records in each. As such, this ranks
     * the records by the value without regard to overlapping or repeated values.
     * 
     * @param string $fieldname
     *            The name of the field to be used to classify the features.
     * @param string $schemetype
     *            One of these: unique, qualitative, diverging, sequential
     * @param integer $schemenumber
     *            The number of colors in the scheme. Used with $schemename to determine which scheme to use.
     * @param string $schemename
     *            The name of the scheme to use. Used with $schemenumber to determine which scheme to use.
     * @param string $symbol
     *            The symbol to use for all entries in the new scheme. Optional, defaults to 'default'
     * @param string $symbol_size
     *            The size of the symbol. Optional, defaults to SymbolSize::MEDIUM constant.
     */
    function setSchemeToQuantile($fieldname, $schemetype, $schemenumber, $schemename, $stroke_color = '#000000', $symbol = 'default', $symbol_size = SymbolSize::MEDIUM)
    {
        if (preg_match('/\W/', $fieldname))
            return false;
            
            // fetch the geometry type as $geomtype; lines are handled in a special fashion
        $parent = $this->parent;
        $geomtype = is_a($parent, 'ProjectLayer') ? $parent->layer->geomtype : $parent->geomtype;
        
        // purge the existing colorscheme, then fetch the ruleset
        $this->clearScheme();
        $ruleset = $this->generateQuantile($fieldname, $schemetype, $schemenumber, $schemename, $stroke_color, $symbol, $symbol_size);
        $numrules = sizeof($ruleset);
        
        // apply the ruleset
        foreach ($ruleset as $rule) {
            $entry = $this->addEntry();
            $entry->description = $rule['description'];
            $entry->criteria1 = $rule['criteria1'];
            $entry->criteria2 = $rule['criteria2'];
            $entry->criteria3 = $rule['criteria3'];
            $entry->symbol = $rule['symbol'];
            $entry->symbol_size = $rule['symbol_size'];
            $entry->stroke_color = $rule['stroke_color'];
            $entry->fill_color = $rule['fill_color'];
            if ($geomtype == GeomTypes::LINE)
                $entry->stroke_color = $rule['fill_color'];
        }
        
        // create the catch-all rule, so anything that isn't classified is at least black and white
        $entry = $this->addEntry();
        $entry->symbol = $symbol;
        $entry->symbol_size = $symbol_size;
        
        // flag the containing Layer or Project as having been updated
        $parent->colorschemetype = Colorschemes::QUANTILE;
        $this->parent->touch();
    }

    /**
     * Generate the rules for 'quantile' type colorscheme.
     * This is used internally by setSchemeToQuantile()
     * Arguments are exactly the same as for setSchemeToQuantile()
     * 
     * @return aray A list of assocarrays, representing a ruleset of rules.
     */
    function generateQuantile($fieldname, $schemetype, $schemenumber, $schemename, $stroke_color = '#000000', $symbol = 'default', $symbol_size = SymbolSize::MEDIUM)
    {
        // fetch the info for this color scheme, particularly the list of colors
        // no such scheme? then fetch a single scheme and bail!
        $colors = Colorschemes::GetColorScheme($schemetype, $schemenumber, $schemename);
        if (! $colors)
            return $this->generateSingle();
        $numcolors = sizeof($colors);
        
        // fetch the underlying Layer object and then the name of the PostGIS table underlying that
        $parent = $this->parent;
        $layer = $this->isProject ? $parent->layer : $parent;
        $table = $layer->url;
        
        if ($layer->type == LayerTypes::ODBC) {
            $odbcinfo = $layer->url;
            list ($odbc, $odbcini, $freetdsconf) = $this->world->connectToODBC($odbcinfo);
            $valueset = odbc_exec($odbc, "SELECT COUNT(*) AS count FROM {$odbcinfo->table}");
            $valueset = odbc_fetch_array($valueset);
            $valueset = $valueset['count'];
        } else {
            // fetch the number of records, and thus determine the stepping value
            // For quantile stepvalue = number of records divided by number of classes
            $valueset = $this->world->db->Execute("SELECT COUNT(*) AS count FROM {$table}")->fields['count'];
        }
        $stepvalue = ceil($valueset / $numcolors);
        
        // generate the ruleset
        // go through the steps, do a subselect. The subselect fetches the records sorted by the field,
        // but with a limit of $stepvalue*$i records. Thus, we're fetching the sorted records in blocks
        // and determining the max value that defines the ceiling for that bracket.
        $ruleset = array();
        for ($i = 0; $i < $numcolors; $i ++) {
            // find the max value for this block
            $offset = $stepvalue * $i;
            if ($layer->type == LayerTypes::ODBC) {
                $max = odbc_exec($odbc, "SELECT MAX({$fieldname}) AS max FROM (SELECT {$fieldname} FROM {$odbcinfo->table} ORDER BY {$fieldname} LIMIT $stepvalue OFFSET $offset) AS foo");
                $max = odbc_fetch_array($max);
                $max = $max['max'];
            } else {
                $max = $this->world->db->Execute("SELECT MAX({$fieldname}) AS max FROM (SELECT {$fieldname} FROM {$table} ORDER BY {$fieldname} LIMIT $stepvalue OFFSET $offset) AS foo")->fields['max'];
            }
            if ($max === false or $max === null)
                continue;
            if ($layer->geom_type == GeomTypes::LINE) {
                $rule = array(
                    'description' => "$fieldname <= $max",
                    'criteria1' => $fieldname,
                    'criteria2' => '<=',
                    'criteria3' => $max,
                    'symbol' => $symbol,
                    'symbol_size' => $symbol_size,
                    'stroke_color' => $colors[$i % $numcolors],
                    'fill_color' => 'trans'
                );
            } else {
                $rule = array(
                    'description' => "$fieldname <= $max",
                    'criteria1' => $fieldname,
                    'criteria2' => '<=',
                    'criteria3' => $max,
                    'symbol' => $symbol,
                    'symbol_size' => $symbol_size,
                    'stroke_color' => $stroke_color,
                    'fill_color' => $colors[$i % $numcolors]
                );
            }
            array_push($ruleset, $rule);
        }
        
        return $ruleset;
    }

    /**
     * Clear the color scheme, and replace it with an equal-interval color scheme.
     * An equal-interval scheme is one in which the data are sorted by the given field, and the highest and lowest
     * values are calculated from that field. A set number of classifications is then calculated by dividing the
     * numeric range by the specified number of steps, e.g. a range of 0-200 in 10 steps would be categorized
     * from 0-19, 20-39, 40-59, and so on. This effectively ranks the data by its value, and works well for
     * data that approximates a normal distribution.
     *
     * @param string $fieldname
     *            The name of the field to be used to classify the features.
     * @param string $schemetype
     *            One of these: unique, qualitative, diverging, sequential
     * @param integer $schemenumber
     *            The number of colors in the scheme. Used with $schemename to determine which scheme to use.
     * @param string $schemename
     *            The name of the scheme to use. Used with $schemenumber to determine which scheme to use.
     * @param string $symbol
     *            The symbol to use for all entries in the new scheme. Optional, defaults to 'default'
     * @param string $symbol_size
     *            The size of the symbol. Optional, defaults to SymbolSize::MEDIUM constant.
     */
    function setSchemeToEqualInterval($fieldname, $schemetype, $schemenumber, $schemename, $stroke_color = '#000000', $symbol = 'default', $symbol_size = SymbolSize::MEDIUM)
    {
        if (preg_match('/\W/', $fieldname))
            return false;
            
            // fetch the geometry type as $geomtype; lines are handled in a special fashion
        $parent = $this->parent;
        $geomtype = $this->isProject ? $parent->layer->geomtype : $parent->geomtype;
        
        // purge the existing colorscheme, then fetch the ruleset
        $this->clearScheme();
        $ruleset = $this->generateEqualInterval($fieldname, $schemetype, $schemenumber, $schemename, $stroke_color, $symbol, $symbol_size);
        $numrules = sizeof($ruleset);
        
        // apply the ruleset
        foreach ($ruleset as $rule) {
            $entry = $this->addEntry();
            $entry->description = $rule['description'];
            $entry->criteria1 = $rule['criteria1'];
            $entry->criteria2 = $rule['criteria2'];
            $entry->criteria3 = $rule['criteria3'];
            $entry->symbol = $rule['symbol'];
            $entry->symbol_size = $rule['symbol_size'];
            $entry->stroke_color = $rule['stroke_color'];
            $entry->fill_color = $rule['fill_color'];
            if ($geomtype == GeomTypes::LINE)
                $entry->stroke_color = $rule['fill_color'];
        }
        
        // create the catch-all rule, so anything that isn't classified is at least black and white
        $entry = $this->addEntry();
        $entry->symbol = $symbol;
        $entry->symbol_size = $symbol_size;
        
        // flag the containing Layer or Project as having been updated
        $parent->colorschemetype = Colorschemes::EQUALINTERVAL;
        $this->parent->touch();
    }

    function generateEqualInterval($fieldname, $schemetype, $schemenumber, $schemename, $stroke_color = '#000000', $symbol = 'default', $symbol_size = SymbolSize::MEDIUM)
    {
        // fetch the info for this color scheme, particularly the list of colors
        // no such scheme? then fetch a single scheme and bail!
        $colors = Colorschemes::GetColorScheme($schemetype, $schemenumber, $schemename);
        if (! $colors)
            return $this->generateSingle();
        $numcolors = sizeof($colors);
        
        // fetch the underlying Layer object and then the name of the PostGIS table underlying that
        $parent = $this->parent;
        $layer = $this->isProject ? $parent->layer : $parent;
        
        if ($layer->type == LayerTypes::ODBC) {
            $odbcinfo = $layer->url;
            list ($odbc, $odbcini, $freetdsconf) = $this->world->connectToODBC($odbcinfo);
            $minmax = odbc_exec($odbc, "SELECT MIN({$fieldname}) AS min,MAX({$fieldname}) AS max FROM {$odbcinfo->table}");
            $minmax = odbc_fetch_array($minmax);
            $minimum = $minmax['min'];
            $maximum = $minmax['max'];
        } else {
            // find the min and max values within this field, and thus determine the stepping value
            $table = $layer->url;
            $steppingvalue = $this->world->db->Execute("SELECT MIN({$fieldname}) AS min,MAX({$fieldname}) AS max FROM {$table}");
            $minimum = $steppingvalue->fields['min'];
            $maximum = $steppingvalue->fields['max'];
        }
        $steppingvalue = ($maximum - $minimum) / $numcolors;
        // generate the ruleset
        // just go through the steps, setting a "field < X" criteria where X is $stepvalue*$i
        $ruleset = array();
        
        for ($i = 0; $i < $numcolors; $i ++) {
            if ($layer->geom_type == GeomTypes::LINE) {
                $rule = array(
                    'description' => sprintf("$fieldname <= %s", $minimum + $steppingvalue * $i),
                    'criteria1' => $fieldname,
                    'criteria2' => '<=',
                    'criteria3' => $minimum + $steppingvalue * $i,
                    'symbol' => $symbol,
                    'symbol_size' => $symbol_size,
                    'stroke_color' => $colors[$i % $numcolors],
                    'fill_color' => 'trans'
                );
            } else {
                
                $rule = array(
                    'description' => sprintf("$fieldname <= %s", $minimum + $steppingvalue * $i),
                    'criteria1' => $fieldname,
                    'criteria2' => '<=',
                    'criteria3' => $minimum + $steppingvalue * $i,
                    'symbol' => $symbol,
                    'symbol_size' => $symbol_size,
                    'stroke_color' => $stroke_color,
                    'fill_color' => $colors[$i % $numcolors]
                );
            }
            array_push($ruleset, $rule);
        }
        return $ruleset;
    }

    function copy($layer, $table = null)
    {
        if($this->getCount() === 0) {
            return false;
        }
        if (is_null($table))
            $table = $this->table;
        foreach ($this->getAllEntries() as $entry) {
            $newid = $this->world->db->Execute("INSERT INTO " . $table . " (" . $this->idfield . ",priority,criteria1,criteria2,criteria3,fill_color,stroke_color,description,symbol,symbol_size
													) SELECT " . $layer->id . ",priority,criteria1,criteria2,criteria3,fill_color,stroke_color,description,symbol,symbol_size FROM " . $this->table . " WHERE id=? RETURNING id", array(
                $entry->id
            ));
            $newid = $newid->fields['id'];
        }
        $newColorScheme = new ColorScheme($this->world, $layer);
        return $newColorScheme;
    }
     function getCount() {
        $query = "SELECT count(*) FROM {$this->table} WHERE {$this->idfield}={$this->parent->id}";
        $count  = intval(System::GetDB()->GetOne($query));
        return $count < 0 ? 0 : $count;
    }

}

?>