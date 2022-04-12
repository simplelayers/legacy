<!--{$subnav}-->
</div>
<style>
    .perm-list thead th {
        font-size: 1.25rem;
        padding-left:.5em;
    }
    .flex-row {
        flex-flow: row;
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        align-content: center;
        justify-content: flex-start;
        align-items: center;
        margin-bottom: .33em;
        gap: 1em;
    }
    .grow {
        flex-grow: 2;
        align-items: center;
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        align-content: center;
        justify-content: flex-end;;
    }
    static {
        flex-grow:1;
    }
    .perm-table {
        width: 100%;
    }
    .list-header {
        width:100%;        
    }
    .perm-table label.inline {
        margin-right:1em;
    }
    .permissions,
    .reporting {
        white-space:nowrap !important;
    }

    .contact-value.reporting {
        width: 22em;
    }
    .contact-value.permission {
        width:18em
    }
    .contact-value.option-set label{
        font-weight: unset;
    }
    .perm-set .option-radio,
    .contact-value .option-radio {
        margin-left: .5rem;
        margin-right:.25rem;
    }
    
    .contact-value .option-radio.first {
        margin-left:0px;
    }
    .table-spacer {
        height: 2em;
        width: 100%;
    }



</style>
<form id="form" action="./?do=layer.permissions&id=<!--{$layer->id}-->" method="post">
    <div style="left:0;right:0;padding:5px 20px 5px 10px;">
        <div class="flex-row">
            <div id="publicPermissions" class="perm-set static inline<!--{if !$hasExternalPerm}-->hidden<!--{/if}-->"><span class="title">Public Layer Permissions: </span>
                <span title="The layer is private."><input class="option-radio" type="radio" name="publicOptions" value="0" <!--{if $layer->sharelevel == 0}-->checked<!--{/if}-->>Private</span> 
                <span title="The layer can be viewed by anyone."><input class="option-radio" type="radio" name="publicOptions" value="1" <!--{if $layer->sharelevel == 1}-->checked<!--{/if}-->>View</span> 
                <span title="The layer can be copied by anyone within the system."><input class="option-radio" type="radio" name="publicOptions" value="2"<!--{if $layer->sharelevel == 2}-->checked<!--{/if}-->>Copy</span> 
                <span title="The layer can be edited by anyone."><input class="option-radio" type="radio" name="publicOptions" value="3"<!--{if $layer->sharelevel == 3}-->checked<!--{/if}-->>Edit</span> 
            </div>
            <div id="publicReportingLvl" class="perm-set grow <!--{if !$hasExternalPerm}-->hidden<!--{/if}-->"><span class="title">Public Reporting Permissions: </span>
                <span title="The layer is private."><input type="radio" class="option-radio" name="publicRptOptions" value="0" <!--{if $layer->reporting_level == 0}-->checked<!--{/if}-->>Private</span> 
                <span title="The layer's content may be viewed by anyone in report UIs."><input class="option-radio" type="radio" name="publicRptOptions" value="1" <!--{if $layer->reporting_level == 1}-->checked<!--{/if}-->>View</span>
                <span title="The layer's non geograhic tabular content may be exported by anyone."><input class="option-radio" type="radio" name="publicRptOptions" value="2"<!--{if $layer->reporting_level == 2}-->checked<!--{/if}-->>Export</span>  
                <span title="The layer's geographic and tabular content may be exported by anyone."><input class="option-radio" type="radio" name="publicRptOptions" value="3"<!--{if $layer->reporting_level == 3}-->checked<!--{/if}-->   >Geo Export</span>
            </div>
        </div>
            <div class="table-spacer"></div>
        <div class="perm-table" >
            <div class="list-header flex-row">

                <label class="static inline" >Contact Permissions </label>
                <div class="static form-inline" id="selectorContact"></div>
                <div class="grow" id="filterContact" ></div>

            </div>
            <!--{include file='list/sharecontact.tpl'}-->
        </div>
        <div class="table-spacer"></div>
        <div class="perm-table">
            <div class="list-header flex-row">
                <label class="static inline" >Group Permissions</label>
                <div class="static form-inline" id="selectorGroup" style=""></div>
                <div class="grow" id="filterGroup" style="float:right;"></div>
            </div>
            <!--{include file='list/sharegroup.tpl'}-->
        </div>
    </div>
    <input type="hidden" name="changes" id="changes" value=""/>
    <div style="clear:both;padding-top:20px;"><input type="submit" id="save" disabled="disabled" value="Save Changes"/></div>
</form>
<script>
    var listOfChanges = new Object;
    listOfChanges.groups = new Object();
    listOfChanges.people = new Object();
    $(function () {
        $(".filterNavGroup").prependTo("#filterGroup");
        $(".filterNavContact").prependTo("#filterContact");
        $('#publicPermissions input').change(function (event) {
            var target = $(event.target);
            var level = target.val();
            addToUpdate('public','permissions', 0, level);
        });
        $('#publicReportingLvl input').change(function (event) {
            var target = $(event.target);
            var level = target.val();
            addToUpdate('public','reporting',0, level);
        });
    });
   
     function addToUpdate(type, target, id, value) {
        if (type == "group") {
            PrepareChangeTarget(type,target);
            listOfChanges.groups[target][id] = value;
            //listOfChanges.groups[id] = value;
        } else if (type == "person") {
            PrepareChangeTarget(type,target);
            listOfChanges.people[target][id] = value;            
        } else if (type == "public") {
            PrepareChangeTarget(type,target);
            listOfChanges.public[target] = value;
        } else {
            return false;
        }
        $('#changes').val(JSON.stringify(listOfChanges));
        $('#save').removeAttr("disabled");
        return true;
    }
    function PrepareChangeTarget(type,target) { 
        if(type === 'person') {
            type = 'people';
        } else if(type === 'group') {
            type = 'groups';
        }
        if(!listOfChanges[type]) {
            listOfChanges[type] = {};
        }
        if (!listOfChanges[type].hasOwnProperty(target)) {
            listOfChanges[type][target] = {};
        }
    }
</script>