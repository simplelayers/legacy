<?php

class ProjectLayerFormatter extends LayerFormatter
{

    const ADDER = "adder";

    const PROPERTIES = "properties";

    const BOUNDS = "bounds";

    const TOOLTIP = "tooltip";

    const TOOLTIP_RICH = "tooltip_rich";

    const LEGEND = "legend";
    
    const EVERYTHING = 'everything';

    public function __construct($world, $user)
    {
        parent::__construct($world, $user);
        $this->options->AddItem(self::ADDER);
        $this->options->AddItem(self::PROPERTIES);
        $this->options->AddItem(self::BOUNDS);
        $this->options->AddItem(self::LEGEND);
        $this->options->AddItem(self::TOOLTIP);
        $this->options->AddItem(self::TOOLTIP_RICH);
    }

    public function LoadFromDefaults($projectLayer, $layer, $preferences)
    {
        if ($this->options->isFlagged(self::CLASSIFICATION, $preferences)) {
            $projectLayer->LoadColorschemeFromDefault();
        }
        if ($this->options->isFlagged(self::TOOLTIP_RICH, $preferences)) {
            $projectLayer->rich_tooltip = $layer->rich_tooltip;
        }
        if ($this->options->isFlagged(self::TOOLTIP, $preferences)) {
            $projectLayer->tooltip = $layer->tooltip;
        }
        
        if ($this->options->isFlagged(self::LABELS, $preferences)) {
            $projectLayer->label_style = $layer->label_style;
            $projectLayer->labelitem = $layer->labelitem;
        }
    }

