<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<base href="<!--{$baseURL}-->">
<script type="text/javascript" src="<!--{$baseURL}-->lib/js/Modernizer.js"></script>
	<title id="headerTitle"><!--{$siteName}--></title>

	<!--{foreach from=$styles item=styleSheet}-->
	<link rel="stylesheet" type="text/css" href="<!--{$styleSheet}-->" />
	<!--{/foreach}-->
	
	<link rel="stylesheet"  type="text/css" href="<!--{$baseURL}-->styles/buttons.css" />
	<link rel="stylesheet"  type="text/css" href="<!--{$baseURL}-->styles/weblay.css" />
	<link rel="stylesheet" href="<!--{$baseURL}--><!--{$themeCSS}-->" />
	<link rel="stylesheet" href="<!--{$baseURL}--><!--{$DialogCSS}-->" />
	
	<link rel="stylesheet" type="text/css" href="<!--{$baseURL}-->styles/header.css" />
	<link rel="stylesheet" type="text/css" href="<!--{$baseURL}-->styles/anonuser.css" />
	<link rel="stylesheet" type="text/css" href="<!--{$baseURL}-->lib/fontawesome-free-5.13.0-web/css/all.min.css" />
	<script type='text/javascript' src="<!--{$baseURL}-->includes/dojo_config.php?&asSrc=1">
	</script>
	
	
	<!--{if $print_css}--><link rel="stylesheet" type="text/css" media="print" id="dispatcher_print_styles" href="styles/<!--{$print_css}-->" /><!--{/if}-->
	
	<!--<link rel="stylesheet" type="text/css" href="<!--{$baseURL}-->styles/style.css" />-->
	<link rel="stylesheet" type="text/css" href="<!--{$baseURL}-->styles/pivot.css" />
	<link rel="stylesheet" type="text/css" href="<!--{$baseURL}-->styles/jquery.checkbox.css" />
	<link rel="stylesheet" type="text/css" href="<!--{$baseURL}-->styles/jquery.editable-select.css" />
	<link rel="stylesheet" type="text/css" href="<!--{$baseURL}-->styles/jquery.tagsinput.css" />
	
	<link rel="stylesheet" type="text/css" id="listmenu-h" href="<!--{$baseURL}-->styles/toolbar.css" />
	
	
	<link href="<!--{$baseURL}-->media/favicon.ico?" type="image/x-icon" rel="icon" />
	<link href="<!--{$baseURL}-->media/favicon.ico?" type="image/x-icon" rel="shortcut icon" />
	
	<!--{foreach from=$scripts item=script}-->
		<script type="text/javascript" src="<!--{$script}-->"></script>		
	<!--{/foreach}-->
	
	
	<!-- /Load JQuery -->
	
	<script type="text/javascript" src="<!--{$baseURL}-->lib/js/jquery.js"></script>
	<script type="text/javascript" src="<!--{$baseURL}-->lib/js/jquery-migrate-1.2.1.min.js"></script>
	<script type="text/javascript" src="<!--{$baseURL}-->lib/js/jquery.tooltip.min.js"></script>
	
	<script type="text/javascript" src="<!--{$baseURL}-->lib/js/jquery.dataTables.min.js"></script>	
	<script type="text/javascript" src="<!--{$baseURL}-->lib/js/jquery.jsonQueue.js"></script>
	<script type="text/javascript" src="<!--{$baseURL}-->lib/js/jquery.pivot.js"></script>
	<script type="text/javascript" src="<!--{$baseURL}-->lib/js/jquery.editable-select.pack.js"></script>
	<script type="text/javascript" src="<!--{$baseURL}-->lib/js/jquery.maskedinput-1.3.min.js"></script>
	<script type="text/javascript" src="<!--{$baseURL}-->lib/js/jquery.tagsinput.js"></script>
	<script type="text/javascript" src="<!--{$baseURL}-->lib/js/jquery.tools.min.js"></script>
	
	<!-- Load JQueryUI -->
	
	<script type="text/javascript" src="<!--{$baseURL}-->lib/js/jqueryui.js"></script>
	<link rel="stylesheet" type="text/css" href="<!--{$baseURL}-->styles/jquery-ui-1.8.19.custom.css" />
	<!-- /Load JQueryUI -->
	
	<!-- Load Custom JQuery/JQueryUI Plugins -->
	<script type="text/javascript" src="<!--{$baseURL}-->media/js/jquery.dataSelector.js"></script>
	<!-- /Load Custom JQuery/JQueryUI Plugins -->
	
	<!--{if !is_null($css_url)}-->	
	<link rel="stylesheet" type="text/css" id="dispatcher_styles" href="<!--{$baseURL}-->styles/<!--{$css_url}-->" />
	<!--{/if}-->
	<!--<script type="text/javascript" src="<!--{$baseURL}-->lib/js/ejs/ejs_production.js"></script>-->
	<script type="text/javascript" src="<!--{$baseURL}-->media/js/scripts.js"></script>
	
	<script type="text/javascript"> 
	<!--{if $loggedIn }-->
		<!--{$layerTypeEnum}-->
		<!--{$geomTypeEnum}-->
	<!--{/if}-->
		$(function(){
			rearmToolTips();
			$( document ).bind("click", function( e ) {				
				$('.displayNotify').css('display','none');
				$('.displayMessages').css('display','none');
				$('.displayHelp').css('display','none');
			});
			if($('#subnav').hasClass('hidden')) {
			} else{
				$('#nav_row').removeClass('hidden');
			}
		});
		function rearmToolTips(){
			if($("*[title]") != null ) return;
			$("*[title]").tooltip({showURL: false,track: true,top: -14,left: 4,delay: 0});
		}
	</script>
                <style>
                     .flex-row {
                        flex-flow: row;
                        display: flex;
                        flex-direction: row;
                        flex-wrap: nowrap;
                        align-content: center;
                        justify-content: flex-start;
                        align-items: center;
                        margin-bottom: .33em;
                        gap: 1em;
                    }
                    .grow {
                        flex-grow: 2;
                        align-items: center;
                        display: flex;
                        flex-direction: row;
                        flex-wrap: nowrap;
                        align-content: center;
                        justify-content: flex-end;;
                    }
                    static {
                        flex-grow:1;
                    }
                    .perm-table {
                        width: 100%;
                    }
                    .list-header {
                        width:100%;        
                    }
                    .perm-table label.inline {
                        margin-right:1em;
                    }
                    .permissions,
                    .reporting {
                        white-space:nowrap !important;
                    }

                    .contact-value.reporting {
                        width: 22em;
                    }
                    .contact-value.permission {
                        width:18em
                    }
                    .contact-value.option-set label{
                        font-weight: unset;
                    }
                    .perm-set .option-radio,
                    .contact-value .option-radio {
                        margin-left: .5rem;
                        margin-right:.25rem;
                    }

                    .contact-value .option-radio.first {
                        margin-left:0px;
                    }
                    .table-spacer {
                        height: 2em;
                        width: 100%;
                    }
                    #navRow {
                        padding: 3px 10px;
                        background: #f8f8f8;
                        white-space: nowrap !important;
                        margin-left: -.25em;
                        margin-right: -.25em;
                    }
                    #navRow .filterNav {
                            display: flex;
                            float: none !important;
                            flex-grow: 1;
                            justify-content: flex-end;
                    }
                    .mr-half {
                        margin-right: .5em;
                    }
    </style>
	
