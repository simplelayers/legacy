<script type="text/javascript" src="lib/js/jquery.checkbox.min.js"></script>
<!--{$subnav}-->
<div class="intro">Your data layers are tables that include fields (columns) called attributes. You may control how attributes appear throughout the system by modifying the information below. In BASIC mode, you have access to tools for presenting the data to end users. In ADVANCED mode there are additional tools for modifying the data itself (renaming, dropping, and adding attributes). </div>
<style>
    .form-control,
    .form-select {
        height:1.5rem;
        line-height:1rem;
        font-size:.75rem;
        padding:.25rem;
    }
    .table-container {
        width:fit-content;
        display:grid;
        gap:.25rem;
        grid-template-rows: auto auto;
        overflow: auto;
    }
    table.bordered {
        border:0px solid #0000;
        outline:2px solid black;
        border-right: 1px solid #000;
    }
    table.bordered tr {
        border-top: 1px solid #000;
        border-bottom: 0px solid #000;
    }
    table.bordered td {
        border-top: 0px;
        border-bottom: 0px;
        text-align:center;
    }
    table.bordered td:nth-child(1) {
        text-align:left;
    }

    .att-action-buttons {
        display:grid;
        gap: .25rem;
        grid-template-columns: auto auto 1fr auto;
    }
    button.btn.btn-primary {
        font-size: .75rem;
        width: fit-content;
        height:fit-content;
    }
