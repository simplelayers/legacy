define(["dojo/_base/declare", "dojo/on", "dojo/dom-attr", "dojo/dom-class", "dijit/_WidgetBase",
    "dijit/_TemplatedMixin", "dijit/_WidgetsInTemplateMixin",
    'dojo/dom', 'dojo/dom-construct', 'dojo/query',
    'sl_components/include_content/widget',
    'sl_components/wms_preview/widget',
    'sl_modules/WAPI',
    'sl_modules/sl_URL', 'sl_modules/Pages',
    "dojo/text!./templates/ui.tpl.html"], function (declare, dojoOn, domAttr, domClass,
        _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, dom, domCon, query,
        include_content,WmsPreview, wapi, sl_url, pages, template) {
    return declare('sl_pages/layer/import', [_WidgetBase, _TemplatedMixin,
        _WidgetsInTemplateMixin], {
        // Some default values for our author
        // These typically map to whatever you're passing to the constructor
        baseClass: 'layer_import',
        templateString: template,
        listner: null,
        projeciton_sel: null,
        format: null,
        usingCommon: true,
        constructor: function () {
            pages.SetPageArg('pageSubnav', 'data');
            pages.SetPageArg('pageTitle', 'Data - Import Layer');
            if (pages.GetPageArg('canCreate') === false) {
                pages.GoTo('?do=layer.list');
            }

        },
        postCreate: function () {
            $('body').attr('style', 'overflow:hidden !important');

            params = {};
            var layerId = pages.GetPageArg('layerId');

            if (layerId)
                this.import_layer_id.value = layerId;
            var format = pages.GetPageArg('format');
            this.format = format;
            $(this.domNode).toggleClass('in-format',this.format);
            domAttr.set(this.import_fmt, 'value', format.toLowerCase());
            domAttr.set(this.import_form, 'action', sl_url.getAPIPath() + '/layers/import/format:html');
            this.title.innerHTML = 'Import - ' + format.toUpperCase();

            pages.SetPageArg('pageTitle', 'Data - Import ' + format.toUpperCase() + ' Layer');
            if (pages.GetPageArg('layerId')) {
                pages.SetPageArg('pageTitle', 'Data - Updating  Layer #' + pages.GetPageArg('layerId') + ' via ' + format.toUpperCase());
            }
            this.common_ins_txt.SetSrc(sl_url.getServerPath() + 'client_ui/pages/layer/import/templates/common.ins.html');
            this.format_ins_txt.SetSrc(sl_url.getServerPath() + 'client_ui/pages/layer/import/formats/' + format.toLowerCase() + '.ins.html');

            dojoOn(this.projection_container, 'content_ready', this.ProjectionsReady.bind(this));
            this.projection_container.SetSrc(sl_url.getAPIPath() + 'map/projections/action:get/format:html');
            dojoOn(this.import_form, 'submit', this.ValidateForm.bind(this));
            this.format_ins_head.innerHTML = format.toUpperCase() + " Instructions:";
            require(['dojo/text!' + sl_url.getServerPath() + 'client_ui/pages/layer/import/formats/' + format.toLowerCase() + '.frm.html'], this.FormatFormReady.bind(this));
        },
        FormatFormReady: function (form) {
            console.log('here');
            if (this.instruct_cfg !== null) {
                var isStandard = '' + domAttr.get(dom.byId('instruct_cfg'), 'data-sl-standard');
                console.log(isStandard);
                if (isStandard == 'false') {
                    domClass.add(this.common_ins, 'hidden');
                }

            }
            console.log('before place');
            if (form != "") {
                domCon.place(form, this.format_frm, 'before');
            }
            var useCommon = '' + domAttr.get(dom.byId('form_cfg'), 'data-sl-use_common');
            if (useCommon == 'false') {
                query('.common').forEach(function (item) {

                    domClass.add(item, 'hidden');
                });
                this.usingCommon = false;
                domAttr.set(this.useCommon, 'value', 'false');
            }
            console.log(useCommon);
            console.log('after place');
            // this.format_frm.remove();
            switch (this.format.toLowerCase()) {
                case 'csv':
                    var radios = query("input[name='geometry_format']");
                    for (var i = 0; i < radios.length; i++) {
                        dojoOn(radios[i], 'click', this.HandleGeomFieldSelection.bind(this));
                    }
                    var fauxEvent = {target: radios[0]};
                    this.HandleGeomFieldSelection(fauxEvent);
                    break;
            }
        },
        HandleGeomFieldSelection: function (event) {
            console.log('here');
            var type = event.target.value;
            domAttr.set(dom.byId('gfmt'), 'value', type);
            const inputs = $("input[name=geometry_format]");
            inputs.each((index, item) => {
                $(this.domNode).toggleClass('csv-' + item.value, item.value === type);
            });
            /* for(var i = 0; i< formItems.length; i++ ) {
             domClass.add(formItems[i],'hidden');
             if(domClass.contains(formItems[i].id, 'csv_'+type)) {
             domClass.remove(formItems[i],'hidden');
             }				
             }*/

        },

        ProjectionsReady: function (event) {
            this.projection_sel = dom.byId('projection');
            $(this.projection_sel).toggleClass('form-select',true);
            dojoOn(this.projection_sel, 'change', this.ProjectSelected.bind(this));
        },

        ValidateForm: function (event) {
            var formdata = event.target;
            dot = /[^ _A-Za-z0-9]/;
            basename = formdata.elements['name'].value;
            checkname = basename.match(dot);
            if (checkname) {
                alert('Base layer names are restricted to alphanumeric characters, spaces, and underscores.  Please rename.');
                event.preventDefault();
                return false;
            }

            if (this.usingCommon) {
                if (!formdata.elements['source'].value) {
                    if (!this.fileURL.value) {
                        alert('Please select a file or provide a URL.');
                        event.preventDefault();
                        return false;
                    }
                }
            }

            // if (!formdata.elements['name'].value) { return false; }
            alert('Files may take a while to upload. Please be patient.');
            return true;
        },

        ProjectSelected: function (event) {
            //console.log(this.projection_sel.value);
        }

    });
});
