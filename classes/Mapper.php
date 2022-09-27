<?php

use symbols\Symbol;
use symbols\Polygon;
use symbols\Lines;
use symbols\Point;
use model\mapping\Labels;
use model\mapping\PixoSpatial;
use model\logging\Log;
use model\mapping\Renderer;
use utils\ParamUtil;
use utils\ClassCtr;
use utils\ColorUtil;
use model\mapping\ALayer;
use utils\SymbolSizes;
use \model\mapping\HilightDriver;

/**
 * See the Mapper class documentation.
 */

/**
 * A class for generating map images and fetching other info about the map-generation context.
 * Its main function is to take a list of layers and render a map image. Secondary functions include
 * being the repository for the list of fonts, list of symbols, and other map-related stuff.
 *
 * A Mapper object can be obtained by the World's getMapper() method. Typically, you will then
 * set its attributes (height, width)s add some layers, then call its render() function to
 * draw the image. The imagethumbnail dispatcher is a great example of real-life usage. @see _dispatch_imagethumbnail()
 *
 * @see _dispatch_imagethumbnail() Public attributes:
 *      - width -- Integer, the width of the image to generate.
 *      - height -- Integer, the height of the image to generate.
 *      - screenshot -- Boolean, indicating whether the image should be treated as a screenshot instead of a map layer. This sets the background color, output format, etc.
 *      - geotiff -- Boolean, indicating whether the image should be treated as a screenshot and then downloaded as a GeoTIFF. If this is set, it will override the behavior of the 'screenshot' flag.
 *      - extent -- A 4-tuple, representing the desired spatial extent to zoom in on. Note that not all layers will be visible or will look good at all spatial extents.
 *      - projection -- A PROJ4 string or EPSG:xxxx string, indicating the projection to be used when rendering the map.
 *      - filter_gids -- Filter the output, drawing only features which match gid = value.
 *      - filter_color -- Filter the output (see above), and color the matching features this color instead of the usual.
 *     
 *      Other assignables to the Mapper instance:
 *      - $m->screenshot = true; Configure the image generation for screenshots.
 *      - $m->geotiff = true; Configure the image generation for Georeferenced TIFF output.
 *      - $m->thumbnail = true; Configure the image generation for lower-quality JPEG thumbnail output.
 *      - $m->lowquality = true; Configure the image generation for good quality but the older/faster renderer with poor line quality.
 *     
 *     
 *      For more info on filtering, see the documentation in renderlayer.php Note that filtering only works on vector
 *      and ODBC layers, and should only be used when a single layer is being rendered.
 *     
 *     
 *     
 */
class Mapper {

    /**
     *
     * @ignore
     *
     */
    private $world;
    static $MODE_LATLON = "latlon";
    static $MODE_WEB = 'web';

    /**
     *
     * @ignore
     *
     */
    public $width;

    /**
     *
     * @ignore
     *
     */
    public $height;

    /**
     *
     * @ignore
     *
     */
    public $filter_field;

    /**
     *
     * @ignore
     *
     */
    public $filter_value;

    /**
     *
     * @ignore
     *
     */
    public $extent;

    /**
     * @ ignore
     */
    public $isDynamic;

    /**
     * @ ignore
     */
    public $quantize;

    /**
     * @ ignore
     */
    public $interlace;

    /**
     * @ ignore
     */
    public $bgcolor;

    /**
     * @ ignore;
     */
    public $projection;
    public $legendMode = false;

    /**
     *
     * @ignore
     *
     */
    public $map;
    private $tempdir;
    public $debugMapFile = false;
    private $mapFileName;
    private $jsPath = '';
    public $mode = 'latlon';
    private $hilightDriver;
    private $hilightMode = null;
    public $globalBuffer = 0;
    public $globalBufferPt = null;
    public $globalBufferBbox = null;
    public $globalInputBBOX = null;
    public $globalInputType = 'feature';
    public $globalCriteria = null;

    /**
     *
     * @var array 
     */
    private $layers;

    function __construct($world = null, $isDynamic = false) {
        $world = System::Get();
        $ini = System::GetIni();

        $this->world = $world;
        $this->width = 144;
        $this->height = 144;
        $this->extent = array(
            0,
            0,
            0,
            0
        );
        $this->mapfile = '';
        $this->layers = array();
        $this->screenshot = false;
        $this->geotiff = false;
        $this->thumbnail = false;
        $this->lowquality = false;
        $this->projection = null;
        $this->map = null;
        $this->filter_gids = null;
        $this->filter_color = null;
        $this->isDynamic = $isDynamic;
        $this->quantize = false;
        $this->interlace = false;
        $this->fontsdir = $ini->maps_fontsdir;
        $this->symbolFile = WEBROOT . $ini->maps_symbolfile;
        $this->jsPath = WEBROOT . $ini->maps_js_path;
        $this->tempdir = $ini->tempdir;
        $this->tempurl = $ini->tempurl;
        $this->mapfileDir = $ini->mapfiledir;
    }

    function SetHilightMode(bool $onOff) {
        $this->hilightMode = $onOff;
    }

    function SetMode($mode = null) {
        if ($mode === null)
            $mode = self::$MODE_LATLON;
        $this->mode = $mode;
    }

    function SetRenderer(Renderer $renderer) {
        $this->layers = $renderer;
    }

    // ///
    // /// core methods for accepting layers and generating an image
    // ///

    /**
     * What mapfile will the Mapper use as it is presently configured?
     *
     * @return array A two-item array: The mapfile which will represent this request, and whether the (existing mapfile if it exists) is stale and needs regeneration.
     */
    function _this_mapfile() {

        // determine the mapfile to be generated
        $firstlayer = $this->layers[0]['layer'];
        $firstclass = get_class($firstlayer);
        $ini = System::GetIni();
        switch ($firstclass) {
            case 'ProjectLayer':
                $mapfile = sprintf("%s%s-%s-%s-%d.map", $this->mapfileDir, 'mapfile', $ini->name, 'projectlayer', $firstlayer->id);
                $mtime = $firstlayer->project->last_modified_unix;
                break;
            case 'Layer':
                $mapfile = sprintf("%s%s-%s-%s-%d.map", $this->mapfileDir, 'mapfile', $ini->name, 'layer', $firstlayer->id);
                $mtime = $firstlayer->last_modified_unix;
                break;
            default:
                $mapfile = sprintf("%s%s.map", $this->mapfileDir, md5(microtime() . mt_rand()));
                $mtime = 0;
                break;
        }
        // look for excuses to force the generation

        $stale = false;
        if (!$stale and!is_file($mapfile))
            $stale = true; // mapfile doesn't exist
        if (!$stale and!filesize($mapfile))
            $stale = true; // mapfile is blank? not good
        if (!$stale and sizeof($this->layers) > 1)
            $stale = true; // more than 1 layer, so not storable
        if (!$stale and filemtime($mapfile) <= $mtime)
            $stale = true; // the Project or Layer has been modified
        return array(
            $mapfile,
            $stale
        );
    }

    function _setRGBFromColorString($color, &$r, &$g, &$b) {
        if (substr($color, 0, 1) == '#')
            $color = substr($color, 1);
        if ($color == "trans") {
            $r = $g = $b = - 1;
            return;
        }

        // convert the #rrggbb colors into R,G,B
        $r = hexdec(substr($color, - 6, 2));
        $g = hexdec(substr($color, - 4, 2));
        $b = hexdec(substr($color, - 2, 2));

        if ($r == 255 && $g == 255 && $b == 255) {
            $r = $g = $b = 254;
        }
    }

    // ///
    // /// core methods for accepting layers and generating an image
    // ///

