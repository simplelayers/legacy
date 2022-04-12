(function($) {
	$.fn.simple_layers.client = function() {
		this.url = document.URL.split('?').shift();
		this.exec( cmd ,params, format, result_handler ) {
			params['do'] = cmd;
			$.ajax({
					url      : this.url,
					dataType : format,
					data     :  params,
					type     :  'POST',
					success  : function(json){ console.log(json); }
			});
		};
		
	};
	
}(jQuery));