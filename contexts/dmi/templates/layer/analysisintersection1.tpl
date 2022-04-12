<p class='instruction'>
The layer <!--{$layer->name}--> will be the starting data set.
<p class='instruction'>
The layer you select below will act as a &quot;mask&quot;.
<p class='instruction'>The new layer created by this intersection will be data from <!--{$layer->name}--> that fits in the range of the &quot;mask&quot;.


<form action="." method="post">
<input type="hidden" name="do" value="layer.analysisintersection2"/>
<input type="hidden" name="layer1id" value="<!--{$layer->id}-->"/>
Operation Type: <input type="radio" name="operationType" value="1" checked> Intersection
<input type="radio" name="operationType" value="2"> Non-Intersection
<br/>
Mask layer: <!--{html_options name=layer2id options=$layers}-->
<input type="submit" name="submit" value="Intersect Layers" />

</form>

<p class="alert wrapped">
Note: With a large amount of data this operation might take some time to complete.
</p>

