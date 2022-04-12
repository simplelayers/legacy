(function( $, window, document, undefined ){
	$.fn.jsonQueue = function( options ) {
		var settings = $.extend( {}, options); 
		return this.each(function() {
			var data = $(this).data('jsonQueue', {
				queue : false,
				current : false,
				url : '',
				currentUrl : '',
				success : function(){},
				currentSuccess : function(){},
				replace : function(){},
				currentReplace : function(){}
			});
		});
	};
	$.fn.nextQueue = function(url, success, replace, context) {
		return this.each(function() {
			var data = $(this).data('jsonQueue');
			function doNext(){
				$.ajax({
				  url: data.currentUrl,
				  dataType: 'json',
				  success: function(jsonData){
						if(data.queue){
							var temp = data.currentReplace;
							data.currentReplace = data.replace;
							data.queue = false;
							temp(jsonData);
							doNext();
						}else{
							data.currentSuccess(jsonData, data.context);
							data.current = false;
						}
					}
				});
			}
			data.currentUrl = url;
			data.context = context;
			data.currentSuccess = success;
			if((data.current == undefined) || (data.current === false)){
				data.current = true;
				data.currentReplace = replace;
				doNext();
			}else{
				data.queue = true;
				data.replace = replace;
			}
		});
	};
})( jQuery );