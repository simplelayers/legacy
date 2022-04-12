define([
	'dojo/topic',
	"dojo/store/Memory",
    'sl_modules/Pages',
    'sl_modules/WAPI'
], function(topic,Memory, pages, wapi){
	return {
		REQUIRES_TEXT:'text',
		REQUIRES_TEXT:'text input',
		REQUIRES_TEXT_AREA:'text area',
		REQUIRES_INTEGER:'int',
		REQUIRES_FLOAT:'float',
		REQUIRES_BOOLEAN:'boolean',
		REQUIRES_DATE : 'date',
		REQUIRES_URL : 'url',
		REQUIRES_FILTERING_SELECT : 'filtering_select',
		
		CacheLayerAttributes:function(searchOnly) {
			params = {};
        	params.layerId = pages.GetPageArg('layerId');
        	params.features = 'meta';
        	params.features += ((searchOnly === undefined) || (searchOnly == true)) ? ',searchable' : '';
        	params.features += ',vocab';
        	console.log(params);
        	wapi.exec('wapi/layers/attributes/action:get/',params,this.ModelLoaded.bind(this));
		},
	
		ModelLoaded:function(results) {
			console.log(results.attributes);
			pages.SetPageArg('dataSource_attributes',results.attributes);
			this.ProcessVocabs();
			topic.publish('sl_model/attributes',{'model':'attributes','data':results.attributes});
			
			
		},
		ProcessVocabs:function() {
			var atts =  pages.GetPageArg('dataSource_attributes');
			for ( var attr in atts) {
				console.log(atts);
				if(atts[attr].vocab==null) continue;
				var vocab = atts[attr].vocab.split(',');
				data = [];
				for( var i in vocab) {
					data.push({'name':vocab[i].trim(),'id':vocab[i].trim()});
				}
				atts[attr].vocab = new Memory({data: data});
				
			}
			pages.SetPageArg('dataSource_attributes',atts);
		},
		GetVocab:function(attribute) {
			var atts =  pages.GetPageArg('dataSource_attributes');
			for ( var attr in atts) {
				if(attr == attribute) {
					if(atts[attr].vocab === null) return [];
					return atts[attr].vocab;
				}
			}
			return [];
		}
		
		
	};
});