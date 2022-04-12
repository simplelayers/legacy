define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom-attr",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'dojo/dom-class',
        'dojo/dom-style',
        'dojo/parser',
        'dojo/dom-construct',
        'dojo/topic',
        'dojo/json',
        'dijit/layout/ContentPane',
        'dijit/layout/StackContainer',
        "dijit/layout/StackController",
        'sl_components/sl_button/widget',
        'sl_pages/admin/master_list/listItem',
        'sl_modules/WAPI',
        'sl_modules/Pages',
        "dojo/text!./templates/permissions_masterlist.tpl.html"],
    function(declare,
    		on,
    		domAttr,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		domClass,
    		domStyle,
    		parser,
    		domCon,
    		topic,
    		json,
    		contentPane,
    		stackContainer,
    		stackController,
    		sl_button,
    		listItem,
    		wapi,
    		pages,
    		template){
        return declare('sl_pages/admin/master_list',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
        	baseClass:'permissions masterlist',
        	templateString: template,
        	permissions:null,
        	imporetViewed:false,
        	baseURL:'',
        	constructor:function() {
        		pages.SetPageArg('pageSubnav','admin');
        		pages.SetPageArg('pageTitle','Admin - Permissions Master List');
        		
        	},
           postCreate:function(){
            	on(this.listing_bttn,'click',this.handleViewSelection.bind(this));
            	on(this.text_bttn,'click',this.handleViewSelection.bind(this));
            	on(this.add_bttn,'click',this.handleAddPermission.bind(this));
            	on(this.update_bttn,'click',this.handleSubmitChanges.bind(this));
            	on(this.update_fromtext_bttn,'click',this.handleImport.bind(this));
            	on(this.refresh_bttn,'click',this.reloadList.bind(this));
            	//this.heading.innerHTML="Permissions: Master List"
            	this.reloadList();
            	topic.subscribe('sl_permissions/masterlist/item/delete',this.handleDelete.bind(this));
            	this.views.selectChild(this.AsList);
            	//var sel = this.viewselecto;
            	//this.viewselector.placeAt(dojo.query('.mainContent').shift());

            	//sel.placeAt(dojo.query('.mainContent').shift());
            },
            reloadList:function() {
            	wapi.exec('wapi/permissions/masterlist',{'request':'list'},this.handleListLoad.bind(this));
            },
            handleListLoad:function(event) {
            	domCon.empty(this.masterlist);
            	this.permissions = event.results;
            	if(!this.permissions) return;
            	for( var i in this.permissions) {
            		var permission=this.permissions[i];
            		this.addPermissionItem(permission);
            	}
            	
            },
            handleViewSelection:function(event) {
            	switch(event.currentTarget) {
            	case this.listing_bttn.bttn:
            		this.views.selectChild(this.AsList);
            		break;
            	case this.text_bttn.bttn:
            		this.views.selectChild(this.AsText);
            		
            		if(!this.importViewed) {
            		//	on(this.update_fromtext_bttn.domNode,'click',this.handleImport.bind(this));
                    	
            		}
            		
            		this.importViewed = true;
            		break;
            	/*case this.markup_bttn.bttn:
            		this.views.selectChild(this.AsMarkup);
            		break;*/
            	}
            	
            	//this.views.selectChild(this.AsList);
            	
            },
            handleAddPermission:function(event) {
            	if(this.newPermission.value=='') return;
            	var perm = {id:null,'permission':this.newPermission.value};
            	if(perm.permission.substr(0,1)!=':') perm.permission = ':'+perm.permission;
            	if(perm.permission.substr(-1,1)!=':') perm.permission = perm.permission+':';
            	perm.permission = perm.permission.replace(/\//gi,':');
            	var item = this.addPermissionItem(perm);
            	if(!item) return;
            	this.permissions.push(item.data);
        	
            } ,
            addPermissionItem:function(newPerm) {
            	for( var i in this.permissions) {
            		var perm = this.permissions[i];
            		if(perm.id != null) continue;
            		if(perm.permission == newPerm.permission) {
            			alert(perm.permission +' already exists','Permission Exists');
            			return false;
            		}
            	}
            	
            	var item = new listItem();
            	item.placeAt(this.masterlist);
            	item.setItem(newPerm);
            	this.newPermission.value = '';
            	return item;
            	
            },
            handleDelete:function(event) {
            	if(event.data.id === null) domCon.destroy(event.target);
            	for( var item in this.permissions) {
            	
            		if(this.permissions[item].permission == event.data.permission) {
            			if(this.permissions[item].id == null) {
            				delete this.permissions[item];
            				continue;
            			}
            			this.permissions[item].isDeleted = event.item.isDeleted;
            			
            		}
            	}
            },
            handleSubmitChanges:function() {
            	
            	var permList = json.stringify(this.permissions);
            	wapi.exec('wapi/permissions/masterlist',{'request':'changeset','permissions':permList},this.handleChangeSetResponse.bind(this));
            },
            handleChangeSetResponse:function(event) {
            	this.handleListLoad(event);
            },
            handleImport:function(event) {
            	var text = this.import_text.value;
            	text = text.replace(/\//gi,':');
            	
            	wapi.exec('wapi/permissions/masterlist',{'request':'import','data':text},this.handleImportResponse.bind(this));
            	this.import_text.value = '';
            },
            handleImportResponse:function(event) {
            	this.handleListLoad(event);
            	this.views.selectChild(this.AsList);
            }

        });
});
