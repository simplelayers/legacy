<?php
namespace model\mapping;

class MapFile
{

    const ROLE_LAYER_PREVIEW = 'layer_preview';
    const ROLE_LAYER_RENDERING = 'layer_rendering';
    const ROLE_MAP_LAYER_RENDERING = 'map_layer_rendering';
    const ROLE_BASEMAP_RENDERING = 'basemap_rendering';
    const ROLE_MAP = 'map_preview';
    const ROLE_SCREENSHOT = 'screenshot';
    const ROLE_WMS = 'wms';
    const ROLE_THUMBNAIL = 'thumbnail';
    const ROLE_UNSPECIFIED = 'unspecified';

    private $width;
    private $height;
    private $extent;
    private $mapfile;
    private $layers;
    private $isScreenShot;
    private $lowQuality;
    private $projection;
    private $map;
    private $filter_gids;
    private $filter_color;
    private $quantize;
    private $interlace;
    private $fontsdir;
    private $symbolFile;
    private $tempdir;
    private $mapfileDir;
    private $role;
    private $target;

    function MapFile($renderer = null, $role = 'unspecified')
    {
        $ini = \System::GetIni();
        
        $this->purpose = $role;
        
        $this->width = 144;
        $this->height = 144;
        $this->extent = array(
            - 180,
            - 90,
            180,
            90
        );
        $this->mapfile = '';
        $this->layers = null;
        $this->screenshot = false;
        $this->geotiff = false;
        $this->thumbnail = false;
        $this->lowquality = false;
        $this->projection = null;
        $this->map = null;
        $this->filter_gids = null;
        $this->filter_color = null;
        $this->quantize = false;
        $this->interlace = false;
        $this->fontsdir = $ini->maps_fontsdir;
        $this->symbolFile = WEBROOT . $ini->maps_symbolfile;
        $this->tempdir = $ini->tempdir;
        $this->tempurl = $ini->tempurl;
        $this->mapfileDir = $ini->mapfiledir;
        if ($renderer)
            $this->SetRenderer($renderer);
        $this->role = $role;
    }

    public function SetRenderer(Renderer $renderer)
    {
        $this->layers = $renderer;
    }

    public function SetTarget($target)
    {
        $this->target = $target;
    }
    
    public function GetTarget($errorMessage) {
        $hasTarget = ! is_null($this->target);
        if(!$hasTarget) throw new \Exception('SimpleLayers MapFile Error:'.$errorMessage);
        return $this->target;
        
    }

    public function GetID()
    {
        switch ($this->role) {
            case self::ROLE_BASEMAP_RENDERING:
            case self::ROLE_LAYER_PREVIEW:
            case self::ROLE_LAYER_RENDERING:
            case self::ROLE_MAP:
            case self::ROLE_MAP_LAYER_RENDERING:
            case self::ROLE_THUMBNAIL:
                $target = $this->GetTarget('Attempting to get an id for an unspecified target');
                $hasId = (is_null($target)) ? isset($this->target->id) : false;
                if (! $hasId)
                    throw new \Exception('SimpleLayers MapFile Error: Attempting to get an id for a target without an id');
                return $this->target - id;
                break;
            case self::ROLE_SCREENSHOT:
            case self::ROLE_UNSPECIFIED:
            case self::ROLE_WMS:
                return "";
        }
    }

    public function GetLastModified()
    {
        switch ($this->role) {
            case self::ROLE_SCCREENSHOT:
            case self::ROLE_UNSPECIFIED:
            case self::ROLE_WMS:
                return "";
        }
        $target =  $this->GetTarget("Attempting to get modification date without a specified target");
        
        switch ($this->role) {
            case self::ROLE_MAP:
            case self::ROLE_BASEMAP_RENDERING:
            case self::ROLE_LAYER_PREVIEW:
            case self::ROLE_LAYER_RENDERING:
            case self::ROLE_THUMBNAIL:
                return $target->last_modified_unix;
            case self::ROLE_MAP_LAYER_RENDERING:
                return $target->project->last_modified_unix;
        }
    }

    public function MapFileInfo()
    {
        if (is_null($this->layers)) {
            return $this->layers;
        }
        $ini = \System::GetIni();
        
        $id = $this->GetID();
        $modDate = $this->GetLastModified();
        
        switch($this->role) {
            case self::ROLE_BASEMAP_RENDERING:
            case self::ROLE_LAYER_PREVIEW:
            case self::ROLE_LAYER_RENDERING:
            case self::ROLE_MAP:
            case self::ROLE_MAP_LAYER_RENDERING:
            case self::ROLE_THUMBNAIL:
                $target = $this->GetTarget('Attempting to get an id for an unspecified target');
                $hasId = (is_null($target)) ? isset($this->target->id) : false;
                if (! $hasId)
                    throw new \Exception('SimpleLayers MapFile Error: Attempting to get an id for a target without an id');
                $filePath = array($this->mapfileDir,'mapfile',$ini->name,$this->role,$id);
                
                break;
            case self::ROLE_SCREENSHOT:
            case self::ROLE_UNSPECIFIED:
            case self::ROLE_WMS:
                $filePath = array($this->tempdir,md5(microtime().mt_rand()));
                $modDate = 0;
                break;
        }

    }
}

?>