    /**
     * Draw the map image, and return the URL of the generated image.
     * Typically, this will be the last method called, after extents are set and layers are added.
     *
     * @return string The URL of the generates map image.
     */
    function _generate_mapfile($forceGeneration = true, $classIndex = null, $dataWKT = null) {
        $ini = System::GetIni();
        // what mapfile are we using, and should we override $force ?
        list ($mapfile, $force) = $this->_this_mapfile();
        if ($forceGeneration) {

            $force = true;
        }
        // if it's not forced, just return a handle to the existing mapfile
        // if (!$force && !$this->isDynamic)
        // return new mapObj ( $mapfile );
        // this defines the size of point symbols for each size class
        // it's an array of arrays, each value itself being a 2-item array for the outline and fill
        // the width of polygon outlines foreach size class
        // initialize the map: projection, etc.
        if (!$this->map)
            $this->init();
        $this->map->setConfigOption('MS_ERRORFILE', '/var/log/mapserv/errors.log');
        $this->map->set('maxsize',4000);
        $this->map->set('debug', 5);
        
        // add the layers

        $tmpFiles = array();

        $numLayers = is_array($this->layers) ? count($this->layers) : $this->layers->Count();


        $filteringFeatures = false;

        $hilightingNormal = false;
        $filtering = false;
        $hilighting = false;
        $hilightingStage = HilightDriver::COMPLETED;
        $hilightLayer = null;
        /* $hilightColors = array(
          'underlay' => array('opacity' => 0, 'glopacity' => 1.0, 'glowColor' => '#ffff00', 'filter_color' => '#ffff00'),
          'natural' => array('opacity' => 1, 'glopacity' => 0, 'glowColor' => 'trans', 'filter_color' => 'trans'),
          'overlay' => array('opacity' => 0, 'glopacity' => 1.0, 'glowColor' => '#ffff00', 'filter_color' => '#ffff00')
          ); */
        for ($i = 0; $i < $numLayers; $i++) {

            if ($i > 2) {
                die('too many layers:' . $numLayers);
            }
            // fetch the entry and make some easy-access scalars
            $lyrEntry = $this->layers[$i];

            $layer = $lyrEntry['layer'];
            list ($player, $l) = ALayer::GetLayers($layer);
            $geomType = intval($l->geomtype);
            $opacity = (int) (abs($lyrEntry['opacity']) * 100);
            $opacity255 = isset($lyrEntry['opacity']) ? (int) (abs($lyrEntry['opacity']) * 255) : 0;

            $glopacity = isset($lyrEntry['glopacity']) ? (int) ($lyrEntry['glopacity'] * 100) : 0;
            $glopacity255 = isset($lyrEntry['glopacity']) ? (int) ($lyrEntry['glopacity'] * 255) : 0;
            $glowColor = isset($lyrEntry['glowColor']) ? $lyrEntry['glowColor'] : 'trans';


            // Determine whether to filter by GIDs
            $filtering = !is_null($layer->filter_gids);
            $buffer = 0;
            $buffering = isset($lyrEntry['buffer']) && isset($layer->filter_gids);
            if ($this->globalBuffer !== 0) {
                $buffering = true;
            }



            if ($buffering) {
                if ($this->globalBuffer !== 0) {
                    $buffer = $this->globalBuffer;
                } else {
                    $buffer = $lyrEntry['buffer'];
                }
                switch ($buffer) {
                    case 0:
                    case null;
                        $buffering = false;
                        break;
                }
            }

            // Determine whether to highlight
            $hilighting = false;

            if (isset($lyrEntry['hilighting'])) {
                if ($lyrEntry['hilighting'] != false) {
                    $hilighting = true;
                }
            } else {
                if (($numLayers === 1) && ($filtering && ($this->globalInputType === 'feature'))) {
                    $hilighting = true;
                }
                if ($numLayers === 1) {

                    if (($this->globalInputType === 'point') && !is_null($this->globalBufferPt)) {

                        $hilighting = true;
                    }
                    if (($this->globalInputType === 'rect') && !is_null($this->globalInputBBOX)) {

                        $hilighting = true;
                    }
                }
            }




            $mainColor = $layer->filter_color;
            $outlineOnly = substr($lyrEntry['opacity'], 0, 1) == "-";
            $filterColor = $layer->filter_color;

            if ($outlineOnly)
                $lyrEntry['opacity'] = substr($entry['opacity'], 1);

            $labels = $lyrEntry['labels'];

            $labelsHelper = Labels::GetLabelsFromALayer($layer);

            $labelField = $lyrEntry['labelField'];

            // Create $l, which points to the Layer object. If this layer entry is a Layer, this is the same thing.
            // But if it's a ProjectLayer, we need the Layer object hidden inside.



            $layertype = intval($l->type);

            $needHilightDriver = true;
            if (!is_null($this->hilightDriver)) {
                $hilighting = $this->hilightDriver->GetHilightStage($i) < HilightDriver::COMPLETED;
                if ($hilighting === true) {
                    $needHilightDriver = false;
                }
            }
            if ($buffering === true) {

                $hilighting = false;
            }

            if ($needHilightDriver) {
                $this->hilightDriver = new HilightDriver(
                        $layer->filter_gids,
                        $layer->filter_color,
                        floatval($lyrEntry['opacity']),
                        floatval($lyrEntry['glopacity']),
                        $hilighting
                );
            }
            $maplayer = new layerObj($this->map);

            $layerEntry['mapLayer'] = $maplayer;
            $this->layers[$i] = $layerEntry;

            // $maplayer->set('opacity', $opacity);
            // if($layertype == LayerTypes::WMS) {

            if ($layertype !== LayerTypes::WMS) {
                // $maplayer->setProjection($this->GetProjection(self::$MODE_WEB));
                //} else {
                $maplayer->setProjection($this->GetProjection(self::$MODE_LATLON));
            }

            $layerOpacity = null;

            if ($hilighting) {

                $hilightingStage = $this->hilightDriver->GetHilightStage($i);

                if ($hilightingStage === HilightDriver::UNINITIALIZED) {
                    // If hilighting (regardless of filtering)  set up the natural and overlay highlight layers
                    // add them after the current layer in the Mapper's layers list.
                    $this->hilightDriver->SetHilightOffset($i);

                    $this->addLayer($layer, 1.0, $labels, $labelField, false, null, null, $i + 1);
                    $naturalLayer['filter_gids'] = $layer->filter_gids;
                    $labelStyle = isset($lyrEntry['label_style']) ? $lyrEntry['label_style'] : null;

                    $this->addLayer($layer, $lyrEntry['opacity'], $labels, $labelField, null, null, $i + 2);
                    $hilightLayer = (object) $this->layers[$i + 2];
                    if (isset($layer->filter_gids)) {
                        $hilightLayer->filter_gids = $layer->filter_gids;
                    }
                    $hilightLayer->label_style = $labelStyle;

                    $labels = false;
                    $numLayers += 2;
                    $filteringFeatures = true;
                    $hilightingStage = $this->hilightDriver->GetHilightStage($i);
                }
                $hilightingNormal = ($hilightingStage === HilightDriver::NATURAL);
                switch ($hilightingStage) {
                    case HilightDriver::UNDERLAY:
                        if ($this->hilightDriver->underlay['filter_color'] === 'trans') {
                            #continue 2;
                        }
                        if ($this->hilightDriver->underlay['glopacity'] <= 0) {
                            #continue 2;
                        }
                        $lyrEntry['alias'] = $l->name . ' - Hilight Uderlay';
                        $layerOpacity = $glopacity;
                        break;
                    case HilightDriver::OVERLAY:
                        if ($this->hilightDriver->underlay['filter_color'] === 'trans') {
                            #continue 2;
                        }
                        if ($this->hilightDriver->underlay['opacity'] <= 0) {
                            #continue 2;
                        }
                        $layerOpacity = $opacity;
                        $lyrEntry['alias'] = $l->name . ' - Hilight Overlay';

                        break;
                    case HilightDriver::NATURAL:
                        $lyrEntry['alias'] = $l->name . ' - Hilight Natural';
                        $layerOpacity = $opacity;
                        break;
                }
            }
            $hilightingStage = $this->hilightDriver->GetHilightStage($i);
            $type = '';
            if ($layertype == LayerTypes::WMS) {
                $type = MS_LAYER_RASTER;
            } elseif (($layertype == LayerTypes::VECTOR) or ( $layertype == LayerTypes::RELATIONAL)) {
                // vector layers require the PostGIS connection string, and also all the coloscheme entries!
                $type = GeomTypes::ToMSType($geomType, true);
            } elseif ($layertype == LayerTypes::RASTER) {
                // raster layers are actually pretty simple
                $type = MS_LAYER_RASTER;
            }
            if (is_null($layerOpacity)) {
                $layerOpacity = 100;
            }

            $maplayer->set('opacity', $layerOpacity);
            $maplayer->set('name', !isset($lyrEntry['alias']) ? $l->name : $lyrEntry['alias']);
            $maplayer->set('status', MS_DEFAULT);


            // the layer header, very minimal

            $proj4 = $this->GetProjection();

            if ($l->minscale && !$lyrEntry['ignoreScale']) {
                $scale = (double) $l->minscale * 10;
                #$maplayer->set('maxscaledenom', $scale);
            }

            // the TYPE, CONNECTIONTYPE, and DATA tags
            if ($layertype == LayerTypes::WMS) {

                $maplayer->set('type', MS_LAYER_RASTER);

                // $url = $this->_renderWebRemoteWMS ( $i );
                // break;
                $urlParts = $this->_generateRemoteWMSURL($i, true, $layer);

                $url = urldecode($urlParts->url);

                $urlParams = $urlParts->params;

                $extString = "EXTENT " . implode(' ', $this->extent);
                $ext = $this->extent;
                // $maplayer->extent->setExtent($ext[0],$ext[1],$ext[2],$ext[3]);
                // $maplayer->set ( 'data', $url );
                $epsg = ($this->mode === self::$MODE_WEB) ? 3857 : 4326;

                $maplayer->setMetaData('wms_srs', 'EPSG:' . $epsg);
                $maplayer->setMetaData('wms_format', $urlParams['FORMAT']);
                $maplayer->setMetaData('wms_server_version', ParamUtil::Get($urlParams, 'VERSION', '1.1.1'));
                $maplayer->setMetaData('wms_name', $urlParams['LAYERS']); // str_replace(',', ' ', $urlParams['LAYERS']));
                //$maplayer->setMetaData('wms_essential', '1');
                $maplayer->setConnectiontype(MS_WMS);

                $maplayer->set('connection', $url);


                // $maplayer->setProjection($this->map->getProjection());
                // $maplayer->setUnits(MS_DD);
                /*
                 * // clean up the WMS URL, because they usually leave a lot of params in, e.g. size, version, format, ...
                 * preg_match ( '/&?LAYERS=([^&]+)/i', $l->url, $wmslayername );
                 * @$wmslayername = $wmslayername [1];
                 * preg_match ( '/&?FORMAT=([^&]+)/i', $l->url, $imageformat );
                 * @$imageformat = $imageformat [1];
                 *
                 * if (! $imageformat)
                 * $imageformat = 'image/png';
                 *
                 * $url = $l->url;
                 * $url = preg_replace ( '/&?VERSION=[^&]+/i', '', $url );
                 * $url = preg_replace ( '/&?SRS=[^&]+/i', '', $url );
                 * $url = preg_replace ( '/&?SERVICE=[^&]+/i', '', $url );
                 * $url = preg_replace ( '/&?REQUEST=[^&]+/i', '', $url );
                 * $url = preg_replace ( '/&?FORMAT=[^&]+/i', '', $url );
                 * $url = preg_replace ( '/&?LAYERS=[^&]+/i', '', $url );
                 * $url = preg_replace ( '/&?BBOX=[^&]+/i', '', $url );
                 * $url = preg_replace ( '/&?WIDTH=[^&]+/i', '', $url );
                 * $url = preg_replace ( '/&?HEIGHT=[^&]+/i', '', $url );
                 * $url .= '&TRANSPARENT=TRUE&';
                 * $url = str_replace ( '?&', '?', $url );
                 * // now add the WMS layer to the mapfile
                 * $maplayer->set ( 'type', MS_LAYER_RASTER );
                 * $maplayer->setConnectiontype( MS_WMS );
                 * $maplayer->set ( 'connection', $url );
                 * $maplayer->setMetaData ( 'wms_srs', 'EPSG:4326' );
                 * $maplayer->setMetaData ( 'wms_format', $imageformat );
                 * $maplayer->setMetaData ( 'wms_server_version', '1.1.1' );
                 * $maplayer->setMetaData ( 'wms_name', $wmslayername );
                 */
            } elseif (($layertype == LayerTypes::VECTOR) or ( $layertype == LayerTypes::RELATIONAL)) {
                // vector layers require the PostGIS connection string, and also all the coloscheme entries!


                $maplayer->setConnectionType(MS_POSTGIS);


                $maplayer->set('connection', "host={$ini->pg_host} port=5432 user={$ini->pg_admin_user} dbname={$ini->pg_sl_db} password=" . $ini->pg_admin_password);

                $gids = preg_replace('/[^\,\d]/', '', $layer->filter_gids);

                $notFilter = (substr($gids, 0, 1) == "-") ? 'not ' : '';


                $gidQuery = $l->url;
                $ext = $this->extent;
                if ($gids === '') {
                    $gids = null;
                }


                if (!is_null($dataWKT)) {
                    $maplayer->set('data', "the_geom from (select 1 as gid, ST_GeometryFromText('$dataWKT') as the_geom) as q1 USING UNIQUE gid USING SRID=4326");
                    $maplayer->getextent()->setextent(floatval($ext[0]), floatval($ext[1]), floatval($ext[2]), floatval($ext[3]));
                } elseif (($buffering === true) && !is_null($gids)) {
                    $SRID = $this->mode === self::$MODE_WEB ? 3857 : 4326;
                    $query = "the_geom2 from (select 1 as gid,ST_Buffer(ST_UNION(the_geom)::geography,$buffer)::geometry as the_geom2 from {$l->url} where gid in ($gids)) as subquery USING UNIQUE gid USING SRID=$SRID";
                    $maplayer->set('data', $query);
                    if (!is_null($gids)) {
                        #$maplayer->setProcessing('NATIVE_FILTER=' . "gid in ($gids)");
                    }


                    $query = <<<QUERY
with geomQuery as 
    (select ST_Buffer(ST_TRANSFORM(the_geom,$SRID),$buffer) as the_geom from {$l->url} where gid in ($gids)) 
    select ST_XMin(the_geom) as minx,ST_YMin(the_geom) as miny,ST_XMax(the_geom)as maxx,ST_YMax(the_geom) as maxy from geomQuery
QUERY;

                    #$result = System::GetDB()->GetRow($query);
                    #$ext = array_values($result);
                    #var_dump($result);
                } elseif (($buffering === true) && is_null($gids)) {
                    $SRID = $this->mode === self::$MODE_WEB ? 3857 : 4326;
                    $wkt = '';
                    if (!is_null($this->globalBufferPt)) {
                        $wkt = "POINT(" . $this->globalBufferPt[0] . ' ' . $this->globalBufferPt[1] . ')';
                    } elseif (!is_null($this->globalBufferBbox)) {
                        list ($minX, $minY, $maxX, $maxY) = $this->globalBufferBbox;
                        $wkt = "POLYGON(($minX $minY,$minX $maxY,$maxX $maxY,$maxX $minY,$minX $minY))";
                    }

                    if ($wkt !== "") {
                        $buffer = $this->globalBuffer;
                        $query = "the_geom from (select 1 as gid, ST_Buffer(ST_GeographyFromText('$wkt'),$buffer,'quad_segs=8')::geometry as the_geom) as q1 using unique gid using srid=$SRID";
                        $maplayer->set('data', $query);
                        $maplayer->getextent()->setextent(floatval($ext[0]), floatval($ext[1]), floatval($ext[2]), floatval($ext[3]));
                    }
                } elseif (!is_null($this->globalInputBBOX)) {
                    $SRID = $this->mode === self::$MODE_WEB ? 3857 : 4326;
                    list ($minX, $minY, $maxX, $maxY) = $this->globalInputBBOX;
                    $wkt = "POLYGON(($minX $minY,$minX $maxY,$maxX $maxY,$maxX $minY,$minX $minY))";

                    if ($wkt !== "") {
                        $buffer = $this->globalBuffer;
                        $query = "the_geom from (select 1 as gid, ST_GeographyFromText('$wkt')::geometry as the_geom) as q1 using unique gid using srid=$SRID";
                        $maplayer->set('data', $query);
                        $maplayer->getextent()->setextent(floatval($ext[0]), floatval($ext[1]), floatval($ext[2]), floatval($ext[3]));
                    }
                } elseif (($this->globalInputType == 'point') && !is_null($this->globalBufferPt)) {
                    $SRID = $this->mode === self::$MODE_WEB ? 3857 : 4326;

                    list($lon, $lat) = $this->globalBufferPt;
                    $wkt = "POINT($lon $lat)";
                    if ($wkt !== "") {
                        $query = "the_geom from (select 1 as gid, ST_GeographyFromText('$wkt')::geometry as the_geom) as q1 using unique gid using srid=$SRID";
                        $maplayer->set('data', $query);
                        //$maplayer->getextent()->setextent(floatval($ext[0]), floatval($ext[1]), floatval($ext[2]), floatval($ext[3]));
                    }
                } else {
                    $SRID = $this->mode === self::$MODE_WEB ? 3857 : 4326;
                    $maplayer->set('data', "the_geom from \"{$l->url}\" using unique gid using srid=$SRID");
                    if ($filtering) {
                        if (!is_null($gids)) {
                            $maplayer->setProcessing('NATIVE_FILTER=' . "gid in ($gids)");
                        }
                    }
                    $maplayer->getextent()->setextent(floatval($ext[0]), floatval($ext[1]), floatval($ext[2]), floatval($ext[3]));
                }

                if ($this->globalInputType === 'point') {
                    if (!is_null($this->globalBufferPt)) {
                        $geomType = GeomTypes::POINT;
                    }
                } elseif ($this->globalInputType === 'rect') {
                    if (!is_null($this->globalInputBBOX)) {
                        $geomType = GeomTypes::POLYGON;
                    }
                } else {
                    $geomType = $l->geomtype;
                }

                $attributetypes = $l->getAttributes();
                if (!isset($attributetypes[$labelField])) {
                    $labels = false;
                }

                // add the colorscheme entries

                if ($buffering) {
                    $geomType = GeomTypes::POLYGON;
                }
                $maplayer->set('type', GeomTypes::ToMSType($geomType));
                $geomtypestring = GeomTypes::GetGeomType($geomType); // $eGeomTypes[$l->geom_type];

                $entries = array();

                if (!$buffering && (in_array($this->globalInputType, array('feature', null)))) {

                    $colorscheme = is_null($player) ? $layer->colorscheme->getAllEntries() : $player->colorscheme->getAllEntries();
                    if (!is_null($classIndex)) {
                        $uniqueEntries = is_null($player) ? $layer->colorscheme->getUniqueCriteria() : $player->colorscheme->getUniqueCriteria();
                        $targetClass = $uniqueEntries[$classIndex]['description'];
                    }
                    $entries = array_slice($colorscheme, 0, $ini->max_colorclasses); // just in case they somehow exceeded it
                }
                if (!is_null($this->globalCriteria)) {

                    $entries = $this->globalCriteria;
                    foreach ($entries as $key => $val) {
                        $entry = (object) $val;
                        $entry->stroke_color = $entry->stroke;
                        $entry->fill_color = $entry->fill;
                        $entry->symbol_size = $entry->size;
                        $entries[$key] = $entry;
                    }
                }
                if (!$buffering && ($this->globalInputType === 'point')) {
                    if (!is_null($this->globalBufferPt)) {
                        
                    }
                }

                $lastClass = null;
                // if (! $filtering) {
                $criteriaGroup = array();
                $classCtr = 0;
                $nameGroup = array();

                foreach ($entries as $entry) {

                    $name = $entry->description;
                    if (!is_null($classIndex)) {
                        if ($name !== $targetClass) {
                            continue;
                        }
                    }
                    $options = array(
                        null,
                        ""
                    );
                    $noName = (in_array($name, $options));
                    if ($noName)
                        $name = $entry->criteria3;
                    $noName = (in_array($name, $options));
                    // $class = new classObj($maplayer);
                    if ($noName) {
                        $name = "Default"; // ($classCtr > 0 ) ? "All Other Values" : "All Features";
                    }
                    if (isset($nameGroup[$name])) {
                        $class = $nameGroup[$name];
                        $hadClass = true;
                        $lastClass = $class;
                        $firstInGroup = false;
                    } else {
                        // if($filtering && $classCtr ==1 ) break;
                        // var_dump('new_group');
                        $class = new classObj($maplayer);
                        $class->name = $name;
                        $nameGroup[$name] = $class;
                        $hadClass = false;
                        $firstInGroup = true;
                        $lastClass = $class;
                        $classCtr++;
                    }
                    
                    $criteria = $entry->criteria1 . $entry->criteria2 . $entry->criteria3;

                    if ($criteria == 'default') {
                        $criteria = "";
                    }
                    if ($this->legendMode === true) {
                        $criteria = "";
                    }
                    $strokeOpacity = (isset($entry->strokeOpacity)) ? $entry->strokeOpacity : null;
                    $fillOpacity = (isset($entry->fillOpacity)) ? $entry->fillOpacity : null;
                    // $class->set('name', $name);
                    // $symbols = explode('+',$entry->symbol);
                    // $symbol ="{$geomtypestring}_{$symbols[0]}";
                    $symbolName = $entry->symbol;
                    $symbolStrokeName = (isset($entry->symbol_stroke)) ? $entry->symbol_stroke : null;
                    $symbol = $symbolName;
                    // if($geomtypestring=='line') $symbol = $symbolName = 'complex_rail';
                    $stroke_color = $entry->stroke_color;

                    // fetch the colors. If this is a filter operation, replace the fill color
                    $fill_color = $entry->fill_color;
                    $this->hilightDriver->SetNaturalInfo($stroke_color, $fill_color, (($hilighting) ? 1.0 : $opacity / 100.0), null);


                    $fr = $fg = $fb = $sr = $sg = $sb = 0;


                    $this->hilightDriver->SetStyleVars(HilightDriver::NATURAL, $sr, $sg, $sb, $fr, $fg, $fb, $xpacity);


                    #ColorUtil::Web2RGB($fill_color, $fr, $fg, $fb);
                    // $this->_setRGBFromColorString($fill_color, $fr, $fg, $fb);
                    #ColorUtil::Web2RGB($stroke_color, $sr, $sg, $sb);
                    // $this->_setRGBFromColorString($stroke_color, $sr, $sg, $sb);

                    $db = System::GetDB(System::DB_ACCOUNT_SU);

                    // the criteria
                    // if (! $filtering) {
                    if ($this->legendMode) {
                        $class->setExpression('');
                    } elseif ($entry->criteria1 and $entry->criteria2) {
                        
                        // $criteria = $entry->criteria1 . $entry->criteria2 . $entry->criteria3;
                        $criteriaGroup[$criteria] = $class;
                        if ($entry->criteria2 == 'contains') {
                            $maplayer->set('classitem', $entry->criteria1);
                            $class->setExpression('/' . $entry->criteria3 . '/i');
                        } elseif (@$attributetypes[$entry->criteria1] == DataTypes::TEXT) {
                            $class->setExpression("(\"[{$entry->criteria1}]\" {$entry->criteria2} \"{$entry->criteria3}\")");
                        } elseif (@$attributetypes[$entry->criteria1] == DataTypes::BOOLEAN) {
                            if ($entry->criteria3 == "t") {
                                $class->setExpression("(\"[{$entry->criteria1}]\" {$entry->criteria2} \"true\")");
                            } else {
                                $class->setExpression("(\"[{$entry->criteria1}]\" {$entry->criteria2} \"false\")");
                            }
                        } else {
                            $class->setExpression("([{$entry->criteria1}] {$entry->criteria2} {$entry->criteria3})");
                        }
                    } else {
                        $class->setExpression("");
                    }
                    // }

                    /*
                     * if($gids !='') {
                     * $class->setExpression( "gid IN '$gids' ");
                     * }
                     */

                    // the cosmetic definitions
                    $label_position = MS_AUTO;
                    $label_angle = 0;
                    $angleMode = false;
                    $minfeaturesize = 10;

                    switch (0 + $geomType) {
                        case GeomTypes::POINT:

                            $size = $entry->symbol_size;

                            // list ($size1, $size2) = SymbolSize::GetSymbolSizes($entry->symbol, $size);

                            if ($filteringFeatures && !$hilightingNormal) {

                                model\mapping\mapfile\Point::SetHilightedSymbol($i, $this->map, $class, $symbol, $entry, $this->hilightDriver, $i, $outlineOnly);
                            } else {

                                \model\mapping\mapfile\Point::SetSymbol($this->map, $class, $symbol, $entry, $this->hilightDriver, $i);
                            }
                            // \model\mapping\mapfile\Point::SetSymbol($this->map, $class, $symbol, $entry, $stroke_color, $fill_color, $opacity);
                            if ($firstInGroup) {
                                // \model\mapping\mapfile\Point::SetLabel($label);
                                // $label->set('anglemode', MS_TRUE);
                            }
                            break;
                        case GeomTypes::POLYGON:
                            $entrySize = $entry->symbol_size;
                            #var_dump("setting polygon : ".$entrySize);
                            $label_position = MS_AUTO;
                            $minfeaturesize = MS_AUTO;

                            Polygon::SetPolygon($this->map, $class, $symbolName, $entrySize, $this->hilightDriver, $i, $hadClass, $fillOpacity, $strokeOpacity);
                            break;
                        case GeomTypes::LINE:
                            $size = $entry->symbol_size;
                            $label_position = MS_AUTO2;
                            $label_angle = MS_AUTO2;
                            $angleMode = true;
                            Lines::SetLine($this->map, $class, $symbolName, $size, $this->hilightDriver, $i);
                            break;
                    }
                    // labels
                    // $labels = false;
                    // if ($labels) {
                    $labelStyleInfo = '';
                    $hasLabels = !is_null($labelField);

                    if (!is_null($hilightLayer) && (!is_null($hilightLayer->label_style))) {
                        $labelStyleInfo = $hilightLayer->label_style;
                    } else {
                        //if (isset($entry->label_style)) {
                        $labelStyleInfo = (!is_null($entry->label_style)) ? $entry->label_style : $layer->label_style;
                        //} else {
                        //     $labelStyleInfo = $layer->label_style;
                        //}
                    }

                    if (!$labelStyleInfo) {
                        $hasLabels = false;
                    }

                    if (!$hadClass && $hasLabels) {
                        $maplayer->set('labelitem', $labelField);
                        if ($filtering) {
                            $labelFilterStyler = new Labels($labelStyleInfo, $label_position);
                        }
                    }

                    if ($hasLabels) {


                        if (!$hadClass) {
                            $targetHilightStage = HilightDriver::OVERLAY;
                            #var_dump('stages');

                            if ($this->hilightDriver->hilighting === true) {

                                $overlay = $this->hilightDriver->overlay;

                                if ($overlay['opacity'] === 0.0) {
                                    $targetHilightStage = HilightDriver::NATURAL;
                                }
                            }

                            $labelStyleInfoFiltered = $labelStyleInfo;
                            $label = new labelObj();
                            $forceLabels = ($this->hilightDriver->hilighting === true) ? 'true' : 'false';

                            $labelForce = <<<UPDATE
LABEL
force $forceLabels
END
UPDATE;
                            $label->updateFromString($labelForce);
                            $forceLabel = 'group'; // ($this->hilightDriver->hilighting) ? 'group' : (MS_FALSE;

                            if ($angleMode === true) {
                                $label->set('anglemode', MS_TRUE);
                            }
                            if ($geomType != GeomTypes::POINT) {
                                $label->set('angle', $label_angle);
                            }
                            $labelStyleInfo = (!is_null($labelStyleInfo)) ? $labelStyleInfo : $layer->label_style;
                            $labelStyler = new Labels($labelStyleInfo, $label_position);
                            $labelStyler->UpdateLabel($label);
                            if (($geomType == GeomTypes::POLYGON)) {
                                if ($label->position === MS_CC) {
                                    $label->position = MS_AUTO;
                                }
                            }



                            // var_dump($this->hilightDriver->hilighting);
                            // var_dump($hilightingStage);
                            if (($this->hilightDriver->hilighting && ($hilightingStage == $targetHilightStage)) && ($geomType == GeomTypes::LINE)) {
                                $this->hilightDriver->SetStyleVars(HilightDriver::UNDERLAY, $sr, $sg, $sb, $fr, $fg, $fb, $xpacity, $glopacity);
                                $glopacity255 = $xpacity * 255 / 100;

                                $label2 = new labelObj();
                                $class->addLabel($label);
                                $class->addLabel($label2);
                                $label->updateFromString($labelForce);
                                $label2->updateFromString($labelForce);
                                if ($label->position === MS_AUTO) {
                                    $label->set('position', MS_CC);
                                }
                                //$label2->updateFromString('force='.$forceLabel);
                                // $label2->set('force', $forceLabel); //$this->hilightDriver->hilighting);


                                if ($angleMode === true) {
                                    $label2->set('anglemode', MS_TRUE);
                                }
                                $labelStyleInfo = (!is_null($labelStyleInfo)) ? $labelStyleInfo : $layer->label_style;
                                $labelStyler = new Labels($labelStyleInfoFiltered, $label_position);
                                $labelStyler->UpdateLabel($label2);

                                ColorUtil::Web2RGB($filterColor, $sr, $sg, $sb);
                                $labelAlpha = $glopacity255;

                                $label2->outlinecolor->setRGB($sr, $sg, $sb, $labelAlpha);
                                $label2->color->setRGB(0, 0, 0, 0);

                                $glowWidth = $labelStyleInfoFiltered['outlinewidth'];
                                $glowWidth = ($glowWidth === 0) ? 2.5 : $glowWidth * 4.5;
                                $glowWidth = round($glowWidth);
                                $label2->outlinewidth = $glowWidth;
                                if ($label2->position === MS_AUTO) {
                                    $label2->set('position', $label->position);
                                }

                                // if ($label2->position === MS_AUTO) {
                                //    $label2->position = \MS_UL;
                                // }
                            }

                            if (($this->hilightDriver->hilighting && ($hilightingStage === $targetHilightStage)) && ($geomType != GeomTypes::LINE)) {
                                $this->hilightDriver->SetStyleVars(HilightDriver::UNDERLAY, $sr, $sg, $sb, $fr, $fg, $fb, $xpacity, $glopacity);

                                $glopacity255 = $xpacity * 255 / 100;
                                $label2 = new labelObj();
                                // $label2->updateFromString('force='.$forceString);
                                $label2->updateFromString($labelForce);
                                //$label2->set('force', MS_TRUE);
                                $class->addLabel($label2);
                                $label2->color->setRGB(0, 0, 0, 0);

                                $labelFilterStyler = new Labels($labelStyleInfoFiltered, $label_position);
                                $labelFilterStyler->UpdateLabel($label2, $filterColor);

                                if ($geomType != GeomTypes::POINT) {
                                    $label2->set('angle', $label_angle);
                                }

                                if ($angleMode === true) {
                                    $label2->set('anglemode', MS_TRUE);
                                    if ($geomType != GeomTypes::POINT) {
                                        $label2->set('angle', $label_angle);
                                    }
                                }

                                #ColorUtil::Web2RGB($filterColor, $sr, $sg, $sb);
                                #var_dump($sr, $sg, $sb, $glopacity255);

                                $label2->outlinecolor->setRGB($sr, $sg, $sb, $glopacity255);

                                $glowWidth = $labelStyleInfoFiltered['outlinewidth'];
                                $glowWidth = ($glowWidth <= 2) ? 2.5 : $glowWidth * 2.5;
                                $glowWidth = round(max($glowWidth, 5));
                                $label2->outlinewidth = $glowWidth;

                                if (($geomType == GeomTypes::POLYGON)) {
                                    if ($label2->position === MS_CC) {
                                        $label2->position = MS_AUTO;
                                    }
                                }
                                if (($geomType === GeomTypes::LINE)) {
                                    if ($label->position === MS_AUTO) {
                                        $label->position = \MS_UL;
                                    }
                                }
                                $class->addLabel($label);
                            }



                            if ($this->hilightDriver->hilighting === false) {
                                # if($hilightingStage === $targetHilightStage) {
                                $class->addLabel($label);

                                #}
                            }

                            // if (! $filtering || ($geomType != GeomTypes::LINE)) {
                        }
                    }
                    if (($geomType === GeomTypes::LINE)) {
                        
                    }
                } // end entries loop
                if ($this->hilightDriver->hilighting === true) {
                    // $maplayer->set('labelcache','off');
                }
                // $maplayer->set('labelcache','off');
                if (isset($nameGroup['Default'])) {
                    $nameGroup['Default']->set('name', ($classCtr === 1) ? 'All featueres' : 'All other features');
                }
            } elseif ($layertype == LayerTypes::RASTER) {
                // raster layers are actually pretty simple
                $maplayer->set('type', MS_LAYER_RASTER);
                $maplayer->set('data', $l->url);
            } else { // layertype not any of the above?
                // error_log('Invalid layer type in Mapper');
            }
        }


        // save the mapfile to disk, and return this already configured object

        $this->map->save($mapfile);

        $this->mapFileName = $mapfile;
        return $this->map;
    }

