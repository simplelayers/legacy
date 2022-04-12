<p class="title">Search maps</p>
<form action="." method="post" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="project.search" />
<input type="text" style="width:3in" name="search" value="<!--{$searchterm}-->" /> <input type="submit" name="submit" value="search" /> <input type="submit" name="submit" value="show all" onClick="document.forms[0].elements['search'].value='all available';return true;" />
</form>

<!--{if $matches}-->
<form action="." method="post">
<input type="hidden" name="do" value="project.bulkbookmarks" />

<table class="bordered">
  <tr>
    <th><a href=".?do=project.search&sort=name&desc=<!--{$sortdesc}-->">Map</a></th>
    <th><a href=".?do=project.search&sort=owner&desc=<!--{$sortdesc}-->">Owner</a></th>
    <th><a href=".?do=project.search&sort=description&desc=<!--{$sortdesc}-->">Description</a></th>
    <th>View</th>
    <th>Info</th>
    <th>Mark</th>
  </tr>
<!--{section loop=$matches name=i}-->
  <!--{cycle values="color,altcolor" assign=class}-->
  <tr>
    <td style="width:2in;" class="<!--{$class}-->"><b><!--{$matches[i]->name|truncate:30:"..."}--></b></td>
    <td style="width:1in;" class="<!--{$class}-->"><a href=".?do=social.peopleinfo&id=<!--{$matches[i]->owner->id}-->"><!--{$matches[i]->owner->username}--></a></td>
    <td style="width:6.25in;" class="<!--{$class}-->"><!--{$matches[i]->description|truncate:95:"..."}--> &nbsp;</td>
    <td style="width:0.25in;" class="<!--{$class}-->"><a href="javascript:openViewer(<!--{$matches[i]->id}-->);">view</a></td>
    <td style="width:0.25in;" class="<!--{$class}-->"><a href=".?do=project.info&id=<!--{$matches[i]->id}-->">info</a></td>
    <td style="width:0.25in;text-align:center;" class="<!--{$class}-->"><input type="checkbox" class="nopad" name="ids[]" value="<!--{$matches[i]->id}-->" <!--{if $user->isProjectBookmarkedById($matches[i]->id)}-->checked<!--{/if}--> /></td>
  </tr>
<!--{/section}-->
</table>

<p style="margin-left:8.42in"><input type="submit" name="submit" value="update bookmark list" style="width:2in" /></p>
</form>

<!--{elseif $searchterm}-->
<p>Nothing matched your search.</p>
<!--{/if}-->
