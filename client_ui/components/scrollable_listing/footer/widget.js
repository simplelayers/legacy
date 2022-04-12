define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        "dojo/dom-attr",
        "dojo/dom-class",
        "dojo/dom-construct",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "sl_components/sl_button/widget",
        "dojo/text!./templates/footer.tpl.html"
        ],
    function(declare,
    		on,
    		topic,
    		domAttr,
    		domClass,
    		domCon,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		sl_button,
    		template){
        return declare('scrollable_listing/footer',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
           
            // Our template - important!
            templateString: template,
            baseClass: "scrollable_listing footer",
            items:null,
            postCreate:function(){
            	var noUpdates = domAttr.get(this.domNode,'data-footer-no_updates');
            	if( noUpdates == '1' ) domClass.add(this.update_bttn,'no_updates');
            	on(this.refresh_bttn,'click',this.Refresh.bind(this));
            	on(this.update_bttn,'click',this.Update.bind(this));
            	
            },
            Refresh:function(event) {
            	var message = {};
            	message.src = this;
            	this.Notify('refresh',message);
            },
            Update:function(event) {
            	var message = {};
            	message.src = this;
            	this.Notify('update',message);
            },
            Notify:function(subject,message) {
            	topic.publish('scrollable_listing/'+subject,message);
            	on.emit(this.domNode,'scrollable_listing/'+subject,{bubbles:true,cancelable:true,'message':message});
            }
        });
});