    function renderStream($force = true, $fileName = null, $flush = true, $base64 = false) {

        // $this->debugMapFile = true;
        // $this-> =true;
        // a single WMS layer can be handled by a simpler call
        // $this->debugMapFile=true;
        // if($this->debugMapFile) header('Content-type:text/plain');
        $force = true;

        if ($force) {
            if (isset($this->mapFileName)) {
                unlink($this->mapFileName);
            }
        }

        if ($this->debugMapFile == false) {
            ob_start();
        }

        $map = $this->_generate_mapfile($force);

        if ($this->debugMapFile == false) {
            $img = ob_get_clean();
        }

        // ob_end_clean();
        // WAPI::SetWapiHeaders(WAPI::FORMAT_PNG);
        // $this->debugMapFile = true;

        if ($this->debugMapFile) {
            // header('Content-type:text/plain');

            if (file_exists($this->mapFileName)) {
                readfile($this->mapFileName);
            }
        }

        // die();
        // ob_start();

        $ext = $this->extent;

        $imgObj = null;

        try {
            if (($ext[0] != $ext[2]) && ($ext[1] != $ext[3])) {
                $map->setExtent($ext[0], $ext[1], $ext[2], $ext[3]);
            }
            if ($this->debugMapFile === false) {
                ob_start();
            }
            $imgObj = $map->draw();
            if ($this->debugMapFile === false) {
                ob_end_clean();
            }

            if (is_null($imgObj)) {
                throw new Exception('Empty Image');
            }
        } catch (Exception $e) {
            error_log($e->getMessage() . ' - ' . $e->getTraceAsString());
            if ($this->debugMapFile === true) {
                echo $e->getMessage() . ' - ' . $e->GetTraceAsString();
            } else {
                error_log($e->getMessage() . ' - ' . $e->getTraceAsString());
            }
            if ($base64 === true) {
                $imgInfo = file_get_contents(WEBROOT . "media/images/empty.png");
                echo base64_encode($imgInfo);
                return;
            }
            readfile(WEBROOT . "media/images/empty.png");
            return;
        }

        if ($imgObj) {
            if ($fileName) {
                if (file_exists($fileName)) {
                    unlink($fileName);
                }
                // header('Content-type: image/png');
                $imgObj->saveImage($fileName);
            } elseif ($base64) {
                header('Content-type: text/plain');
                ob_start();
                $imgObj->saveImage();
                $data = ob_get_contents();
                ob_end_clean();
                return base64_encode($data);
            } else {
                header('Content-type: image/png');
                $imgObj->saveImage();
            }
        }

        return;
        if ($flush) {
            ob_end_flush();
        } else {
            ob_end_clean();
        }

        // return $this->_renderSingleLayerviaCGI ();
        // }
        // fine, we need to call shell_exec() ourselves
        // $command = sprintf ( "shp2img -s %d %d -m %s -e %f %f %f %f", $this->width, $this->height, $this->mapfile, $this->extent [0], $this->extent [1], $this->extent [2], $this->extent [3] );
        // return shell_exec ( $command );
    }

