define(["dojo/_base/declare",
        "dojo/dom",
        "dojo/dom-attr",
        'dojo/dom-class',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "sl_modules/sl_URL",
        "dojo/text!./templates/icon.tpl.html"
        ],
    function(declare,
    		dom,
    		domAttr,
    		domClass,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		sl_url,
    		template){
        return declare('sl_components/icon',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
        	baseClass : "sl_icon",
            problemId:null,
            startState:null,
            reporter:null,
            reporterName:null,
            templateString: template,
            iconMap:{
            	permission:{
            		owner: 'weblay_ico:lock_open',
            		read: 'weblay_ico:eye_open',
            		edit: 'weblay_ico:pencil',
            		none: 'weblay_ico:lock',
            		lpa: 'weblay_ico:badge'},
            	status: {
            		reported:'weblay_ico:box_in',
            		pending:'weblay_ico:box_in',
            		open:'weblay_ico:tools',
            		fixed:'weblay_ico:check',
            		complete:'weblay_ico:check',
            		nonproblem:'weblay_ico:univ_no'
            	},
            	people: {
            		person:'weblay_ico:person',
            		group:'weblay_ico:people'
            	},
            	components: {
            		map:'weblay_ico:map',
            		layer:'weblay_ico:stack',
            		system:'weblay_ico:system',
            		sharing:'weblay_ico:connections'
            	}
            	
            },
            postCreate:function(){ 
            	this.iconType = domAttr.get(this.domNode,'data-icon-type');
            	domAttr.set(this.domNode,'src',sl_url.getEmptyImgURL());
            	var icon = domAttr.get(this.domNode,'data-icon');
            	if(icon) this.setValue(icon);
            	
            },
            setValue:function(val) {
            	if(typeof(val) == 'function') {
            		return this.inherited(arguments);
            	}
            	if(!this.iconType) throw new Error('icon type not set');
            	if(!this.iconMap[this.iconType]) throw new Error('unrecognized icon type');
            	if(!this.iconMap[this.iconType].hasOwnProperty(val)) throw new Error('unrecognized value for icon type: '+this.iconType+'.'+val);
            	ico_info = this.iconMap[this.iconType][val].split(':');
            	domClass.add(this.domNode,ico_info[0]);
            	domClass.add(this.domNode,ico_info[1]);
            },
            
            ready:function(){
            	parser.parse();
            }

        });
});
