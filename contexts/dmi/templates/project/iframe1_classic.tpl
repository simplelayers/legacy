<p class="title">HTML for embedding your Map:<br/>
   <!--{$project->name}--> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href=".?do=project.edit1&id=<!--{$project->id}-->">details</a>
</p>

<p>This tool will generate a block of HTML called an &quot;iframe&quot;<br/>
You can paste this HTML into a web page to embed your map into that page.</p>

<form action="." method="post">
<input type="hidden" name="do" value="project.iframe2_classic"/>
<input type="hidden" name="id" value="<!--{$project->id}-->"/>

<p>
<b>What size will you want the embedded map?</b><br/>
Width: <input type="text" style="width:0.5in" name="width" value="735" /> pixels
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Height: <input type="text" style="width:0.5in" name="height" value="735" /> pixels
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="noresize" value="1" /> Do not scale map
</p>

<table>
<tr>
  <td> <b>What tools should be shown?</b> <br/>
       <!--{html_checkboxes name=toolcode options=$toolcodes separator='<br/>' selected=$toolselected}-->
  </td>
  <td style="width:1in;">&nbsp;</td>
  <td> <b>What other features should be shown?</b> <br/>
       <!--{html_checkboxes name=featurecode options=$featurecodes separator='<br/>' selected=$featureselected}-->
  </td>
</tr>
</table>


<p><input type="submit" name="submit" value="generate iframe html"/></p>


</form>
