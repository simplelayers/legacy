<p class="title">Search layers</p>
<form action="." method="post" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="layer.search" />
<input type="text" style="width:3in" name="search" value="<!--{$searchterm}-->" /> <input type="submit" name="submit" value="search" /> <input type="submit" name="submit" value="show all" onClick="document.forms[0].elements['search'].value='all available';return true;" />
</form>

<!--{if $matches }-->
<form action="." method="post">
<input type="hidden" name="do" value="layer.bulkbookmarks" />

<table class="bordered">
  <tr>
    <th><a href=".?do=layer.search&sort=name&desc=<!--{$sortdesc}-->">Layer</a></th>
    <th><a href=".?do=layer.search&sort=geomtypestring&desc=<!--{$sortdesc}-->">Type</a></th>
    <th><a href=".?do=layer.search&sort=owner&desc=<!--{$sortdesc}-->">Owner</a></th>
    <th><a href=".?do=layer.search&sort=description&desc=<!--{$sortdesc}-->">Description</a></th>
    <th>Info</th>
    <th>Mark</th>
  </tr>
<!--{section loop=$matches name=i}-->
  <!--{cycle values="color,altcolor" assign=class}-->
  <!--{assign var='type' value=$matches[i]->type }-->
  <tr>
    <td style="width:2in;" class="<!--{$class}-->"><b><!--{$matches[i]->name|truncate:30:"..."}--></b></a> &nbsp;</td>
    <td style="width:0.5in;" class="<!--{$class}-->"><!--{$matches[i]->geomtypestring}--></td>
    <td style="width:1in;" class="<!--{$class}-->"><a href=".?do=social.peopleinfo&id=<!--{$matches[i]->owner->id}-->"><!--{$matches[i]->owner->username}--></a></td>
    <td style="width:6in;" class="<!--{$class}-->"><!--{$matches[i]->description|truncate:80:"..."}--> &nbsp;</td>
    <td style="width:0.25in;" class="<!--{$class}-->"><a href=".?do=layer.info&id=<!--{$matches[i]->id}-->">info</a></td>
    <td style="width:0.25in;text-align:center;" class="<!--{$class}-->"><input type="checkbox" class="nopad" name="ids[]" value="<!--{$matches[i]->id}-->" <!--{if $user->isLayerBookmarkedById($matches[i]->id)}-->checked<!--{/if}--> /></td>
  </tr>
<!--{/section}-->
</table>

<p style="margin-left:8.22in"><input type="submit" name="submit" value="update bookmark list" style="width:2in" /></p>
</form>

<!--{elseif $searchterm}-->
<p>Nothing matched your search.</p>
<!--{/if}-->
