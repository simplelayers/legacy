<?php
/**
 * Miscellaneous functions.
 *
 * This is simply some useful functions that don't belong as part of the model, but which
 * are too commonly used to not be functions. Life's like that sometimes, eh?
 *
 * @package    Overview
 */

///// functions.php
///// just some commonly used functions that are very useful

/**
 * Make a JavaScript alert popup containing the specified text.
 * @param string $message The message to be displayed in the alert popup.
 * @return string A JavaScript block which will display an alert() dialog.
 */
function javascriptalert($string='') {
   if (!$string) { return ''; }
   $string = str_replace("'","\\'",$string);
   $string = str_replace("\n","\\n",$string);
   return sprintf("<script type=\"text/javascript\">\n\n   alert('%s');\n\n</script>\n\n\n",$string);
}

function javascriptlog($message) {
	$string = str_replace("'","\\'",$string);
	$string = str_replace("\n","\\n",$string);
	return sprintf("<script type=\"text/javascript\">\n<!--\n   console.log('%s');\n//-->\n</script>\n\n\n",$string);
}

function timeToHowLongAgo($time){
	if($time < 60){return $time." Seconds Ago";
	}elseif($time < 3600){$time=round($time/60);return $time." Minute".(($time > 1)?'s' : '')." Ago";
	}elseif($time < 86400){$time=round($time/3600);return $time." Hour".(($time > 1)?'s' : '')." Ago";
	}elseif($time < 604800){$time=round($time/86400);return $time." Day".(($time > 1)?'s' : '')." Ago";
	}elseif($time < 2592000){$time=round($time/604800);return $time." Week".(($time > 1)?'s' : '')." Ago";
	}elseif($time < 31536000){$time=round($time/2592000);return $time." Month".(($time > 1)?'s' : '')." Ago";
	}else{$time=round($time/31536000);return $time." Year".(($time > 1) ? 's' : '')." Ago";
	}
}

/**
 * Print a block of HTML telling the user to "please stand by" while we work, and flush it immediately
 * to the output buffer. This is a very rare thing, a function writing directly to the output buffer!
 * This should only be used on pages that are about to be cleared via a redirect.
 * @see redirect()
 * @param string $message A message that will be displayed above the image.
 * @return string A block of HTML containing the message and an image.
 */
function busy_image($message='') {
   $retstr  = "<p style=\"text-align:center;font-size:125%;\">\n";
   $retstr .= htmlentities($message) . "<br/>\n";
   $retstr .= "<img src=\"media/busy.gif\" style=\"border-style:none;\"/>\n";
   $retstr .= "</p>\n";
   print $retstr;
   ob_flush();
}


/**
 * Similar in concept to the busy_image() this function takes a message sends it immediately
 * to the output stream, then flushes the output stream. This is useful for cases where output needs to
 * be done quickly, despite a very lengthy time of operatioin behind the scenes; the same circumstances
 * where you'd use busy_image()
 * @param string $message A message that will be displayed,
 */
function ping($message) {
   print $message;
   ob_flush();
}


/**
 * Used by the viewer-dispatchers to generate a "status denied" message.
 * This takes a reason, and returns the appropriate status=NO string containing the original query string and the reason given.
 * This is another of those weird methods which actually writes to the output buffer directly, definitely not MVC friendly.
 * @param string $reason The reason or message to report in the status=NO string. You may find the DENIED_* constants useful.
 * @return string A query string suitable for parsing by Flash. See the code for the format, very straightforward.
 */
function denied($reason='Unspecified') {
   ping("&status=NO&reason=$reason&{$_SERVER['QUERY_STRING']}&");
}



/**
  * Print the HTTP headers to download a file.
  * @param string $filename The filename for the download.
  */
function print_download_http_headers($filename) {
   $filename = urlencode($filename);

   header('Content-type: application/x-unknown');
   header("Content-disposition: attachment; filename=\"$filename\"", true);
}




/**
 * This takes a string and returns the cleaned-up version of it.
 *
 * The cleanup includes trimming whitespace, removing HTML tags, and escaping HTML entities.
 * This cleanup should be suitable for all uses: category names, project names, description blocks, ...
 *
 * @param string $string The string to sanitize.
 * @return string The string, cleaned up.
 */
