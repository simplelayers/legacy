
class Controller {
    mapId;
    loadedData;
    name;
    tags;
    description;
    bbox0;
    bbox1;
    bbox2;
    bbox3;
    boundsElements;
    listOfLayers;
    availLayerList;
    mapLayerList;
    dataSelector;
    filter;
    geomType;
    dialog;
    constructor() {

        this.mapId = $('#id');
        this.name = $('#name');
        this.tags = $('textarea[name="tags"]').tagsInput({});
        this.description = $('#description');
        this.bbox0 = $('#bbox0');
        this.bbox1 = $('#bbox1');
        this.bbox2 = $('#bbox2');
        this.bbox3 = $('#bbox3');
        this.boundsElements = [this.bbox0, this.bbox1, this.bbox2, this.bbox3];
        this.includeBB = $('#bounds_cb');
        this.listOfLayers = null;
        this.availLayerList = $('#availLayerList');
        this.mapLayerList = $('#mapLayerList');
        this.dataSelector = $('#inline_selector').dataSelector();
        this.submitButton = $('#submitButton');
        this.fitler = $('#filter');
        this.filterClearButton = $('.filter-clear-button');
        this.dialog = new ResetLayerModal();
        this.Ready();

    }
    HideSubNav() {
        $(".subnav").css("display", "none");
    }
    Ready() {
        const me = this;
        $('.tundra').toggleClass('hidden-overflow', true);
        $('.contentarea').toggleClass('no-nav-row', true);
        $(".contentarea").toggleClass('flex-content', true);

        this.includeBB.on('change', function () {
            me.UpdateBounds();
        });
        this.UpdateBounds();
        this.filterClearButton.on('click', function () {
            $('#filter').val('');
            me.CheckFilter();
        });

        $('#inline_selector').toggleClass('form-group', true).toggleClass('form-inline', true);
        $('#inline_selector select').toggleClass('form-control', true);
        this.tags.toggleClass('form-control', true);
        this.HideSubNav();

        this.dataSelector.bind('update', function (e, data) {
            me.listOfLayers = data;
            me.Load();
        }).bind('loading', function (e) {
            me.availLayerList.empty();
        });
        const sortableParams = {
            connectWith: "#mapLayerList",
            distance: 10,
            placeholder: "ui-state-highlight",
            tolerance: 'pointer',
            start: function (e, ui) {

                ui.placeholder.height('24px');// ui.item.height());
            }
        }
        $("#availLayerList, #mapLayerList").sortable(sortableParams).disableSelection();
        this.availLayerList.sortable({
            stop: function (event, ui) {
                this.RebuildList();
            }
        });
        this.mapLayerList.sortable({
            receive: function (event, ui) {
                me.CreateDelete(ui.item);
            }
        });

        this.fitler.keyup(function (e) {
            e.preventDefault();
            me.CheckFilter();
        });
        this.submitButton.on('click', function () {
            me.Save();
        });
        this.Load();

    }
    EmptyLists() {
        this.mapLayerList.empty();
        this.availLayerList.empty();
    }