    function legendImage() {
        $ini = System::GetIni();
        // constants for the image: positions and offsets and the like
        $x_layername = 12; // the X (horizontal) position of the layer names, in pixels
        $x_classname = 55; // the X (horizontal) position of the class names, in pixels
        $x_classicon = 25; // the X (horizontal) position of the class icons, in pixels
        $fontsize_layername = 10;
        $fontfile_layername = $ini->fontdir . '/Vera.ttf'; // the font for the layer names
        $fontsize_classname = 8;
        $fontfile_classname = $ini->fontdir . '/VeraIt.ttf'; // the font for the class names
        $icon_width = 50;
        $icon_height = 18; // the width and height of the icon images
        $y = - 10; // the Y offset, where we start counting as we lay out each item
        $y_offset_layer = 60; // how far to move down to place a new layer header?
        $y_offset_class = 21; // how far to move down to place a new class header?
        // $data is an array of arrays, each one being a msLayerObj and an array of msClassObj objects
        // This way we can count up the number of layers and classes (to determine the image size) and not have to fetch the objects from the map again
        $height = 10; // an initial offfset for fudging the height
        $map = $this->_generate_mapfile();
        $data = array();
        $width = $this->width;
        foreach ($map->getlayersdrawingorder() as $i) {
            $layer = $map->getLayer($i);
            $classes = array();
            for ($i = 0; $i < $layer->numclasses; $i++)
                array_push($classes, $layer->getClass($i));
            $thisdata = array(
                'layer' => $layer,
                'classes' => $classes
            );
            array_push($data, $thisdata);
            $height += $y_offset_layer;
            $height += sizeof($classes) * $y_offset_class;
        }
        $height -= 8 * sizeof($data); // take back some height, from space that will be freed up in the layer-header spacing
        if ($height < $this->height)
            $height = $this->height;

        // figure up the colors
        // create the new image, and initialize the colors
        $image = imagecreatetruecolor($width, $height);

        $text_color = imagecolorallocate($image, hexdec(substr($this->fgcolor, - 6, 2)), hexdec(substr($this->fgcolor, - 4, 2)), hexdec(substr($this->fgcolor, - 2, 2)));
        $bg_color = imagecolorallocate($image, hexdec(substr($this->bgcolor, - 6, 2)), hexdec(substr($this->bgcolor, - 4, 2)), hexdec(substr($this->bgcolor, - 2, 2)));
        imagefill($image, 0, 0, $bg_color);

        // go through each layer and each class, and lay down the labels
        foreach ($data as $layerinfo) {
            $y += $y_offset_layer;
            imagettftext($image, $fontsize_layername, 0, $x_layername, $y, $text_color, $fontfile_layername, $layerinfo['layer']->name);
            $y -= 8; // for the first class in the layer, bring it a bit higher/closer to the title we just drew
            foreach ($layerinfo['classes'] as $class) {
                $y += $y_offset_class;
                imagettftext($image, $fontsize_classname, 0, $x_classname, $y + 5, $text_color, $fontfile_classname, $class->name);
                $icon = sprintf("%s/%s.png", $ini->tempdir, md5(microtime() . mt_rand()));
                $class->createLegendIcon($icon_width, $icon_height)->saveImage($icon);
                $icon = imagecreatefrompng($icon);
                imagecopy($image, $icon, $x_classicon, $y - 10, 0, 0, $icon_width, $icon_height);
            }
        }

        // and save it
        $filename = md5(microtime() . mt_rand());
        $filename = "{$ini->tempdir}/legend-{$filename}.png";
        imagepng($image, $filename);

        return $filename;
    }

