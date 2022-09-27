<?php
namespace utils\tiles;

class TileInfo
{

    private $data;

    private $zoomLevels = 18;

    private $projIn;

    private $projOut;

    private $ne;

    private $sw;

    public function __construct()
    {}

    public function TileToExt($x, $y, $z)
    {
        $grid_count = pow(2, $z);
        $block_size = 360.0 / $grid_count;
        
        $temp_x = - 180.0 + ($block_size * $x);
        $temp_y = 180.0 - ($block_size * $y);
        
        $temp_x2 = $temp_x + $block_size;
        $temp_y2 = $temp_y - $block_size;
        
        $lon = $temp_x;
        $lat = (2.0 * atan(exp($temp_y / 180.0 * pi())) - pi() / 2.0) * 180.0 / pi();
        
        $lon2 = $temp_x2;
        $lat2 = (2.0 * atan(exp($temp_y2 / 180.0 * pi())) - pi() / 2.0) * 180.0 / pi();
        
        if (is_null($this->projIn))
            $this->projIn = ms_newProjectionObj("proj=latlong");
        if (is_null($this->projOut))
            $this->projOut = ms_newProjectionObj("+init=epsg:3785"); // +proj=merc +a=6378137 +b=6378137 +lat_ts=0.0 +lon_0=0.0 +x_0=0.0 +y_0=0 +units=m +k=1.0 +nadgrids=@null");
        if (is_null($this->sw))
            $this->sw = ms_newPointObj();
        if (is_null($this->ne))
            $this->ne = ms_newPointObj();
        $this->sw->setXY($lon, $lat2);
        $this->ne->setXY($lon2, $lat);
        $this->sw->project($this->projIn, $this->projOut);
        $this->ne->project($this->projIn, $this->projOut);
        $array = array();
        $array[0] = (int) $this->sw->x;
        $array[1] = (int) $this->sw->y;
        $array[2] = (int) $this->ne->x;
        $array[3] = (int) $this->ne->y;
        return ($array);
    }

    public function BBOX_WKT($exts)
    {
        list ($swX, $swY, $neX, $neY) = $exts;
        $wkt = 'POLYGON((';
        $wkt .= "$swX $swY,";
        $wkt .= "$swX $neY,";
        $wkt .= "$neX $neY,";
        $wkt .= "$neX $swY,";
        $wkt .= "$swX $swY";
        $wkt .= "))";
        return $wkt;
    }

    public static function getTileRange($lon, $lat, $lon2, $lat2)
    {
        $tiles = array();
        
        $numTiles = 0;
        for ($z = 0; $z < 19; $z ++) {
            $nwTile = self::getTile($lon, $lat2, $z);
            $seTile = self::getTile($lon2, $lat, $z);
            $xMin = min($nwTile[0], $seTile[0]);
            $xMax = max($nwTile[0], $seTile[0]);
            $yMin = min($nwTile[1], $seTile[1]);
            $yMax = max($nwTile[1], $seTile[1]);
            $xDiff = 1 + ($xMax - $xMin);
            $yDiff = 1 + ($yMax - $yMin);
            $tileCount = $xDiff * $yDiff;
           
            $numTiles += $tileCount;
            $tiles[$z] = array(
                'min' => array(
                    $z,
                    $nwTile[0],
                    $nwTile[1]
                ),
                'max' => array(
                    $z,
                    $seTile[0],
                    $seTile[1]
                ),
                'tiles' => $tileCount
            );
        }
        $spaceReq = $numTiles * (180000);
        return array(
            'tiles' => $tiles,
            'tile_count' => $numTiles,
            'spaceReq' => $spaceReq
        );
    }

    public static function getTile($lon, $lat, $z)
    {
        $xtile = floor((($lon + 180) / 360) * pow(2, $z));
        $ytile = floor((1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / pi()) / 2 * pow(2, $z));
        return array(
            $xtile,
            $ytile
        );
    }
}

?>
