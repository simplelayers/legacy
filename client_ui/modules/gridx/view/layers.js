define([
	'dojo/_base/declare',
	'dojo/dom-class',
	'dojox/gesture/tap',
	'../../core/_Module',
	'sl_modules/gridx/view/layers',
	'../Puller',
	'../Bar'
], function(declare, domClass, tap, _Module, LayersView){

/*=====
	return declare(_Module, {
		// summary:
		//		module name: quickFilter.
		//		Directly show gridx/support/QuickFilter in gridx/modules/Bar at the top/right position.
		// description:
		//		This module is only for convenience. For other positions or more configurations, please use gridx/modules/Bar directly.
		//		This module depends on "bar" and "filter" modules.
	});
=====*/

	return declare(_Module, {
		name: 'layersView',

		required: ['bar', 'filter', 'puller'],

		autoApply: true,

		delay: 700,

		preload: function(){
			var t = this,
				g = t.grid,
				bar = g.bar,
				prot = QuickFilter.prototype,
				args = {
					bar: 'top',
					row: 0,
					col: 1,
					pluginClass: LayersView,
					className: 'gridxBarLayersView',
					hookPoint: this,
					hookName: 'layersView',
					autoApply: t.arg('autoApply'),
					delay: t.arg('delay'),
					menuClass: t.arg('menuClass', 'dijit.Menu'),
					menuItemClass: t.arg('menuItemClass', 'dijit.MenuItem')
				};
			if(g.touch){
				/*args.bar = 'quickFilter';
				args.priority = 0.5;
				bar.loaded.then(function(){
					g.puller.bind(bar.quickFilterNode);
				});
				*/
			}
			bar.defs.push(args);
		}
	});
});
