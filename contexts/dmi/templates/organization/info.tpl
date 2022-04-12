<div>

<form action="./?do=organization.editinfo" method="post" enctype="multipart/form-data">
	<div style="float:right;width:50%;">
		<input type="hidden" name="id" value="<!--{$org->id}-->" />
		<!--{if $profileVisible}-->
		<p><b>Owner:</b><br/><span id="email"><a href="./?do=contact.info&contactId=<!--{$org->owner->id}-->"><!--{$org->owner->realname}--></a></span></p>
		<!--{else}-->
		<p><b>Owner:</b><br/><span id="email"><!--{$org->owner->realname}--></span></p>
		<!--{/if}-->
		<!--{if $profileVisible}-->
		<p><b>Phone Number:</b><br/><span id="phone"><!--{$org->owner->phone}--></span></p>
		<p><b>Address:</b><br/><span id="address" class="wrapped"><!--{$org->owner->contactinfo|nl2br}--></span></p>
		<!--{/if}-->
		<!--<p><b>Tags:</b><br/><div id="tags" style="max-width:6in;"><!--{$taglinks}--></div></p>-->		
	</div>
	<div style="float:left;width:50%;">
		<p id="picture">
			<img src="<!--{$baseURL}-->/wapi/organization/organizations/orgId:<!--{$org->id}-->/action:get/target:org_media/media:logo" />
		</p>
		
		<p id='desc_container' <!--{if $org->description eq ''}--> class='hidden'<!--{/if}-->><b >Description:</b><br/><span id="desc" class='wrapped'><!--{$org->description|nl2br}--></span></p>
		<!--<span id="hide" style="display:none;"><p><b>Privacy:</b><br/><input type="checkbox" name="hide" <!--{if !$person->visible}-->checked<!--{/if}-->/> Hide my profile</p></span>-->
		<!--{if $org->plan}-->
		<p>
			<b>Plan:</b>
			<span id='plan'>
			<!--{if ($pageArgsInfo['orgActor']=='org_owner') || $pageOptions['plans_edit'] }-->
			<a href='<!--{$baseURL}-->/organization/license/orgId:<!--{$org->id}-->'><!--{$org->plan['data']['planName']}--></a>
			<!--{else}-->
			<!--{$org->plan['data']['planName']}-->
			<!--{/if}-->
			</span>
		</p>
		<p><b>Organization's Disclaimer text</b><p>
		<div id='disclaimer_view'><iframe style="border:none;width: 600px;height:100%;text-align:left;" src="<!--{$disclaimerURL}-->"></iframe></div>
		<div id='disclaimer_edit' class='hidden'><textarea  name="disclaimer" style='text-wrap:normal;'  rows="20" cols="50" wrap="hard" ><!--{$disclaimer}--></textarea></div>
		
		<!--{elseif $pageOptions['plans_create']}-->
		<p>
			<b>Plan:</b>
				<span id='plan'>
				<a href='<!--{$baseURL}-->/organization/license/orgId:<!--{$org->id}-->'>Create Plan</a>
				</span></p>
		<!--{/if}-->
		
		<!--{if $pageArgsInfo['orgActor']=='org_owner' or $pageArgsInfo['pageActor']=='admin'}--><button id='cancel_button' type='button'>Cancel</button> <button id="edit">Edit</button><!--{/if}-->
		
	</div>
</form>

<script>
	$(function() {
		$('#edit').click(toggleEdit);
		$('#cancel_button').click(toggleEdit);
		
	});
	var edit = false;//true; // will toggle this false with toggleEdit
	function toggleEdit(){
		
		if(edit){
			if($('#desc').text() == '') $('#desc_container').addClass('hidden');
			$('#name').addClass('hidden');
			$('#name_display').removeClass('hidden');
			$('#cancel_button').addClass('hidden');
			$('#disclaimer_edit').addClass('hidden');
			$('#disclaimer_view').removeClass('hidden');
			$('#edit').text('Edit');
			edit = false;
			return true;
		}else{
			$('#name').removeClass('hidden');
			$('#name_display').addClass('hidden');
			$('#cancel_button').removeClass('hidden');		
			//$('#picture').append('<br/>Upload a new profile image:<br/><input style="width:90%;" type="file" name="file" />')
			
			$('#desc').html('<textarea style="width:90%;height:90px;" name="desc">'+$('#desc').text()+'</textarea>');
			//$('#tags').html('<textarea style="width:90%;height:90px;" name="tags">'+$('#tags .tagsinput').text()+'</textarea>');
			$('#hide').css('display', 'inline');
			$('#edit').text('Save');
			
			$('#disclaimer_view').addClass('hidden');
			$('#disclaimer_edit').removeClass('hidden');
			
			
			
			/*$('#tags textarea').tagsInput({
				width: 'auto',
				autocomplete_url:'./?do=wapi.organization.tags'
			});*/
			$('#desc_container').removeClass('hidden');
			edit = true;
			return false;
		}
	}
	$('#name').addClass('hidden');
	$('#name_display').removeClass('hidden');
	$('#cancel_button').addClass('hidden');
	
	//edit = toggleEdit(edit);
</script>
<div style="clear:both;"></div>

</div>
