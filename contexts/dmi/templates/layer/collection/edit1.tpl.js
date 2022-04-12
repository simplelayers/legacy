/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Controller {
    tags;
    save_btn;
    addAll_btn;
    removeAll_btn;
    constructor() {
        this.listOfLayers = {};
        this.loaded = false;
        this.tags = null;
    }
    GetGeom(layer) {
        if (layer["type"] === 2) {
            return 'raster';
        } else if (layer["type"] === 3) {
            return 'wms';
        } else if (layer["type"] === 6) {
            return 'collection';
        } else {
            if (window.geomTypeEnum !== null) {
                let geomType = window.geomTypeEnum[Number(layer['geom'])];
                switch (geomType) {
                    case 'point':
                    case 'polygon':
                    case 'line':
                        return geomType;
                        break;
                }
            }
        }
        return 'unknown';
    }
    Ready() {
        this.save_btn = $('#save_btn')[0];
        this.addAll_btn = $('#addAll_btn')[0];
        this.clearAll_btn = $('#clearAll_btn')[0];
        $(this.save_btn).click(() => {
            this.Save();
        });
        $(this.addAll_btn).click(() => {
            $('#sortable1 li').each((index, item) => {
                if ($(item).css('display') !== 'none')
                    this.MoveRight($(item));
            });
        });
        $(this.clearAll_btn).click(() => {
            $('#sortable2 li').each(function (index, item) {
                if ($(item).css('display') !== 'none') $(item).remove();
            });
            this.RebuildList();
        });
        this.tags = $('textarea[name="tags"]').tagsInput({});
        $('.contentarea').toggleClass('flex-content', true);
        $('.tundra').toggleClass('hidden-overflow', true);
        $('#layer_selector').toggleClass('form-group', true).toggleClass('form-inline', true);
        $('#layer_selector').dataSelector().bind("update", (e, data) => {
            this.listOfLayers = data;
            this.Load();
        }).bind("loading", (e) => {
            $('#sortable1').empty();
        });
        $('#layer_selector select').toggleClass('form-control', true);
        $(".subnav").css("display", "none");
	// console.log('wtf',data);
        $.extend({postJSON: (url, data, callback) => {
                return jQuery.post(url, data, callback, "json");
            }});
        $("#sortable1, #sortable2").sortable({
            connectWith: "#sortable2",
            distance: 10,
            placeholder: "ui-state-highlight",
            tolerance: 'pointer'
        }).disableSelection();
        $("#sortable1").sortable({
            stop: (event, ui) => {
                this.RebuildList();
            },
            containment: 'window'
        });
        $("#sortable2").sortable({
            containment: '#sortable2',
            receive: (event, ui) => {
                createDelete(ui.item);
            },
            axis: "y"
        });
        $("#filter").keyup((e) => {
            e.preventDefault();
            this.CheckFilter();
        });
    }
    CheckFilter() {
        var toFind = $("#filter").val().toLowerCase();
        $("#sortable1 li").each((index,item) => {
            if ($(item).text().toLowerCase().indexOf(toFind) !== -1) {
                $(item).css("display", "list-item");
            } else {
                $(item).css("display", "none");
            }
        });
    }

    RebuildList(type) {
        $('#sortable1').empty();
        $.each(this.listOfLayers.view, (i, layer) => {
            if (($('#sortable2 #' + layer["id"]).length === 0) && (layer["type"] !== "6")) {
                $('#sortable1').append(this.MakeElement(layer["id"], layer["owner_name"], this.GetGeom(layer), "window.controller.MoveRight($(this).parent().parent());", layer["name"], 'add'));
            }
        });
        this.CheckFilter();
    }

    CreateDelete(item) {
        $('#check', item).html('<button type="button" class="btn btn-sm btn-danger" onclick="$(this).parent().parent().remove();window.controller.RebuildList();"><span class="fas fa-minus"></span></button>');
        return item.html();
    }

    MoveRight(item) {
        this.CreateDelete(item);
        item.appendTo('#sortable2');
    }

    SetDisabled(bool) {
        $("#sortable1").sortable("option", "disabled", bool);
        $("#sortable2").sortable("option", "disabled", bool);
    }
    Save()
    {
	const layerDescription = $('#description').val();
        this.SetDisabled(true);
        var allToSave = [];
        let error = "No layers selected.\r\n";
        $("#sortable2 li").each(function (i) {
            allToSave.push(this.id);
            error = '';
        });
        if ($("#name").val() === '') {
            error += "Layer needs a name.";
        }
        if (error !== '') {
            alert(error);
            this.SetDisabled(false);
        } else {
            if ($('#id').val() === 'new') {
		 $.postJSON('./?do=wapi.layer.collections&crud=c&name=' + $("#name").val(), {layers: '' + allToSave,description:layerDescription,tags:this.tags.val()}, (data) => {
                    if (data.userError) {
                        alert(data.userError);
                        return false;
                    }
                    $('#id').val(data.id);
                    alert('saved');
                    window.location.href = "./?do=layer.collection.edit1&id=" + $('#id').val();
                }).complete(function () {
                    setDisabled(false);
                });
            } else {
		$.postJSON('./?do=wapi.layer.collections&crud=u&name=' + $("#name").val() + '&layer=' + $('#id').val(), {layers: '' + allToSave,description:layerDescription,tags:this.tags.val()}, (data) => {
                    if (data.userError) {
                        alert(data.userError);
                        return false;
                    }
                    alert('saved');
                }).complete(function () {
                    if(!!setDisabled) setDisabled(false);
                });
            }
        }
    }

    Load() {
        if (this.loaded) {
            this.RebuildList();
        } else {
            $('#sortable2').empty();
            $('#sortable1').empty();
            this.SetDisabled(true);
            if ($('#id').val() !== 'new') {
                $(".subnav").css("display", "table");
                $.getJSON('./?do=wapi.layer.collections&crud=r&format=json&layer=' + $('#id').val(), (data) => {
                    if (data.userError) {
                        alert(data.userError);
                        return false;
                    }
		    // console.log(data);
                    $.each(data.sublayers, (i, layer) => {
                        $('#sortable2').append(this.MakeElement(layer["id"], layer["owner_name"], layer["geom"], "$(this).parent().parent().remove();window.controller.RebuildList();", layer["name"], 'delete'));
                    });
                    $('#name').val(data.name);
                    this.RebuildList();
                }).complete(() => {
                    this.SetDisabled(false);
                });
            } else {
                this.RebuildList();
                this.SetDisabled(false);
            }
            this.loaded = true;
        }
    }

    MakeElement(id, owner, geom, onclick, name, icon) {
        const faIcon = (icon === 'delete') ? 'minus' : 'plus';
        const buttonMode = (icon === 'delete') ? 'danger' : 'primary';
        return '<li id="' + id + '" class="ui-state-default"><a onclick="$(\'#filter\').val($(this).html());window.controller.CheckFilter();" class="collectionname">' + owner + '</a><a class="collectiontype">' + geom + '</a><span id="check"><button type="button" class="btn btn-sm btn-' + buttonMode + '" onclick="' + onclick + '"><span class="fas fa-' + faIcon + '"></span></button></span>' + name + '</li>';
    }
}


$(() => {

    /*$.extend({PostJSON: function (url, data, callback) {
     return new jQuery.post(url, data, callback, "json");
     }});*/
    window.controller = new Controller();
    window.controller.Ready();
});
