<?php

class WMSUtil {

    private $url;
    private $params = array();

    public function constructor($url) {
        $segs = explode('?', $url);

        $this->url = $url;
        if (count($segs) > 1) {
            $paramData = explode("&", $segs [1]);
            foreach ($paramData as $paramInfo) {
                $info = explode("=", $paramInfo);
                $param = $info [0];
                $val = (count($info) > 1) ? $info [1] : true;
                $this->$param = $val;
            }
        }
    }

    public function __set($what, $val) {
        switch ($what) {
            default :
                $this->params [$what] = $val;
                break;
        }
    }

    public function __get($what) {
        return $this->params [$what];
    }

    public function GetURL($params = NULL) {

        $params = is_null($params) ? $this->params : $params;
        $url = $this->url . '?';
        foreach ($params as $key => $val) {
            $url .= '&' . $key . '=' . urlencode($val);
        }
        return $url;
    }

    public function GetCapabilities() {
        $params = array_slice($this->params, 0);
        $params ['REQUEST'] = 'GetCapabilities';
        $params ['VERSION'] = '1.1.1';
        readfile($this->GetURL($params));
    }

    public function LoadCapabilities() {
        $params = array_slice($this->params, 0);
        $params ['REQUEST'] = 'GetCapabilities';
        $params ['VERSION'] = '1.1.1';
        $data = file_get_contents($this->GetURL($params));
        return new WMSCapabilities(new SimpleXMLElement($data));
    }

}

class WMSCapabilities {

    const SERVICE_NAME = 'Name';
    const SERVICE_TITLE = 'Title';
    const SERVICE_ABSTRACT = 'Abstract';
    const SERVICE_ONLINE_RESOURCE = 'OnlineResource';
    const SERVICE_CONTACT_INFORMATION = 'ContactInformation';
    const SERVICE_FEES = 'Fees';
    const SERVICE_ACCESS_CONSTRAINTS = 'AccessConstraints';
    const CAPABILITY_REQUEST = "Request";
    const CAPABILITY_EXCEPTION = "Exception";
    const CAPABILITY_VENDOR_SPEC = "VendorSpecificCapabilities";
    const CAPABILITY_USER_DEF_SYM = "UserDefinedSymbolization";
    const CAPABILITY_LAYER = "Layer";

    /* @var $xml SimpleXMLElement */

    private $xml;

    public function constructor(SimpleXMLElement $xml) {
        $this->xml = $xml;
    }

    public function __get($what) {
        return (string) @$this->xml [$what];
    }

    public function getService($item) {
        switch ($item) {
            case self::SERVICE_ONLINE_RESOURCE :
                $item = $this->xml->Service->$item;
                #$item = $item['xlink:href'];
                $atts = $item->attributes('xlink', true);
                return (string) $atts ['href'];
        }
        return (string) $this->xml->Service->$item;
    }

    public function getCapability($item) {
        switch ($item) {
            case self::CAPABILITY_REQUEST :
                return new WMSRequest($this->xml->Capability->$item);
            case self::CAPABILITY_LAYER :
                return new WMSLayer($this->xml->Capability->$item);
        }
        return $this->xml->Capability->$item;
    }

}

class WMSLayer {

    private $xml;
    private $parent = false;

    const BBOX = "bbox";
    const LAYER_NAME = 'Name';
    const LAYER_TITLE = 'Title';
    const LAYER_ABSTRACT = 'Abstract';
    const LAYER_KEYWORD_LIST = "KeywordList";
    const LAYER_SRS = "SRS";
    const LAYER_BOUNDING_BOX = "BoundingBox";
    const LAYER_DIMENSION = "Dimension";
    const LAYER_EXTENT = "Extent";
    const LAYER_ATTRIBUTION = "Attribution";
    const LAYER_AUTHORITY_URL = "AuthorityURL";
    const LAYER_IDENTIFIER = "Identifier";
    const LAYER_METADATA_URL = "MetadataURL";
    const LAYER_DATA_URL = "DataURL";
    const LAYER_FEATURE_LIST_URL = "FeatureListURL";
    const LAYER_STYLE = "Style";
    const LAYER_SCALE_HINT = "ScaleHint";
    const LAYER_LAYER = "Layer";

    private $isCRS = false;