function sanitize_string($string) {
   $string = trim($string);
   $string = preg_replace('#[^a-zA-Z0-9_\-\s@\(\).\\/,]#', "_", $string);
   $string = strip_tags($string);
   $string = htmlentities($string);
   if (!$string) $string = ' ';
   return $string;
}


/**
 * This function prints the HTTP META tag to redirect the browser to the specified action.
 *
 * This is used extensively by the dispatchers, to send the browser to another page.
 * Example: print redirect("layerinfo&id=1234");
 *
 * @param string $action The dispatcher action, plus any additional arguments.
 * @return string A HTML block containing the appropriate refresh tags, to take the user to the specified action.
 */
function redirect($action=null) {
	$url = BASEURL;
	if(strpos($action,'/')) {
	    $url.=$action;
	} else {
	   $url.= (is_null($action)) ? '' : ((substr($action,0,1)=='?') ? $action : "?do=$action");
	}
	#die();
	
	echo "<script type='text/javascript'>window.location='$url';</script>";//<meta http-equiv=\"refresh\" content=\"0;url=".BASEURL."?do=$action\" />";
	return;
}




/**
 * Run a credit card, and return true/false indicating whether the charge went through.
 * @param float $amount The amount to charge the credit card.
 * @param string $description A description for the charge.
 * @param string $cardholder The cardholder's name.
 * @param string $cardnumber The credit card number.
 * @param string $expmonth The 2-digit month of the card's expiration, e.g. 01 for January.
 * @param string $expyear The 2-digit or 4-digit year of the card's expiration, e.g. 2006 or 06.
 * @return boolean True if the payment went through. False if it didn't.
 */
 /* function run_creditcard($key,$amount,$description,$cardholder,$cardnumber,$expmonth,$expyear) {
 $tran = new umTransaction;
   $tran->testmode = 1; // 1=just testing   0=really do it

   $tran->key         = $key;
   $tran->card        = preg_replace('/\D/','',$cardnumber);
   $tran->exp         = $expmonth . substr($expyear,-2,2);
   $tran->amount      = $amount;
   $tran->cardholder  = $cardholder;
   $tran->description = $description;
   $tran->invoice     = md5(microtime());
   $tran->street      = ' ';
   $tran->zip         = ' ';
   $tran->ip          = $_SERVER['REMOTE_ADDR'];

   return (bool) $tran->Process();
   
}
*/


/**
 * Given a pair of point coordinates, return the distance.
 * Note that coordinate parameters are expected to be in geographic projection (latlong) using WGS84/NAD83.
 * @param Database $db A database connection, like that provided by $world->db
 * @param float $point1lat The latitude of the first point.
 * @param float $point1lon The longitude of the first point.
 * @param float $point2lat The latitude of the second point.
 * @param float $point2lon The longitude of the second point.
 * @return array The distance between the points, in an array of units: feet, miles, meters, kilometers
 */
function distance($db,$lat1,$lon1,$lat2,$lon2) {
   // use the PosTGIS function distance_spheroid() to fetch the linear distance in meters
   $geom1 = "ST_GeometryFromText('POINT($lon1 $lat1)',4326)";
   $geom2 = "ST_GeometryFromText('POINT($lon2 $lat2)',4326)";
   $spheroid = 'SPHEROID["WGS 84",6378137,298.257223563]';
   $distance = $db->Execute("SELECT distance_spheroid($geom1,$geom2,'$spheroid') AS meters");
   $distance = $distance->fields['meters'];

   // make up the conversions
   $meters     = $distance;
   $kilometers = $meters / 1000;
   $feet       = $meters * 3.2808399;
   $miles      = $feet / 5280;

   // done!
   return array($feet,$miles,$meters,$kilometers);
}

function wkt_distance($db,$pt1,$pt2) {
   // use the PosTGIS function distance_spheroid() to fetch the linear distance in meters
   $geom1 = "ST_GeometryFromText('$pt1',4326)";
   $geom2 = "ST_GeometryFromText('$pt2',4326)";
   
   $spheroid = 'SPHEROID["WGS 84",6378137,298.257223563]';
   
   $distance = $db->Execute("SELECT ST_DistanceSpheroid($geom1,$geom2,?) AS meters",array($spheroid));
   
   $distance = $distance->fields['meters'];

   // make up the conversions
   $meters     = $distance;
   $kilometers = $meters / 1000;
   $feet       = $meters * 3.2808399;
   $inches = $feet/12;
   $miles      = $feet / 5280;

   // done!
   return array($feet,$miles,$meters,$kilometers,$inches);
}


