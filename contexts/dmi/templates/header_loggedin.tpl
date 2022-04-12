<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><!--{$siteName}--></title>
	<link rel="stylesheet" type="text/css" href="styles/header.css" />
	<link rel="stylesheet" type="text/css" href="styles/weblay.css" />
	<link rel="stylesheet" type="text/css" href="styles/anonuser.css" />
	<link rel="stylesheet" type="text/css" href="styles/buttons.css" />
	
	<!--{if $print_css}--><link rel="stylesheet" type="text/css" media="print" id="dispatcher_print_styles" href="styles/<!--{$print_css}-->" /><!--{/if}-->
	
	<link rel="stylesheet" type="text/css" href="styles/style.css" />
	<link rel="stylesheet" type="text/css" href="styles/pivot.css" />
		<link rel="stylesheet" type="text/css" href="styles/jquery.checkbox.css" />
	<link rel="stylesheet" type="text/css" href="styles/jquery.editable-select.css" />
	<link rel="stylesheet" type="text/css" href="styles/jquery.tagsinput.css" />
	
	<link rel="stylesheet" type="text/css" id="listmenu-h" href="styles/toolbar.css" />
	
	<link href="media/favicon.ico?" type="image/x-icon" rel="icon" />
	<link href="media/favicon.ico?" type="image/x-icon" rel="shortcut icon" />
	
	<script type="text/javascript" src="lib/js/fsmenu.js"></script>
	<script type="text/javascript" src="lib/js/json2.js"></script>
	<!-- Load JQuery -->
	<script type="text/javascript" src="lib/js/jquery.js"></script>
	<script type="text/javascript" src="jquery-migrate-1.2.1.min.js"></script>
	<script type="text/javascript" src="lib/js/jquery.tooltip.min.js"></script>
	<script type="text/javascript" src="lib/js/jquery.dataTables.min.js"></script>	
	<script type="text/javascript" src="lib/js/jquery.jsonQueue.js"></script>
	<script type="text/javascript" src="lib/js/jquery.pivot.js"></script>
	<script type="text/javascript" src="lib/js/jquery.editable-select.pack.js"></script>
	<script type="text/javascript" src="lib/js/jquery.maskedinput-1.3.min.js"></script>
	<script type="text/javascript" src="lib/js/jquery.tagsinput.js"></script>
	<script type="text/javascript" src="lib/js/jquery.tools.min.js"></script>

	
	<!-- /Load JQuery -->
	
	<!-- Load JQueryUI -->
	<script type="text/javascript" src="lib/js/jqueryui.js"></script>
	<link rel="stylesheet" type="text/css" href="styles/jquery-ui-1.8.19.custom.css" />
	<!-- /Load JQueryUI -->
	
	<!-- Load Custom JQuery/JQueryUI Plugins -->
	<script type="text/javascript" src="media/js/jquery.dataSelector.js"></script>
	<!-- /Load Custom JQuery/JQueryUI Plugins -->
	
	<!--{if !is_null($css_url)}-->	
	<link rel="stylesheet" type="text/css" id="dispatcher_styles" href="styles/<!--{$css_url}-->" />
	<!--{/if}-->
	<!--<script type="text/javascript" src="lib/js/ejs/ejs_production.js"></script>-->
	<script type="text/javascript" src="media/js/scripts.js"></script>
	
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
		});
		function rearmToolTips(){
			if($("*[title]") != null ) return;
			$("*[title]").tooltip({showURL: false,track: true,top: -14,left: 4,delay: 0});
		}
	</script>
		 <script type="text/javascript" src="lib/js/dojo.1.9.1/dojo/dojo.js"
               data-dojo-config="async: true"></script>
	
</head>
<body >
	<div class="header main">
		<div class="logo">
		   <a href="http://www.simplelayers.com/"><img alt=''  class='whiteshadow' src="logo.php" /></a>
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
					<img alt=""  class="avatar" src="wapi/contact/icon?&amp;size=small" />
				</div>
			</div>
			<div class="actions">
				<button id="logout_button"  onclick="document.location='<!--{$baseURL}-->/?do=account.logout'" class="color button ico_button red sm" > <img alt=''  class="weblay_ico power_ico" src="media/images/empty.png" /><span class="sl_ico_label" >Log out</span> </button>
				<button id="profile_button" onclick="document.location='<!--{$baseURL}-->/?do=contact.info'" class="color button ico_button normal sm" > <img alt=''  class="weblay_ico person" src="media/images/empty.png" /><span class="sl_ico_label" >Edit Profile</span> </button>
			</div>
		</div>
		  
		<!--{/if}-->
	
	</div>

<!--{if $user }--><!--{if $loggedIn}-->
<!--{include file='toolbar.tpl'}-->
<!--{/if}--><!--{/if}--><div id="contentWrapper">