    public function WriteXML($projectLayer, $preferences = null)
    {
        $alevels = AccessLevels::GetEnum();
        $ltypes = LayerTypes::GetEnum();
        
        if (is_null($preferences)) {
            $preferences = $this->options->GetMaxValue(); // $this->options[self::BASIC] | $this->options[self::OWNER] | $this->options[self::SUBLAYERS] | $this->options[self::PROPERTIES];
        }
        
        if (($projectLayer instanceof Layer)) {
            throw new Exception("Attempting to use ProjectLayer formatter with Layer");
        } elseif (! ($projectLayer instanceof ProjectLayer)) {
            throw new Exception("Attempting to use ProjectLayer formatter with invalid layer");
        }
        $layer = $projectLayer->layer;
        $layerid = $layer->id;
        
        $layerType = $layer->type;
        
        echo '<layer ';
        $this->toAttribute('id', $layer->id);
        $this->toAttribute('plid', $projectLayer->id);
        $this->toAttribute('type', $layerType);
        $this->toAttribute('typeLabel', $ltypes[$layerType]);
        $this->toAttribute('name', $layer->name);
        $access = $this->world->auth->isAnonymous ? 1 : $layer->getPermissionById($this->user->id);
        $this->toAttribute('access', $access);
        $this->toAttribute('access_label', $alevels[$access]);
        $this->toAttribute('z', $projectLayer->z);
        $geomtypestring = ($layerType == LayerTypes::COLLECTION) ? "collection" : $layer->geomtypestring;
        
        $this->toAttribute('geom', $geomtypestring);
        $this->toAttribute('default_criteria', $layer->default_criteria);
        
        if ($this->options->isFlagged(self::OWNER, $preferences)) {
            $owner = $layer->owner;
            
            $this->toAttribute("owner", $owner->id);
            $this->toAttribute("owner_name", $owner->username);
            $this->toAttribute('owner_realname', $owner->realname);
        }
        
        if ($this->options->isFlagged(self::ADDER, $preferences)) {
            $adder = $projectLayer->whoadded;
            if (is_null($adder)) {
                $adder = System::GetSystemOwner();
            }
            
            $this->toAttribute("adder", $adder->id);
            $this->toAttribute("adder_name", $adder->username);
        }
        
        if ($this->options->isFlagged(self::PROPERTIES, $preferences)) {
            $this->toAttribute("opacity", $projectLayer->opacity);
            $this->toAttribute("layer_on", $projectLayer->on_by_default);
            $this->toAttribute("search_on", $projectLayer->searchable);
            $this->toAttribute("bbox", $layer->getSpaceExtent());
        }
        $this->toAttribute("sharing", $layer->sharelevel);
        if (! is_null($projectLayer->parent)) {
            $this->toAttribute('parentLayer', $projectLayer->parent);
        }
        
        echo ">";
        echo "<searchtip>";
        $this->toCDATA($layer->searchtip);
        echo "</searchtip>";
        
        if ($this->options->isFlagged(self::DESCRIPTION, $preferences)) {
            echo "<description>";
            echo $this->toCDATA($layer->description);
            echo "</description>";
        }
        
        if ($this->options->isFlagged(self::TAGS, $preferences)) {
            echo "<tags>";
            $this->toCDATA($layer->tags);
            echo "</tags>";
        }
        
        if ($this->options->isFlagged(self::LABELS, $preferences)) {
            echo '<labels ';
            $this->toAttribute('attribute', $projectLayer->labelitem);
            $this->toAttribute('labels_on', $projectLayer->labels_on);
            echo ' >';
            echo "<label_style><![CDATA[" . $projectLayer->label_style_string . "]]></label_style>";
            echo '</labels>';
        }
        
        if ($this->options->isFlagged(self::ATTRIBUTES, $preferences)) {
            echo "<layerAttributes>";
            $attributes = $layer->getAttributesVerbose(false, true);
            
            foreach ($attributes as $attribute => $value) {
                echo "<attribute";
                $this->toAttribute('name', $attribute);
                $this->toAttribute('requirement', $value['requires']);
                $attType = $value['requires'];
                if ($attType == "int") {
                    $attType = "numeric";
                } elseif ($attType == "float") {
                    $attType = "numeric";
                } elseif ($attType == "boolean") {
                    $attType = "numeric";
                } else
                    $attType = 'text';
                $this->toAttribute('type', $attType);
                $this->toAttribute('maxlength', $value['maxlength']);
                $this->toAttribute('display', $value['display']);
                $this->toAttribute('visible', $value['visible'] ? '1' : '0');
                $this->toAttribute('z', $value['z']);
                $this->toAttribute('searchable', $value['searchable'] ? '1' : '0');
                echo " />";
            }
            echo "</layerAttributes>";
        }
        
        if ($this->options->isFlagged(self::TOOLTIP, $preferences)) {
            echo "<tooltip tooltip_on=\"" . $projectLayer->tooltip_on . "\" ><![CDATA[" . htmlentities($projectLayer->tooltip) . "]]></tooltip>";
        }
        if ($this->options->isFlagged(self::TOOLTIP, $preferences)) {
            echo "<tooltip_rich ><![CDATA[" . htmlentities($projectLayer->rich_tooltip) . "]]></tooltip_rich>";
        }
        
        if ($this->options->isFlagged(self::CLASSIFICATION, $preferences)) {
            $colorscheme = $projectLayer->colorscheme;
            if ($colorscheme) {
                $symbol = $colorscheme->getAllEntries();
                echo "<classification";
                if (! is_null($symbol)) {
                    $symbol = $colorscheme->getAllEntries();
                    if (isset($symbol[0])) {
                        $symbol = $symbol[0];
                        
                        $this->toAttribute('type', $projectLayer->colorschemetype);
                        $this->toAttribute('stroke_color', $projectLayer->colorschemestroke);
                        $this->toAttribute('fill_color', $projectLayer->colorschemefill);
                        $this->toAttribute('symbol', $projectLayer->colorschemesymbol);
                        $this->toAttribute('symbol_size', $projectLayer->colorschemesymbolsize);
                        $this->toAttribute('attribute', $projectLayer->colorschemecolumn);
                        $this->toAttribute('label_style', htmlentities($projectLayer->label_style_string));
                    }
                }
                echo "/>";
            }
        }
        
        if ($this->options->isFlagged(self::SUBLAYERS, $preferences)) {
            
            if ($layer->type == LayerTypes::COLLECTION) {
                
                echo '<sublayers>';
                $subs = $projectLayer->project->getSubLayers($projectLayer->id);
                
                foreach ($subs as $sub) {
                    $this->WriteXML($sub, $preferences);
                }
                echo '</sublayers>';
            }
        }
        echo '</layer>';
    }

    public function WriteJSON($projectLayer, $preferences = null)
    {
        json_encode($this->MakeJSON($projectLayer, $preferences));
    }

