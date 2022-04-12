/**
 * 
 */
function SetupSimpleLayers( pageArgs  ) {
require([ "dojo/_base/declare",
         'sl_modules/Pages.js',
         'sl_modules/Pages',
			'sl_modules/Permissions',
			'sl_components/sl_nav/widget',], function(declare,pages,permissions,sl_nav) {
		declare('SimpleLayers', {
			pageArgs:pageArgs
		});
});
}
