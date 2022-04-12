<p class="title">Describe yourself to the community</p>

<form action="." method="post">
<input type="hidden" name="do" value="account.details2" />

<p>List this account so other people in the community can see it: <input type="checkbox" class="nopad" name="visible" value="1" <!--{if $user->visible }-->checked<!--{/if}--> /><br/><span class="small">If this is not checked, only people on your Friends list will be able to see you.</span></p>

<p>Name:<br/><input type="text" name="realname" style="width:3.5in;" maxlength="50" value="<!--{$user->realname}-->"></p>

<p>Email:<br/>
<input type="text" name="email" style="width:3.5in;" maxlength="50" value="<!--{$user->email}-->"><br/>
<input type="checkbox" class="nopad" name="email_public" value="1" <!--{if $user->email_public}-->checked<!--{/if}--> /> Make my email address visible to other users.
</p>

<p>
Other contact info (address, phone, etc):<br/>
<textarea name="contactinfo" rows="5" cols="50">
<!--{$user->contactinfo}-->
</textarea>
</p>

<p>
Description:<br/>
<textarea name="description" rows="5" cols="50">
<!--{$user->description}-->
</textarea>
</p>

<p>
Comma-separated tags:<br/><i>e.g. san francisco, earthquakes, fault zones</i><br/>
<textarea name="tags" rows="5" cols="50">
<!--{$user->tags}-->
</textarea>
</p>

<p><input type="submit" name="submit" value="save changes"/></p>

</form>