</style>
<script>
    $(function () {
        $("#rows").sortable({
            items: "tr:not(.noSort)",
            stop: function (event, ui) {
                letLeave = false;
                $('#saveButton').removeAttr('disabled');
                setOrder();
            }
        });
        $("input[name='drop']").checkbox({cls: 'jquery-checkbox', empty: 'media/empty.png'});
        function goodbye(e) {
            if (letLeave)
                return;
            if (!e)
                e = window.event;
            e.cancelBubble = true;
            e.returnValue = 'This page is asking you to confirm that you want to leave - data you have entered may not be saved.';

            if (e.stopPropagation) {
                e.stopPropagation();
                e.preventDefault();
            }
        }
        window.onbeforeunload = goodbye;
        $("#rows select").change(function () {
            letLeave = false;
            $('#saveButton').removeAttr('disabled');
        });
        $("#rows input").change(function () {
            letLeave = false;
            $('#saveButton').removeAttr('disabled');
        });
        $("#rows :text").keyup(function () {
            letLeave = false;
            $('#saveButton').removeAttr('disabled');
        });
        editAttributes();
        setOrder();
    });
    var data = Array();
    function sendForm() {

        if ($('#rows input[name="drop"]:checked').length) {
            if (!confirm("Are you sure you would like to save these changes. Any deleted attributes will result in it's column data being lost."))
                return false;
        }
        var data = [];
        $("#rows tr").each(function () {
            if ($(this).children("td").length) {
                var temp = new Object;
                temp.startName = $(this).find('input[name="startName"]').val();
                temp.name = $(this).find('input[name="name"]').val();
                temp.display = $(this).find('input[name="display"]').val();
                temp.startType = $(this).find('input[name="startType"]').val();
                temp.type = $(this).find('select[name="type"]').val();
                temp.visible = ($(this).find('input[name="visible"]:checked').length ? true : false);
                temp.searchable = ($(this).find('input[name="searchable"]:checked').length ? true : false);
                temp.drop = ($(this).find('input[name="drop"]:checked').length ? true : false);
                data.push(temp);
            }
        });
        data = {'data': JSON.stringify(data)};
        letLeave = true;
        if (confirm('If you have made changes to attribute types this may alter underlying data. Not all conversions are possible and you should only proceed if the changes you have made make sense for the underlying data. Press Ok to continue saving changes and please be patient if the layer has many records and attributes, or press Cancel to abort.')) {
            disableEverything();
            $.ajax({
                type: 'POST',
                url: "./?do=vector.attributessavechanges_wapiold&id=<!--{$layer->id}-->",
                data: data,
                success: function (data) {
                    if(typeof data === 'string') {
                        data = JSON.parse(data).response;
                    }
                    if (data.status === 'error') {
                        if (!data.problem) {
                            alert('There was a problem uploading changes: ' + data.problem);
                            window.location.href = "./?do=vector.attributes&id=<!--{$layer->id}-->";
                            return;
                        } else {
                            alert('There was an unknown problem uploading changes');
                            window.location.href = "./?do=vector.attributes&id=<!--{$layer->id}-->";
                            return;
                        }
                    }
                    alert('Your changes have been saved.');
                    window.location.href = "./?do=vector.attributes&id=<!--{$layer->id}-->";
                },
                dataType: "text"
            });
        }
        ;

    }
    function disableEverything() {
        $('.form-content').find('*').attr("disabled", "disabled");
    }
    function createAdd() {
        var newRow = $('<tr>' +
                '<td class="order"></td>' +
                '<td style="width:2in;"><span class="name"></span><input name="startName" type="hidden" value="_newColumnName_"/><input name="name" class="hide form-control" type="text" value=""/></td>' +
                '<td style="width:2in;"><input name="display" type="text" value=""/></td>' +
                '<td style="width:1in;"><input name="startType" type="hidden" value=""/><select class="form-select"  name="type">' +
    <!--{foreach from=$columntypes key=typekey item=type}-->
        '<option label="<!--{$type}-->" value="<!--{$type}-->"><!--{$type}--></option>' +
    <!--{/foreach}-->
        '</select></td>' +
        '<td style="width:0.25in;text-align:center;"><input type="checkbox" class="nopad" name="visible" value="" checked/></td>' +
                '<td style="width:0.25in;text-align:center;"><input type="checkbox" class="nopad" name="visible" value="" checked/></td>' +
                '<td class="hide" style="width:0.25in;text-align:center;"><a href="#" onclick="$(this).parent().parent().remove();return false;"><img class="nopad" class="sudoDelete" src="media/icons/delete.png"/></a></td>' +
                '</tr>'
                ).appendTo('#rows').find('.hide').show();
        letLeave = false;
        $('#saveButton').removeAttr('disabled');
        setOrder();
    }
    function resetAttributes() {
        if (confirm('Clear attribute default customizations?')) {
            disableEverything();
            require(["sl_modules/WAPI"], function (wapi) {
                wapi.exec(
                        "layers/attributes/action:reset_attributes/layerId:" +<!--{$layer->id}--> + "/",
                        {}
                , function (response) {
                    alert('Attribute data has been reset. You may need to reload any maps open with this layer to see attribute changes in some interfaces.')
                    window.location.href = "./?do=vector.attributes&id=<!--{$layer->id}-->";
                });
            });
        
        }
    }
    function editAttributes() {
        if ($('#adv').text() == "Enter Advanced") {
            $(".hide").toggleClass("hide", false).toggleClass("unhide", true).show();
            $("#rows span.name").hide()
            $('#adv').text("Enter Basic");
            $('#adv').text("Enter Basic");

            $('#enterNextModeText').text("ADVANCED")
        } else {
            $(".unhide").toggleClass("unhide", false).toggleClass("hide", true).hide();
            cancelVocab();
            $("#rows span.name").show()
            $("#rows span.name").each(function () {
                $(this).text($(this).parent().find('[name="name"]').val());
            });
            $('#adv').text("Enter Advanced");

            $('#enterNextModeText').text("BASIC")
        }
        rearmToolTips();
    }

    var vocabAttribute = '';
    var vocabLayer = "<!--{$layer->id}-->";
    function editVocab(attribute, layerId) {
        vocabAttribute = attribute;
        $("#vocabHeading").val("Editing Controlled Vocabulary for " + attribute);
        $("#vocab_entry").val('Loading...');
        $("#vocab_editor_v0").toggleClass('hidden', false);
        require(["sl_modules/WAPI"], function (wapi) {
            wapi.exec("layers/attributes/action:get_vocab/layerId:" + layerId + "/attribute:" + attribute, {}, vocabReady);
        });

    }
    function cancelVocab() {
        $('#vocab_editor_v0').toggleClass('hidden', true);
    }

    function vocabReady(response) {
        $("#vocab_entry").val(response.results.vocab);
    }

    function saveVocab() {
        var vocab = $("#vocab_entry").val();

        require(["sl_modules/WAPI"], function (wapi) {
            wapi.exec("layers/attributes/action:set_vocab/layerId:" + vocabLayer + "/attribute:" + vocabAttribute, {vocab: vocab}, vocabSaved);
        });
        $("#vocab_entry").val('Saving...');

    }

    function vocabSaved() {
        $('#vocab_editor_v0').toggleClass('hidden', true);
    }
    $(function () {
        rearmToolTips();
    });
    var letLeave = true;
    function setOrder() {
        $('.order').each(function (index, element) {
            $(element).html(index + 1);
        });
    }