/**
 * @ignore
 * Create a SWF for loading a project, and return the filename of the generated SWF.
 * The caller will presumably either send a redirect or call readfile() to send the content.
 * @param string $tempdir The temporary directory where the SWF should be dumped.
 * @return string The filename (not including the directory) of the generated SWF.
 */
/**
 /function createSataySWFLoader($tempdir,$whichswf,$params) {
   // convert the array of params into a URL-encoded string
   foreach ($params as $k=>$v) $params[$k] = sprintf("%s=%s",urlencode($k),urlencode($v));
   $params = implode('&',array_values($params));

   // create the SWF and save it to a file, then return the filename
   $swf = new SWFMovie();
   $swf->add(new SWFAction("
              var requiredFlashVersion = 8;
              var pluginUrl = 'https://www.adobe.com/products/flashplayer/';
              var version = System.capabilities.version;
              var majorVersion = Number(version.split(',')[0].split(' ')[1]);
              if (majorVersion >= requiredFlashVersion) { loadMovie('{$whichswf}?{$params}',_level0); }
              else { getURL(pluginUrl); }
             "));
   $filename = md5(microtime().mt_rand()) . '.swf';
   $filehandle = fopen("{$tempdir}/{$filename}",'w');
   $swf->save($filehandle,9);
   return $filename;
}
*/




/**
 * Given a comma-joined list of tags, generate hyperlinks to turn it into a list of
 * hyperlinked tags.
 *
 * This is useful for turning tags into search terms, for example.
 *
 * Example:
 *
 * activate_tags('santa barbara, earthquakes, risk mitigation','http://www.google.com/search?q=');
 *
 * @param string $string The string of tags. Extraneous spaces are fine, and will be standardized.
 * @param string $baseurl The base URL which will be prepended to the tag.
 * @return string A comma-joined string of tags, each one being a hyperlink.
 */
function activate_tags($string,$baseurl) {
   // go through the tags and turn them into search hyperlinks
   $links = array();
   foreach (explode(',',$string) as $tag) {
      $encoded = urlencode(trim($tag));
      if(!empty($encoded)) array_push($links, "<span class=\"tag\"><a href=\"{$baseurl}{$encoded}\">$tag</a></span>" );
   }
   // put them back together for the return
	if(empty($links)) return '<div class="tagsinput" style="width:auto; min-height: 90px; height: 100%; max-width:6in;"></div>';
	else return '<div class="tagsinput" style="width:auto; min-height: 90px; height: 100%; max-width:6in;">'.implode('<span style="display:none;">, </span>',$links).'<div class="tags_clear"></div></div>';
}
function pgsql2pgsql($world,$fromLayer,$toLayer,$quiet=false){
	if(is_string($toLayer))
		$toTable = $toLayer;
	else
		$toTable = $toLayer->url;
	if(is_string($fromLayer)){
		$fromTable = $fromLayer;
		$type = LayerTypes::VECTOR;
	}else{
		$fromTable = $fromLayer->url;
		$type = $fromLayer->type;
	}
	if (!$quiet) ping('Starting...<br/>');
	$db = $world->db;
	if (!$quiet) ping('Copying Table Structure...<br/>');
	if($type == LayerTypes::VECTOR){
		$db->Execute("DROP TABLE IF EXISTS {$toTable}");
		$db->Execute("CREATE TABLE {$toTable} (LIKE {$fromTable} INCLUDING DEFAULTS INCLUDING CONSTRAINTS INCLUDING STORAGE INCLUDING COMMENTS)");
	}else{
		$config = $world->db->Execute('SELECT url FROM layers WHERE id=?', array($fromTable->id) )->fields['url'];
		$config = unserialize($config);
		$db->Execute("CREATE TABLE {$toTable} (LIKE {$fromTable} INCLUDING DEFAULTS INCLUDING CONSTRAINTS INCLUDING STORAGE INCLUDING COMMENTS)");
	}
	if (!$quiet) ping('Correcting Sequence Data...<br/>');
	$db->Execute("CREATE SEQUENCE {$toTable}_gid_seq START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1");
	$db->Execute("ALTER TABLE {$toTable} ALTER COLUMN gid SET DEFAULT nextval('{$toTable}_gid_seq'::regclass)");
	$db->Execute("ALTER TABLE {$toTable} ADD PRIMARY KEY (gid)");
	if (!$quiet) ping('Indexing...<br/>');
	$db->Execute("CREATE INDEX {$toTable}_index_the_geom ON $toTable USING GIST (the_geom)");
    $db->Execute("CREATE INDEX {$toTable}_index_oid ON $toTable (oid)");
	if (!$quiet) ping('Copying Table Data...<br/>');
	$db->Execute("INSERT INTO {$toTable} SELECT * FROM {$fromTable}");
	if (!$quiet) ping('Correcting Sequence Position...<br/>');
	$db->Execute("SELECT setval('{$toTable}_gid_seq'::regclass, max(gid), false) FROM {$toTable};");
	if (!$quiet) ping('Adding Layer Type...<br/>');
	$db->Execute("INSERT INTO geometry_columns (f_table_catalog,f_table_schema,f_table_name,f_geometry_column,coord_dimension,srid,type) VALUES (?,?,?,?,?,?,?)", array('','public',$toTable,'the_geom',2,4326,$db->Execute('SELECT type FROM geometry_columns WHERE f_table_name=?', array($fromTable) )) );
}

