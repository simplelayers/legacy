<?php include($DOCUMENT_ROOT . "/Templates/templates.php"); 
 echo"<HTML><body>";  
  // OJW 2006  GNU GPL v2 or later
 
  $Lat = $_GET["lat"];
  $Long = $_GET["long"];
  print "<p><form action=\"./\" method=\"get\">";
  printf("Lat: <input type=\"text\" name=\"lat\" value=\"%f\"> \n", $Lat);
  printf("Long: <input type=\"text\" name=\"long\" value=\"%f\"> \n", $Long);
  printf("<input type=\"submit\" value=\"Convert\">\n");
  print "</form></p>";
 
  $PX = ($Long + 180) / 360; 
  $PY = Lat2Y($Lat);

  for($Z = 3; $Z < 16; $Z++){
    $Size = pow(2,$Z);
    $X = $PX * $Size;
    $Y = $PY * $Size;
    
    printf("<p%s><a href=\"%s?x=%d&amp;y=%d&amp;z=%d\">%d,%d</a> at zoom %d %s</p>",
      $Z == 12 ? " style=\"font-weight:bold;\"":"",
      "http://osmathome.bandnet.org/Browse/",
      $X, 
      $Y, 
      $Z,
      $X, 
      $Y, 
      $Z,
      $Z == 12 ? " -- use this for tiles@home":"");
  }
 
  print "<hr>";
  $X = $_GET["x"]+0;
  $Y = $_GET["y"]+0;
  $Zoom = $_GET["z"]+0;
  print "<p><form action=\"temp_test.php\" method=\"get\">";
  printf("X: <input type=\"text\" name=\"x\" value=\"%d\"> \n", $X);
  printf("Y: <input type=\"text\" name=\"y\" value=\"%d\"> \n", $Y);
  printf("Zoom: <input type=\"text\" name=\"z\" value=\"%d\"> \n", $Zoom);
  printf("<input type=\"submit\" value=\"Convert\">\n");
  print "</form></p>";
 
list($N, $S) = Project($Y, $Zoom);
list($W, $E) = ProjectL($X, $Zoom);
  
printf("<p>Latitude: %f to %f</p>",$S,$N);
printf("<p>Longitude: %f to %f</p>",$W,$E);

printf("<p>Size: %f x %f</p>", $E-$W, $N-$S);
printf("<p>Centre: %f, %f</p>", ($N+$S)/2, ($E+$W)/2);


function Lat2Y($Lat){
  $LimitY = ProjectF(85.0511);
  $Y = ProjectF($Lat);
  
  $PY = ($LimitY - $Y) / (2 * $LimitY);
  return($PY);
}
function ProjectF($Lat){
  $Lat = deg2rad($Lat);
  $Y = log(tan($Lat) + (1/cos($Lat)));
  return($Y);
}
function Project($Y, $Zoom){
  $LimitY = ProjectF(85.0511);
  $RangeY = 2 * $LimitY;
  
  $Unit = 1 / pow(2, $Zoom);
  $relY1 = $Y * $Unit;
  $relY2 = $relY1 + $Unit;
  
  $relY1 = $LimitY - $RangeY * $relY1;
  $relY2 = $LimitY - $RangeY * $relY2;
    
  $Lat1 = ProjectMercToLat($relY1);
  $Lat2 = ProjectMercToLat($relY2);
  return(array($Lat1, $Lat2));  
}
function ProjectMercToLat($MercY){
  return(rad2deg(atan(sinh($MercY))));
}
function ProjectL($X, $Zoom){
  $Unit = 360 / pow(2, $Zoom);
  $Long1 = -180 + $X * $Unit;
  return(array($Long1, $Long1 + $Unit));  
}
 ?>
</body></html>