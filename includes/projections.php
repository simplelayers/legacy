<?php

/**
 * This include file defines projections used by the system. It is loaded by library.php
 * and is in a separate file solely for readability, due to the length of this content.
 * 
 * The only content is the $PROJECTIONS array, which maps projection info ontoa human-readable label.
 * The projection part (left-hand side) can be either PROJ4 text or else ESRI WKT for the projection.
 *
 * @package Overview
 */

$GLOBALS['PROJECTIONS'] = array(
''=>'Unspecified Projection',
// LatLong unprojected coordinates, using various datums
'+proj=longlat +ellps=WGS84 +datum=WGS84 +no_defs'  => 'Lat/Long (DEFAULT)',
'+proj=longlat +ellps=intl +no_defs' => 'Lat/Long with PSAD56 datum',

// Google Maps and other commercial providers
'+proj=merc +a=6378137 +b=6378137 +lat_ts=0.0 +lon_0=0.0 +x_0=0.0 +y_0=0 +k=1.0 +units=m +nadgrids=@null +no_defs <>' => 'Commercial tile maps (Google etc)',

// UTM
'+proj=utm +zone=10 +ellps=GRS80 +datum=NAD83 +units=m +no_defs' => 'UTM Zone 10N - NAD83',
'+proj=utm +zone=11 +ellps=GRS80 +datum=NAD83 +units=m +no_defs' => 'UTM Zone 11N - NAD83',
'+proj=utm +zone=12 +ellps=GRS80 +datum=NAD83 +units=m +no_defs' => 'UTM Zone 12N - NAD83',
'+proj=utm +zone=13 +ellps=GRS80 +datum=NAD83 +units=m +no_defs' => 'UTM Zone 13N - NAD83',
'+proj=utm +zone=14 +ellps=GRS80 +datum=NAD83 +units=m +no_defs' => 'UTM Zone 14N - NAD83',
'+proj=utm +zone=15 +ellps=GRS80 +datum=NAD83 +units=m +no_defs' => 'UTM Zone 15N - NAD83',
'+proj=utm +zone=16 +ellps=GRS80 +datum=NAD83 +units=m +no_defs' => 'UTM Zone 16N - NAD83',
'+proj=utm +zone=17 +ellps=GRS80 +datum=NAD83 +units=m +no_defs' => 'UTM Zone 17N - NAD83',
'+proj=utm +zone=17 +south +ellps=intl +units=m +no_defs'        => 'UTM zone 17S - PSAD56',
'+proj=utm +zone=18 +ellps=GRS80 +datum=NAD83 +units=m +no_defs' => 'UTM Zone 18N - NAD83',
'+proj=utm +zone=18 +ellps=intl +units=m +no_defs'               => 'UTM zone 18N - PSAD56',
'+proj=utm +zone=18 +south +ellps=intl +units=m +no_defs'        => 'UTM zone 18S - PSAD56',
'+proj=utm +zone=19 +ellps=GRS80 +datum=NAD83 +units=m +no_defs' => 'UTM Zone 19N - NAD83',
'+proj=utm +zone=19 +ellps=intl +units=m +no_defs'               => 'UTM zone 19N - PSAD56',
'+proj=utm +zone=19 +south +ellps=intl +units=m +no_defs'        => 'UTM zone 19S - PSAD56',
'+proj=utm +zone=20 +ellps=intl +units=m +no_defs'               => 'UTM zone 20N - PSAD56',
'+proj=utm +zone=20 +south +ellps=intl +units=m +no_defs'        => 'UTM zone 20S - PSAD56',
'+proj=utm +zone=21 +ellps=intl +units=m +no_defs'               => 'UTM zone 21N - PSAD56',
'+proj=utm +zone=22 +south +ellps=intl +units=m +no_defs'        => 'UTM zone 22S - PSAD56',
'+proj=utm +zone=44 +ellps=WGS84 +datum=WGS84 +units=m +no_defs' => 'UTM Zone 44N - WGS84',
'+proj=utm +zone=45 +ellps=WGS84 +datum=WGS84 +units=m +no_defs' => 'UTM Zone 45N - WGS84',

// Lambert projections
'+proj=laea +lat_0=45 +lon_0=-100 +x_0=0 +y_0=0 +a=6370997 +b=6370997 +units=m +no_defs'  => 'US National Atlas Equal Area (Lambert)',

// CA state plane
'+proj=lcc +lat_1=41.66666666666666 +lat_2=40 +lat_0=39.33333333333334 +lon_0=-122 +x_0=609601.2192024384 +y_0=0 +ellps=clrk66 +datum=NAD27 +to_meter=0.3048006096012192 +no_defs' => 'CA State Plane Zone I (ft) - NAD83',
'+proj=lcc +lat_1=39.83333333333334 +lat_2=38.33333333333334 +lat_0=37.66666666666666 +lon_0=-122 +x_0=609601.2192024384 +y_0=0 +ellps=clrk66 +datum=NAD27 +to_meter=0.3048006096012192 +no_defs' => 'CA State Plane Zone II (ft) - NAD83',
'+proj=lcc +lat_1=38.43333333333333 +lat_2=37.06666666666667 +lat_0=36.5 +lon_0=-120.5 +x_0=609601.2192024384 +y_0=0 +ellps=clrk66 +datum=NAD27 +to_meter=0.3048006096012192 +no_defs' => 'CA State Plane Zone III (ft) - NAD83',
'+proj=lcc +lat_1=37.25 +lat_2=36 +lat_0=35.33333333333334 +lon_0=-119 +x_0=609601.2192024384 +y_0=0 +ellps=clrk66 +datum=NAD27 +to_meter=0.3048006096012192 +no_defs' => 'CA State Plane Zone IV (ft) - NAD83',
'+proj=lcc +lat_1=35.46666666666667 +lat_2=34.03333333333333 +lat_0=33.5 +lon_0=-118 +x_0=609601.2192024384 +y_0=0 +ellps=clrk66 +datum=NAD27 +to_meter=0.3048006096012192 +no_defs' => 'CA State Plane Zone V (ft) - NAD83',
'+proj=lcc +lat_1=33.88333333333333 +lat_2=32.78333333333333 +lat_0=32.16666666666666 +lon_0=-116.25 +x_0=609601.2192024384 +y_0=0 +ellps=clrk66 +datum=NAD27 +to_meter=0.3048006096012192 +no_defs' => 'CA State Plane Zone VI (ft) - NAD83',
'+proj=lcc +lat_1=34.41666666666666 +lat_2=33.86666666666667 +lat_0=34.13333333333333 +lon_0=-118.3333333333333 +x_0=1276106.450596901 +y_0=127079.524511049 +ellps=clrk66 +datum=NAD27 +to_meter=0.3048006096012192 +no_defs' => 'CA State Plane Zone VII (ft) - NAD83',
'+proj=lcc +lat_1=40 +lat_2=41.66666666666666 +lat_0=39.33333333333334 +lon_0=-122 +x_0=2000000 +y_0=500000.0000000002 +ellps=GRS80 +datum=NAD83 +to_meter=0.3048006096012192 +no_defs' => 'NAD 1983 StatePlane California I FIPS 0401 Feet',
'+proj=lcc +lat_1=38.33333333333334 +lat_2=39.83333333333334 +lat_0=37.66666666666666 +lon_0=-122 +x_0=2000000 +y_0=500000.0000000002 +ellps=GRS80 +datum=NAD83 +to_meter=0.3048006096012192 +no_defs' => 'NAD 1983 StatePlane California II FIPS 0402 Feet',
'+proj=lcc +lat_1=37.06666666666667 +lat_2=38.43333333333333 +lat_0=36.5 +lon_0=-120.5 +x_0=2000000 +y_0=500000.0000000002 +ellps=GRS80 +datum=NAD83 +to_meter=0.3048006096012192 +no_defs' => 'NAD 1983 StatePlane California III FIPS 0403 Feet',
'+proj=lcc +lat_1=36 +lat_2=37.25 +lat_0=35.33333333333334 +lon_0=-119 +x_0=2000000 +y_0=500000.0000000002 +ellps=GRS80 +datum=NAD83 +to_meter=0.3048006096012192 +no_defs' => 'NAD 1983 StatePlane California IV FIPS 0404 Feet',
'+proj=lcc +lat_1=34.03333333333333 +lat_2=35.46666666666667 +lat_0=33.5 +lon_0=-118 +x_0=2000000 +y_0=500000.0000000002 +ellps=GRS80 +datum=NAD83 +to_meter=0.3048006096012192 +no_defs' => 'NAD 1983 StatePlane California V FIPS 0405 Feet',
'+proj=lcc +lat_1=32.78333333333333 +lat_2=33.88333333333333 +lat_0=32.16666666666666 +lon_0=-116.25 +x_0=2000000 +y_0=500000.0000000002 +ellps=GRS80 +datum=NAD83 +to_meter=0.3048006096012192 +no_defs' => 'NAD 1983 StatePlane California VI FIPS 0406 Feet',
'+proj=aea +lat_1=34 +lat_2=40.5 +lat_0=0 +lon_0=-120 +x_0=0 +y_0=-4000000 +ellps=clrk66 +datum=NAD27 +units=m +no_defs' => 'California Teale Albers',

// NV state plane
'+proj=tmerc +datum=NAD83 +lon_0=-116d40 +lat_0=34d45 +k=.9999 +x_0=500000 +y_0=6000000 +units=ft +no_defs' => 'NV State Plane Nevada Central FIPS 2702 Feet - NAD83',
'+proj=tmerc +datum=NAD83 +lon_0=-115d35 +lat_0=34d45 +k=.9999 +x_0=200000 +y_0=8000000 +units=ft +no_defs' => 'NV State Plane Nevada East FIPS 2701 Feet - NAD83',
'+proj=tmerc +datum=NAD83 +lon_0=-118d35 +lat_0=34d45 +k=.9999 +x_0=800000 +y_0=4000000 +units=ft +no_defs' => 'NV State Plane Nevada West FIPS 2703 Feet - NAD83',

// NM state plane
'+proj=tmerc +lat_0=31 +lon_0=-107.8333333333333 +k=0.999917 +x_0=829999.9999999999 +y_0=0 +ellps=GRS80 +datum=NAD83 +to_meter=0.3048006096012192 +no_defs' => 'NAD 1983 StatePlane New Mexico West FIPS 3003 Feet',

);


?>
