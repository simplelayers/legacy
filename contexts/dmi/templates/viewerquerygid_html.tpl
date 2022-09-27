<html>
<head>
  <title><!--{$project->name}--> :: <!--{$layer->name}--> :: <!--{$label}--></title>
</head>
<body>

<style type="text/css">
body { font-size:10pt; }
table { border-collapse:collapse; width:100%; }
input[type="TEXT"] { width:100%; }
td { vertical-align:top; }
th { vertical-align:top; text-align:right; }
.title { font-weight:bold; font-size:14pt; }
tr.row1 { background-color:#EEEEEE; }
tr.row2 { background-color:#FFFFFF; }
</style>

<style type="text/css" media="print">
.noprint { display:none; }
</style>


<p>
   <span class="title">Feature Name: <!--{$label}--></span>
   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   <!--{if $editing}-->
   <a class="noprint" href=".?do=viewerquerygid&project=<!--{$smarty.request.project}-->&layer=<!--{$smarty.request.layer}-->&gid=<!--{$gid}-->&format=html">view</a>
   <!--{elseif $editable}-->
   <a class="noprint" href=".?do=viewerquerygid&project=<!--{$smarty.request.project}-->&layer=<!--{$smarty.request.layer}-->&gid=<!--{$gid}-->&format=html&edit">edit</a>
   <!--{/if}-->
   <!--{if ! $editing and ! $downloading }-->
   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   <a class="noprint" href=".?do=viewerquerygid&project=<!--{$smarty.request.project}-->&layer=<!--{$smarty.request.layer}-->&gid=<!--{$gid}-->&format=html&download">download</a>
   <!--{/if}-->
   <!--{if ! $editing and ! $downloading }-->
   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   <a class="noprint" href="javascript:print();">print</a>
   <!--{/if}-->
</p>

<!--{if $editing}-->
<form action="." method="post">
<input type="hidden" name="do" value="viewersavefeature" />
<input type="hidden" name="project" value="<!--{$smarty.request.project}-->" />
<input type="hidden" name="layer" value="<!--{$smarty.request.layer}-->" />
<input type="hidden" name="gid" value="<!--{$gid}-->" />
<input type="hidden" name="format" value="html" />
<!--{/if}-->


<table>
<!--{foreach from=$feature key=attrib item=value}-->
<tr class="<!--{cycle values="row1,row2"}-->">
  <th>
    <!--{$attrib|escape:'htmlall'}-->
  </th>
  <td>
    &nbsp;
  </td>
  <td>
    <!--{if $editing}-->
    <input type="text" name="attrib_<!--{$attrib|escape:'htmlall'}-->" value="<!--{$value|escape:'htmlall'}-->" />
    <!--{else}-->
    <!--{$value|escape:'htmlall'}-->
    <!--{/if}-->
  </td>
</tr>
<!--{/foreach}-->
</table>


<!--{if $editing}-->
<p><input type="submit" value="save changes" /></p>
<!--{/if}-->

</form>
</body>
</html>
