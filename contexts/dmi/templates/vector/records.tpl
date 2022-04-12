<!--{$subnav}-->
<p>There are <!--{$matchingrecords}--> features <!--{if $criteria1}-->matching your criteria<!--{else}-->in this layer<!--{/if}-->.</p>

<!-- this form is to set the records per page -->
<form action="." method="post" name="perpage">
<input type="hidden" name="do" value="vector.records"/>
<input type="hidden" name="id" value="<!--{$layer->id}-->"/>
Show <!--{html_options name=limit values=$perpagechoices output=$perpagechoices selected=$limit}--> records per page. <input type="submit" name="submit" value="go"/>
</form>


<!-- this form is for the filters -->
<form action="." method="post" name="filter">
<input type='hidden' name='offset' value="<!--{$offset}-->"/>
<input type="hidden" name="do" value="vector.records"/>
<input type="hidden" name="id" value="<!--{$layer->id}-->"/>
<input type='hidden' name='limit' value="<!--{$limit}-->"/>
<input type='hidden' name='sort' value="<!--{$sort}-->"/>
<input type='hidden' name='desc' value="<!--{$desc}-->"/>

<script type="text/javascript">

function sortedFilter(orderBy) {
	var form = document.filter;
	if(form.elements['sort'].value == orderBy) {
		
		form.elements['desc'].value = (form.elements['desc'].value == '1') ? '0' : '1';
		
	} else {
		form.elements['desc'].value = '0';
	}
	form.elements['sort'].value = orderBy;
	form.elements['offset'].value='0';
	form.submit.click();
}

function nextPage() {
	var form = document.filter;
	form.elements['offset'].value = '<!--{$offset_next}-->';
	form.submit.click();	
}

function prevPage() {
	document.filter.elements['offset'].value = "<!--{$offset_prev}-->";
	document.filter.submit.click();
}

function submitenter(myfield,event) {
   if ((event.which && event.which == 13) || (event.keyCode && event.keyCode == 13)) {
      document.forms['filter'].elements['submit'].click();
      return false;
   }
   return true;
}
function resetFilter(id) {
   var form = document.forms['filter'];
   form.elements['filter'+id+'_criteria1'].selectedIndex = 0;
   form.elements['filter'+id+'_criteria2'].selectedIndex = 0;
   form.elements['filter'+id+'_criteria3'].value = '';
   return false;
}
function togglesearches(howmany) {
   for (i=howmany-1; i>=0; i--) {
      var currentblock = document.getElementById('filter'+i);
      var currentselector  = document.getElementById('filter'+(i)+'_criteria2');
      var previousselector = document.getElementById('filter'+(i-1)+'_criteria2');
      if (i==0 || previousselector.selectedIndex || currentselector.selectedIndex) currentblock.style.display = 'block';
      else currentblock.style.display = 'none';
   }
}
</script>
<!--{section name=i loop=$filternumbers}-->
<!--{assign var=number value=$filternumbers[i]}-->
<!--{assign var=value1 value=$criteria1.$number}-->
<!--{assign var=value2 value=$criteria2.$number}-->
<!--{assign var=value3 value=$criteria3.$number}-->
<div id="filter<!--{$number}-->" style="display:none;">
Filter: <select name="filter<!--{$number}-->_criteria1"><!--{html_options values=$criteria1_list output=$criteria1_list selected=$value1}--></select>
        <select id="filter<!--{$number}-->_criteria2" name="filter<!--{$number}-->_criteria2" onChange="return togglesearches(<!--{$howmanyfilters}-->);"><!--{html_options options=$criteria2_list selected=$value2}--></select>
        <input type="text" name="filter<!--{$number}-->_criteria3" value="<!--{$value3|escape:'htmlall'}-->" onKeyPress="return submitenter(this,event);"/>
        <input type="submit" value="clear" onClick="return resetFilter(<!--{$number}-->);"/> <br/>

</div>
<!--{/section}-->
<script type="text/javascript">togglesearches(<!--{$howmanyfilters}-->);</script>
<select name="andor" style="width:3.0in;margin-left:0.5in;"><!--{html_options options=$andor_options selected=$andor}--></select><br/>
<br/>
<input type="submit" name="submit" value="filter" style="width:1.0in;margin-left:0.5in;" />
</form>


<!-- the prev and next links -->
<div class="alert" style="width:10in;text-align:center;">
<!--{if $offset_prev!==''}-->
<a href="javascript:prevPage()" style="float:left;">Prev Page</a>
<!--{/if}-->
<!--{if $offset_next!==''}-->
<a href="javascript:nextPage()" style="float:right;">Next Page</a>
<!--{/if}-->
Page <!--{$pagenumber}--> of <!--{$totalpages}-->
<br/>
</div>


<!-- this third form encloses the table itself, and is for bulk update/delete -->
<form action="." method="post" onSsubmit="check_form(this);" name="updatedelete">
<input type="hidden" name="do" value="vector.recordupdate"/>
<input type="hidden" name="id" value="<!--{$layer->id}-->"/>

