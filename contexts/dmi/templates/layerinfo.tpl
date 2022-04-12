<p class="title">Layer information:<br/>
  <!--{$layer->name|escape:'htmlall'}--> (<!--{$layer->geomtypestring}-->)
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <!--{$ownerlink}-->
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <!--{$bookmarklink}-->
</p>


<!--{if $thumbnail}-->
<div style="float:right;">
  <img class="thumbnail" src=".?do=imagethumbnail&id=<!--{$layer->id}-->" />
  <br/>
  <!--{$layer->getExtentPretty()}-->
</div>
<!--{/if}-->


<p>
<b>Description:</b><br/>
<!--{$layer->description|nl2br}-->
</p>

<!--{if $recordcount}-->
<p>There are <!--{$recordcount}--> features in this layer.</p>
<!--{/if}-->

<p>
<b>Tags:</b><br/>
<!--{$taglinks}-->
</p>


<script type="text/javascript">
	function reminder() {
	   alert('It will take a moment to prepare your download.\nPlease be patient.');
	}
</script>
