<?php

namespace formats;


use utils\OGR\OGR_KML_Util;

class KML extends LayerFormat
{
    public $inputTemplate = 'import/kml1.tpl';

    public $reimportTemplate = 'import/kml1.tpl';

    public function __construct()
    {
        $this->label = 'CSV Format';
        $this->ext = 'kml';
        $this->targetPattern = '*.[kK][mM][lL]';
        array_push($this->zipExts, 'kmz');
        array_push($this->moveExts, 'kmz', 'kml');
    }


    protected function GetOGRImportLayer($file, $layer)
    {
        $ogrKMLUtil = new OGR_KML_Util();
        $ogrKMLUtil->Import($file, $layer->id);
        return $ogrKMLUtil;
    }


    protected function ProcessErrorMessage($message)
    {
        switch ($message) {
        }
        return $message;
    }
}
