<p class="title">HTML for embedding map:<br/>
   <!--{$project->name}--> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href=".?do=project.edit1&id=<!--{$project->id}-->">map details</a>
</p>

<!--{capture assign=html }-->
<iframe name="cgmap" src="<!--{$worldurl}-->?<!--{$project->id}-->&embed&tools=<!--{$toolcode}-->&features=<!--{$featurecode}--><!--{$noresize}-->" style="width:<!--{$width}-->px;height:<!--{$height}-->px;" scrolling="no"></iframe>
<!--{/capture}-->

<p>You can paste this code into your page to see a map:</p>
<code style="margin-left:0.5in;"><!--{$html|escape:'htmlall'}--></code>