    GetGeom(layer) {
        if (layer.hasOwnProperty('children')) {
            if (layer.children.length > 0) {
                return 'collection';
            }
            ;
        }
        if (layer.hasOwnProperty('type')) {
            if (layer["type"] == 2) {
                return 'raster';
            }
            if (layer["type"] == 3) {
                return 'wms';
            }
            if (layer["type"] == 4) {
                return 'point';
            }
            if (layer["type"] == 6) {
                return 'collection';
            }
        }
        ;
        this.geomType = geomTypes[layer['geom']];
        switch (this.geomType) {
            case 'point':
            case 'polygon':
            case 'line':
                return this.geomType;
                break;
        }
        if (layer['geom']) {
            return layer['geom'];
        }
        return 'unknown';
    }
    CheckFilter() {
        var toFind = $("#filter").val().toLowerCase();
        $("li", this.availLayerList).each(function (index) {
            if ($(this).text().toLowerCase().indexOf(toFind) != -1) {
                $(this).css("display", "list-item");
            } else {
                $(this).css("display", "none");
            }
        });
    }
    RebuildList(type) {
        const me = this;
        this.availLayerList.empty();
        if (this.listOfLayers.view) {
            $.each(this.listOfLayers.view, function (i, layer) {
                me.availLayerList.append(
                        me.MakeElement(layer["id"],
                                layer["owner_name"],
                                me.GetGeom(layer),
                                function () {
                                    me.MoveRight($(this).parent().parent());
                                },
                                layer["name"],
                                'add',
                                layer["children"],
                                true,
                                layer["description"],
                                layer
                                ).bind(this)
                        );
                if (layer["children"] != undefined) {
                    $("#subSort" + layer["id"]).sortable({
                        forcePlaceholderSize: true,
                        placeholder: "ui-state-highlight",
                        axis: "y"
                    });
                }
            });
        }
        this.CheckFilter();
        rearmToolTips();
    }
    CreateDelete(item) {
        const me = this;
        $(item).children('#check').html('<button class="item-del btn btn-sm btn-danger" ><span class="fas fa-minus"></span></button>');
        $('button.item-del', $(item).children('#check')).on('click', function () {
            if (confirm('This layer and any properties and styles will be removed from the map. This change will only be committed when you click the Save button and will not be undoable at that time. Do you want to continue?')) {
                $(this).parent().parent().remove();
                me.RebuildList();
            }

        });
        return item.html();
    }
    MoveRight(item) {
        var clone = item.clone();
        clone.prependTo('#mapLayerList');
        this.CreateDelete(clone);
    }
    SetDisabled(bool) {
        this.availLayerList.sortable("option", "disabled", bool);
        this.mapLayerList.sortable("option", "disabled", bool);
    }
    GetBounds() {
        let bounds = [];
        for (let i = 0; i < this.boundsElements.length; i++) {
            bounds.push(this.boundsElements[i].val());
        }
        return bounds.join(',');
    }

