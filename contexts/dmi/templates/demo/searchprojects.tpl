<center>
<div class="greybox">

<p class="title">Search Maps</p>
<form action="." method="post" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="demo.searchprojects" />
<input type="text" style="width:3in" name="search" value="<!--{$searchterm}-->" /> <input type="submit" name="submit" value="search" /> <input type="submit" name="submit" value="show all" onClick="document.forms[1].elements['search'].value='all available';return true;" />
</form>

<table class="bordered">
  <tr>
    <th>Map</th>
    <th>View</th>
    <th>Info</th>
    <th>Owner</th>
    <th>Description</th>
  </tr>
<!--{section loop=$matches name=i}-->
  <tr>
    <td style="width:2in;"><b><!--{$matches[i]->name|truncate:30:"..."}--></b></td>
    <td style="width:0.1in;"><a href="javascript:openViewer(<!--{$matches[i]->id}-->);">view</a></td>
    <td style="width:0.1in;"><a href=".?do=demo.projectinfo&id=<!--{$matches[i]->id}-->">info</a></td>

    <td style="width:1in;"><a href=".?do=demo.peopleinfo&id=<!--{$matches[i]->owner->id}-->"><!--{$matches[i]->owner->username}--></a></td>
    <td style="width:5in;"><!--{$matches[i]->description|truncate:70:"..."}--> &nbsp;</td>
  </tr>
<!--{/section}-->
</table>

</div>
</center>
