<!--{$subnav}-->


<style>
    #inline_selector .form-select {
        display: inline-block;
    }

    .filter-spacer {
        display:inline-flex;
        width: 100%;
        flex-grow: 1;
    }
    .filter-form.padless {
        padding:0px !important;
    }

    .btn.btn-outline-secondary {
        color: #fff;
    }
    .progress-container {
        width: 100%;
        position: absolute;
        display: flex;
        flex-flow: column;
        justify-content: center;
        padding: 8%;
        height: calc(100% - 4.5em);
        width: calc(100% - 4.5em);
        overflow: hidden;
    }
    .ui-state-default {
        background:transparent;
    }
    .btn-sm {
        width: 2em;
        height: 2em;
        padding: 0px !important;
        margin-top:2px;
        margin-bottom:2px;
        margin-right: .5em;
    }
    .no-info,
    .info {
        border-radius: 1rem !important;
    }
    .no-info {
        opacity: .5;
    }
    .form-inline {
        flex-wrap:nowrap;
        white-space: nowrap;
    }
    .filter-form {
        padding-right:.5em !important;		
    }

    .map-list-group>label {
        margin-bottom: 0.78em !important;
    }
    .list-wrapper {
        border: 2px inset #ccc;
        height: 18em;
        overflow:auto;
    }
    .list-wrapper li {
        padding-left: .5em;

    }
    .list-wrapper li:nth-child(even) {
        background: #EEE;
    }
    .list-wrapper li:nth-child(odd) {
        background: #fff;
    }
    .list-wrapper li span {
        cursor: default;
    }

    .bounds-section {
        line-height: 1em;
        margin-top: 1.5em;
        padding-bottom: 0px;
    }
    .bounds-section .instructions {
        height: 1.5em;
    }
    form {
        padding-top: 1em;
    }
    .cardinal {
        margin-bottom: .5em !important;
    }
    #description {
        line-height: 1.4em;
    }
    .submit-footer  {
        padding-top:.25em;
        margin-bottom: 0px;
    }
    inline_selector.form-group {
        margin-top:.25em;
        margin-bottom:.25em;
    }
    .listing-heading
    {
        padding-left: 0px;

        margin-top: .25em;
        margin-bottom: .25em !important;
    }

</style>
<script>
    var listOfLayers = {};
    var loaded = false;
    <!--{$geomTypes}-->
</script>
<script src="contexts/dmi/templates/project/edit1.tpl.js" type="text/javascript"></script>

