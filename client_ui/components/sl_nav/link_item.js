define(["dojo/_base/declare",
        "dojo/parser", 
        "dojo/on",
        "dojo/dom-construct",
        "dojo/dom-class",
        "dojo/dom-attr",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'sl_modules/Pages',
        'sl_modules/sl_URL',
        "dojo/text!./templates/link_item.tpl.html",
        "dojo/text!./templates/label_item.tpl.html"
        ],
    function(declare,
    		parser,
    		on,
    		domCon,
    		domClass,
    		domAttr,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		pages,
    		sl_url,
    		template,
    		labelTemplate){
        return declare('sl_components/sl_nav/link_item',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            baseClass : "link_item",
            templateString: template,
            label:'',
            sl_ref:'',
            valid:true,
            confirm:null,
            tooltip:null,
            constructor:function(args) {
            	this.sl_ref = args.ref;
                
            	this.templateString = (this.sl_ref === null) ? labelTemplate : template; 
            	this.label = args.title;
            	
            	if(args.hasOwnProperty('tooltip')) {
            		this.tooltip = args.tooltip;            		
            	} else {
            		this.tooltip = this.label;
            	}
            	if(this.sl_ref === null) return;	
            	if(this.sl_ref.substr(0,11)=='javascript:') {
            		var matches = this.sl_ref.match(/\[[^\]]+/g);
                	if(matches.length) {
    	            	for( var m in matches) {
    	            		var matchArg = matches[m].substr(1);
    	        			this.sl_ref = this.sl_ref.replace('['+matchArg+']',pages.GetPageArg(matchArg));
    	        		}
                	}
                	
            		return;
        		}
        	
            	var isLegacy = (this.sl_ref.substr(0,1)=='?');
            	if(args.confirm) this.confirm = args.confirm;
            	this.sl_ref = (isLegacy) ? sl_url.getServerPath() + this.sl_ref : sl_url.getServerPath()+args.ref;
            	
            	if(!args.fields) {
            		var matches = this.sl_ref.match(/\[[^\]]+/g);
            		for( var m in matches) {
            			var matchArg = matches[m].substr(1);
            			this.sl_ref = this.sl_ref.replace('['+matchArg+']',pages.GetPageArg(matchArg));
            		}
            	}
            	
            	
            	for( var f in args.fields) {
        			var field = args.fields[f];
        			if(field.type=='pageArg') {
        				if(pages.GetPageArg(field.value)===null) {
        					this.valid =false;
        					//break;
        				}
        			}
        			if(isLegacy){
        				if(field.type=='pageArg') {
        					
        					this.sl_ref = this.sl_ref+'&'+field.name+'='+pages.GetPageArg(field.value);	
        				} else {
        					this.sl_ref = this.sl_ref+='&'+field.name+'='+field.value;
        				}
        			} else {
        				if(field.type=='pageArg') {
        					this.sl_ref+=field.name+':'+pages.GetPageArg(field.value)+'/';	
        				} else {
        					this.sl_ref+=field.name+':'+field.value+'/';
        				}
        			}
        		}
            	
            },
            postCreate:function(){
            	this.hyperlink.innerHTML = this.label;
            	if(this.confirm) {
            		var confirm = this.confirm;
            		
            		var confirmArgs = confirm.match(/\[[^\]]+/g);
            		
            		for(var i in confirmArgs) {
            			arg = confirmArgs[i].substr(1);
            			if(pages.GetPageArg(arg)) {
            				confirm = confirm.replace('['+arg+']', pages.GetPageArg(arg));
            			}
            		}
            		this.confirm = confirm;
            		on(this.hyperlink,'click',this.HandleLinkClick.bind(this));
            		domClass.add(this.hyperlink,'confirm_req');
            	} else {
            		
            		domAttr.set(this.hyperlink,'href',this.sl_ref);
            	}
            	if(!(this.tooltip===null)) domAttr.set(this.hyperlink,'title',this.tooltip);
            	//on(this.link,'click',this.HandleLinkClick);
            },
            HandleLinkClick:function(event) {
                event.preventDefault();
            	var answer = confirm(this.confirm);
        		if (answer){
        			//alert('would navigate to:' +this.sl_ref);
                                this.sl_ref = this.sl_ref.replace('//wapi/','/wapi/');                                
        			pages.GoToURL(this.sl_ref);
        			
        		}
            }
            
         });
});