    Save() {
        const me = this;
        this.SetDisabled(true);
        const layerSet = [];
        let error = "";
        $("li.top", this.mapLayerList).each(function (i) {
            const data = jQuery.data(this, 'layerData');
            if (!(!!data)) {
                layerSet.push({layerId: this.id, playerId: 'null'});
                return;
            }
            const plid = data.hasOwnProperty('plid') ? data.plid : 'null';
            const entry = {layerId: data.id, playerId: plid};
            layerSet.push(entry);
            if (data.hasOwnProperty('children')) {
                for (const child of data.children) {
                    const childPlid = child.hasOwnProperty('plid') ? child.plid : 'null';
                    const childEntry = {layerId: child.id, playerId: childPlid};
                    layerSet.push(childEntry);
                }
                ;
            }
            ;
        });
        error = '';
        if (this.name.val() == '') {
            error += 'Project needs a name.'
        }
        if (error != "") {
            alert(error);
            return false;
        }
        const params = {};
        params.mapId = this.mapId.val();
        params.name = this.name.val();
        params.tags = this.tags.val();
        params.description = this.description.val();
        params.layers = layerSet;
        this.mapLayerList.empty();
        this.mapLayerList.append('<div class="progress-container"><div class="progress"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%">Saving</div></div></div>');

        if (this.includeBB.prop('checked') === true) {
            params.bbox = this.GetBounds();
        }
        params.format = 'json';
        this.SetDisabled(false);
        console.log(params);

        $.PostJSON('wapi/map/save_changes/', params, function (response) {
            me.Saved(response)
        });
    }
    Saved(response) {
        this.loaded = false;
        this.Load();
    }
    ReLoad() {
        this.loaded = false;
        this.Load();
    }
    Load() {
        const me = this;

        if (this.loaded === true) {
            this.RebuildList();
        } else {
            this.EmptyLists();
            this.mapLayerList.append('<div class="progress-container"><div class="progress"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%">Loading</div></div></div>');
            this.availLayerList.append('<div class="progress-container"><div class="progress"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%">Loading</div></div></div>');

            this.SetDisabled(true);
            if ($('#id').val() != 'new') {
                $(".subnav").css("display", "table");
                $.getJSON('./?do=wapi.project.layers&crud=r&format=json&project=' + $('#id').val(), this.Loaded.bind(this)).complete(function () {
                    me.SetDisabled(false);
                });
            } else {
                this.RebuildList();
                this.SetDisabled(false);
            }
            this.loaded = true;
        }
    }
    Loaded(data) {
        this.EmptyLists();
        this.loadedData = data;
        console.log(data);
        const me = this;
        if (data.userError) {
            alert(data.userError);
            return false;
        }
        $.each(data, (function (i, layer) {
            $('#mapLayerList').append(
                    me.MakeElement(layer["plid"],
                            layer["owner_name"],
                            this.GetGeom(layer),
                            function () {
                                if (confirm('This layer and any properties and styles will be removed from the map. This change will only be committed when you click the Save button and will not be undoable at that time. Do you want to continue?')) {
                                    $(this).parent().parent().remove();
                                    me.RebuildList();
                                }
                            },
                            layer["name"],
                            'delete',
                            layer["children"],
                            true,
                            layer["description"], layer)
                    )
            if (layer["children"] != []) {
                $("#subSort" + layer["id"]).sortable({
                    forcePlaceholderSize: true,
                    placeholder: "ui-state-highlight",
                    axis: "y"
                });
            }
        }).bind(this));
        this.RebuildList();

    }
    /*
     * Load(){ if(this.loaded === false){ this.RebuildList(); }else{
     * this.EmptyLists();
     * 
     * this.SetDisabled(true); if($('#id').val() != 'new'){
     * $(".subnav").css("display", "table");
     * $.getJSON('./?do=wapi.project.layers&crud=r&format=json&project='+$('#id').val(),
     * function(data) { if(data.userError){alert(data.userError);return false;}
     * $.each(data, function(i, layer) { $('#mapLayerList').append(
     * makeElement(layer["plid"], layer["owner_name"], this.GetGeom(layer),
     * "$(this).parent().parent().remove();rebuildList();", layer["name"],
     * 'delete', layer["children"], true, layer["description"],layer) );
     * if(layer["children"] != []){ $( "#subSort"+layer["id"] ).sortable({
     * forcePlaceholderSize: true, placeholder: "ui-state-highlight", axis: "y"
     * }); } }); rebuildList(); }).complete(function (){ setDisabled(false); });
     * }else{ rebuildList(); setDisabled(false); } loaded = true; } }
     */
    MakeElement(id, owner, geom, onClick, name, icon, children, top, desc, data) {
        const me = this;
        if (children == undefined) {
            children = [];
        }
        let item = '<li id="' + id + '" class="ui-state-default';
        if (top)
            item += ' top';
        item += '" style="" >';
        item += '<a  class="collectionname" title="Layer owner ' + owner + ', click to filter available layers by this owner">';
        item += owner;
        item += '</a>';
        item += '<a class="collectiontype" title="layer type">' + this.GetGeom(data) + '</a>';
        item += '<span id="check">';
        if (top) {
            const buttonMode = (icon === 'delete') ? 'danger' : 'primary';
            const faClass = (icon === 'delete') ? 'minus' : 'plus';
            const title = (icon === 'delete') ? 'Remove item from map list' : 'Add item to map list';
            item += '<button type="button" class="action-button btn btn-sm btn-' + buttonMode + '" title="' + title + '">';
            item += '<span class="fas fa-' + faClass + '"></span>';
            item += '</button>';
        }
        item += '</span>';
        if (desc === null) {
            desc = '';
        }
        let infoClass = 'info';
        let btnClass = 'info-button';
        let infoIcon = 'info';
        if (desc.trim() === '') {
            desc = 'No description available';
            if (icon === 'add') {
                infoClass = 'no-info';
                infoIcon = 'info';
                btnClass = 'config-button';
            }
        }
        if (icon !== 'add') {
            infoIcon = 'cog';
            infoClass = 'cog';
            btnClass = 'config-button';
        }
        item += '<button type="button" class="btn btn-sm btn-primary ' + btnClass + ' ' + infoClass + '"  title="' + desc + '"><span class="fas fa-' + infoIcon + '"></span></button>';
        item += '<span>';
        if (!!data.plid) {
            item += `<button type="button" class="btn btn-sm btn-primary info-button info"
 title="${data.id}:${data.plid} ${name}"><span class="fas fa-info"></span></button>`;
        } 
        item += `${name}`;
        item += '</span>';
        item += '</li>';
        var element = $(item);
        element.data('layerData', data);
        $('.config-button', element).on('click', function () {

            me.ShowModal(data, function () {
                me.ReLoad();
            });

        });
        $('.action-button', element).on('click', onClick);
        $('.collectionname', element).on('click', function () {
            $('#filter').val($(this).html());
            me.CheckFilter();
        });
        if (top) {
            if (children) {
                var $sublist = $('<ul class="subSort" id="subSort' + id + '"></ul>');
                $.each(children, function (i, layer) {
                    $sublist.append(me.MakeElement(layer["plid"],
                            layer["owner_name"],
                            me.GetGeom(layer),
                            this.DoNothing,
                            layer["name"],
                            'none',
                            layer["children"],
                            false,
                            layer["description"],
                            layer)
                            );
                });
                element.append($sublist);
            }
            ;
        }
        ;
        return element;
    }
    UpdateBounds() {
        const me = this;
        for (const bound of this.boundsElements) {
            bound.prop('disabled', function (i, v) {
                return !me.includeBB.prop('checked'); });
        }
    }
    ShowModal(data, handler) {
        this.dialog.Show(data, handler);
    }

