<!DOCTYPE html>
<html>
<head>
<!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js">
</script>

<![endif]-->
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
    width: 600px;
    margin: 0 auto;
    border-collapse:collapse;
    border:0px;
}
h1 {
    background-color: #202021;
    color: #F8F8F8;
    padding: 6px;
    margin-bottom: 10px;
    text-align: center;
    font-weight:bold;
    font-size: 14pt;
    line-height: 24pt;    
}
main {
    height: 100%;
    text-align: left;
    vertical-align:top;
    padding-top:25px;
    padding-left: 25px;
    padding-right: 25px;
    font-size: 14pt;
    line-height:14pt;
    
}

.sender_name {
	
	font-style: italic;
	vertical-align:middle;
	
}

footer {
    vertical-align:bottom;
    padding-top: 30px;
    padding-bottom:15px;
    width: 100%;
    font-size: 12pt;
    font-style: italic;
}

.main {
	padding: 20px;
	font-size: 14pt;
	line-height: 14pt;
	
}

.main.message {
	text-align:left;
	font-size: 14pt;
	line-height: 18pt;
}

table .centered {
	margin-left: auto;margin-right: auto;
}

table tr {
	min-height: 28pt;
}


table tr td {
	padding-bottom: 10px;	
	text-align:left;
	vertical-align:middle;
	
}

table tr th {
	text-align: right;
	padding-bottom: 10px;	
	padding-right: 10px;
	vertical-align:middle;
}


</style>
</head>
<body align="center">
<section class='doc'>
<header>
	<section><img  src="<!--{$logo}-->"></img></section>
	<h1>Mail Response</h1>
</header>

<!--{if $need_password==true}-->
<section class="main">
<form action='<!--{$form_action}-->' method="post">
<table class="centered">
<tr><td colspan="2">In order to proces your response, the password for the following user is required:</td></tr>
<tr><th>User:</td><td class='sender_name'><!--{$actor->realname}--> - (<!--{$actor->username}-->)</td></tr>
<tr><th>Password:</td><td><input type='password' name='password' ></input></td></tr>
<tr><th>&nbsp;</td><td ><button>Submit</button></td></tr>
</table>
</section>
<!--{else}-->
<section class="main message">
<!--{$message}-->
</table>
</section>
<!--{/if}-->
<footer >powered by SimpleLayers</footer>
</section>
</body>
</html>