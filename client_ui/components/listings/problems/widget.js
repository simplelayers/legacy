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
        'sl_components/listings/problems/problem',
        'sl_components/selectors/victem_selector/widget',
        'sl_components/sl_button/widget',        
        "dojo/text!./templates/problem_listing.tpl.html"],
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
    		sl_problem,
    		sl_victems,
    		sl_button,
    		template){
        return declare('sl_components/listings/problems',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,sl_problem,sl_victems,sl_button], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            baseClass : "problem_listing",
            templateString: template,
            problems:null,
            constructor:function() {
            	return this;
            },
            startup:function(){
            	this.inherited(arguments);
            },
            postCreate:function(){
            	var by = 'mine';
            	sl_wapi.exec('reporting/problems',{crud:'retrieve','by':by},this.problemsLoaded.bind(this));
            },
            problemsLoaded:function(result) {
            	this.problems = result.results;
            	domCon.empty(this.domNode);
            	for(var p in this.problems) {
            		problemInfo = this.problems[p];
            		var problem = new sl_problem(problemInfo);
            		problem.placeAt(this.domNode);
            		problem.startup();            		
            	}
            }
        });
});