<table class="bordered" style="width:10in;">
<tr>
<!--{if $editaccess}-->
    <th style="width:0.2in;text-align:center;"><input type="checkbox" class="nopad" onClick="toggleAll(this.checked);"/></th>
    <th style="width:0.2in;">Edit</th>
<!--{elseif $viewaccess}-->
    <th style="width:0.2in;">View</th>
<!--{/if}-->
<!--{foreach from=$attribs key=attrib item=datatype}-->
  <th>
    <a href="javascript:sortedFilter('<!--{$attrib}-->')"><!--{$attrib}--></a>
    <br/>
    <!--{$datatype}-->
  </th>
<!--{/foreach}-->
</tr>

<!--{section loop=$records name=i }-->
<!--{cycle values="color,altcolor" assign=class}-->
  <tr>
  <!--{if $editaccess}-->
    <td class="<!--{$class}-->" style="width:0.2in;text-align:center;"><input type="checkbox" class="nopad" name="gids[]" value="<!--{$records[i].gid}-->"/></td>
    <td class="<!--{$class}-->" style="width:0.2in;"><a href=".?do=vector.recordedit1&id=<!--{$layer->id}-->&gid=<!--{$records[i].gid}-->" target='record'>edit</a></td>
  <!--{elseif $viewaccess}-->
    <td class="<!--{$class}-->" style="width:0.2in;"><a href=".?do=vector.recordedit1&id=<!--{$layer->id}-->&gid=<!--{$records[i].gid}-->">view</a></td>
  <!--{/if}-->
  <!--{foreach from=$attribs key=attrib item=datatype}-->
  <!--{if $datatype== "url"}-->
  	<!--{if $records[i].$attrib}-->
   		<td class="<!--{$class}-->"><a href="<!--{$records[i].$attrib|url_href|escape:htmlall}-->" target="_blank"><!--{$records[i].$attrib|url_name|truncate:50:"..."|escape:htmlall}--></a></td>
 	<!--{else}-->
 		<td class="<!--{$class}-->">&nbsp;</td>
 	<!--{/if}-->
 <!--{else}-->
  	  <td class="<!--{$class}-->"><!--{$records[i].$attrib|truncate:50:"..."|escape:htmlall}--> &nbsp;</td>
  <!--{/if}-->
  
  <!--{/foreach}-->
  </tr>
<!--{/section}-->

</table>

<!--{if $editaccess}-->
<p><input type="submit" name="submit" value="update selected records" style="width:2in;" onClick="document.forms['updatedelete'].elements['do'].value='vector.recordupdate';return check_form(document.forms['updatedelete']);"/> Set <!--{html_options name=column values=$criteria1_list output=$criteria1_list selected=$criteria1}--> to <input type="text" name="value" style="width:2in;"></p>
<p><input type="submit" name="submit" value="delete selected records" style="width:2in;" onClick="document.forms['updatedelete'].elements['do'].value='vector.recorddelete';return check_form(document.forms['updatedelete']);"/></p>
</p>
<!--{/if}-->

</form>

<!--{if $editaccess}-->

<!-- the fourth form: for adding a new record -->

<form action="." method="post" name="new">
<input type="hidden" name="do" value="vector.recordnew"/>
<input type="hidden" name="id" value="<!--{$layer->id}-->"/>
<input type="submit" name="submit" value="create a new record" style="width:2in;" nClick="document.forms['updatedelete'].elements['do'].value='vectorrecordnew';return"/>
</form>
<!-- the fifth form: for purging all records -->

<form action="." method="post" name="new" onSubmit="return confirm('This will delete all data in the layer!\nAre you really sure you want to do this?\nClick OK to delete all data, or Cancrel to cancel.');">
<input type="hidden" name="do" value="vector.purge"/>
<input type="hidden" name="id" value="<!--{$layer->id}-->"/>
<p><input type="submit" name="submit" value="purge all data" style="width:2in;"/>
</form>
<table style="width:10in;" >
<tr><td>
</td>

<!--{/if}-->

<script type="text/javascript">

function submit(doValue,layerId ) {
	
	

}

function check_form(formdata) {
   if (formdata.elements['do'].value=='vector.recordupdate') return check_update(formdata);
   if (formdata.elements['do'].value=='vector.recorddelete') return check_delete(formdata);
   return false;
}
function check_update(formdata) {
   if (!formdata.elements['column'].selectedIndex) return false;
   //if (!formdata.elements['value'].value) return false;
   return confirm('Really update the selected records?\nThere is no way to undo this!\n\nClick OK to update the selected records, or Cancel to cancel.');
}
function check_delete(formdata) {
   return confirm('Really delete the selected records?\nThere is no way to un-delete them!\n\nClick OK to delete the selected records, or Cancel to cancel.');
}
function toggleAll(status) {
   var elements = document.forms['updatedelete'].elements;
   for (i=0;i<elements.length;i++) {
      var element = elements[i];
      if (element.type=='checkbox' && element.name=='gids[]') element.checked = status;
   }
}
</script>
