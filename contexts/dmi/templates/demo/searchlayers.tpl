<center>
<div class="greybox">

<p class="title">Search layers</p>
<form action="." method="post" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="demo.searchlayers" />
<input type="text" style="width:3in" name="search" value="<!--{$searchterm}-->" /> <input type="submit" name="submit" value="search" /> <input type="submit" name="submit" value="show all" onClick="document.forms[1].elements['search'].value='all available';return true;" />
</form>

<table class="bordered">
  <tr>
    <th>Layer</th>
    <th>Type</th>
    <th>Owner</th>
    <th>Description</th>
  </tr>
<!--{section loop=$matches name=i}-->
  <!--{assign var='type' value=$matches[i]->type }-->
  <tr>
    <td style="width:2in;"><a href=".?do=demo.layerinfo&id=<!--{$matches[i]->id}-->"><!--{$matches[i]->name|truncate:30:"..."}--></a> &nbsp;</td>
    <td style="width:0.1in;"><!--{$matches[i]->geomtypestring}--></td>
    <td style="width:1in;"><a href=".?do=demo.peopleinfo&id=<!--{$matches[i]->owner->id}-->"><!--{$matches[i]->owner->username}--></a></td>
    <td style="width:5in;"><!--{$matches[i]->description|truncate:75:"..."}--> &nbsp;</td>
  </tr>
<!--{/section}-->
</table>

</div>
</center>
