define(["dojo/_base/declare",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin",
        "dijit/_WidgetsInTemplateMixin",
        "dojo/dom-construct",
        "dojo/dom-attr",
        "dojo/on",
        "dojo/string",
        "dojo/_base/lang",
        'dojo/dom-style',
        'dojo/topic',
        "sl_components/sl_button/widget",
        "sl_modules/sl_URL",
        "dojo/text!./templates/url_params.tpl.html", 
        "dojo/text!./templates/url_params_item.tpl.html",
        "dojo/text!./templates/url_params_editableItem.tpl.html"
        ],
		
    function(declare, 
    		_WidgetBase, 
    		_TemplatedMixin, 
    		_WidgetsInTemplateMixin,
    		domConstruct,
    		domAttr,
    		on,
    		string,
    		lang,
    		domStyle,
    		dojoTopic,
    		sl_button,
    		sl_URL,
    		template, 
        	item_template,
        	editableItem_template){
        return declare([_WidgetBase, _TemplatedMixin,_WidgetsInTemplateMixin,sl_button], {
        	item_template:item_template,
        	editableItem_template:editableItem_template,
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            url: "",
            editable:false,
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
           
            // Our template - important!
            templateString: template,
            current_state: 'view',
            start_width:0,
            // A class to be applied to the root node in our template
            baseClass: "url_params",
            _urlParams:{},
            postCreate: function() {
            	
            	this.setState(this.current_state);
            	if(this.editable) {
            		
            		on( this.urlParams_editSave,'click', lang.hitch(this,this.switchState));
            	} else {
            		
            		this.urlParams_editSave.hide();
            	}
            },
            startup:function() {
            	this.inherited(arguments);
            	
            	
            	
            },
            setState:function( whichState ) {
            	var oldState = this.current_state;
            	switch(whichState) {
            	case 'edit':
            		this.urlParams_editSave.setLabel('Save');
            		this.urlParams_editSave.setColor('blue');
            		this.urlParams_editSave.setIcon('save_ico');
                	break;
            	case 'view':
            		this.urlParams_editSave.setLabel('Edit')
            		this.urlParams_editSave.setColor('normal');
            		this.urlParams_editSave.setIcon('pencil');
            		break;
            	}
            	this.current_state = whichState;
            	this.setURL(this.url);
            	dojoTopic.publish('url_params/state/changed',{from:oldState,to:this.current_state,url:this.url,target:this.urlParams,url_params:this})
            },
            switchState:function() {
            	switch(this.current_state) {
            	case 'edit':
            		this.url = this.getURL();
            		this.setState('view');
            		break;
            	case 'view':
            		this.setState('edit');
            		break;
            	}
            	
            },
            
        	setURL: function(newURL) {
        		
        		this._urlParams = sl_URL.getURLParams(newURL);
        		
        		domConstruct.empty(this.urlParams);
        		//domConstruct.empty(this.urlParams);
        		var node =  domConstruct.toDom( string.substitute(this.item_template, {paramName:"Params",paramValue:"Param Value"}) );
    			
    			this.urlParams.appendChild(node);
    			
        		for(var i=0; i < this._urlParams.length;i++) {
        			
        			var whichTemplate = (this.current_state == 'edit' ) ? this.editableItem_template :this.item_template;  
        			var node =  domConstruct.toDom( string.substitute(whichTemplate, this._urlParams[i]));;
        			
        			this.urlParams.appendChild(node);
        			//var widget = new url_item();
        			
        			//widget.setItem(params[i])
        			//widget.placeAt(this.urlParams);
        		}
        		//domStyle.set(this.urlParams,'width',this.start_width);
        		
        	},
            getWidth:function() {
            	return	Math.round(domStyle.get(this.urlParams,'width'));
            },
            getURL:function() {
            	if( this.current_state=='view') {
            		return this.url;
            	}
            	
        		var nodes=dojo.query('.sl_url_val_input');
        		var url = sl_URL.getURLBase(this.url);
        		url+='?';
        		nodes.forEach(function(node, index, nodelist){
        		    // for each node in the array returned by dojo.query,
        		    // execute the following code
        		   var param = domAttr.get(node,'name');
        		   var val = domAttr.get(node,'value');
        		   url+='&'+param+'='+encodeURIComponent(lang.trim(val));            		   
        		},url);
        		return url;
            },
            getParams:function() {
            	return this._urlParams;
            }
        });
});
