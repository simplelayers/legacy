/***
   Miscellaneous JavaScript components used here and there.
***/


	function rearmToolTips(){
		$("*[title]").tooltip({showURL: false,track: true,top: -14,left: 4,delay: 0});
	}


/*
 * TODO: Find all places where this is used and use jquery instead.
 * This is used in HTML to toggle the display of a DIV element.
 * @param string divname The name of the HTML DIV to toggle.
 */
function toggleDiv(divname) {
   var target = document.getElementById(divname);
   var newviz = target.style.display=='none' ? 'block' : 'none';
   target.style.display = newviz;
}


/*
 * TODO: Determine if there is a Jquery tool for getting screen dimentsions browser-independently.
 * Open a popup window to the viewer, with the selected project loaded.
 * This tests for their Flash version, sets the screen size, toggles debugging features, etc.
 * @param integer projectid The unique ID# of the project to be viewed.
 */
function openViewer(projectid,event) {
		if(event === undefined) {
			event = {ctrlKey:false};
		}

   // figure up the URL, and the size of the window
   // also, if this is running under a ~ URL, enable the Location bar for debugging purposes
   //var url = '.?' + projectid;
   //var url = "/~art/cg3beta_debug/viewer/?do=start&application=CG3Viewer&project=" + projectid;
	 
  			 
   var w = 1000;
   var h = 750;
   let url ='';
   var location = (window.location.href.indexOf('~') == -1) ? 'no' : 'yes';
   var environment = window.location.href.match(/https:\/\/([^\.]+).simplelayers/)[1];
   var appURL = '';
   var dmi_viewer = 'sl_viewer';
   if(event.ctrlKey === true) {
       dmi_viewer = 'viewer';
   }
   switch(environment) {
       case 'dev':
          appURL = 'https://apps-dev.simplelayers.com/dmi/sl-viewer/'+projectid;
        break;
       case 'staging':
           appURL = 'https://apps-staging.simplelayers.com/dmi/sl-viewer/'+projectid;
           break;
       case 'secure':
           appURL = 'https://apps.simplelayers.com/dmi/sl-viewer/'+projectid;
           break;
   }
   window.open(appURL,'_blank');
   if(!!event) {
  	 console.log(event);
   }
}

function monthStrToInt(str){
	switch(str){
		case 'Jan.':
		  return 1; break;
		case 'Feb.':
		  return 2; break;
		case 'Mar.':
		  return 3; break;
		case 'Apr.':
		  return 4; break;
		case 'May.':
		  return 5; break;
		case 'Jun.':
		  return 6; break;
		case 'Jul.':
		  return 7; break;
		case 'Aug.':
		  return 8; break;
		case 'Sep.':
		  return 9; break;
		case 'Oct.':
		  return 10; break;
		case 'Nov.':
		  return 11; break;
		case 'Dec.':
		  return 12; break;
	}
}
function daysInMonthStr(month, year) {
    return daysInMonth(monthStrToInt(month), year);
}
function daysInMonth(month, year) {
    return new Date(year, month, 0).getDate();
}
function setOptionsForMonth(select, month, year){
	select.clearList();
	select.addOption('*');
	if(month != '*'){
		var days = daysInMonthStr(month, year);
		for (var i=1; i<=days; i++){
			select.addOption(i);
		}
		select.text.val('1');
	}else{
		select.text.val('*');
	}
}