    DoNothing() {}
}
class ResetLayerModal {
    http;
    dialog;
    content;
    header;
    title;
    layerName;
    itemLayerName;
    itemLayerDesc;
    body;
    options;
    layerProps;
    layerClasses;
    labelStyle;
    inheritPopup;
    inheritHover;
    subsUpdate;
    subsUpdateReorder;
    subsReset;
    footer;
    okButton;
    params;
    opts;
    data;
    showHandler;
    constructor() {

        this.dialog = $('#layerResetDialog');
        this.content = $('.modal-content', this.dialog);
        this.header = $('.modal-header', this.dialog);
        this.title = $('.modal-title', this.dialog);
        this.layerName = $('.layer-name', this.dialog);
        this.itemLayerName = $('.item-layer-name', this.dialog);
        this.itemLayerDesc = $('.item-layer-description', this.dialog);

        this.body = $('.modal-body', this.dialog);
        this.progress = $('.progress-bar', this.dialog);
        this.options = $('.options', this.dialog);
        this.layerProps = $('.layer-props_cb', this.dialog);
        this.layerClasses = $('.layer-classes_cb', this.dialog);
        this.labelStyle = $('.label-style_cb', this.dialog);
        this.inheritPopup = $('.inherit-popup_cb', this.dialog);
        this.inheritHover = $('.inherit-hover_cb', this.dialog);
        this.subsUpdate = $('.sublayers-update_rad', this.dialog);
        this.subsUpdateReorder = $('.sublayers-update_reorder_rad', this.dialog);
        this.subsReset = $('.sublayers-reset_rad', this.dialog);
        this.params = $('.param', this.dialog);
        this.opts = $('.opt', this.dialog);
        this.okButton = $('.ok-button', this.dialog);
        this.cancelButton = $('.cancel-button', this.dialog);
        this.footer = $('.modal-footer', this.dialog);
        const me = this;
        this.closeButton = $('.close-button', this.dialog);
        this.closeButton.on('click', function () {
            me.Hide();
        });
        this.cancelButton.on('click', function () {
            me.Hide();
        })
        this.okButton.on('click', function () {
            const params = {};
            params.properties = me.layerProps.prop('checked') === true ? 'true' : 'false';
            if (me.IsCollection() === true) {
                if (me.subsUpdate.prop('checked') === true) {
                    params.subs = 'update';
                } else if (me.subsReset.prop('checked') === true) {
                    params.subs = 'reset';
                } else if (me.subsUpdateReorder.prop('checked') === true) {
                    params.subs = 'updateReorder';
                }
            }
            if (me.IsVector() === true) {
                params.labelStyle = me.labelStyle.prop('checked') === true ? 'true' : 'false';
                params.classes = me.layerClasses.prop('checked') === true ? 'true' : 'false';
                params.inheritPopup = me.inheritPopup.prop('checked') === true ? 'true' : 'false';
                params.inheritHover = me.inheritHover.prop('checked') === true ? 'true' : 'false';

            }
            params.playerId = me.data.plid;
            params.layerId = me.data.id;
            me.progress.html('Saving changes');
            me.body.toggleClass('body-options', false);
            me.body.toggleClass('body-xfer', true);
            me.Save(params);
        });
    }
    Show(data, handler) {
        const me = this;
        this.showHandler = handler;
        this.data = data;
        this.body.toggleClass('body-options', true);
        this.body.toggleClass('body-xfer', false);
        $('p', this.body).html(this.data.name);
        this.itemLayerName.html(this.data.name);
        this.itemLayerDesc.html(this.data.description);

        this.params.each(function (index, param) {
            $(param).prop('checked', false);
        });
        this.opts.each(function (index, opt) {
            $(opt).prop('checked', false);
        });
        if (me.IsCollection() === true) {
            this.body.toggleClass('vector', false);
            this.body.toggleClass('coll', true);
        }
        if (me.IsVector() === true) {
            this.body.toggleClass('vector', true)
            this.body.toggleClass('coll', false);
        }
        if ((me.IsCollection() === false) && (me.IsVector() === false)) {
            this.body.toggleClass('vector', false)
            this.body.toggleClass('coll', false);
        }
        ;
        $('button', this.footer).prop('disabled', false);
        this.dialog.modal('show');

    }
    Save(params) {
        const me = this;
        jQuery.ajax({
            type: 'POST',
            url: 'wapi/map/reset_map_layer/',
            cache: false,
            data: params,
            success: this.Saved.bind(this),
            error: this.SaveProblem.bind(this)
        });
        $('button', this.footer).prop('disabled', true);
    }
    Saved(response) {
        if (this.showHandler) {
            this.Hide();
            this.body.toggleClass('body-options', true);
            this.body.toggleClass('body-xfer', false);
            this.showHandler();

        }
    }
    SaveProblem() {
        if (this.showHandler) {
            this.Hide();

            this.body.toggleClass('body-options', true);
            this.body.toggleClass('body-xfer', false);


            alert("There was an unknown problem saving this layer");
            this.showHandler();

        }
    }
    Hide() {
        this.dialog.modal('hide');
    }
    IsCollection() {
        if (this.data.hasOwnProperty('type')) {
            return Number(this.data.type) === 6;
        } else if (this.data.hasOwnProperty('geom')) {
            return this.data.geom === 'collection';
        }
        return false;
    }
    IsVector() {
        if (this.data.hasOwnProperty('type')) {
            return this.data.type === 1;
        }
        if (this.data.hasOwnProperty('geom')) {
            return ['point', 'line', 'polygon'].indexOf(this.data.geom) > -1;
        }
        return false;
    }
}
$(function () {

    $.extend({PostJSON: function (url, data, callback) {
            return new jQuery.post(url, data, callback, "json");
        }});
    window.controller = new Controller();

});