/**
 * Call shp2pgsql to convert a shapefile to a PostGIS table.
 * Note that no sanity checking is done on the parameters; that's up to the caller!
 *
 * @see Person::createLayer()
 * @param World $world A World object. This is mostly used for the 'db' connection, but also to generate tempfiles.
 * @param string $shapefile The shapefile to import.
 * @param string $table The target table. Note that this table will be overwritten with the shapefile's contents.
 */
function shp2pgsql($world,$shapefile,$table,$quiet=false,$reimport=null) {
	 // run shp2pgsql and dump the output into a tempfile
   $sqlfile = sprintf("%s/%s.sql", dirname($shapefile),basename($shapefile,'.shp') );
   if (!$quiet) ping("tempfile $sqlfile");
   if (!$quiet) ping('Converting... ');
   if ($reimport==true){
	   exec("shp2pgsql -W LATIN1 -a -s 4326 \"$shapefile\" \"$table\" > $sqlfile");
   }else{
	   exec("shp2pgsql -W LATIN1 -d -s 4326 \"$shapefile\" \"$table\" > $sqlfile");
   }		
   if (!$quiet) ping('done<br/>');
	//error_log("Writing sql file: $sqlfile");
   // the old trick of slurping in the file with file_get_contents() just uses too much memory
   // instead, we have to be more clever. We read lines and keep concatenating until we get a line ending with ;
   // This uses very little memory indeed!
   $sqlfile = fopen($sqlfile,'r');
   $db = $world->db;
   //$db->debug = true;
   $command = '';
   $i = 0;
   $start = -1;
   if (!$quiet) ping('Importing');
   while (($line = fgets($sqlfile))!=false) {
      $command .= $line; if (substr($command,-2)!=";\n") continue;
      if ($command!="BEGIN;\n" and $command!="COMMIT;\n" and substr($command,-3,2)!=');' and substr($command,-3,2)!='";') continue;
      // finally, a completed command
      $command = substr($command,0,strlen($command)-2); // remove the ;\n from the end
      // sanity checks; if it's a CREATE TABLE, sanitize some data types
      if (substr($command,0,13)=='CREATE TABLE ') {
         $command = preg_replace('/ varchar\(\d*\)/i', 'text', $command); // replace varchar(x) fields with unlimited-length text fields
         $command = preg_replace('/ date/i', 'text', $command); // replace date and time fields with varchar
         $command = preg_replace('/ time/i', 'text', $command); // replace date and time fields with varchar
      }
      // sanity checks, for anything else such as INSERTs
      $command = preg_replace('/("\w+")"/','$1', $command); // column names with " marks? sheesh! this works for both CREATE TABLE and later INSERTs
      // execute it, send a . to the screen to show progress, and purge the command string
      //echo "----\n";
      //echo $command."\n";
      $db->Execute($command);
      
      $i++; if ($i%100==0) ping('.');

      $command = '';
   }
   $command = "UPDATE $table SET the_geom=ST_SimplifyPreserveTopology(the_geom, 0.0001) WHERE ST_IsValid(the_geom) = false;";
   $db->Execute($command);
   
   if (!$quiet) ping('done<br/>');

   // iterate through the columns and find any ill-named ones
   if (!$quiet) ping('Checking column names...');
   $fix_cols = $db->Execute("SELECT * FROM $table LIMIT 1");
   foreach ($fix_cols->fields as $column=>$value) {
      if (preg_match('/^\w+$/',$column)) continue;
      $newcolumn = preg_replace('/\W/', '_', $column);
      $db->Execute("ALTER TABLE $table RENAME COLUMN \"$column\" to \"$newcolumn\"");
   }
   if (!$quiet) ping('done<br/>');

   // lastly: create the appropriate indexes
   if (!$quiet) ping('Indexing');
   $db->Execute("CREATE INDEX {$table}_index_the_geom ON $table USING GIST (the_geom)");
   if (!$quiet) ping('.');
   $db->Execute("CREATE INDEX {$table}_index_oid ON $table (oid)");
   if (!$quiet) ping('.done<br/>');
}



