<?php

namespace utils;

class LayerUtil {

    public static function ChangeLayerIds($sourceLayerId, $targetLayerId) {

        $db = \System::GetDB(\System::DB_ACCOUNT_SU);
        $sourceLayer = \Layer::GetLayer($sourceLayerId);
        $targetLayer = \Layer::GetLayer($targetLayerId);



        if (is_null($sourceLayer) || is_null($targetLayer)) {
            throw new \Exception('LayerUtil::ChangeLayerIds : A layer must exist for each id');
        }


        if (!is_null($sourceLayer->url) && !is_null($targetLayer->url)) {
            RelationUtil::ReplaceRelations($sourceLayer->url, $targetLayer->url);
        }


        /*
          $cols  = explode(",","category,sharelevel,description,keywords,colorschemetype,url,labelitem,searchtip,default_criteria,tags,minscale,tooltip");
          $colNames = array();
          foreach($cols as $col) {
          $colNames[] = '"'.$col.'"';
          }
          $colNames = implode(',',$colNames);

          $record = $db->GetRow("select $colNames from layers where id=$sourceLayerId");

          $update = "Update layers  set ";
          $vals = array();
          $isFirst = true;


          foreach($record as $key=>$val) {

          if(!$isFirst) $update.=',';
          $update .= " \"$key\"=? ";
          $vals[]=$val;
          $isFirst = false;
          }

          $update.=" where id=$targetLayerId";

          $db->Execute($update,$vals);
         */

        // update map layers
        $db->Execute("update project_layers set layer={$targetLayer->id} where layer=$sourceLayerId");

        $db->Execute("update layer_collections set layer_id={$targetLayerId} where layer_id=$sourceLayerId");
        $db->Execute("update layer_collections set parent_id={$targetLayerId} where parent_id=$sourceLayerId");

        $db->Execute("delete from layer_default_colors where layer=$targetLayerId");
        $sourceLayer->colorscheme->copy($targetLayer);
        //$db->Execute("update layer_default_colors set layer=$targetLayerId where layer=$sourceLayerId");
        $db->Execute("update layer_bookmarks set layer=$targetLayerId where layer=$sourceLayerId");
        $db->Execute("update layersharing set layer=$targetLayerId where layer=$sourceLayerId");
        $db->Execute("update layersharing_socialgroups set layer_id=$targetLayerId where layer_id=$sourceLayerId");
        //$db->Execute("update layer_categories set layer=$targetLayerId where layer=$sourceLayerId");
        //try {
        $targetLayer->field_info = $sourceLayer->field_info;
    }

    public static function ReplaceOriginal(\Layer $layer) {
        $originalid = $layer->originalid;

        if (is_null($originalid))
            return;
        self::ChangeLayerIds($originalid, $layer->id);
        $layer->replacedid = $originalid;
    }

    public static function RevertToOriginal(\Layer $layer) {
        $originalid = $layer->originalid;

        if (is_null($originalid))
            return;

        self::ChangeLayerIds($layer->id, $originalid);
        $layer->replacedid = null;
    }

    public static function GenerateIcons($layer) {
        $sys = \System::Get();
        $ini = \System::GetIni();

        $mapper = $sys->getMapper();
        $defaultProj4 = $sys->projections->defaultProj4;
        $projector = new \Projector_MapScript();
        $mapper->init(true, $projector->mapObj);
        $mapper->extent = array(-180, -90, 180, 90);
        $mapper->addLayer($layer, 1.0, 0);
        $mapper->quantize = false;
        $mapper->map = $mapper->_generate_mapfile(true);

        if (!file_exists($ini->legend_media)) {
            mkdir($ini->legend_media);
        }
        $layerObj = $layer['layer'];
        $hasColorscheme = $layerObj->colorscheme;
        
        if (!$hasColorscheme) {
            return [];
        }
        $colorscheme = $layerObj->colorscheme->getAllEntries();
        $entries = array_slice($colorscheme, 0, $ini->max_colorclasses); // just in case they somehow exceeded it

        $files = array();
        foreach ($mapper->map->getlayersdrawingorder() as $i) {
            $layer = $mapper->map->getLayer($i);
            for ($i = 0; $i < $layer->numclasses; $i++) {
                $icon = sprintf("%s%s.png", $ini->legend_media, $colorscheme[$i]->id);

                //$iconPath =  sprintf("%s/%s", $ini->tempdir,$icon);
                $class = $layer->getClass($i);
                $class->createLegendIcon(22, 25)->saveImage($icon);
                $files[] = $icon;
                //$icon = str_replace("/maps/","./",$icon);
                //$icon = "./?do=wapi.asset.get&asset=$icon&format=png&token=".SimpleSession::Get()->GetID();
                //$infoEntry =  array('icon'=>$icon,'class'=>$entries[$i],'class_name'=>$class->name);
                //$info[] = $infoEntry;
            }
        }
        return $files;
    }

}