    public function MakeJSON($projectLayer, $preferences = null)
    {
        $alevels = AccessLevels::GetEnum();
        $ltypes = LayerTypes::GetEnum();
        
        if (is_null($preferences)) {
            $preferences = $this->options->GetMaxValue(); // $this->options[self::BASIC] | $this->options[self::OWNER] | $this->options[self::SUBLAYERS] | $this->options[self::PROPERTIES];
        }
        
        if (($projectLayer instanceof Layer)) {
            throw new Exception("Attempting to use ProjectLayer formatter with Layer");
        } elseif (! ($projectLayer instanceof ProjectLayer)) {
            throw new Exception("Attempting to use ProjectLayer formatter with invalid layer");
        }
        
        $layer = $projectLayer->layer;
        $layerid = $layer->id;
        $layerType = $layer->type;
        
        $lyr = array();
        $lyr['id'] = $layer->id;
        $lyr['plid'] = $projectLayer->id;
        $lyr['type'] = $layerType;
        $lyr['typeLabel'] = $ltypes[$layerType];
        $lyr['name'] = $layer->name;
        
        $access = $this->world->auth->isAnonymous ? 1 : $layer->getPermissionById($this->user->id);
        $lyr['access'] = $access;
        $lyr['access_label'] = $alevels[$access];
        $lyr['z'] = $projectLayer->z;
        $geomtypestring = ($layerType == LayerTypes::COLLECTION) ? "collection" : $layer->geomtypestring;
        $lyr['geom'] = $geomtypestring;
        $lyr['default_criteria'] = $layer->default_criteria;
        
        if ($this->options->isFlagged(self::OWNER, $preferences)) {
            $owner = $layer->owner;
            $lyr['owner'] = $owner->id;
            $lyr['owner_name'] = $owner->username;
        }
        
        if ($this->options->isFlagged(self::ADDER, $preferences)) {
            $adder = $projectLayer->whoadded;
            $lyr['adder'] = $adder->id;
            $lyr['adder_name'] = $adder->username;
        }
        
        if ($this->options->isFlagged(self::PROPERTIES, $preferences)) {
            $lyr["opacity"] = $projectLayer->opacity;
            $lyr["layer_on"] = $projectLayer->on_by_default;
            $lyr['search_on'] = $projectLayer->searchable;
            $lyr['bbox'] = $layer->getSpaceExtent();
        }
        $lyr["sharing"] = $layer->sharelevel;
        $lyr['searchTip'] = $layer->searchtip;
        
        if ($this->options->isFlagged(self::TAGS, $preferences)) {
            $lyr['tags'] = $layer->tags;
        }
        
        if ($this->options->isFlagged(self::LABELS, $preferences)) {
            $lyr['labels'] = array();
            $lyr['labels']['attribute'] = $projectLayer->labelitem;
            $lyr['labels']['labels_on'] = $projectLayer->labels_on;
            $lyr['labels']['label_style'] = $projectLayer->label_style_string;
        }
        
        if ($this->options->isFlagged(self::ATTRIBUTES, $preferences)) {
            $lyratts = array();
            $attributes = $layer->getAttributesVerbose(false, true);
            
            foreach ($attributes as $attribute => $value) {
                $lyratts['name'] = $attribute;
                $lyratts['requirement'] = $value['requires'];
                $attType = $value['requires'];
                if ($attType == "int") {
                    $attType = "numeric";
                } elseif ($attType == "float") {
                    $attType = "numeric";
                } elseif ($attType == "boolean") {
                    $attType = "numeric";
                } else
                    $attType = 'text';
                $lyratts['type'] = $attType;
                $lyratts['maxlength'] = $value['maxlength'];
                $lyratts['display'] = $value['display'];
                $lyratts['visible'] = $value['visible'] ? '1' : '0';
                
                $lyratts['z'] = $value['z'];
                $lyratts['searchable'] = $value['searchable'] ? '1' : '0';
            }
            $lyr['layerAttributes'] = $attributes;
        }
        
        if ($this->options->isFlagged(self::TOOLTIP, $preferences)) {
            $lyr['tooltip'] = array(
                'tooltip_on' => $projectLayer->tooltip_on,
                'value' => $projectLayer->tooltip
            );
        }
        
        if ($this->options->isFlagged(self::CLASSIFICATION, $preferences)) {
            $colorscheme = $projectLayer->colorscheme;
            $lyrcs = array();
            
            if ($colorscheme) {
                $symbol = $colorscheme->getAllEntries();
                if (! is_null($symbol)) {
                    $symbol = $colorscheme->getAllEntries();
                    $symbol = $symbol[0];
                    $lyrcs['type'] = $projectLayer->colorschemetype;
                    $lyrcs['stroke_color'] = $projectLayer->colorschemestroke;
                    $lyrcs['fill_color'] = $projectLayer->colorschemefill;
                    $lyrcs['symbol'] = $projectLayer->colorschemesymbol;
                    $lyrcs['symbol_size'] = $projectLayer->colorschemesymbolsize;
                    $lyrcs['attribute'] = $projectLayer->colorschemecolumn;
                }
            }
            $lyr['classification'] = $lyrcs;
        }
        
        if ($this->options->isFlagged(self::SUBLAYERS, $preferences)) {
            if ($layer->type == LayerTypes::COLLECTION) {
                $sublyrs = array();
                
                $subs = $projectLayer->project->getSubLayers($projectLayer->id);
                
                foreach ($subs as $sub) {
                    $sublyrs[] = $this->MakeJSON($sub, $preferences);
                }
                $lyr['sublayers'] = $sublyrs;
            }
        }
        
        return $lyr;
    }
}

?>