/**
 * Dump the specified PgSQL/PostGIS table to a shapefile.
 * @param World $world A World object. This is mostly used for the 'db' connection, but also to generate tempfiles.
 * @param string $table The table to dump.
 * @param string $filename The filename for the shapefile, minus the .shp extension.
 * @return a 4-tuple of the files comprising the shapefile
 */
function pgsql2shp($world,$table,$filename,$quiet=false,$srs=null) {
	$ini = System::GetIni();
  // generate the filenames we'll be using
   $random = md5(microtime() . $table . mt_rand() );
   $tempDir = $ini->tempdir."{$random}/";
   
   mkdir("{$tempDir}");

   $view     = "temp_{$table}_{$random}";
   $shpfile  = "{$tempDir}$filename.shp";
   $shxfile  = "{$tempDir}$filename.shx";
   $dbffile  = "{$tempDir}$filename.dbf";
   $prjfile  = "{$tempDir}$filename.prj";
   
   
   // run pgsql2shp to dump the table to a shapefile
   $command = escapeshellcmd("pgsql2shp -f \"{$shpfile}\" -P '".$ini->pg_admin_password."' -u {$ini->pg_admin_user} -h {$ini->pg_host} {$ini->pg_sl_db} {$table}");
   $res = shell_exec($command);
   
   if($srs !== false) {
    // create a stock PRJ file
    $srs = is_null($srs) ? 'GEOGCS["WGS 84",DATUM["WGS_1984",SPHEROID["WGS 84",6378137,298.257223563,AUTHORITY["EPSG","7030"]],TOWGS84[0,0,0,0,0,0,0],AUTHORITY["EPSG","6326"]],PRIMEM["Greenwich",0,AUTHORITY["EPSG","8901"]],UNIT["degree",0.01745329251994328,AUTHORITY["EPSG","9122"]],AUTHORITY["EPSG","4326"]]' : $srs;
    file_put_contents($prjfile,$srs);
   }
   // return a 4-tuple of the files comprising the shapefile
   return array($shpfile,$shxfile,$dbffile,$prjfile);
}



/**
  * a function to generate a unique field name. this is in response to ticket #146, where someone noticed that truncating the field name
  * to 10 characters doesn't work if that truncated version is not unique, e.g. gepologiczone and geologiczonecode are both geologiczo!
  * @param already An associative array whose keys are the fieldnames that already exist.
  * @param fieldname The string fieldname.
  * @return The string fieldname, which may or may not have been modified to be unique.
  */
function generate_unique_fieldname($already,$fieldname) {
   $letters = array('a','b','c','d','e','f','g','h','i','j','k','l','m',
                    'n','o','p','q','r','s','t','u','v','w','x','y','z',
                    '1','2','3','4','5','6','7','8','9','0');
   $position = $maxposition = 10;
   $fieldname = substr($fieldname,0,$maxposition);
   while (@$already[$fieldname]) {
      if (strlen($fieldname) >= $position) $fieldname = substr($fieldname,0,$position-1);
      $fieldname .= $letters[array_rand($letters)];
      $position -= 1; if (!$position) break;
   }
   return $fieldname;
}



/**
 * Fetch a color scheme by its type, number, and name; return the array of colors
 * @param string $type The type of color scheme, one of the COLORSCHEMETYPE_ constants from colorschemes.php
 * @param integer $number How many steps in the color palette?
 * @param string $name The name of the color scheme, as represented in $COLORSCHEMES in colorschemes.php
 * @return A list-array of HTML color codes, or else null if there was no such color scheme.
 */
