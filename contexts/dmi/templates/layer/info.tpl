
<p class="title">Layer information:</p>
<!--{if $thumbnail}-->
<div style="float:right;">
  <!--{if $layer->type!=LayerTypes::COLLECTION}--><img class="thumbnail" src=".?do=download.imagethumbnail&id=<!--{$layer->id}-->" /><!--{/if}-->
  <br/>
  <!--{$layer->getExtentPretty()}-->
</div>
<!--{/if}-->


<p>
<b>Description:</b><br/>
<!--{$layer->description|escape:'html'|nl2br}-->
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
