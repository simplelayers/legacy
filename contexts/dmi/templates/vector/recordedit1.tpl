<form action="." method="post">
<input type="hidden" name="do" value="vector.recordedit2"/>
<input type="hidden" name="id" value="<!--{$layer->id}-->"/>
<input type="hidden" name="gid" value="<!--{$gid}-->"/>



<table class="bordered">
<!--{foreach from=$record key=column item=value}-->
<tr>
  <th><!--{$column}--> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; (<!--{$columns.$column.requires}-->)</th>
</tr>
<tr>
  <td><textarea <!--{if !$isRecordEditor}-->readonly<!--{/if}--> name="column_<!--{$column}-->" style="width:9in;height:1in;"><!--{$value|escape:'htmlall'}--></textarea></td>
</tr>
<!--{/foreach}-->
</table>
<!--{if $isRecordEditor}-->
<!--{if $hasGeom}-->
<!--{if !$includeWKT}-->
<button type='button' name='include_wkt' class='button'  onClick='location.href="<!--{$withGeomURL}-->"'>Include Geometry</button>
<!--{else}-->
<button type='button' name='include_wkt' class='button'  onClick='location.href="<!--{$withoutGeomURL}-->"'>Exclude Geometry</button>
<!--{/if}-->
<!--{/if}-->
<p><button class='button' name="submit" style="width:2in;" >Save Changes</button></p>
<!--{/if}-->

</form>
