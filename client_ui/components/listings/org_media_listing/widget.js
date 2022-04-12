define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        'dojo/json',
        "dojo/query",
        'dojo/dom-construct',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "sl_components/listings/org_media_listing/item",
        "dojo/text!./templates/items.tpl.html",
        "sl_modules/Pages",
        "sl_modules/WAPI"],
    function(declare,
    		on,
    		topic,
    		json,
    		query,
    		domCon,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		item,
    		template,
    		pages,
    		wapi
    		){
        return declare('listings/org_media_listing',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
           
            // Our template - important!
            templateString: template,
            baseClass: "org_media_listing gallery",
            item_obj: item,
            media:null,
            postCreate:function(){
            	this.Refresh();
            	//on(this.add_bttn,'click',this.AddItem.bind(this));
            },
            Refresh:function(event) {
            	wapi.exec('organization/organizations',{'action':'list','target':'org_media','orgId':pages.GetPageArg('orgId')},this.ModelReady.bind(this));
            },
            AddItem:function(event) {
            	            	
            },
            ItemAdded:function(response) {
            	
        	},
        	ModelReady:function(response){ 
        		
        		this.media = response.results;
            	//domCon.empty(this.domNode);
        	
            	console.log(this.media);
            	for( var media_name in this.media) {
            
            		var item = {};
            		item[media_name] = this.media[media_name];
            		var newItem =new this.item_obj({'item':item});
            		newItem.placeAt(this);
            		topic.subscribe('liistngs/org_media_listing/item_deleted/'+newItem.id,this.RemoveItem.bind(this));
            		
            		/*if(media_name.indexOf('_link')>0) {
            			domCon.place('<tr><td>'+media_name+"</td><td><a href='"+media[media_name]+"'>Test Link</a>",this.media_list);
            		} else {
            			domCon.place('<tr><td>'+media_name+"</td><td><img src='"+media[media_name]+"'></img></td></tr>",this.media_list);
            		}*/
            		
            	}
            },
        	RemoveItem:function(info) {
        		var url = info.itemValue;
        		url = url.replace('action:get','action:delete');
        		url = pages.MakeRelPath(url);
        		url+="/orgId:"+pages.GetPageArg('orgId');
        		
        		wapi.exec(url,{},this.Reload.bind(this));
        	},
            Reload:function() {
            	query('li').forEach(function(item){
            		domCon.destroy(item);
            	});
            	this.Refresh();
        		//
            }
        	

        });
});
