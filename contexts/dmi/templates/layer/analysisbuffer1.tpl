<p class='instruction'>This utility will create a new data layer, by buffering the features in the specified layer. Points will become circles, line segments will become ellipses, and polygons will become larger. The resulting data will be stored in a new data layer; the original data will not be modified.</p>

<form action="." method="post" onSubmit="return alert('This can be a very slow operation, taking several minutes.');">
<input type="hidden" name="do" value="layer.analysisbuffer2"/>
<input type="hidden" name="id" value="<!--{$layer->id}-->"/>

<!--{* buffer options are in meters *}-->
Buffer amount:
<select name="buffer">
<option value="30.480 100 feet">100 feet</option>
<option value="100 100 meters">100 meters</option>
<option value="1000 1 kilometer">1 km</option>
<option value="1609.34 1 mile">1 mile</option>
<option value="5000 5 kilometers">5 km</option>
<option value="8046.72 5 miles">5 mile</option>
<option value="10000 10 kilometers">10 km</option>
<option value="16093.4 10 miles">10 miles</option>
<option value="25000 25 kilometers">25 km</option>
<option value="40233.6 25 miles">25 miles</option>
<option value="50000 50 kilometers">50 km</option>
<option value="80467.2 50 miles">50 miles</option>
<option value="100000 100 kilometers">100 km</option>
<option value="160934.4 100 miles">100 miles</option>
</select>

<input type="submit" name="submit" value="go" />

</form>
