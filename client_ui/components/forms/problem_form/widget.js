define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom",
        "dojo/dom-attr",
        "dojo/dom-construct",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'dojo/dom-class',
        'dojo/dom-style',
        'sl_modules/WAPI',
        'sl_components/selectors/victem_selector/widget',
        'sl_components/sl_button/widget',    
        'sl_components/icon/widget',
        'sl_modules/sl_URL',
        "dojo/text!./templates/form.tpl.html"],
    function(declare,
    		on,
    		dom,
    		domAttr,
    		domCon,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		domClass,
    		domStyle,
    		sl_wapi,
    		sl_victems,
    		sl_button,
    		sl_icon,
    		sl_url,
    		template){
        return declare('sl_components/forms/problem_form',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,sl_victems,sl_button,sl_icon], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            baseClass : "problem_form",
            problemId:null,
            startState:null,
            reporter:null,
            reporterName:null,
            templateString: template,
            constructor:function(reporter,problemId) {
            	this.problemId = problemId;
            	this.reporter = reporter;
            	if(!this.reporter) throw "reporter not set";
            	if(!this.reporter.hasOwnProperty('id')) throw "reporter id not set";
            	if(!this.reporter.hasOwnProperty('realname')) throw "reporter realname not set";
            	
            	return this;
            },
            changeState:function(newState) {
            	this.currentState = newState;
            	
            },
            startup:function(){
            	this.inherited(arguments);
            	this.reporterName.innerHTML = this.reporter.realname;
            	this.victem_sel.startup();
            	on(this.cancel_button,'click',(function(e) {
            		
            	}).bind(this));
            	on(this.ok_button,'click',this.createProblem.bind(this));
            	
            	
            },
            postCreate:function(){
            	if(!this.problemId) {
            		this.startState = 'start';
            		
            	} else {
            		this.startState = 'laoding';
            		//ToDo: Load Problem Data
            	}
            },
            createProblem:function() {
            	this.changeState('creating');
            	var problem = {reporter:this.reporter.id};
            	problem.victem = this.victem_sel.getValue();
            	if(problem.victem.type=='Reporter')problem.victem.id=this.reporter.id;
            	problem.subject = this.subject.options[this.subject.selectedIndex].value;
            	problem.notes = this.notes.value;
            	sl_wapi.exec('reporting/problems',{data:dojo.toJson(problem),crud:'create'},this.problemCreated.bind(this));
            	
            	
            	//ToDo: Gather form info and save to server
            },
            problemCreated:function(result) {
            	this.problem = result.problem;
            	var viewMode = result.viewmode;
            	
            	switch(viewMode) {
            		case 'view':
            			this.changeState('view');
            			break;
            		case 'edit':
            			this.changeState('edit');
            			break;
            	}
            	var loc = (''+window.location).split('/');
            	loc.pop();
            	loc = loc.join('/')+'/'	;
            	window.location = loc+'problem_listing.php'
            }
        });
});