function get_color_scheme($type,$number,$name) {
   global $COLORSCHEMES;
   return $COLORSCHEMES[$type][$number][$name];
}




/**
 * Given three criteria arguments (field, operator, value) generate the corresponding SQL.
 * The list of criteria_operator choices is: == > < <= >= contains
 * @param dbhandle A database handle, e.g. $world->db
 * @param criteria_field The field for the criterion, e.g. "population"
 * @param criteria_operator The operator for the criterion, e.g. ">"
 * @param criteria_value The comparison value for the criterion, e.g. "1000000"
 * @return A string of SQL appropriate for inclusion in a WHERE clause. If the criteria are blank, the string 'true' will be returned, which is appropriate for use in WHERE clauses both with and without other comparisons.
 */
function criteria_to_sql($db,$criteria1,$criteria2,$criteria3) {
   $criteria1 = preg_replace('/\W/','',$criteria1); // the field is always a plain old word

   if ($criteria1=='' or $criteria2=='' or ($criteria2!='isnull' and $criteria3=='')) {
      $sql = 'true';
   }
   elseif (in_array($criteria2,array('<','>','<=','>='))) {
      $criteria3 = $db->qstr(strtolower(trim($criteria3)));
      $sql = "\"{$criteria1}\" {$criteria2} {$criteria3}";
   }
   elseif ($criteria2 == '==') {
      $criteria3 = $db->qstr(strtolower(trim($criteria3)));
      $sql = "\"{$criteria1}\" = {$criteria3}";
   }
   elseif( $criteria2 == '!contains' ) {
		$criteria3 = $db->qstr("%".strtolower($criteria3)."%");
      	$sql = "lower(\"{$criteria1}\" NOT LIKE $criteria3";
   }
   elseif ($criteria2 == 'contains') {
      $criteria3 = $db->qstr("%".strtolower($criteria3)."%");
      $sql = "lower(\"{$criteria1}\") LIKE $criteria3";
   }
   elseif ($criteria2 == 'isnull') {
      $sql = "$criteria1 IS NULL OR $criteria1::text=''";
   }
   else {
      $sql = true;
   }
   return $sql;
}




/**
 * Given the WKT of a point, return a  2-tuple of the X and Y values from it.
 * This is a very simple function, but saves tedium!
 * @param string $wkt A Well-Known Text (WKT) representation of a point, e.g. POINT(-123.456 78.90)
 * @return array A 2-item array containing the X and Y values (longitude and latitude) of the point.
 */
function parse_point($wkt) {
   $coords = array();
   preg_match('/POINT\((\-?\d+\.?\d*) (\-?\d+\.?\d*)\)/', $wkt,$coords);
   array_shift($coords);
   return $coords;
}


/**
 * Given a WMS GetMap URL (the URL of a image request), prune it down to only the base components
 * by removing the width, height, bbox, and other stuff that vaires between requests
 * @param string $url The URL of a WMS GetMap request, a working picture
 * @return string The shorter version of the WMS URL, which may contain LAYERS= parameter
 */
function pruneWMSurl($url) {
/*   $url = preg_replace('/&?VERSION=[^&]+/i','',$url);
   $url = preg_replace('/&?SRS=[^&]+/i','',$url);
   $url = preg_replace('/&?SERVICE=[^&]+/i','',$url);
   $url = preg_replace('/&?REQUEST=[^&]+/i','',$url);
   $url = preg_replace('/&?FORMAT=[^&]+/i','',$url);
   $url = preg_replace('/&?BBOX=[^&]+/i','',$url);
   $url = preg_replace('/&?WIDTH=[^&]+/i','',$url);
   $url = preg_replace('/&?HEIGHT=[^&]+/i','',$url);

   if ($aggressive) {
//      $url = preg_replace('/&?STYLES=[^&]*/   //i','',$url);
/*      $url = preg_replace('/&?TRANSPARENT=[^&]+/i','',$url);
      $url = preg_replace('/&?LAYERS=[^&]+/i','',$url);
      $url = preg_replace('/&?EXCEPTIONS=[^&]+/i','',$url);
  }
 */
   $url = preg_replace('/\?.*/','',$url);
   $url = preg_replace('/\&.*/', '', $url);
   $url = trim($url);
   return $url;
}

function wapi_exception_handler($exception) {
	WAPI::HandleError($exception);
}

?>
