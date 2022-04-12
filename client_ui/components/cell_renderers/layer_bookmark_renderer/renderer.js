define([ "dojo/_base/declare", "dojo/on", "dojo/dom", "dojo/dom-attr",
		'dojo/dom-class', "dijit/_WidgetBase", "dijit/_TemplatedMixin",
		"dijit/_WidgetsInTemplateMixin", "sl_modules/sl_URL","sl_modules/WAPI",
		"sl_modules/model/utils/AccessUtil", "sl_components/layer_icon/widget",
		"dojo/text!./renderer.tpl.html" ], function(declare, dojoOn, dom,
		domAttr, domClass, _WidgetBase, _TemplatedMixin,
		_WidgetsInTemplateMixin, sl_url, sl_wapi, accessUtil, icon, template) {
	return declare('sl_components/cellRenderers/layer_bookmark_renderer', [
			_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin ], {
		templateString : template,
		getDecorator : function() {
			return this.decorator.bind(this);
		},
		decorator : function() {
			return this.templateString;
		},
		setCellValue : function(gridData, storeData, cellWidget) {
			var item = cellWidget.cell.row.item();
			var isBookMarked = item['bookmarked'] == 'true';
			var icon = cellWidget.icon;
			icon.setValue(isBookMarked ? 'toggled_on icos_bookmarked'
					: 'icos_bookmarked');
			var wapi = sl_wapi;
			var dA = domAttr;
			var id = item['id'];
			domAttr.set(icon.domNode,'layer',id);
			
			
			
			dojoOn(icon, 'click', function() {
				if(dA.get(icon.domNode,'layer')!=id) return;
				
				isBookMarked = !isBookMarked;
				icon.setValue(isBookMarked ? 'toggled_on icos_bookmarked'
						: 'icos_bookmarked');
				event.preventDefault(true);
				wapiURL = 'wapi/layers/layer';
				action = isBookMarked ? 'bookmark_set' : 'bookmark_unset';
				
				wapi.exec(wapiURL,{id:id,action:action});
			});
			

			icon.setHREF("#");

		}

	});
});
