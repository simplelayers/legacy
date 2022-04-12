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
        'dojo/date/locale',
        'dijit/Dialog',
        'sl_modules/sl_URL',
        'sl_components/icon/widget',
        'sl_components/sl_button/widget',        
        "dojo/text!./templates/problem_item.tpl.html"],
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
    		dojoDate,
    		dijitDialog,
    		sl_url,
    		sl_icon,
    		sl_button,
    		template){
        return declare('sl_components/listings/problems',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,sl_icon,sl_button], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            baseClass : "problem_listing",
            templateString: template,
            problem:null,
            constructor:function(problem) {
            	this.problem = problem;
            	return this;
            },
            startup:function(){
            	this.inherited(arguments);
            	var latestStatus = this.problem.status.slice(-1).pop();
            	var problemDate = dojoDate.format(new Date(this.problem.updated * 1000),{datePattern:'MMMM/d/yyyy'});
            	var reporterName = this.problem.reporter.name;
            	var victemType = this.problem.victem.type;
            	var victemName = this.problem.victem.name;
            	var victem = this.problem.victem.id;
            	
            	var ref = this.id;
            	
            	
            	this.lastStatus.innerHTML = problemDate;
            	
            	on(this.notes_button,'click',this.notesClicked.bind(this));
            	
            	this.statusIco.setValue(latestStatus.status.toLowerCase());
            	var subject = this.problem.subject;
            	var firstChar = subject.substr(0,1);
            	subject = firstChar.toUpperCase()+subject.substr(1);
            	
            	this.problemTitle.innerHTML = subject+' Problem';
            	domAttr.set(this.problemRef,'href','multi_select.php?id='+this.problem.id);
            	this.problemRef.innerHTML = this.problem.id;
            	
            	this.subject_ico.setValue(this.problem.subject);
            	
            	//this.subjectLabel.innerHTML = subject;
            	this.subjectLink.innerHTML = 'n/a';
            	switch(subject) {
            		case 'Map':
            			domAttr.set(this.subjectLink,'href',sl_url.getServerPath('trimSandbox')+'?do=map.list');
            			break;
            		case 'Layer':
            			domAttr.set(this.subjectLink,'href',sl_url.getServerPath('trimSandbox')+'?do=layer.list');
            			break;
            	}
            	this.context.innerHTML = 'n/a';
            	
            	if(reporterName == "") reporterName = "Unnamed ("+this.problem.reporter.id+")";
            	if(victemName == "") victemName = "Unnamed ("+this.problem.victem.id+")";
            	
            	
            	domAttr.set(this.reporterLink,'href',sl_url.getServerPath('trimSandbox')+'?do=contact.info&id='+this.problem.reporter.id);
            	this.reporterLink.innerHTML = reporterName;
            	
            	switch(victemType) {
            		case 'Group':
            			this.victem_type_ico.setValue('group');
            			domAttr.set(this.victemLink,'href',sl_url.getServerPath('trimSandbox')+'?do=group.info&id='+victem);
            			this.victemLink.innerHTML = victemName;
            			break;
            		case 'Person':
            			this.victem_type_ico.setValue('person');
            			domAttr.set(this.victemLink,'href',sl_url.getServerPath('trimSandbox')+'?do=contact.info&id='+victem);
            			this.victemLink.innerHTML = victemName;
            			
            			break;
            		case 'Reporter':
            			this.victem_type_ico.setValue('person');
            			domAttr.set(this.victemLink,'href',sl_url.getServerPath('trimSandbox')+'?do=contact.info&id='+this.problem.reporter.id);
            			this.victemLink.innerHTML = reporterName;
            			break;
            		case 'Public':
            			this.victem_type_ico.setValue('person');
            			domAttr.set(this.victemLink,'href',sl_url.getServerPath('trimSandbox')+'?do=contact.info&id=0');
            			this.victemLink.innerHTML = 'Public';
            			break;
            	}
            	
            	
            	
            	
            	
            },
            notesClicked:function() {
            	dialog = new dijitDialog({
            	    title: "Notes",
            	    content: this.problem.notes,
            	    style: "width: 300px"
            	});
            	dialog.show();
            },
            
            postCreate:function(){
            	
            },
           
        });
});