    /**
     * Generates a WMS URL to use for rendering a map.
     *
     * @param
     *            layerOffset int the offset in $this->layers to get the url for
     */
    function _generateRemoteWMSURL($layerOffset = 0, $asObj = false, $layer = null) {

        $ini = System::GetIni();
        $sl_wms = BASEURL; //$ini->sl_wms_basepath;

        $layer = is_null($layer) ? $this->layers[$layerOffset]['layer'] : $layer;

        $layer = is_a($layer, 'Layer') ? $layer : $layer->layer;


        // custom data url
        $url = is_null($layer->custom_data) ? null : $layer->custom_data['get_map'];


        // if no custom data url, use old field
        if (!$url) {
            $url = $layer->url;
        }

        $urlParts = explode('/', $url);
        $baseParts = explode('/', BASEURL);

        if (in_array($urlParts[2], ['50.117.112.42', 'staging.simplelayers.com'])) {
            $urlParts[0] = $baseParts[0];
            $urlParts[2] = $baseParts[2];
        }

        $url = implode('/', $urlParts);

        $isSLWMS = stripos($url, $sl_wms) === 0;

        if (stripos($url, "?") === false) {
            $url .= '?';
        }
        list ($base, $query) = explode('?', $url);
        $base .= "?";

        $params = array();
        parse_str($query, $params);

        // die();
        /*
         * if (isset($params['MAP']) || isset($params['map'])) {
         * $base .= "&map=" . $params['map'];
         * unset($params['map']);
         * }
         */

        $keys = array_keys($params);
        $newParams = array();
        foreach ($keys as $key) {
            $newParams[strtoupper($key)] = $params[$key];
        }

        $params = $newParams;
        unset($newParams);

        array_change_key_case($params, CASE_UPPER);

        $version = isset($params['VERSION']) ? $params['VERSION'] : '1.3.0';

        // get version

        $version = explode(".", $version);
        $majorVersion = (int) $version[0];
        $minorVersion = (int) $version[1];
        // get bbox
        $bbox = "{$this->extent[0]},{$this->extent[1]},{$this->extent[2]},{$this->extent[3]}";
        if (($majorVersion >= 1) && ($minorVersion >= 3)) {
            $bbox = "{$this->extent[1]},{$this->extent[0]},{$this->extent[3]},{$this->extent[2]}";
        }
        $params['BBOX'] = $bbox;
        $epsg = ($this->mode === self::$MODE_WEB) ? 3857 : 4326;

        if (isset($params['CRS'])) {
            $params['CRS'] = 'EPSG:' . $epsg;
        } elseif (isset($params['SRS'])) {
            $params['SRS'] = 'EPSG:' . $epsg;
        } else {
            if (($majorVersion >= 1) && ($minorVersion >= 3)) {
                $params['CRS'] = "EPSG:$epsg";
            } else {
                $params["SRS"] = "EPSG:$epsg";
            }
        }

        if (!isset($params['STYLES'])) {
            $params['STYLES'] = '';
        }
        if (stripos($url, "STYLES") < 0) {
            $url .= "&STYLES=";
        }
        // now replace those same params with known values

        $params['FORMAT'] = 'image/png';

        $params['TRANSPARENT'] = 'true';
        $params['SERVICE'] = 'WMS';
        $params['WIDTH'] = $this->width;
        $params['HEIGHT'] = $this->height;

        if ($isSLWMS) {
            $url = strpos($base, '?') ? $base . '&' . http_build_query($params) : $base . '?' . http_build_query($params);
        } else {
            $urlParts = explode('?', $url);
            $url = $urlParts[0];
        }
        $url = urldecode($url);

        if ($asObj) {
            $obj = new stdClass();
            $obj->base = $base;
            $obj->params = $params;
            $obj->url = $url;
            return $obj;
        }

        // all set!
        return $url;
    }