<form id="form" action="." method="post" onSubmit="return false;
        event.preventDefa        ul          t()">
    <input type="hidden" name="do" value="project.e        di          t2"/>
    <input type="hidden" name="id" id="id" value="<!--{$project->id}-->"/>
    <input type="hidden" id="layerlist" name="layerlist" value="" >

    <!-- at the top: the simple attributes -->
    <div class="flex-row col-12">
        <div class="flex-column col-6">
            <div class="form-group col-12">
                <label class="form-label">Name:</label>
                <input id="name" class="form-control" type="text" name="name" value="<!--{$project->name}-->" maxlength="50"  >
            </div>
            <div class="form-group col-12">
                <label class="form-label">Tags:</label>
                <textarea name="tags" class="form-control tagsinput" ><!--{$project->tags}--></textarea>
            </div>
        </div>

        <div class="flex-column col-6">
            <div class="form-group col-12">
                <label class="form-label">Description:</label>
                <textarea id="description" class="form-control" name="description" rows="5"><!--{$project->description}--></textarea>
            </div>

        </div>
    </div>
    <div class="col-12">
        <div class="col-12 flex-row text-align">
            <label class="form-label ">Layer Sources</label>
            <div id="inline_selector" class="flex-row"></div>
        </div>
        <div class="col-12 flex-row">
            <div class="col-6 flex-column">
                <div class="input-group flex-row col-12 listing-heading">
                    <label class="form-label">Layers</label>
                    <div class="filter-spacer"></div>
                    <div class="input-group filter-form form-inline padless">
                        <input type="text" class="form-control inline auto-width" id="filter" placeholder="Filter" />
                        <button type="button" class="filter-clear-button btn-outline-secondary btn btn-primary" ><span class="fas fa-times"></span></button>
                    </div>	
                </div>
                <div class="list-wrapper float-none">
                    <ul id="availLayerList" class="sortableList">
                </div> 
                </ul>	
            </div>
            <div class="map-list-group col-6 flex-column listing-heading">
                <label class="form-label">Layers in Map</label>
                <div class="list-wrapper">
                    <ul id="mapLayerList" class="sortableList">
                </div>
            </div>
        </div>
    </div>
    <div class="bounds-section form-group col-12 no-bottom-margin">
        <div class="input-group col-12 flex-row no-bottom-margin">
            <label class="form-label">Show initial map bounds</label>
            <input id="bounds_cb" type="checkbox" >
        </div>
        <div class="col-12 flex-row">
            <div class="col-3 flex-column cardinal form-group">
                <label class="form-label">West longitude:</label>
                <input id="bbox0" class="form-control" type="text" name="bbox0" value="<!--{$bbox[0]}-->"  >
            </div>
            <div class="col-3 flex-column cardinal form-group">
                <label class="form-label">South latitude:</label>
                <input id="bbox1" class="form-control" type="text" name="bbox1" value="<!--{$bbox[1]}-->"  >
            </div>
            <div class="col-3 flex-column cardinal form-group">
                <label class="form-label">East longitude:</label>
                <input id="bbox2" class="form-control" type="text" name="bbox2" value="<!--{$bbox[2]}-->"  >
            </div>
            <div class="col-3 flex-column cardinal form-group">
                <label class="form-label">North latitude:</label>
                <input id="bbox3" 	class="form-control" type="text" name="bbox3" value="<!--{$bbox[3]}-->" >
            </div>	
        </div>
    </div>
    <div class="submit-footer form-group col-12">
        <div class="col-12">
            <button id="submitButton" class="btn btn-md btn-primary pull-right" type="button" name="submit" >Save Changes</button>
        </div>
    </div>
</form>

