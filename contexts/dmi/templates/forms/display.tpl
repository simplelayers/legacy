<!--{$form->name}-->
<form action="./?do=forms.submit&id=2" autocomplete="off" method="post" enctype="multipart/form-data">
<table>
<!--{foreach from=$form->fields key=name item=info}-->
	<!--{if $info->dataType <= 6}-->
		<tr><td><!--{$info->display}--></td><td>
		<!--{if $info->dataType == 1}-->
			<input type="text" name="<!--{$name}-->" <!--{if $info->min != 0}-->required="required"<!--{/if}--> <!--{if $info->max}-->maxlength="<!--{$info->max}-->"<!--{/if}--> <!--{if $info->default !== ""}-->value="<!--{$info->default}-->"<!--{/if}--> />
		<!--{elseif $info->dataType == 2}-->
			<input type="number" name="<!--{$name}-->" <!--{if $info->min != 0}-->min="<!--{$info->min}-->"<!--{/if}--> <!--{if $info->max}-->max="<!--{$info->max}-->"<!--{/if}--> <!--{if $info->default !== ""}-->value="<!--{$info->default}-->"<!--{/if}--> />
		<!--{elseif $info->dataType == 3}-->
			<input type="date" name="<!--{$name}-->" value="<!--{$info->default}-->" />
		<!--{elseif $info->dataType == 4}-->
			<input type="checkbox" name="<!--{$name}-->" <!--{if $info->default != 0}-->checked="checked"<!--{/if}--> />
		<!--{elseif $info->dataType == 5}-->
			<select name="<!--{$name}-->">
				<!--{foreach from=$info->option key=pos item=text}-->
					<option<!--{if $pos == $info->odefault}--> selected="selected"<!--{/if}--> value="<!--{$text}-->"><!--{$text}--></option>
				<!--{/foreach}-->
			</select>
		<!--{elseif $info->dataType == 6}-->
			<!--{foreach from=$info->option key=pos item=text}-->
				<input type="radio" name="<!--{$name}-->" <!--{if $info->odefault == $pos}-->checked="checked"<!--{/if}--> value="<!--{$text}-->"/> <!--{$text}--><br/>
			<!--{/foreach}-->
		<!--{/if}-->
		</td></tr>
	<!--{/if}-->
<!--{/foreach}-->
<tr><td><input type="submit" value="Submit"/></td><td><input type="reset" value="Reset"/></td></tr>
</table>
</form>