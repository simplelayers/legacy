(function( $ ) {

	$.fn.layerListItem = function(data) {
		this.data = data;
		this.listing = this.filter('ul');
		this.listing = $(this.listing[0]);
		
		this.getItemTemplate = function() {
			return $("<li>item</li>").clone();
		};
		
		this.populateItem = function($template,data) {
			if(data.hasOwnProperty('label')) {
				return $template.text(data.label);
			} else {
				return $template;
			};
		};
		
		this.addItemToListing  = function() {
			$item = this.populateItem(this.getItemTemplate(),this.data);
			$item.data('item_data',this.data);
			$(this.listing).append($item);
		};
		return this;
	};
	
} (jQuery ));