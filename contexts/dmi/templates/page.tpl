<script src="lib/bootstrap5/js/bootstrap.bundle.min.js"></script>
<script src="lib/bootstrap5/js/bootstrap.min.js"></script>
<script src='<!--{$dojoURL}-->' ></script>
<script>
		
		
			// Require the module we just created
			require(["dojo/ready",
					"dojo/dom",
					"dojo/dom-construct",
					'dojo/dom-class',
					'dojo/_base/fx',
					<!--{if $page}-->'sl_pages/<!--{$context}-->/<!--{$page}-->/page',<!--{/if}-->
					'sl_modules/Pages',
					'sl_modules/Permissions',
					'sl_components/sl_nav/widget',
					'sl_modules/model/sl_PageArgs'
					], 
				 function(ready,dom,domConstruct,domClass,fx,<!--{if $page}-->component,<!--{/if}-->pages,permissions,sl_nav,slPageArgs){
				 	// ready(function(){
				 	isLegacy=<!--{if $isLegacy}-->true<!--{else}-->false<!--{/if}-->;
				 	
				 	pages.MergePageData( <!--{$pageArgs}-->);
					
					if(!isLegacy) {			
						try{
						 	var widget = new component();
							widget.placeAt(dojo.query('.mainContent').shift());
							widget.startup();
							
						} catch(error) {
					 		console.log(error);		
						}
					}
					
					
					var nav = new sl_nav();
					pages.SetPageNav(nav);
					var res = dojo.query('#nav_area').shift();
					//console.log(res);
					nav.placeAt(res);
					
					nav.startup();
					
					var pageSubnav  = (pages.GetPageArg('pageSubnav'));
					if(pageSubnav) nav.SetSubnav(pageSubnav);	
					var contentArea = dojo.query('.contentarea');
					if(contentArea.length) {
						contentArea = contentArea.shift();
					} else {
						contentArea = dojo.query('.mainContent').shift();
					}
					//console.log(contentArea);
					if(contentArea) {
						fx.fadeIn({ node: contentArea}).play();				
					} else {
						mainContent =dojo.query('.mainContent'); 
					}
					var title= 'Simple Layers';
					var page_title = pages.GetPageArg('pageTitle');
					if(page_title) {
						title+= ' - ' +page_title;
						document.title = title;
						dom.byId('pageTitle').innerHTML = page_title;
						domClass.remove(dom.byId('subnav'),'hidden');
						domClass.remove(dom.byId('nav_row'),'hidden');
					} else {
						domClass.add(dom.byId('nav_row'),'hidden');
						domClass.add(dom.byId('subnav_row'),'hidden');
						//domClass.add(dom.byId('nav_area'),'hidden');
						//domClass.add(dom.byId('subnav'),'hidden');
					}
                                        
                                        slPageArgs.pageArgs = <!--{$jsonArgs}-->;
                                        window.SL = slPageArgs;
				// });
				});		
				
		
		
</script>