    function _renderWebRemoteWMS($layerOffset = null) {
        $ini = System::GetIni();
        $filename = md5(mt_rand() . microtime()) . '.png';
        // $tempfile = $world->config['tempdir'] . '/' . $filename;

        $tempurl = $ini->tempurl . '/' . $filename;
        $fileData = $this->_renderStreamRemoteWMS($layerOffset);
        // 
        // die();

        if (is_null($fileData))
            throw new Exception("Empty Image");
        header('Content-type: image/png');
        echo $fileData;
        // $url = $this->_generateRemoteWMSURL($layerOffset);
        // file_put_contents($tempurl, $fileData);
        // return $tempurl;
    }

    function _renderStreamRemoteWMS($layerOffset = null) {
        $url = $this->_generateRemoteWMSURL($layerOffset);
        $url = urldecode($url);
        var_dump($url);
        die();
        ob_start();
        $contents = file_get_contents($url);
        ob_end_clean();

        return $contents;
    }

    /**
     * For the most common case, there being only 1 layer, we call shp2img CGI program because
     * shell_exec() runs at 1/6 speed versus normal shell.
     * Why? Nobody knows and PHP doesn't care.
     *
     * @return string A binary data string, the image data.
     */
    function _renderSingleLayerviaCGI() {
        $url = sprintf("https://www.cartograph.com/cgi-bin/shp2img?mapfile=%s&width=%d&height=%d&extent=%f+%f+%f+%f&odbcini=%s", basename($this->mapfile, '.map'), $this->width, $this->height, $this->extent[0], $this->extent[1], $this->extent[2], $this->extent[3], @$this->odbcini);
        return file_get_contents($url);
    }

