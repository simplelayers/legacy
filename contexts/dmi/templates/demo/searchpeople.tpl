<center>
<div class="greybox">

<p class="title">Search people</p>
<form action="." method="post" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="demo.searchpeople" />
<input type="text" style="width:3in" name="search" value="<!--{$searchterm}-->" /> <input type="submit" name="submit" value="search" /> <input type="submit" name="submit" value="show all" onClick="document.forms[0].elements['search'].value='all available';return true;" />
</form>

<table class="bordered">
  <tr>
    <th>User</th>
    <th>Name</th>
    <th>Description</th>
  </tr>
<!--{section loop=$matches name=i}-->
  <tr>
    <td style="width:1in;"><a href=".?do=demo.peopleinfo&id=<!--{$matches[i]->id}-->"><!--{$matches[i]->username}--></a> &nbsp;</td>
    <td style="width:2in;"><!--{$matches[i]->realname|truncate:30:"..."}--> &nbsp;</td>
    <td style="width:5in;"><!--{$matches[i]->description|truncate:70:"..."}--> &nbsp;</td>
  </tr>
<!--{/section}-->
</table>

</div>
</center>
