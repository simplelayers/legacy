/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Controller {
    attributeNames;
    attributeTypes;
    pageArgs;
    listForString;
    listForInt;
    listForBoolean;
    searchField;
    defaultSearch;
    constructor(args) {
        $('.contentarea').toggleClass('flex-content',true);
        this.pageArgs = args.pageArgs;
        console.log(this.pageArgs);
        this.attributeNames = [];
        this.attributeTypes = [];
        for (const attribute of this.pageArgs.attributes) {
            this.attributeNames.push(attribute[0]);
            this.attributeTypes.push(attribute[1]);
        }
        this.listForInt = $('<select></select>');
        this.listForString = $('<select></select>');
        this.listForBoolean = $('<select></select>');
        this.searchField = $('#search_field')[0];
        this.SetOptions();
        this.defaultSearch = $('#defaultsearch');
        this.SetDefaultCriteria();
        $('textarea[name="tags"]').tagsInput({
            width: '100%'
        });
        $('#minscale').change();
        $('#minscale').val(this.pageArgs.minScale);

        $('#search_field').change(()=> {
            $.each(this.attributeNames, (index, value) => {
                if (value == $('#search_field option:selected').val()) {
                    var type = this.attributeTypes[index];
                    switch (type) {
                        case 'boolean':
                            $('#comparison').empty();
                            this.listForBoolean.find('option').clone().appendTo('#comparison');
                            break;
                        case 'integer':
                        case 'int':
                        case 'float':
                            $('#comparison').empty();
                            this.listForInt.find('option').clone().appendTo('#comparison');
                            break;
                        default:
                            $('#comparison').empty();
                            this.listForString.find('option').clone().appendTo('#comparison');
                    }
                }
            });
        });
        $('#search_field').change();
        this.RebuildList();
    }
    RebuildList() {
        window.rebuildList();
    }
    SetOptions() {
        $('<option></option>').val('==').html('equals').appendTo(this.listForInt);
        $('<option></option>').val('==').html('equals').appendTo(this.listForString);
        $('<option></option>').val('==').html('equals').appendTo(this.listForBoolean);
        $('<option></option>').val('&gt;').html('is greater than').appendTo(this.listForInt);
        $('<option></option>').val('&gt;=').html('is greater than or equal to').appendTo(this.listForInt);
        $('<option></option>').val('&lt;').html('is less than').appendTo(this.listForInt);
        $('<option></option>').val('&lt;=').html('is less than or equal to').appendTo(this.listForInt);
        $('<option></option>').val('contains').html('contains').appendTo(this.listForInt);
        $('<option></option>').val('contains').html('contains').appendTo(this.listForString);
        $('<option></option>').val('!contains').html('lacks').appendTo(this.listForInt);
        $('<option></option>').val('!contains').html('lacks').appendTo(this.listForString);
        for (const attName of this.attributeNames) {
            $('<option></option>').val(attName).html(attName).appendTo(this.searchField);
        }
    }
    SetDefaultCriteria() {
        if (this.pageArgs.defaultCriteria !== null) {
            if (this.pageArgs.defaultCriteria.length > 0) {
                $('#search_field').val(this.pageArgs.defaultCriteria[0]);
            }
	    if (this.pageArgs.defaultCriteria.length > 1) {
                const me = this;
                setTimeout(function(){
                    $('#comparison').val(me.pageArgs.defaultCriteria[1]);
                    if(this.pageArgs.defaultCriteria.length > 2) {
                        setTimeout(function(){
                            $('#comparison').val(me.pageArgs.defaultCriteria[2]);
                        },500)
                    }
                },500);
            }
            $(this.defaultSearch).checked = true;
             $('.criterial').toggleClass('enabled',true);
        } else {
            $(this.defaultSearch).checked = false;
            $('.criterial').toggleClass('enabled',false);
         
        }
    }
    ToggleCriteria() {
        $('.criterial').toggleClass('enabled');
    }
    CheckForm(formdata) {
        if (!formdata.elements['id'].value)
            return false;
        if (!formdata.elements['name'].value)
            return false;
        const dot = /[^ _A-Za-z0-9]/;
        const basename = formdata.elements['name'].value;
        const checkname = basename.match(dot);
        if (checkname) {
            alert('Layer names are restricted to alphanumeric characters, spaces, and underscores.  Please rename.');
            return false;
        }
        return true;
    }
    Reminder() {
        alert('It will take a moment to prepare your download.\nPlease be patient.');
    }
    ToggleList() {
        $('.listed').toggleClass('show-list');
    }

}

function StartWhenReady() {
    if (!window.SL) {
        setTimeout(() => {
            return StartWhenReady();
        }, 20);
        return;
    }
    window.controller = new Controller(window.SL);
}
$(() => {
   

    $('#navRow').toggleClass('no-nav', true);
    StartWhenReady();
});