    public function constructor(SimpleXMLElement $layer, $parent = null) {
        $this->xml = $layer;
        $this->parent = $parent;
    }

    public function getNode() {
        return $this->xml;
    }

    public function __get($what) {
        switch ($what) {
            case self::BBOX :
                $latlon = $this->__get(self::LAYER_BOUNDING_BOX);

                if (!is_null($latlon))
                    return $latlon;

                $geogbox = isset($this->xml->EX_GeographicBoundingBox) ? $this->xml->EX_GeographicBoundingBox : null;

                if (is_null($geogbox))
                    return is_null($this->parent) ? null : $this->parent->$what;
                $x1 = (float) $geogbox->westBoundLongitude;
                $x2 = (float) $geogbox->eastBoundLongitude;
                $y1 = (float) $geogbox->southBoundLatitude;
                $y2 = (float) $geogbox->northBoundLatitude;

                $latlon = array();
                if ($this->isCRS) {
                    $crs = (string) $this->CRS;
                    if ((substr($crs, 0, 4) == 'EPSG') || (substr($crs, 0, 3) == 'CRS')) {
                        $latlon ['miny'] = min($y1, $y2);
                        $latlon ['minx'] = min($x1, $x2);
                        $latlon ['maxy'] = max($y1, $y2);
                        $latlon ['maxx'] = max($x1, $x2);
                    }
                } else {
                    $latlon ['minx'] = min($x1, $x2);
                    $latlon ['miny'] = min($y1, $y2);
                    $latlon ['maxx'] = max($x1, $x2);
                    $latlon ['maxy'] = max($y1, $y2);
                }

                return array('srs' => $this->SRS, 'bbox' => implode(',', $latlon));

            case self::LAYER_BOUNDING_BOX :
                if (!isset($this->xml->BoundingBox)) {

                    $srs = $this->SRS;

                    $latlon = $this->LatLonBoundingBox;
                    if (is_null($latlon))
                        return null;
                    if ($latlon == "")
                        return null;
                    $bbox = implode(',', array((float) $latlon ['minx'], (float) $latlon ['miny'], (float) $latlon ['maxx'], (float) $latlon ['maxy']));
                    return array('srs' => $this->SRS, 'bbox' => $bbox);
                } elseif (isset($this->xml->BoundingBox)) {
                    $latlon = $this->xml->$what;
                    $bbox = implode(',', array((float) $latlon ['minx'], (float) $latlon ['miny'], (float) $latlon ['maxx'], (float) $latlon ['maxy']));
                    return array('srs' => $this->SRS, 'bbox' => $bbox);
                } else {
                    return null;
                }
            case 'SRS' :
                if (!is_null($this->CRS)) {
                    $this->isCRS = true;
                    return (string) $this->CRS;
                }
                break;
            case self::LAYER_LAYER :
                $layerList = array();
                foreach ($this->xml->Layer as $layer) {
                    $layerList [] = new WMSLayer($layer, $this);
                }
                return $layerList;
                break;
            case 'queryable' :
            case 'opaque' :
            case 'nosubsets' :
                return (isset($this->xml [$what])) ? 0 : (int) $this->xml [$what];
            case 'cascaded' :
            case 'fixedWidth' :
            case 'fixedHeight' :
                return (string) isset($this->xml [$what]) ? $this->xml [$what] : '';
        }
        $retval = isset($this->xml->$what) ? (string) $this->xml->$what : null;
        if (is_null($retval) && isset($this->parent))
            return $this->parent->$what;
        return $retval;
    }

}

class WMSRequest {

    private $xml;

    const GET_CAPABILITIES = 'GetCapabilities';
    const GET_MAP = 'GetMap';
    const GET_FEATURE_INFO = 'GetFeatureInfo';
    const DESCRIBE_LAYER = 'DescribeLayer';
    const GET_STYLES = 'GetStyels';
    const PUT_STYLES = 'PutStyels';

    public function constructor(SimpleXMLElement $request) {
        $this->xml = $request;
    }

    public function getRequest($what) {
        $request = $this->xml->$what->DCPType->HTTP;
        $info = array();
        if (isset($request->Get)) {
            $info ['Get'] = $request->Get->OnlineResource ['href'];
        }
        if (isset($request->Post)) {
            $info ['Post'] = $request->Post->OnlineResource ['href'];
        }
        return $info;
    }

}

?>