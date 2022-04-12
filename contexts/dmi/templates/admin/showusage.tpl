<!--{$subnav}-->

<p>
Database usage: <!--{$person->diskUsageDB()|string_format:'%.1f' }--> MB<br/>
Raster file usage: <!--{$person->diskUsageFiles()|string_format:'%.1f' }--> MB<br/>
Disk space allowed: <!--{$person->diskUsageAllowed()|string_format:'%.1f' }--> MB<br/>
Disk space remaining: <!--{$person->diskUsageRemaining()|string_format:'%.1f' }--> MB
</p>


<p>
<form action="." method="post">
<input type="hidden" name="do" value="admin.adjustdisk"/>
<input type="hidden" name="id" value="<!--{$person->id}-->"/>

<select name="adjust1"><option value="add">Raise</option><option value="subtract">Lower</option></select>
this user's disk allowance by 
<select name="adjust2">
<option value="50">50 MB</option>
<option value="100">100 MB</option>
<option value="250">250 MB</option>
<option value="500">500 MB</option>
<option value="1000">1 GB</option>
<option value="2000">2 GB</option>
<option value="5000">5 GB</option>
</select> 
<input type="submit" value="go">

</form>
</p>


<table class="bordered">
<tr>
  <th>Layer name</th>
  <th>Type</th>
  <th>Usage (MiB)</th>
  <th>Info</th>
</tr>
<!--{section loop=$layers name=i}-->
<!--{cycle values="color,altcolor" assign=class}-->
<tr>
  <td class="<!--{$class}-->" style="width:3in;"><!--{$layers[i]->name}--></td>
  <td class="<!--{$class}-->" style="width:1in;"><!--{$layers[i]->geomtypestring}--></td>
  <td class="<!--{$class}-->" style="width:1in;text-align:right;"><!--{$layers[i]->diskusage|string_format:'%.1f'}--></td>
  <td class="<!--{$class}-->" style="width:0.25in;"><a href=".?do=layer.edit1&id=<!--{$layers[i]->id}-->">info</a></td>
</tr>
<!--{/section}-->
</table>
