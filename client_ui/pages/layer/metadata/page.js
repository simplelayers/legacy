define(["dojo/_base/declare", "dojo/on", "dojo/dom-attr", "dojo/dom-style", "dojo/dom-class", "dijit/_WidgetBase",
    "dijit/_TemplatedMixin", "dijit/_WidgetsInTemplateMixin",
    'dojo/dom', 'dojo/dom-construct', 'dojo/query',
    'sl_modules/WAPI',
    'sl_modules/sl_URL', 'sl_modules/Pages',
    "dojo/touch",
    "sl_components/listings/attribute_listing/widget",
    "dojo/text!./ui.tpl.html"], function (declare, dojoOn, domAttr, domStyle, domClass,
        _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, dom, domCon, query,
        wapi, sl_url, pages,
        dojoTouch,
        attributesListing,
        template) {
    return declare('sl_pages/layer/metadata', [_WidgetBase, _TemplatedMixin,
        _WidgetsInTemplateMixin], {
// Some default values for our author
// These typically map to whatever you're passing to the constructor
        baseClass: 'page',
        templateString: template,
        $template: null,
        data: null,
        lastIndent: null,
        ids: null,
        constructor: function () {
            pages.SetPageArg('pageSubnav', 'data');
            pages.SetPageArg('pageTitle', 'Data - Metadata');
            this.layerId = pages.GetPageArg('layer');
            this.lastIndent = 0;
            this.ids = 0;
        },
        startup: function () {
            this.$template = $(this.containerTemplate)[0];
            this.$template.remove();
            
            this.download_btn.addEventListener('click',()=>{
                window.open(wapi.baseURL+'v4/layers/metadata/layer:'+this.layerId+'/action:save/','_blank'); 
            });
            wapi.exec('v4/layers/metadata', {layer: this.layerId}, (reply) => {
                this.data = reply.response;
                this.title.innerHTML = this.data.layerName;
                this.layerId_lbl.innerHTML = this.data.layerId;
                $(this.contentContainer).toggleClass('hidden', false);
                let doc = '';
                console.log(this.data.SourceMetadata);
                if(this.data.SourceMetadata) {
                    doc += this.WriteNode('Source Metadata',this.data.SourceMetadata);
                }
                /*for (const key in this.data.SourceMetadata) {
                    if (!this.data.SourceMetadata.hasOwnProperty(key))
                        continue;
                    doc += this.WriteNode('Source Metadata', this.data.SourceMetadata[key])
                }*/
                $(this.contentBody).append($(doc));
                this.contentContainer.addEventListener('click', (event) => {
                    let node = $(event.target);
                    if (node.hasClass('element') === false) {
                        const parent = $(event.target).closest('.element');
                        if (parent.length > 0) {
                            node = $(parent[0]);
                        }
                    }
                    const id = node.attr('data-node-id');
                    const $caret = $(`div[data-toggle-group=${id}]`);
                    if (node.hasClass('open')) {
                        $caret.toggleClass('fa-caret-down', false);
                        $caret.toggleClass('fa-caret-right', true);
                        $($('.node-body',node)[0]).toggleClass('hidden',true);
                        node.toggleClass('open', false);
                        node.toggleClass('closed', true);                        
                    } else {
                        $caret.toggleClass('fa-caret-down', true);
                        $caret.toggleClass('fa-caret-right', false);
                        $($('.node-body',node)[0]).toggleClass('hidden',false);

                        node.toggleClass('open', true);
                        node.toggleClass('closed', false);
                    }
                    if (!id) {
                        return;
                    }
                });
            });
        },
        WriteNode: function (nodeName, node) {
            const intVal = !isNaN(parseInt(nodeName));
            
            this.ids += 1;
            let template = this.$template.outerHTML;
            template = template.replace(/{{nodeId}}/g, this.ids);
            let attributes = [];
            if (node?._attributes) {
                attributes = node?._attributes;
            }
            let atts = '';
            for (const att in attributes) {
                if (!attributes.hasOwnProperty(att))
                    continue;
                atts += `<div class="attribute-node"><label class="element-name">${att}</label><span>${attributes[att]}</span></div>`;
            }
            template = template.replace(/{{indent}}/g, this.lastIndent)
            template = template.replace(/{{elementName}}/g, nodeName === 'value' ? '' : nodeName);
            template = template.replace(/{{attElements}}/g, atts);
            template = template.replace(/{{intHidden}}/g,intVal ? 'hidden':'')
            if (typeof node !== 'object') {
                template = template.replace(/{{showToggle}}/g, 'hidden');
                template = template.replace(/{{value}}/g, node);
                template = template.replace(/{{children}}/g, '');
            } else {
                template = template.replace(/{{showToggle}}/g, '');
                template = template.replace(/{{value}}/g, '');
                let children = '';
                this.lastIndent += 1;
                for (let child in node) {
                    if (!node.hasOwnProperty(child))
                        continue;
                    if (child === '_attributes')
                        continue;
                    children += this.WriteNode(child, node[child]);
                }
                this.lastIndent -= 1;
                template = template.replace(/{{children}}/g, children);
            }
            return template;
        }
    });
});