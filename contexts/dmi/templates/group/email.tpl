<!DOCTYPE HTML>
<html style="padding:0;margin:0;height:100%;width:100%">
<head>
<title><!--{$subject}--></title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<style>
body {
    background-color: gray;
    color: #202021;
    text-align: center;
}
.doc {
    position: relative;
    border-radius: 12px;
    background-color: #FAFAFA;
    color: #030928;
    text-align: center;
    height: 300px;
    width: 600px;
    margin: 0 auto;
    border-collapse:collapse;
    border:0px;
}
.group_name {
    background-color: #202021;
    color: #F8F8F8;
    padding: 6px;
    margin-bottom: 10px;
    text-align: center;
    font-weight:bold;
    font-size: 14pt;
    line-height: 24pt;    
}
.message {
    height: 100%;
    text-align: left;
    vertical-align:top;
    padding-top:25px;
    padding-left: 25px;
    padding-right: 25px;
    font-size: 14pt;
    line-height:14pt;
    
}
.footer {
    text-align: center;
    vertical-align:bottom;
    margin-bottom:10px;
    width: 100%;
    font-style: italic;
}

</style>
</head>
<body  style="padding:0;margin:0;height:100%;width:100%" >
<table class= 'doc'>
<tr><td align="center"><img src="<!--{$logo}-->" /></td></tr>	
<tr><td align="center" class='group_name'><!--{$group->title}--></td></tr>
<tr><td class="message" ><!--{foreach from=$message item=line }--><!--{$line}--><br/><!--{/foreach}--></tr></td>				
<tr><td height="25">&nbsp;</td>
<tr><td class='footer' align="center">powered by SimpleLayers</td></tr>
</table>	
</body>
</html>