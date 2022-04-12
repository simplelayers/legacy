<!--{$subnav}-->
<form action="." method="post">
<input type="hidden" name="do" value="admin.usersetupbookmarks2"/>



<p>
  New signups will have the selected layers <i>bookmarked</i> by default.<br/>
  <div class="small" style="margin-left:0.5in;">
    Hint: Use ctrl-click to select individual items.<br/>
    Hint: Only publicly-shared layers are shown in this list.<br/>
    <select multiple="true" name="bookmark_layers[]" style="width:5in;height:5in;font-family:monospace;">
      <!--{foreach from=$bookmarklayers key=id item=thing}-->
      <option value="<!--{$id}-->" <!--{$thing.selected}-->><!--{$thing.label}--></option>
      <!--{/foreach}-->
    </select>
  </div>
</p>


<p>
  New signups will have the selected projects <i>bookmarked</i> by default.<br/>
  <div class="small" style="margin-left:0.5in;">
    Hint: Use ctrl-click to select individual items.<br/>
    Hint: Only publicly-shared projects are shown in this list.<br/>
    <select multiple="true" name="bookmark_projects[]" style="width:5in;height:5in;font-family:monospace;">
      <!--{foreach from=$bookmarkprojects key=id item=thing}-->
      <option value="<!--{$id}-->" <!--{$thing.selected}-->><!--{$thing.label}--></option>
      <!--{/foreach}-->
    </select>
  </div>
</p>



<p><input type="submit" name="submit" value="save changes"/>
</form>
