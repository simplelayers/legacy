<!--{$subnav}-->
<form action="." method="post">
<input type="hidden" name="do" value="admin.usersetupfriends2"/>


<p>
  New signups will have the selected people on their Friends list.<br/>
  <div class="small" style="margin-left:0.5in;">Hint: Use ctrl-click to select individual items.<br/>
    <select multiple="true" name="friends[]" style="width:5in;height:5in;font-family:monospace;">
      <!--{foreach from=$people key=id item=thing}-->
      <option value="<!--{$id}-->" <!--{$thing.selected}-->><!--{$thing.label}--></option>
      <!--{/foreach}-->
    </select>
  </div>
</p>



<p><input type="submit" name="submit" value="save changes"/>
</form>
