<a href=".?do=default.colorscheme&id=<!--{$layer->id}-->">cancel editing</a>
<form action="." method="post">
<input type="hidden" name="do" value="default.colorschemesymbol2"/>
<input type="hidden" name="id" value="<!--{$layer->id}-->"/>

<p>This utility will set the symbol for all of the existing classifications to whatever you select.</p>

<p>
Set all classes to this symbol: 
<!--{html_options name=symbol options=$symbols}-->
<!--{html_options name=symbolsize options=$symbolsizes selected=$smarty.const.SYMBOLSIZE_MEDIUM}-->
<br/>
</p>


<p><input type="submit" name="submit" value="set symbol for all classes" style="width:3in;"/></p>
</form>