    // ///
    // /// methods for adding layers to the Mapper for rendering
    // ///

    /**
     * Add a layer to the map for rendering.
     *
     * @param Layer $layer
     *            A Layer or ProjectLayer object.
     */
    function addLayer(&$layer, $opacity = 1.0, $labels = false, $labelField = null, $baseLayer = null, $ignoreScale = false, $glopacity = null, $glowColor = null, $at = null) {
        if (is_null($labelField)) {
            $labelField = (isset($layer->labelitem)) ? $layer->labelitem : null;
        }
        if (is_a($this->layers, 'model\mapping\Renderer')) {
            $this->layers->AddLayer($layer, $opacity, $labels, $labelField, $baseLayer, $ignoreScale, $glopacity, $glowColor, null);
            return;
        }
        $layer = array(
            'layer' => $layer,
            'opacity' => $opacity,
            'labels' => $labels,
            'labelField' => $labelField,
            'baseLayer' => $baseLayer,
            'ignoreScale' => $ignoreScale,
            'glopacity' => $glopacity,
            'glowColor' => $glowColor
        );

        if (is_null($at)) {
            $this->layers[] = $layer;
        } else {
            array_splice($this->layers, $at, 0, $layer);
        }
    }

    /**
     * This method removes all Layers from the Mapper, so you can start fresh.
     */
    function clearLayers() {
        $this->layers = array();
    }

    // ///
    // /// methods pertaining to the global list of symbols, fonts, etc.
    // ///

    /**
     * Return an array of symbols appropriate for the given geometry type.
     *
     * @param string $geomtypestring
     *            The string geometry type; use $layer->geomtypestring
     * @return array An associative array, mapping the symbol's internal name => the symbol's human-friendly name.
     */
    function listSymbols($geomtypestring) {
        $symbols = array();
        switch ($geomtypestring) {
            case 'point':
                return Point::GetEnum();
            case 'polygon':
                return Polygon::GetEnum();
            case 'line':
                return Lines::GetEnum();
        }
        return null;
        foreach (file($this->symbolFile) as $line) {

            @preg_match('/^\s*name\s+\"([^_]+)_(\w+)\"/i', $line, $parts);

            if (!isset($parts[1]))
                continue;
            if ($parts[1] != $geomtypestring)
                continue;
            if (substr($parts[2], - 7) == '_filled')
                continue;

            $label = $parts[2];
            if ($label == 'default')
                $label = $geomtypestring == 'point' ? 'circle' : 'solid';
            $symbols[$parts[2]] = $label;
        }
        return $symbols;
    }

