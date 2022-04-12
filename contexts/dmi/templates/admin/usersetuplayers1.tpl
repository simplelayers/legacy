<!--{$subnav}-->
<form action="." method="post">
<input type="hidden" name="do" value="admin.usersetuplayers2"/>


<p>
  New signups will have the selected layers <i>copied into their account</i> by default.
  <div class="small" style="margin-left:0.5in;">
    Hint: Use ctrl-click to select individual items.<br/>
    Hint: Only publicly-shared layers are shown in this list.<br/>
    <select multiple="true" name="copy_layers[]" style="width:5in;height:5in;font-family:monospace;">
      <!--{foreach from=$copylayers key=id item=thing}-->
      <option value="<!--{$id}-->" <!--{$thing.selected}-->><!--{$thing.label}--></option>
      <!--{/foreach}-->
    </select>
  </div>
</p>

<p>
  A project will be created in the new user's account, containing all of the selected layers.<br/>
  Enter the name and description for this default project.<br/>
  Name:<br/>
  <input type="text" name="project_name" value="<!--{$project_name}-->" maxlength="50" style="width:4in;" />
  <br/>
  Description:<br/>
  <textarea name="project_desc" style="width:8in;height:1in;"><!--{$project_desc}--></textarea>
</p>



<p><input type="submit" name="submit" value="save changes"/>
</form>
