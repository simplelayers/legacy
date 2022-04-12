<script type="text/javascript">
function check_form(formdata) {
   if (!formdata.elements['message'].value) { alert('The message cannot be blank.'); return false; }
   if (!formdata.elements['subject'].value) { alert('The subject cannot be blank.'); return false; }
}
</script>
<p><i>Note: Your email address will appear as the email's sender  address when this email message is sent regardless of your profile visibility settings .<i></p>

<form action="." method="post" onSubmit="return check_form(this);">
<input type="hidden" name="contactId" value="<!--{$pageArgsInfo['contactId']}-->"/>
<input type="hidden" name="do" value="contact.email2"/>


<p>Subject:<br/>
<input type="text" name="subject" size="105" maxlength="105"/>
</p>
<p>Message:<br/>
<textarea name="message" rows="10" cols="80"></textarea>
</p>
<p><input type="submit" name="submit" value="send message"/></p>

</form>
