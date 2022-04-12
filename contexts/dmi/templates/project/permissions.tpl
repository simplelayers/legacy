<!--{$subnav}-->

<form id="form" action="./?do=project.permissions&id=<!--{$project->id}-->" method="post">
    <div style="left:0;right:0;padding:5px 20px 5px 10px;">
        <div id="publicPermissions" class='<!--{if !$hasExternalPerm}-->hidden<!--{/if}-->'><span class="title">Public View Permissions: </span>
            <span title="Your map is public and it shows up in searches."><input type="radio" name="publicOptions" value="2" <!--{if !$project->private}-->checked<!--{/if}-->> Public</span> -
            <span title="Your map is can be viewed directly but is not listed in searches."><input type="radio" name="publicOptions" value="1" <!--{if ($project->private && $project->allowlpa)}-->checked<!--{/if}-->> Unlisted</span> -
            <span><input type="radio" name="publicOptions" value="0"<!--{if ($project->private && !$project->allowlpa)}-->checked<!--{/if}-->> Private</span>
        </div><br/>
        <div id="contactPermissionList" class="perm-table">
            <div class="form-group flex-row">
                <label class="form-label" >Contact Permissions </label>
                <div class="static form-inline flex-row" id="selectorContact"></div>
                <div class="grow flex-row" id="filterContact" ></div>
            </div>
            <!--{include file='list/sharecontact.tpl'}-->                            
        </div>
        <div class="table-spacer"></div>
        <div id="contactPermissionList" class="perm-table">
            <div class="list-header flex-row">
                <label class="form-label" >Group Permissions</label>
                <div class="static form-inline flex-row" id="selectorGroup" style=""></div>
                <div class="grow flex-row" id="filterGroup" style="float:right;"></div>
            </div>
            <!--{include file='list/sharegroup.tpl'}-->
        </div>
        <input type="hidden" name="changes" id="changes" value=""/>
        <div style="clear:both;padding-top:20px;"><input type="submit" id="save" disabled="disabled" value="Save Changes"/></div>
</form>
<script>
    var listOfChanges = new Object;
    listOfChanges.groups = new Object();
    listOfChanges.people = new Object();
    $(function () {
        $('.contentarea').toggleClass('flex-content');
        $(".filterNavGroup").prependTo("#filterGroup");
        $(".filterNavContact").prependTo("#filterContact");
        $('#publicPermissions input').change(function (event) {
            var target = $(event.target);
            var level = target.val();
            addToUpdate("public", 'permissions',null, level);
        });

    });
    function addToUpdate(type, target, id, value) {
        if (target !== 'permissions') {
            return;
        }
        console.log(type, id, value);
	switch(type) {
          case 'group':
            listOfChanges.groups[id] = value;
            break;
	  case 'person':
            listOfChanges.people[id] = value;
            break;
          case 'public': 
            listOfChanges.public = value;
            break;
	  default:
	    return false;
	}
        console.log(listOfChanges);
        $('#changes').val(JSON.stringify(listOfChanges));
        $('#save').removeAttr("disabled");
        return true;
    }

</script>