    /**
     * Initialize the Mapper's map object: projection, image formats, et cetera.
     *
     * @param boolean $adjustunits
     *            Should the units and extents be changed to fit the given projection?
     * @param boolean $forRendering
     *            if false, the map obj will be setup with size and extent params. Otherwise it will set up image and legend formats etc.
     */
    function init($adjustunits = false, $mapObj = null) {
        $ini = System::GetIni();
        $mapUnits = ($this->mode === self::$MODE_LATLON) ? MS_DD : MS_METERS;

        if ($mapObj == null) {
            // load up a blank mapfile, set all the usual stuffi
            $this->map = new mapObj(NULL);
            $this->map->maxsize = 4000;
            // print(__CLASS__.':'. __FUNCTION__.":check 2<br>");
            if ($this->legendMode) {
                $map->legend->color->setRGB(255, 255, 255);
            }
            $this->projection = $this->GetProjection();
            // the projection should be set first so that when size and extent are set they are being set for the appropriate projection.
            // print(__CLASS__.':'. __FUNCTION__.":check 3<br>");

            $this->map->set('units', $mapUnits); // units should be set with projection
            // if ($this->projection != $this->world->projections->defaultSRID) {

            $this->map->setProjection($this->projection, $adjustunits);
            // }
            // print(__CLASS__.':'. __FUNCTION__.":check 4<br>");

            $hasExtent = false;

            if (!(('' . $this->extent[0] == '' . $this->extent[2]) && ('' . $this->extent[1] == '' . $this->extent[3]))) {
                $hasExtent = true;
                $this->map->extent->setextent($this->extent[0], $this->extent[1], $this->extent[2], $this->extent[3]);
            }
            // var_dump($this->map->extent->minx);
            // print(__CLASS__.':'. __FUNCTION__.":check 5<br>");

            if (isset($this->width) && isset($this->height) && $hasExtent) {
                $this->map->setSize($this->width, $this->height);
            }
        } else {
            $this->isDynamic = true;
            $this->map = $mapObj;
            $this->width = $mapObj->width;
            $this->height = $mapObj->height;
            $this->extent = array();
            $this->extent[0] = $mapObj->extent->minx;
            $this->extent[1] = $mapObj->extent->miny;
            $this->extent[2] = $mapObj->extent->maxx;
            $this->extent[3] = $mapObj->extent->maxy;
        }
        $this->map->set('name', 'renderer');
        $this->map->set('status', MS_ON);

        // error_log(var_export($this->world->config,true));
        $this->map->setSymbolSet($this->symbolFile);
        $this->map->setFontSet($this->fontsdir . '/fonts.inc');
        $this->map->web->set('imagepath', $ini->tempurl . '/'); // tempdir );
        $this->map->web->set('imageurl', $ini->tempurl . '/');
        $this->map->outputformat->setOption('INTERLACE', 'OFF');
        // Set up the image format and background color, depending on the context: screenshot, map layer, downloadable.
        // There's a known bug in mapscript that selectOutputFormat() doesn't work, and that the AGG driver doesn't work
        // properly, failing over to old GD. So, we do two tricks:
        // a) We lie about what "gif" means (since we can't change from it!) as far as driver and format and all.
        // b) In the _render() method, we use shp2img instead of $this->map->draw() because shp2img actually works.
        // print(__CLASS__.':'. __FUNCTION__.":check 6<br>");
        if ($this->geotiff) {
            $this->map->imagecolor->setRGB(204, 204, 204);
            $this->map->outputformat->set('name', 'png');
            $this->map->outputformat->set('mimetype', 'image/tiff');
            $this->map->outputformat->set('driver', 'GDAL/GTiff');
            $this->map->outputformat->set('extension', 'tiff');
            $this->map->outputformat->set('imagemode', MS_IMAGEMODE_RGB);
            $this->map->outputformat->set('transparent', MS_OFF);
            // $this->map->outputformat->setOption ( 'COMPRESS', 'DEFLATE' );
        } elseif ($this->screenshot) {
            $this->map->imagecolor->setRGB(204, 204, 204);
            $this->map->outputformat->set('name', 'gif');
            $this->map->outputformat->set('mimetype', 'image/jpeg');
            if (@$this->lowquality) {
                $this->map->outputformat->set('driver', 'GD/JPEG');
            } else {
                $this->map->outputformat->set('driver', 'AGG/PNG');
            }
            $this->map->outputformat->set('extension', 'png');
            $this->map->outputformat->set('imagemode', MS_IMAGEMODE_RGB);
            $this->map->outputformat->set('transparent', MS_OFF);
            $this->map->outputformat->setOption('quality', '100');
            $this->map->outputformat->setOption('INTERLACE', MS_OFF);
        } elseif ($this->thumbnail) {
            $this->map->imagecolor->setRGB(255, 255, 255);

            $this->map->outputformat->set('name', 'gif');
            $this->map->outputformat->set('mimetype', 'image/jpeg');
            $this->map->outputformat->set('driver', 'AGG/JPEG');
            $this->map->outputformat->set('extension', 'jpg');
            $this->map->outputformat->set('imagemode', MS_IMAGEMODE_RGB);
            $this->map->outputformat->set('transparent', MS_ON);
            $this->map->outputformat->setOption('quality', '100');
            $this->map->outputformat->setOption('INTERLACE', MS_OFF);
        } else {
            $this->map->imagecolor->setRGB(255, 255, 254);
            $this->map->outputformat->set('name', 'PNG24');
            // if (@$this->lowquality) {
            // $this->map->outputformat->set ( 'mimetype', 'image/png' );
            // $this->map->outputformat->set ( 'driver', 'GD/PNG' );
            // $this->map->outputformat->set ( 'imagemode', MS_IMAGEMODE_RGB );
            // $this->map->outputformat->set ( 'transparent', MS_ON );
            // } else {
            $this->map->outputformat->set('mimetype', 'image/png; mode=24bit');
            $this->map->outputformat->set('driver', 'AGG/PNG');
            $this->map->outputformat->set('imagemode', MS_IMAGEMODE_RGBA);
            $this->map->outputformat->set('transparent', MS_ON);
            // }#

            if (!$this->interlace)
                $this->map->outputformat->setOption('INTERLACE', 'OFF');
            if ($this->quantize) {
                $this->map->outputformat->setOption('QUANTIZE_FORCE', 'ON');
                $this->map->outputformat->setOption('QUANTIZE_COLORS', 256);
                $this->map->outputformat->setOption('QUANTIZE_NEW', 'ON');
                $this->map->outputformat->setOption('QUANTIZE_DITHER', 'OFF');
            }
        }

        // print(__CLASS__.':'. __FUNCTION__.":check 7<br>");
        // configure the legend
        $this->map->legend->set('width', $this->width);
        $this->map->legend->set('height', $this->height);
        $this->map->legend->set('status', $this->legendMode === true);
        $this->map->legend->imagecolor->setRGB(255, 255, 255);
        $this->map->legend->label->color->setRGB(0, 0, 0);
        $this->map->legend->label->set('size', 9);
        // print(__CLASS__.':'. __FUNCTION__.":check 6.1<br>");
        // print(__CLASS__.':'. __FUNCTION__.":check 7<br>");

        $this->map->legend->label->set('font', 'Vera');
        $this->map->legend->label->set('position', MS_UR);
        $this->map->legend->set('keysizex', 18);
        $this->map->legend->set('keysizey', 12);
        $this->map->legend->set('keyspacingx', 15);
        $this->map->legend->set('keyspacingy', 14);

        return $this->map;
    }

    function SetPixospatialMapObj(PixoSpatial $pixo, $proj4 = null) {
        if (is_null($proj4))
            $proj4 = $this->GetProjection();

        $extent = $this->GetProjectedExtents($pixo->GetBBox());

        list ($minLon, $minLat, $maxLon, $maxLat) = explode(',', $extent);

        $reporting = error_reporting();
        // ob_start();
        if (is_null($this->map))
            $this->map = new mapObj(null);

        $this->map->setProjection($proj4, MS_TRUE);
        error_reporting(0);
        try {
            $this->map->setExtent((double) $minLon, (double) $minLat, (double) $maxLon, (double) $maxLat);
            $this->map->setSize((int) $pixo->GetWidth(), (int) $pixo->GetHeight());
        } catch (Exception $e) {
            Log::Debug($e->getMessage());
        }
        // ob_end_clean();
        error_reporting($reporting);
    }

    static function Get($isDynamic = false) {
        return new Mapper();
    }

    public function GetProjection($mode = null) {
        if (is_null($mode))
            $mode = $this->mode;

        switch ($mode) {
            case self::$MODE_WEB:
                return "+proj=merc +a=6378137 +b=6378137 +lat_ts=0.0 +lon_0=0.0 +x_0=0.0 +y_0=0 +k=1.0 +units=m +nadgrids=@null +wktext  +no_defs";
                break;
            case self::$MODE_LATLON:
                return "+proj=longlat +ellps=WGS84 +datum=WGS84 +no_defs";
                break;
        }
    }

    public function GetProjectedExtents($bbox, $direct = true) {
        if ($this->mode === self::$MODE_LATLON)
            return $bbox;
        if (!is_array($bbox)) {
            $bbox = explode(",", $bbox);
        }

        if (count($bbox) != 4) {
            throw new \Exception('invalid extents');
        }
        list ($minx, $miny, $maxx, $maxy) = $bbox;
        if ($direct) {
            $projFrom = new projectionObj($this->GetProjection(self::$MODE_LATLON));
            $projTo = new projectionObj($this->GetProjection(self::$MODE_WEB));
        } else {
            $projFrom = new projectionObj($this->GetProjection(self::$MODE_WEB));
            $projTo = new projectionObj($this->GetProjection(self::$MODE_LATLON));
        }

        $pt = new pointObj();
        $pt->setXY($minx, $miny);
        $pt->project($projFrom, $projTo);
        $minx = $pt->x;
        $miny = $pt->y;
        $pt->setXY($maxx, $maxy);
        $pt->project($projFrom, $projTo);
        $maxx = $pt->x;
        $maxy = $pt->y;
        return implode(',', array(
            $minx,
            $miny,
            $maxx,
            $maxy
        ));
    }

    public function ShowMapFile() {
        if ($this->mapFileName) {
            if (file_exists($this->mapFileName)) {
                readfile($this->mapFileName);
                return;
            }
        }
    }

}

?>