<div id="layerResetDialog" class="modal fade" role="dialog">
    <style>
        .stateful {
            display:none !important;
        }
        .modal-content {
            border-radius:0px;
        }
        .modal-content .modal-header {
            padding: .5em 0em 0em 1em;
            border-radius:0px;
            background:#000;
            color:#fff;
        }
        .modal-content .modal-header .close-button {
            border-radius: 1em;
            color: #fff;
            opacity: .5;
            margin-right: .5em;
            width: 24px;
            margin-top: -8px;
            height: 24px;
            padding:0px !important;
        }
        .modal-content .modal-header .close-button:hover {
            opacity: 1;
        }
        .modal-content .modal-header .close-button:active {
            opacity: .66;
        }
        .modal-content .modal-header .close-button span {
            margin: 0px;
            margin-top: .33em;
            /* position: relative; */
            padding: 0px;
            font-size: 9pt;
            vertical-align: top;
        }

        .modal-content .modal-body {
            margin:0px;
            padding-top:0px;
            padding-bottom:0px;
            border-radius:0px;
            margin: 0px -1em 0px -1em;
        }
        .modal-content .options {
            padding: .5em 0px 0px 0px;
            display: block;
            width: 100%;
            border: none;
            box-shadow: none;
            border-radius:0px;
        }
        .modal-content .divider {
            margin-top: .4em;
            border-bottom: .1em solid black;
            margin-bottom: .4em;
        }
        .modal-content .item {
            padding-left: 1em;
            padding-right: 1em;
        }
        .modal-content .item input[type="radio"],
        .modal-content .item input[type="checkbox"] {
            margin-right: 1em;
            cursor:pointer;
        }
        .modal-content .item {
            cursor: default;
        }
        .modal-content .modal-footer {
            background: #000;
            color:#fff;
            padding:.5em;
        }
        .modal-content .instruction {
            display:block;
            padding-left: 2.2em;
        }
        .modal-content .intsruction.item-layer-description {
            padding-left: 2em;
        }
        .modal-content .item-layer-name {
            margin-left:1em;
        }
        .modal-body.coll .stateful.req-coll ,
        .modal-body.vector .stateful.req-vector,
        .modal-body.body-options .stateful.req-options,
        .modal-body.body-xfer .stateful.req-xfer {
            display: block !important;
        }
        .xfer {
            padding:2em;
        }
    </style>
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close-button bn btn-small btn-danger" data-dismiss="modal"><span class="fas fa-times"></span></button>
                <h4 class="modal-title">Reset Layer Settings</h4>
                <h5 class="layer-name"></h5>
            </div>
            <div class="modal-body body-options">
                <div class="options stateful req-options">
                    <div class="item">
                        <label class="form-label">Layer:<span class="item-layer-name"></span></label>
                        <p class="instruction item-layer-description"></p>
                    </div>
                    <div class="divider"></div>
                    <div class="item stateful req-vector">
                        <input class="param layer-classes_cb" type="checkbox" >Layer Styles
                        <small class="instruction form-texts text-muted">Include the layer's classification (color rules)</small>
                    </div>
                    <div class="item  stateful req-vector">
                        <input class="param label-style_cb" type="checkbox" >Label Style
                        <small class="instruction form-texts text-muted">Include the layer's general label style</small>
                    </div>
                    <div class="divider stateful req-vector"></div>
                    <div class="item  stateful req-vector" title="Clear result popup content so that it inherits from the source layer at map load">
                        <input class="param inherit-popup_cb" type="checkbox">Inherit Popup Text
                        <small class="instruction form-texts text-muted">Clear result popup content so that it inherits from the source layer at map load</small>
                    </div>
                    <div class="item  stateful req-vector" >
                        <input class="param inherit-hover_cb" type="checkbox">Inherit Hovertip
                        <small class="instruction form-text text-muted">Clear result hover tip content so that it inherits from the source layer at map load.</small>
                    </div>

                    <div class="item stateful req-coll" >
                        <input class="opt sublayers-update_rad" name="sublayer_opts" type="radio">Update Sub-layer set
                        <small class="instruction form-text text-muted">Update map with current set of sub layers for this collection.  Adds missing sublayers to beginning of sub layer list, removes sub layers no longer in original collection.</small>
                    </div>
                    <div class="item stateful req-coll" >
                        <input class="opt sublayers-update_reorder_rad" name="sublayer_opts" type="radio">Update and Reorder Sub-layer set and order
                        <small class="instruction form-text text-muted">Update map with current set of sub layers for this collection and update the order to reflect the original layer colleciton. Adds missing sublayers and removes sub layers no longer in collection.</small>
                    </div>
                    <div class="item stateful req-coll" >
                        <input class="opt sublayers-reset_rad" name="sublayer_opts" type="radio">Rebuild sub-layers
                        <small class="instruction form-text text-muted">Reset sublayers for this collection to reflect the source collection. This removes all of this layer's sub layers and their settings from the map and re-adds the sub layers to the map with their defaults</small> 
                    </div>
                </div>
                <div class="xfer stateful req-xfer">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped active" role="progressbar"
                             aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%"></div>
                    </div>	
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="ok-button btn btn-primary" >Continue</button>
                <button type="button" class="cancel-button btn btn-danger" data-dismiss="modal">Cancel</button>
            </div>
        </div>

    </div>
</div>
<script type="text/javascript">
    function check_form(formdata) {
        if (!formdata.elements['name'].value) {
            return false;
        }
        return save();
    }

</script>