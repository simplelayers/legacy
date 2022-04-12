
document.write('<div style="position:absolute;left:0;top:0;margin:0;padding:0;width:<!--{$width}-->;height:<!--{$height}-->;overflow:hidden;scrollbars:no">');
document.write('	<object  classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="100%" height="100%" id="myFlashContent">');
document.write('		<param name="movie" value="<!--{$swf}-->" />');
document.write('		<!--[if !IE]>-->');
document.write('			<object type="application/x-shockwave-flash" data="<!--{$swf}-->" width="100%" height="100%">');
document.write('		<!--<![endif]-->');
document.write('			<table width="100%" height="100%" border="0" >');
document.write('			<tr><td align="center" valign="middle">');
document.write('			<div style="width:350;height:200;text-align:left">');
document.write('				<H3>This map requires a newer version of the <i>Adobe Flash Player</i> plugin to display properly.</H3>');
document.write('				<p>Please use the button below to install the latest plugin</p>');
document.write('				<div style="width:90%;text-align:center;">');
document.write('				<a href="https://www.adobe.com/go/getflashplayer"><img src="https://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a>');
document.write('				</div>');
document.write('			</div>');
document.write('			</td></tr>');
document.write('			</table>');
document.write('		<!--[if !IE]>-->');
document.write('			</object>');
document.write('		 <!--<![endif]-->');
document.write('	</object>');
document.write('</div>');

swfobject.registerObject("myFlashContent", "10.3");