</head>
<body class='tundra <!--{$context}-->' >
<table class='pageLayout'>
<tbody>
<tr id='page_layout_header'><td>
	<div class="header main">
		<div class="logo">
		   <a href="http://www.simplelayers.com/"><img alt=''  class='whiteshadow' src="<!--{$baseURL}-->logo.php" /></a>
		</div>
		<!--{if $loggedIn }-->
		<div class="avatarea" >
			<div class="user_display" >
				<div class="user_info">
					<div class="logged_in_heading" >Logged in as:</div>
					<div class="username"><!--{$fullname}--></div>
					<div id='seatname' class="seat_label"><!--{$seatname}--></div>		
				</div>
				<div class="avatar_container">
					<img alt=""  class="avatar" src="<!--{$baseURL}-->wapi/contact/icon?&amp;size=small" />
				</div>
			</div>
			<div class="actions">
				<button id="logout_button"  onclick="document.location='<!--{$baseURL}-->/?do=account.logout'" class="color button ico_button red sm" > <img alt=''  class="weblay_ico power_ico" src="<!--{$baseURL}-->media/images/empty.png" /><span class="sl_ico_label" >Log out</span> </button>
				<button id="profile_button" onclick="document.location='<!--{$baseURL}-->/?do=contact.info'" class="color button ico_button normal sm" > <img alt=''  class="weblay_ico person" src="<!--{$baseURL}-->media/images/empty.png" /><span class="sl_ico_label" >Edit Profile</span> </button>
			</div>
		</div>
		  
		<!--{/if}-->	
	</div>
</td></tr>
<tr id='nav_row' class='hidden'><td >
<div id='nav_area' ></div>
</td></tr>
<tr id='subnav_row'>
	<td id='subnav' class='hidden'>
		<div id="pageTitle" class="title"></div>
		
	<div id="navRow" class='hidden flex-row'>
		<div class="static input-group" id="selector"></div>
		<!--<div class="clear"></div>-->
	</div>
</td></tr>
<tr id='page_content'><td >
<!--{if !$isModule}--><div class='contentarea'><!--{/if}-->
