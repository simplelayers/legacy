<!--{$subnav}-->
<div>
<form action="./?do=contact.editinfo" method="post" enctype="multipart/form-data">
	<div style="float:right;width:50%;">
		<input type="hidden" name="contactId" value="<!--{$person->id}-->" />
		
		<p><b>Name:</b><br/><span id="name"><!--{$person->realname}--></span></p>
		<p><b>Organization & Role:</b><br/><span id="org_name"><!--{$pageArgsInfo['contactOrgName']}--> - <!--{$pageArgsInfo['contactSeat']}--> </span></p>
		<p><b>Email address:</b><br/><span id="email"><a href="./?do=contact.email1&id=<!--{$person->id}-->"><!--{$person->email|escape:'html'}--></span></a></p>
		<p><b>Phone Number:</b><br/><span id="phone"><!--{$person->phone}--></span></p>
		<p><b>Address:</b><br/><span id="address" class='wrapped' ><!--{$person->contactinfo|nl2br}--></span></p>
		<p><b>Tags:</b><br/><div id="tags" style="max-width:6in;"><!--{$taglinks}--></div></p>
	</div>
	<div style="float:left;width:50%;">
		
		<p id="picture">
			<!--{if $hasIcon}--><img id='profile_avatar' name='profile_avatar' src="<!--{$baseURL}-->wapi/contact/icon?&id=<!--{$person->id}-->" /><!--{/if}-->
			<!--{if !$hasIcon}-->
				<!--{icon icoset='anon_user' icon='metal_light'}-->
			<!--{/if}-->
					
		</p>
		<p><b>Description:</b><br/><span id="desc" class="wrapped"><!--{$person->description|nl2br}--></span></p>
		<span id="hide" style="display:none;"><p><b>Privacy:</b><br/><input type="checkbox" name="hide" <!--{if !$person->visible}-->checked<!--{/if}-->/> Hide my profile</p></span>
		<p id="pwreset" ></p>
		<!--{if $canEditContact}--><button id="edit">Edit</button><!--{/if}-->
	</div>
</form>

<script>
	$(function() {
		$('#edit').click(toggleEdit);
	});
	var edit = false;
	function resetAvatar() {
		document.images['profile_avatar'].src = "<!--{$baseURL}-->wapi/contact/icon?&id=<!--{$person->id}-->&reset=1";
		
		
		
	}
	
	function toggleEdit(){
		if(edit){
			return true;
		}else{
		$('#pwreset').append('Change Password: <input type="password" name="password1" ></input>');
			$('#picture').append('<br><a href="#" onClick="resetAvatar()">reset image</a><br>Upload a new profile image:<br/><input style="width:90%;" type="file" name="file" />')
			$('#email').html('<input style="width:90%;" type="text" name="email" value="'+$('#email').text()+'" />');
			$('#name').html('<input style="width:90%;" type="text" name="name" value="'+$('#name').text()+'" />');
			$('#phone').html('<input style="width:90%;" type="text" name="phone" value="'+$('#phone').text()+'" />');
			$('#address').html('<textarea style="width:90%;height:90px;" name="address">'+$('#address').text()+'</textarea>');
			$('#desc').html('<textarea style="width:90%;height:90px;" name="desc">'+$('#desc').text()+'</textarea>');
			$('#tags').html('<textarea style="width:90%;height:90px;" name="tags">'+$('#tags .tagsinput').text()+'</textarea>');
			$('#hide').css('display', 'inline');
			$('#edit').text('Save');
			$('#phone input').mask("(999) 999-9999? x99999");
			$('#tags textarea').tagsInput({
				width: 'auto',
				autocomplete_url:'./?do=wapi.contact.tags'
			});
			
			edit = true;
			return false;
		}
	}
</script>
<div style="clear:both;"></div>
</div>
<!--{if $person->id != $user->id}-->
<div style="float:left;width:auto;">
<b>Shared Layers:</b><br/>&nbsp;
<div id='shared_layers' >
<!--{include file='list/layer.tpl'}-->
</div>
<script>
	$(function() {
		queue = $('#list').jsonQueue();
		queue.nextQueue("./?do=wapi.views&type=owner&object=layer&owner=<!--{$person->id}-->&format=json", function(jsonData, context) {
			listOfLayers = jsonData;
			rebuildList();
		},
		function(jsonData){});
		
	});
</script>
</div>
<div style="float:left;width:auto;" id='shared_maps'>
<b>Shared Maps:</b><br/>&nbsp;
<!--{include file='list/projectcontact.tpl'}-->
<script>
	$(function() {
		queue2 = $('#list2').jsonQueue();
		queue2.nextQueue("./?do=wapi.views&type=owner&object=project&owner=<!--{$person->id}-->&format=json", function(jsonData, context) {
			listOfProjects = jsonData;
			rebuildList2();
		},
		function(jsonData){});
		
	});
</script>
<div style="height:20px;"></div>
</div>
<!--{/if}-->