</script>
<form action="." method="post" onSubmit="sendForm();
        return false;">
    <!--{if !$isRelational}-->
        <div class="form-content" style="width:100%;min-width: 1024px;">
            <div class="form-content" style="margin-bottom:5px;">
                <button type="button" class="btn btn-primary" id="adv" onclick="editAttributes();
                       return false;">Enter Basic</button>
            </div>

        <!--{/if}-->

        <div class="form-content table-container">
            <table class="bordered" id="rows">
                <tr class="noSort">
                    <th style="padding-bottom:2px;cursor:auto;">Display Order<img style="margin-left:5px;margin-bottom:-4px;" src="media/icons/information.png" title="Order of display to end users, from left to right. Populate each cell in the column from 1, 2, 3, ..."/></th>
                    <th style="padding-bottom:2px;cursor:auto;">Attribute<img style="margin-left:5px;margin-bottom:-4px;" src="media/icons/information.png" title="The attribute's name. Change in adv. mode."/></th>
                    <th style="padding-bottom:2px;cursor:auto;">Display Name<img style="margin-left:5px;margin-bottom:-4px;" src="media/icons/information.png" title="The name displayed to the end user"/></th>
                    <th style="padding-bottom:2px;cursor:auto;">Type<img style="margin-left:5px;margin-bottom:-4px;" src="media/icons/information.png" title="Data type. Text changed to 'url' will be treated as hyperlinks."/></th>
                    <th style="padding-bottom:2px;cursor:auto;">Visible<img style="margin-left:5px;margin-bottom:-4px;" src="media/icons/information.png" title="Checked items will show in search results."/></th>
                    <th style="padding-bottom:2px;cursor:auto;">Searchable<img style="margin-left:5px;margin-bottom:-4px;" src="media/icons/information.png" title="Checked items are selectable in serching operations."/></th>
                    <th style="padding-bottom:2px;cursor:auto;" class="hide">Vocab<img style="margin-left:5px;margin-bottom:-4px;" src="media/icons/information.png" title="Manage a controlled vocabulary to use for this attribute."/></th>
                    <th style="padding-bottom:2px;cursor:auto;" class="hide">Delete<img style="margin-left:5px;margin-bottom:-4px;" src="media/icons/information.png" title="Deleted attributes cannot be recovered after &quot;save changes&quot; is entered."/></th>
                </tr>
                <!--{foreach from=$columns key=columnname item=column}-->
                    <!--{cycle values="color,altcolor" assign=class}-->
                    <tr>
                        <td class="hidden"><input type="hidden" name="requires" value="<!--{$column.requires}-->"></td>
                        <td class="order"><!--{abs($column.z)}--></td>
                        <td class="<!--{$class}-->"><span class="name"><!--{$column.name}--></span><input name="startName" type="hidden" value="<!--{$column.name}-->"/><input name="name" class="hide form-control" type="text" value="<!--{$column.name}-->"/></td>
                        <td class="<!--{$class}-->"><input class="form-control" name="display" type="text" value="<!--{$column.display}-->"/></td>
                        <td class="<!--{$class}-->" style="width:1.25in;"><input name="startType" type="hidden" value="<!--{$column.requires}-->"/><!--{if in_array($column.type, $conversions) && !$isRelational}--><select class="form-select" name="type"><!--{html_options output=$conversions values=$conversions selected=$column.type}--></select><!--{else}--><span style="padding-left:2px;"><!--{$column.type}--></span><!--{/if}--></td>
                        <td class="<!--{$class}-->" style="width:0.25in;text-align:center;"><input type="checkbox" class="nopad" name="visible" <!--{if $column.visible === true}-->checked="true"<!--{/if}-->/></td>
                        <td class="<!--{$class}-->" style="width:0.25in;text-align:center;"><input type="checkbox" class="nopad" name="searchable" <!--{if $column.searchable === true}-->checked="true"<!--{/if}-->/></td>
                        <td class="<!--{$class}--> hide" style="text-align:center;"><button type="button" class="btn btn-primary" onClick="editVocab('<!--{$columnname}-->',<!--{$layer->id}-->)" value="V">V</button></td>
                        <td class="<!--{$class}--> hide" style="width:0.25in;text-align:center;"><input type="checkbox" class="nopad" name="drop" /></td>
                    </tr>
                <!--{/foreach}-->
            </table>
            <div class="form-content att-action-buttons">
                <!--{if !$isRelational}-->
                    <button type="button" class=" btn btn-primary" onclick="createAdd();
                            return false;" class="hide">Add New Attribute</button>
                    <button type="button" class="btn btn-primary" type="button" onclick="resetAttributes();
                            return false;">Rebuild Attributes</button>
                <!--{/if}-->
                <span class="spacer" ></span>
                <button  class="btn btn-primary" id="saveButton" type="button" name="submit" disabled='true' onClick="sendForm()" style="width:2in;">Save Changes</button>
            </div>

        </div>

        <div id="vocab_editor_v0" class="form-content vocab_editor_v0 hidden" style="position:absolute;left: 0px;right:0px ;background:#ffffff;padding-left:10px;padding-right:10px;bottom: 16px;top: 362px;min-width:300px">
            <H2 id='vocabHeading'>Vocabulary editor</H2>
            <p>Provide a comma-separated list of values in the data entry field below. This will be used for data entry in certain utilities as a drop-down list of options.
            </p>
            <textarea id="vocab_entry" style="display:block;width:960px;height: 25%" columns=10 >Loading ...</textarea>
            <button  class="button" type="button" onClick="cancelVocab()">Cancel</button><button class="button" type="button" onClick="saveVocab()">Save Vocab Changes</button>
        </div>



</form>
</div>