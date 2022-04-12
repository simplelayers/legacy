<?php
/**
 * This function is part of functions.php but is separated out solely for readability
 * @package Overview
 */


/**
 * Generate a color picker, with a palette and a color preselected.
 * @param string $formname The name of the HTML form where this color picker will be used.
 *                         This does require that the HTMl form have a name.
 * @param string $htmlfieldname The name of the form element that will be created to store the color.
 * @param string $title A label or title for the color picker.
 *                      Optional, default to nothing.
 * @param string $preselected The color that should be selected by default, in HTML format, e.g. #FFFFFF.
 *                            Optional, defaults to #FFFFFF (white).
 * @param boolean $transparent True or false, indicating whether the "transparent" color should be offered as an option.
 *                             Optional, defaults to false.
 * @return string A block of HTML and JavaScript, a color picker palette with imagemap, etc.
 */
function color_picker($formname, $htmlfieldname, $title='', $preselected='#ffffff', $includetransparent=false, $function='') {
   $retstr = '';
   $retstr .=         "<script type=\"text/javascript\">\n";
   $retstr .=         "   var mapBackground = '#FFFFFF'\n";
   $retstr .=         "   function set_color(newcolor,htmlfieldname) {\n";
   $retstr .=         "      var divname = 'sample_' + htmlfieldname;\n";
   $retstr .= sprintf("      var formname = '%s';\n", $formname);
   $retstr .= sprintf("      mapBackground = newcolor;\n");
   $retstr .= sprintf("      document.forms[formname].elements[htmlfieldname].value = newcolor;\n",$formname,$htmlfieldname);
   $retstr .= sprintf("      %s",$function);
 
   $retstr .=         "      if (newcolor == 'trans') {\n";
   $retstr .=         "         document.getElementById(divname).style.backgroundColor = '';\n";
   $retstr .=         "         document.getElementById(divname).style.background = 'url(media/x.png)';\n";
   $retstr .=         "      } else { \n";
   $retstr .=         "         document.getElementById(divname).style.background = '';\n";
   $retstr .=         "         document.getElementById(divname).style.backgroundColor = newcolor;\n";
   $retstr .=         "      }\n";
   $retstr .=         "   }\n";
   $retstr .=         "</script>\n";
   $retstr .= sprintf("<input type=\"input\" name=\"%s\" value=\"%s\" onKeyUp=\"set_color({$htmlfieldname}.value,'$htmlfieldname')\">\n", $htmlfieldname, $preselected );

   $retstr .= <<<ENDOFBLOCK
      <style type="text/css">
         div.swatch { height:44px; width:44px;  border-style:solid; border-width:1px; }
      </style>
      <map name="colmap_small_$htmlfieldname">
      <area shape="rect" coords=" 1, 1,10,10" href="javascript:set_color('#FF0000','$htmlfieldname')">
      <area shape="rect" coords=" 1,12,10,21" href="javascript:set_color('#FF6600','$htmlfieldname')">
      <area shape="rect" coords=" 1,23,10,32" href="javascript:set_color('#FFFF00','$htmlfieldname')">
      <area shape="rect" coords=" 1,34,10,43" href="javascript:set_color('#00FF00','$htmlfieldname')">
      <area shape="rect" coords=" 1,45,10,54" href="javascript:set_color('#3366FF','$htmlfieldname')">
      <area shape="rect" coords=" 1,56,10,65" href="javascript:set_color('#663399','$htmlfieldname')">
      <area shape="rect" coords="12, 1,21,10" href="javascript:set_color('#FFFFFF','$htmlfieldname')">
      <area shape="rect" coords="12,12,21,21" href="javascript:set_color('#CCCCCC','$htmlfieldname')">
      <area shape="rect" coords="12,23,21,32" href="javascript:set_color('#999999','$htmlfieldname')">
      <area shape="rect" coords="12,34,21,43" href="javascript:set_color('#666666','$htmlfieldname')">
      <area shape="rect" coords="12,45,21,54" href="javascript:set_color('#333333','$htmlfieldname')">
      <area shape="rect" coords="12,56,21,65" href="javascript:set_color('#000000','$htmlfieldname')">
      </map>
      <map name="colmap_$htmlfieldname">
      <area shape="rect" coords="  1, 1,  7,10" href="javascript:set_color('#00FF00','$htmlfieldname')">
      <area shape="rect" coords="  9, 1, 15,10" href="javascript:set_color('#00FF33','$htmlfieldname')">
      <area shape="rect" coords=" 17, 1, 23,10" href="javascript:set_color('#00FF66','$htmlfieldname')">
      <area shape="rect" coords=" 25, 1, 31,10" href="javascript:set_color('#00FF99','$htmlfieldname')">
      <area shape="rect" coords=" 33, 1, 39,10" href="javascript:set_color('#00FFCC','$htmlfieldname')">
      <area shape="rect" coords=" 41, 1, 47,10" href="javascript:set_color('#00FFFF','$htmlfieldname')">
      <area shape="rect" coords=" 49, 1, 55,10" href="javascript:set_color('#33FF00','$htmlfieldname')">
      <area shape="rect" coords=" 57, 1, 63,10" href="javascript:set_color('#33FF33','$htmlfieldname')">
      <area shape="rect" coords=" 65, 1, 71,10" href="javascript:set_color('#33FF66','$htmlfieldname')">
      <area shape="rect" coords=" 73, 1, 79,10" href="javascript:set_color('#33FF99','$htmlfieldname')">
      <area shape="rect" coords=" 81, 1, 87,10" href="javascript:set_color('#33FFCC','$htmlfieldname')">
      <area shape="rect" coords=" 89, 1, 95,10" href="javascript:set_color('#33FFFF','$htmlfieldname')">
      <area shape="rect" coords=" 97, 1,103,10" href="javascript:set_color('#66FF00','$htmlfieldname')">
      <area shape="rect" coords="105, 1,111,10" href="javascript:set_color('#66FF33','$htmlfieldname')">
      <area shape="rect" coords="113, 1,119,10" href="javascript:set_color('#66FF66','$htmlfieldname')">
      <area shape="rect" coords="121, 1,127,10" href="javascript:set_color('#66FF99','$htmlfieldname')">
      <area shape="rect" coords="129, 1,135,10" href="javascript:set_color('#66FFCC','$htmlfieldname')">
      <area shape="rect" coords="137, 1,143,10" href="javascript:set_color('#66FFFF','$htmlfieldname')">
      <area shape="rect" coords="145, 1,151,10" href="javascript:set_color('#99FF00','$htmlfieldname')">
      <area shape="rect" coords="153, 1,159,10" href="javascript:set_color('#99FF33','$htmlfieldname')">
      <area shape="rect" coords="161, 1,167,10" href="javascript:set_color('#99FF66','$htmlfieldname')">
      <area shape="rect" coords="169, 1,175,10" href="javascript:set_color('#99FF99','$htmlfieldname')">
      <area shape="rect" coords="177, 1,183,10" href="javascript:set_color('#99FFCC','$htmlfieldname')">
      <area shape="rect" coords="185, 1,191,10" href="javascript:set_color('#99FFFF','$htmlfieldname')">
      <area shape="rect" coords="193, 1,199,10" href="javascript:set_color('#CCFF00','$htmlfieldname')">
      <area shape="rect" coords="201, 1,207,10" href="javascript:set_color('#CCFF33','$htmlfieldname')">
      <area shape="rect" coords="209, 1,215,10" href="javascript:set_color('#CCFF66','$htmlfieldname')">
      <area shape="rect" coords="217, 1,223,10" href="javascript:set_color('#CCFF99','$htmlfieldname')">
      <area shape="rect" coords="225, 1,231,10" href="javascript:set_color('#CCFFCC','$htmlfieldname')">
      <area shape="rect" coords="233, 1,239,10" href="javascript:set_color('#CCFFFF','$htmlfieldname')">
      <area shape="rect" coords="241, 1,247,10" href="javascript:set_color('#FFFF00','$htmlfieldname')">
      <area shape="rect" coords="249, 1,255,10" href="javascript:set_color('#FFFF33','$htmlfieldname')">
      <area shape="rect" coords="257, 1,263,10" href="javascript:set_color('#FFFF66','$htmlfieldname')">
      <area shape="rect" coords="265, 1,271,10" href="javascript:set_color('#FFFF99','$htmlfieldname')">
      <area shape="rect" coords="273, 1,279,10" href="javascript:set_color('#FFFFCC','$htmlfieldname')">
      <area shape="rect" coords="281, 1,287,10" href="javascript:set_color('#FFFFFF','$htmlfieldname')">
      <area shape="rect" coords="  1,12,  7,21" href="javascript:set_color('#00CC00','$htmlfieldname')">
      <area shape="rect" coords="  9,12, 15,21" href="javascript:set_color('#00CC33','$htmlfieldname')">
      <area shape="rect" coords=" 17,12, 23,21" href="javascript:set_color('#00CC66','$htmlfieldname')">
      <area shape="rect" coords=" 25,12, 31,21" href="javascript:set_color('#00CC99','$htmlfieldname')">
      <area shape="rect" coords=" 33,12, 39,21" href="javascript:set_color('#00CCCC','$htmlfieldname')">
      <area shape="rect" coords=" 41,12, 47,21" href="javascript:set_color('#00CCFF','$htmlfieldname')">
      <area shape="rect" coords=" 49,12, 55,21" href="javascript:set_color('#33CC00','$htmlfieldname')">
      <area shape="rect" coords=" 57,12, 63,21" href="javascript:set_color('#33CC33','$htmlfieldname')">
      <area shape="rect" coords=" 65,12, 71,21" href="javascript:set_color('#33CC66','$htmlfieldname')">
      <area shape="rect" coords=" 73,12, 79,21" href="javascript:set_color('#33CC99','$htmlfieldname')">
      <area shape="rect" coords=" 81,12, 87,21" href="javascript:set_color('#33CCCC','$htmlfieldname')">
      <area shape="rect" coords=" 89,12, 95,21" href="javascript:set_color('#33CCFF','$htmlfieldname')">
      <area shape="rect" coords=" 97,12,103,21" href="javascript:set_color('#66CC00','$htmlfieldname')">
      <area shape="rect" coords="105,12,111,21" href="javascript:set_color('#66CC33','$htmlfieldname')">
      <area shape="rect" coords="113,12,119,21" href="javascript:set_color('#66CC66','$htmlfieldname')">
      <area shape="rect" coords="121,12,127,21" href="javascript:set_color('#66CC99','$htmlfieldname')">
      <area shape="rect" coords="129,12,135,21" href="javascript:set_color('#66CCCC','$htmlfieldname')">
      <area shape="rect" coords="137,12,143,21" href="javascript:set_color('#66CCFF','$htmlfieldname')">
      <area shape="rect" coords="145,12,151,21" href="javascript:set_color('#99CC00','$htmlfieldname')">
      <area shape="rect" coords="153,12,159,21" href="javascript:set_color('#99CC33','$htmlfieldname')">
      <area shape="rect" coords="161,12,167,21" href="javascript:set_color('#99CC66','$htmlfieldname')">
      <area shape="rect" coords="169,12,175,21" href="javascript:set_color('#99CC99','$htmlfieldname')">
      <area shape="rect" coords="177,12,183,21" href="javascript:set_color('#99CCCC','$htmlfieldname')">
      <area shape="rect" coords="185,12,191,21" href="javascript:set_color('#99CCFF','$htmlfieldname')">
      <area shape="rect" coords="193,12,199,21" href="javascript:set_color('#CCCC00','$htmlfieldname')">
      <area shape="rect" coords="201,12,207,21" href="javascript:set_color('#CCCC33','$htmlfieldname')">
      <area shape="rect" coords="209,12,215,21" href="javascript:set_color('#CCCC66','$htmlfieldname')">
      <area shape="rect" coords="217,12,223,21" href="javascript:set_color('#CCCC99','$htmlfieldname')">
      <area shape="rect" coords="225,12,231,21" href="javascript:set_color('#CCCCCC','$htmlfieldname')">
      <area shape="rect" coords="233,12,239,21" href="javascript:set_color('#CCCCFF','$htmlfieldname')">
      <area shape="rect" coords="241,12,247,21" href="javascript:set_color('#FFCC00','$htmlfieldname')">
      <area shape="rect" coords="249,12,255,21" href="javascript:set_color('#FFCC33','$htmlfieldname')">
      <area shape="rect" coords="257,12,263,21" href="javascript:set_color('#FFCC66','$htmlfieldname')">
      <area shape="rect" coords="265,12,271,21" href="javascript:set_color('#FFCC99','$htmlfieldname')">
      <area shape="rect" coords="273,12,279,21" href="javascript:set_color('#FFCCCC','$htmlfieldname')">
      <area shape="rect" coords="281,12,287,21" href="javascript:set_color('#FFCCFF','$htmlfieldname')">
      <area shape="rect" coords="  1,23,  7,32" href="javascript:set_color('#009900','$htmlfieldname')">
      <area shape="rect" coords="  9,23, 15,32" href="javascript:set_color('#009933','$htmlfieldname')">
      <area shape="rect" coords=" 17,23, 23,32" href="javascript:set_color('#009966','$htmlfieldname')">
      <area shape="rect" coords=" 25,23, 31,32" href="javascript:set_color('#009999','$htmlfieldname')">
      <area shape="rect" coords=" 33,23, 39,32" href="javascript:set_color('#0099CC','$htmlfieldname')">
      <area shape="rect" coords=" 41,23, 47,32" href="javascript:set_color('#0099FF','$htmlfieldname')">
      <area shape="rect" coords=" 49,23, 55,32" href="javascript:set_color('#339900','$htmlfieldname')">
      <area shape="rect" coords=" 57,23, 63,32" href="javascript:set_color('#339933','$htmlfieldname')">
      <area shape="rect" coords=" 65,23, 71,32" href="javascript:set_color('#339966','$htmlfieldname')">
      <area shape="rect" coords=" 73,23, 79,32" href="javascript:set_color('#339999','$htmlfieldname')">
      <area shape="rect" coords=" 81,23, 87,32" href="javascript:set_color('#3399CC','$htmlfieldname')">
      <area shape="rect" coords=" 89,23, 95,32" href="javascript:set_color('#3399FF','$htmlfieldname')">
      <area shape="rect" coords=" 97,23,103,32" href="javascript:set_color('#669900','$htmlfieldname')">
      <area shape="rect" coords="105,23,111,32" href="javascript:set_color('#669933','$htmlfieldname')">
      <area shape="rect" coords="113,23,119,32" href="javascript:set_color('#669966','$htmlfieldname')">
      <area shape="rect" coords="121,23,127,32" href="javascript:set_color('#669999','$htmlfieldname')">
      <area shape="rect" coords="129,23,135,32" href="javascript:set_color('#6699CC','$htmlfieldname')">
      <area shape="rect" coords="137,23,143,32" href="javascript:set_color('#6699FF','$htmlfieldname')">
      <area shape="rect" coords="145,23,151,32" href="javascript:set_color('#999900','$htmlfieldname')">
      <area shape="rect" coords="153,23,159,32" href="javascript:set_color('#999933','$htmlfieldname')">
      <area shape="rect" coords="161,23,167,32" href="javascript:set_color('#999966','$htmlfieldname')">
      <area shape="rect" coords="169,23,175,32" href="javascript:set_color('#999999','$htmlfieldname')">
      <area shape="rect" coords="177,23,183,32" href="javascript:set_color('#9999CC','$htmlfieldname')">
      <area shape="rect" coords="185,23,191,32" href="javascript:set_color('#9999FF','$htmlfieldname')">
      <area shape="rect" coords="193,23,199,32" href="javascript:set_color('#CC9900','$htmlfieldname')">
      <area shape="rect" coords="201,23,207,32" href="javascript:set_color('#CC9933','$htmlfieldname')">
      <area shape="rect" coords="209,23,215,32" href="javascript:set_color('#CC9966','$htmlfieldname')">
      <area shape="rect" coords="217,23,223,32" href="javascript:set_color('#CC9999','$htmlfieldname')">
      <area shape="rect" coords="225,23,231,32" href="javascript:set_color('#CC99CC','$htmlfieldname')">
      <area shape="rect" coords="233,23,239,32" href="javascript:set_color('#CC99FF','$htmlfieldname')">
      <area shape="rect" coords="241,23,247,32" href="javascript:set_color('#FF9900','$htmlfieldname')">
      <area shape="rect" coords="249,23,255,32" href="javascript:set_color('#FF9933','$htmlfieldname')">
      <area shape="rect" coords="257,23,263,32" href="javascript:set_color('#FF9966','$htmlfieldname')">
      <area shape="rect" coords="265,23,271,32" href="javascript:set_color('#FF9999','$htmlfieldname')">
      <area shape="rect" coords="273,23,279,32" href="javascript:set_color('#FF99CC','$htmlfieldname')">
      <area shape="rect" coords="281,23,287,32" href="javascript:set_color('#FF99FF','$htmlfieldname')">
      <area shape="rect" coords="  1,34,  7,43" href="javascript:set_color('#006600','$htmlfieldname')">
      <area shape="rect" coords="  9,34, 15,43" href="javascript:set_color('#006633','$htmlfieldname')">
      <area shape="rect" coords=" 17,34, 23,43" href="javascript:set_color('#006666','$htmlfieldname')">
      <area shape="rect" coords=" 25,34, 31,43" href="javascript:set_color('#006699','$htmlfieldname')">
      <area shape="rect" coords=" 33,34, 39,43" href="javascript:set_color('#0066CC','$htmlfieldname')">
      <area shape="rect" coords=" 41,34, 47,43" href="javascript:set_color('#0066FF','$htmlfieldname')">
      <area shape="rect" coords=" 49,34, 55,43" href="javascript:set_color('#336600','$htmlfieldname')">
      <area shape="rect" coords=" 57,34, 63,43" href="javascript:set_color('#336633','$htmlfieldname')">
      <area shape="rect" coords=" 65,34, 71,43" href="javascript:set_color('#336666','$htmlfieldname')">
      <area shape="rect" coords=" 73,34, 79,43" href="javascript:set_color('#336699','$htmlfieldname')">
      <area shape="rect" coords=" 81,34, 87,43" href="javascript:set_color('#3366CC','$htmlfieldname')">
      <area shape="rect" coords=" 89,34, 95,43" href="javascript:set_color('#3366FF','$htmlfieldname')">
      <area shape="rect" coords=" 97,34,103,43" href="javascript:set_color('#666600','$htmlfieldname')">
      <area shape="rect" coords="105,34,111,43" href="javascript:set_color('#666633','$htmlfieldname')">
      <area shape="rect" coords="113,34,119,43" href="javascript:set_color('#666666','$htmlfieldname')">
      <area shape="rect" coords="121,34,127,43" href="javascript:set_color('#666699','$htmlfieldname')">
      <area shape="rect" coords="129,34,135,43" href="javascript:set_color('#6666CC','$htmlfieldname')">
      <area shape="rect" coords="137,34,143,43" href="javascript:set_color('#6666FF','$htmlfieldname')">
      <area shape="rect" coords="145,34,151,43" href="javascript:set_color('#996600','$htmlfieldname')">
      <area shape="rect" coords="153,34,159,43" href="javascript:set_color('#996633','$htmlfieldname')">
      <area shape="rect" coords="161,34,167,43" href="javascript:set_color('#996666','$htmlfieldname')">
      <area shape="rect" coords="169,34,175,43" href="javascript:set_color('#996699','$htmlfieldname')">
      <area shape="rect" coords="177,34,183,43" href="javascript:set_color('#9966CC','$htmlfieldname')">
      <area shape="rect" coords="185,34,191,43" href="javascript:set_color('#9966FF','$htmlfieldname')">
      <area shape="rect" coords="193,34,199,43" href="javascript:set_color('#CC6600','$htmlfieldname')">
      <area shape="rect" coords="201,34,207,43" href="javascript:set_color('#CC6633','$htmlfieldname')">
      <area shape="rect" coords="209,34,215,43" href="javascript:set_color('#CC6666','$htmlfieldname')">
      <area shape="rect" coords="217,34,223,43" href="javascript:set_color('#CC6699','$htmlfieldname')">
      <area shape="rect" coords="225,34,231,43" href="javascript:set_color('#CC66CC','$htmlfieldname')">
      <area shape="rect" coords="233,34,239,43" href="javascript:set_color('#CC66FF','$htmlfieldname')">
      <area shape="rect" coords="241,34,247,43" href="javascript:set_color('#FF6600','$htmlfieldname')">
      <area shape="rect" coords="249,34,255,43" href="javascript:set_color('#FF6633','$htmlfieldname')">
      <area shape="rect" coords="257,34,263,43" href="javascript:set_color('#FF6666','$htmlfieldname')">
      <area shape="rect" coords="265,34,271,43" href="javascript:set_color('#FF6699','$htmlfieldname')">
      <area shape="rect" coords="273,34,279,43" href="javascript:set_color('#FF66CC','$htmlfieldname')">
      <area shape="rect" coords="281,34,287,43" href="javascript:set_color('#FF66FF','$htmlfieldname')">
      <area shape="rect" coords="  1,45,  7,54" href="javascript:set_color('#003300','$htmlfieldname')">
      <area shape="rect" coords="  9,45, 15,54" href="javascript:set_color('#003333','$htmlfieldname')">
      <area shape="rect" coords=" 17,45, 23,54" href="javascript:set_color('#003366','$htmlfieldname')">
      <area shape="rect" coords=" 25,45, 31,54" href="javascript:set_color('#003399','$htmlfieldname')">
      <area shape="rect" coords=" 33,45, 39,54" href="javascript:set_color('#0033CC','$htmlfieldname')">
      <area shape="rect" coords=" 41,45, 47,54" href="javascript:set_color('#0033FF','$htmlfieldname')">
      <area shape="rect" coords=" 49,45, 55,54" href="javascript:set_color('#333300','$htmlfieldname')">
      <area shape="rect" coords=" 57,45, 63,54" href="javascript:set_color('#333333','$htmlfieldname')">
      <area shape="rect" coords=" 65,45, 71,54" href="javascript:set_color('#333366','$htmlfieldname')">
      <area shape="rect" coords=" 73,45, 79,54" href="javascript:set_color('#333399','$htmlfieldname')">
      <area shape="rect" coords=" 81,45, 87,54" href="javascript:set_color('#3333CC','$htmlfieldname')">
      <area shape="rect" coords=" 89,45, 95,54" href="javascript:set_color('#3333FF','$htmlfieldname')">
      <area shape="rect" coords=" 97,45,103,54" href="javascript:set_color('#663300','$htmlfieldname')">
      <area shape="rect" coords="105,45,111,54" href="javascript:set_color('#663333','$htmlfieldname')">
      <area shape="rect" coords="113,45,119,54" href="javascript:set_color('#663366','$htmlfieldname')">
      <area shape="rect" coords="121,45,127,54" href="javascript:set_color('#663399','$htmlfieldname')">
      <area shape="rect" coords="129,45,135,54" href="javascript:set_color('#6633CC','$htmlfieldname')">
      <area shape="rect" coords="137,45,143,54" href="javascript:set_color('#6633FF','$htmlfieldname')">
      <area shape="rect" coords="145,45,151,54" href="javascript:set_color('#993300','$htmlfieldname')">
      <area shape="rect" coords="153,45,159,54" href="javascript:set_color('#993333','$htmlfieldname')">
      <area shape="rect" coords="161,45,167,54" href="javascript:set_color('#993366','$htmlfieldname')">
      <area shape="rect" coords="169,45,175,54" href="javascript:set_color('#993399','$htmlfieldname')">
      <area shape="rect" coords="177,45,183,54" href="javascript:set_color('#9933CC','$htmlfieldname')">
      <area shape="rect" coords="185,45,191,54" href="javascript:set_color('#9933FF','$htmlfieldname')">
      <area shape="rect" coords="193,45,199,54" href="javascript:set_color('#CC3300','$htmlfieldname')">
      <area shape="rect" coords="201,45,207,54" href="javascript:set_color('#CC3333','$htmlfieldname')">
      <area shape="rect" coords="209,45,215,54" href="javascript:set_color('#CC3366','$htmlfieldname')">
      <area shape="rect" coords="217,45,223,54" href="javascript:set_color('#CC3399','$htmlfieldname')">
      <area shape="rect" coords="225,45,231,54" href="javascript:set_color('#CC33CC','$htmlfieldname')">
      <area shape="rect" coords="233,45,239,54" href="javascript:set_color('#CC33FF','$htmlfieldname')">
      <area shape="rect" coords="241,45,247,54" href="javascript:set_color('#FF3300','$htmlfieldname')">
      <area shape="rect" coords="249,45,255,54" href="javascript:set_color('#FF3333','$htmlfieldname')">
      <area shape="rect" coords="257,45,263,54" href="javascript:set_color('#FF3366','$htmlfieldname')">
      <area shape="rect" coords="265,45,271,54" href="javascript:set_color('#FF3399','$htmlfieldname')">
      <area shape="rect" coords="273,45,279,54" href="javascript:set_color('#FF33CC','$htmlfieldname')">
      <area shape="rect" coords="281,45,287,54" href="javascript:set_color('#FF33FF','$htmlfieldname')">
      <area shape="rect" coords="  1,56,  7,65" href="javascript:set_color('#000000','$htmlfieldname')">
      <area shape="rect" coords="  9,56, 15,65" href="javascript:set_color('#000033','$htmlfieldname')">
      <area shape="rect" coords=" 17,56, 23,65" href="javascript:set_color('#000066','$htmlfieldname')">
      <area shape="rect" coords=" 25,56, 31,65" href="javascript:set_color('#000099','$htmlfieldname')">
      <area shape="rect" coords=" 33,56, 39,65" href="javascript:set_color('#0000CC','$htmlfieldname')">
      <area shape="rect" coords=" 41,56, 47,65" href="javascript:set_color('#0000FF','$htmlfieldname')">
      <area shape="rect" coords=" 49,56, 55,65" href="javascript:set_color('#330000','$htmlfieldname')">
      <area shape="rect" coords=" 57,56, 63,65" href="javascript:set_color('#330033','$htmlfieldname')">
      <area shape="rect" coords=" 65,56, 71,65" href="javascript:set_color('#330066','$htmlfieldname')">
      <area shape="rect" coords=" 73,56, 79,65" href="javascript:set_color('#330099','$htmlfieldname')">
      <area shape="rect" coords=" 81,56, 87,65" href="javascript:set_color('#3300CC','$htmlfieldname')">
      <area shape="rect" coords=" 89,56, 95,65" href="javascript:set_color('#3300FF','$htmlfieldname')">
      <area shape="rect" coords=" 97,56,103,65" href="javascript:set_color('#660000','$htmlfieldname')">
      <area shape="rect" coords="105,56,111,65" href="javascript:set_color('#660033','$htmlfieldname')">
      <area shape="rect" coords="113,56,119,65" href="javascript:set_color('#660066','$htmlfieldname')">
      <area shape="rect" coords="121,56,127,65" href="javascript:set_color('#660099','$htmlfieldname')">
      <area shape="rect" coords="129,56,135,65" href="javascript:set_color('#6600CC','$htmlfieldname')">
      <area shape="rect" coords="137,56,143,65" href="javascript:set_color('#6600FF','$htmlfieldname')">
      <area shape="rect" coords="145,56,151,65" href="javascript:set_color('#990000','$htmlfieldname')">
      <area shape="rect" coords="153,56,159,65" href="javascript:set_color('#990033','$htmlfieldname')">
      <area shape="rect" coords="161,56,167,65" href="javascript:set_color('#990066','$htmlfieldname')">
      <area shape="rect" coords="169,56,175,65" href="javascript:set_color('#990099','$htmlfieldname')">
      <area shape="rect" coords="177,56,183,65" href="javascript:set_color('#9900CC','$htmlfieldname')">
      <area shape="rect" coords="185,56,191,65" href="javascript:set_color('#9900FF','$htmlfieldname')">
      <area shape="rect" coords="193,56,199,65" href="javascript:set_color('#CC0000','$htmlfieldname')">
      <area shape="rect" coords="201,56,207,65" href="javascript:set_color('#CC0033','$htmlfieldname')">
      <area shape="rect" coords="209,56,215,65" href="javascript:set_color('#CC0066','$htmlfieldname')">
      <area shape="rect" coords="217,56,223,65" href="javascript:set_color('#CC0099','$htmlfieldname')">
      <area shape="rect" coords="225,56,231,65" href="javascript:set_color('#CC00CC','$htmlfieldname')">
      <area shape="rect" coords="233,56,239,65" href="javascript:set_color('#CC00FF','$htmlfieldname')">
      <area shape="rect" coords="241,56,247,65" href="javascript:set_color('#FF0000','$htmlfieldname')">
      <area shape="rect" coords="249,56,255,65" href="javascript:set_color('#FF0033','$htmlfieldname')">
      <area shape="rect" coords="257,56,263,65" href="javascript:set_color('#FF0066','$htmlfieldname')">
      <area shape="rect" coords="265,56,271,65" href="javascript:set_color('#FF0099','$htmlfieldname')">
      <area shape="rect" coords="273,56,279,65" href="javascript:set_color('#FF00CC','$htmlfieldname')">
      <area shape="rect" coords="281,56,287,65" href="javascript:set_color('#FF00FF','$htmlfieldname')">
      </map>
ENDOFBLOCK;

$retstr .= <<<ENDOFBLOCK
      <table>
      <tr>
ENDOFBLOCK;

   if ($includetransparent) {
      if ($preselected != 'trans') {
         $retstr .= sprintf("  <td style=\"vertical-align:top;text-align:left;width:1in;white-space:nowrap;font-size:70%%;\">\n<b>%s</b>\n\n<div class=\"swatch\" id=\"sample_%s\" style=\"background-color:%s;\"> &nbsp; </div>\n\n<a href=\"javascript:set_color('trans','%s');\">transparent</a>\n</td>\n", $title, $htmlfieldname, $preselected, $htmlfieldname );
      } else {
         $retstr .= sprintf("  <td style=\"vertical-align:top;text-align:left;width:1in;white-space:nowrap;font-size:70%%;\">\n<b>%s</b>\n\n<div class=\"swatch\" id=\"sample_%s\" style=\"background:url(media/x.png);\"> &nbsp; </div>\n\n<a href=\"javascript:set_color('trans','%s');\">transparent</a>\n</td>\n", $title, $htmlfieldname, $htmlfieldname );
      }
   }
   else {
      $retstr .= sprintf("  <td style=\"vertical-align:top;text-align:left;width:1in;white-space:nowrap;font-size:70%%;\">\n<b>%s</b>\n<br/>\n<div class=\"swatch\" id=\"sample_%s\" style=\"background-color:%s;\"> &nbsp; </div>\n</td>\n", $title, $htmlfieldname, $preselected );
   }

$retstr .= <<<ENDOFBLOCK
      <td><a><img usemap="#colmap_small_$htmlfieldname" src="media/images/colortable_small.gif" border="0"></a></td>
      <td><a><img usemap="#colmap_$htmlfieldname" src="media/images/colortable.gif" border="0"></a></td>
      </tr>
      </table>
ENDOFBLOCK;

   return $retstr;
}



?>
