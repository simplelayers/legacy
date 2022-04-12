<html>
<head>
  <title>Map Viewer :: <!--{$project->name|escape:'html'}--></title>
  <style type="text/css">
    body { margin:0px 0px 0px 0px; padding:0px 0px 0px 0px;
           background-color:#FFFFFF;
         }
 </style>
</head>
<body>

<script type="text/javascript">
   // if we're embedding, the window's top should be inaccessible due to browser cross-domain security
   // if we're not embedding, the window's top should be accessible since it's this very window/frame
   // so we use the $embedded flag to generate this JavaScript to compare embed-flag versus this test
   try { var win = window.parent.location.href; } catch (error) { win = null; }
   <!--{if $embedded}--> <!--{assign var=condition value='win == document.location.href'}-->
   <!--{else}--> <!--{assign var=condition value='win != document.location.href' }-->
   <!--{/if}-->
   if (<!--{$condition}-->) document.location.href = 'media/notallowed.html';
</script>

<div style="position:absolute; left:0px; top:0px; width:100%; height:100%; overflow:auto;">
<!--{$embedded_movie}--><br>
<!--{if $relatedwidth and $relatedheight}-->
<iframe frameborder="no" name="related" scrolling="auto" style="position:relative;width:<!--{$relatedwidth}-->;height:<!--{$relatedheight}-->;"/>
<!--{/if}-->
</div>

</body>
</html>
