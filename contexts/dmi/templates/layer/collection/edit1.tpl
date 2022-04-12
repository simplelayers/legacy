<style>
    .collection-group {
        height: calc(100vh - 40.5em);
    }
    .collection-group .list-view {
        margin-top: .25em;
    }
    .collection-group>label {
        padding-top: 0px !important;
    }

    .list-view {
        max-height: calc(100vh - 40.5em);
        overflow: auto;
        padding: 0px;
        border: 2px inset #ccc8;
        flex-grow: 1;
        height: 100%;
        overflow: auto;
        /* display: flex; */
        /* flex-direction: column; */
        flex-wrap: nowrap;
        align-content: flex-start;
        justify-content: flex-start;
        align-items: stretch;
    }
    .form-inline input[type="text"] {
        width: auto !important;
        max-width: auto !important;
    }
    .primary-form {
        margin-left: 1em;
        margin-right: 1em;
    }
    .available-group {
        justify-content:left;
        text-align:left;
        max-height: calc(100vh - 36.5em);
    }
    .available-group>label {
        line-height: 2.25em;
        vertical-align:middle;
    }

    .footer {
        margin: 1em 0em .5em 0em;
        min-height: initial !important;
        max-height: initial !important;

    }
    #description {
        line-height: 1.4em;
    }
    .sortableList {
        min-height: 2.5em;
    }
    .list-view .btn-sm {
        width: 2em;
        height: 2em;
        padding: 0px !important;
        margin: 2px 1em 2px 2px;
    }
    .list-view .ui-state-default {
        background: transparent;	
    }
    .sortableList li:nth-child(even) {
        background:#fff !important;	
    }
    .sortableList li:nth-child(odd) {
        background: #EEE !important;
    }
    .collection-editor .form-group label {
        margin-right: 1em;
    }
    .filter-form {
        margin-bottom: .5em !important;
        text-align:right;
    }
    .filter-form #filter{
        display: inline-block !important;
    }
    .filter-form label {
        display: none !important;
    }
    .stateful {
        display :none;
    }

    #filter {
        display: inline-block !important;
        margin-right: .5em;
    }
</style>
<script src="contexts/dmi/templates/layer/collection/edit1.tpl.js" type="text/javascript"></script>


<!--{$subnav}-->

<form class="collection-editor entry" action="." method="post" enctype="multipart/form-data" onsubmit="save();
        return false;">
    <input type="hidden" name="do" value="layer.editcollection2"/>
    <input type="hidden" name="id" id="id" value="<!--{$id}-->"/>
    <div class="flex-row col-12">
        <div class="flex-column col-6">
            <div class="form-group col-12">
                <label class="form-label">Name:</label>
                <input id="name" class="form-control" type="text" name="name" value="<!--{$layer->name}-->" maxlength="50"  >
            </div>
            <div class="form-group col-12">
                <label class="form-label">Tags:</label>
                <textarea name="tags" class="form-control tagsinput" ><!--{$layer->tags}--></textarea>
            </div>
        </div>

        <div class="flex-column col-6">
            <div class="form-group col-12">
                <label class="form-label">Description:</label>
                <textarea id="description" class="form-control" name="description" rows="5"><!--{$layer->description}--></textarea>
            </div>

        </div>
    </div>
    <div class="form-group flex-row">
        <label class="form-label">Layer Sources:</label>
        <div id="layer_selector" class="flex-row"></div>
    </div>
    <div class="flex-row flex-split">
        <div class="flex-column available-group col-6 form-group form-inline">
            <label class="form-label">Available Layers</label>
            <div class="form-group form-inline filter-form inline no-right-inset col-10">
                <input class="form-control inline" type="text" id="filter" placeholder="Filter" />
                <button class="btn btn-md btn-primary" type="button" onclick="$('#filter').val('');
                        window.controller.CheckFilter();">Clear</button>
            </div>
            <div class="list-view float-none">
                <ul id="sortable1" class="sortableList">
            </div>
        </div>
        <div class="flex-column collection-group col-6">
            <label class="form-label">Layers in Collection</label>
            <div class="list-view">
                <ul id="sortable2" class="sortableList">
            </div>
            <div class="footer">
                <button id="addAll_btn" class="btn  btn-primary" type="button" >Add All</button>
                <button id="save_btn" class="btn  btn-primary" type="button" >Save</button>
                <button id="clearAll_btn" class="btn  btn-danger" type="button" >Clear All</button>
            </div>
        </div>
    </div>
</div>


</form>
