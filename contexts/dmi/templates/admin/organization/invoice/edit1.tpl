<!--{$subnav}-->

<form action="." method="post">
<input type="hidden" name="do" value="admin.organization.invoice.edit2"/>
<input type="hidden" name="id" value="<!--{$invoice->id}-->"/>
<table style="width:100%;">
	<tr><th style="width:220px;"></th><th></th></tr>
	<tr><td>Creation Date:</td><td><input type="text" name="created" value="<!--{$invoice->created}-->"/></td></tr>
	<tr><td>Sent Date:</td><td><input type="text" name="sent" value="<!--{$invoice->sent}-->"/></td></tr>
	<tr><td>Paid Date:</td><td><input type="text" name="paid" value="<!--{$invoice->paid}-->"/></td></tr>
	<tr><td>Status:</td><td><input type="text" name="status" value="<!--{$invoice->status}-->"/></td></tr>
	<tr><td>Customer ID:</td><td><input type="text" name="customer_id" value="<!--{$invoice->customer_id}-->"/></td></tr>
	<tr><td>Purchase Order:</td><td><input type="text" name="purchase_order" value="<!--{$invoice->purchase_order}-->"/></td></tr>
	<tr><td>Billing Name:</td><td><input type="text" name="billing_name" value="<!--{$invoice->billing_name}-->"/></td></tr>
	<tr><td>Street1:</td><td><input type="text" name="street1" value="<!--{$invoice->street1}-->"/></td></tr>
	<tr><td>Street2:</td><td><input type="text" name="street2" value="<!--{$invoice->street2}-->"/></td></tr>
	<tr><td>City:</td><td><input type="text" name="city" value="<!--{$invoice->city}-->"/></td></tr>
	<tr><td>State:</td><td><input type="text" name="state" value="<!--{$invoice->state}-->"/></td></tr>
	<tr><td>Zip:</td><td><input type="text" name="zip" value="<!--{$invoice->zip}-->"/></td></tr>
	<tr><td>Phone:</td><td><input type="text" name="phone" value="<!--{$invoice->phone}-->"/></td></tr>
	<tr><td colspan="2"><strong>Licensing</strong></td></tr>
	<tr><td>Power Users:</td><td><table class="licensing">
		<tr><td>Seats:</td><td><input type="number" name="po_seats" value="<!--{$invoice->po_seats}-->"/></td></tr>
		<tr><td>Part Number:</td><td><input type="text" name="po_part_number" value="<!--{$invoice->po_part_number}-->"/></td></tr>
		<tr><td>Initial price per user:</td><td><input type="number" name="po_initial_price" value="<!--{$invoice->po_initial_price}-->"/></td></tr>
		<tr><td>Subsequent price per user:</td><td><input type="number" name="po_subsequent_price" value="<!--{$invoice->po_subsequent_price}-->"/></td></tr>
		<!--<tr><td>Initial disk space per user:</td><td><input type="number" name="po_initial_disk_space" value="<!--{$invoice->po_initial_disk_space}-->"/></td></tr>
		<tr><td>Subsequent disk space per user:</td><td><input type="number" name="po_subsequent_disk_space" value="<!--{$invoice->po_subsequent_disk_space}-->"/></td></tr>-->
	</table></td></tr>
	<tr><td>Executive Users:</td><td><table class="licensing">
		<tr><td>Seats:</td><td><input type="number" name="ex_seats" value="<!--{$invoice->ex_seats}-->"/></td></tr>
		<tr><td>Part Number:</td><td><input type="text" name="ex_part_number" value="<!--{$invoice->ex_part_number}-->"/></td></tr>
		<tr><td>Initial price per user:</td><td><input type="number" name="ex_initial_price" value="<!--{$invoice->ex_initial_price}-->"/></td></tr>
		<tr><td>Subsequent price per user:</td><td><input type="number" name="ex_subsequent_price" value="<!--{$invoice->ex_subsequent_price}-->"/></td></tr>
		<!--<tr><td>Initial disk space per user:</td><td><input type="number" name="ex_initial_disk_space" value="<!--{$invoice->ex_initial_disk_space}-->"/></td></tr>
		<tr><td>Subsequent disk space per user:</td><td><input type="number" name="ex_subsequent_disk_space" value="<!--{$invoice->ex_subsequent_disk_space}-->"/></td></tr>-->
	</table></td></tr>
	<tr><td>Staff Users:</td><td><table class="licensing">
		<tr><td>Seats:</td><td><input type="number" name="st_seats" value="<!--{$invoice->st_seats}-->"/></td></tr>
		<tr><td>Part Number:</td><td><input type="text" name="st_part_number" value="<!--{$invoice->st_part_number}-->"/></td></tr>
		<tr><td>Initial price per user:</td><td><input type="number" name="st_initial_price" value="<!--{$invoice->st_initial_price}-->"/></td></tr>
		<tr><td>Subsequent price per user:</td><td><input type="number" name="st_subsequent_price" value="<!--{$invoice->st_subsequent_price}-->"/></td></tr>
		<!--<tr><td>Initial disk space per user:</td><td><input type="number" name="st_initial_disk_space" value="<!--{$invoice->st_initial_disk_space}-->"/></td></tr>
		<tr><td>Subsequent disk space per user:</td><td><input type="number" name="st_subsequent_disk_space" value="<!--{$invoice->st_subsequent_disk_space}-->"/></td></tr>-->
	</table></td></tr>
	<tr class="licensing"><td>Extra Cost:</td><td><input type="number" name="extra" value="<!--{$invoice->extra}-->" /></td></tr>
	<tr class="licensing"><td>Sales Tax Percentage:</td><td><input type="number" name="sales_tax" value="<!--{$invoice->sales_tax}-->" /></td></tr>
	<tr class="licensing"><td>Comment:</td><td><textarea name="comment"><!--{$invoice->comment|replace:'<br />':""}--></textarea></td></tr>
	<tr><td>Sub Total:</td><td><input type="number" name="subtotal" value="" readonly="readonly"/></td></tr>
	<tr><td>Tax:</td><td><input type="number" name="tax" value="" readonly="readonly"/></td></tr>
	<tr><td>Balance Due:</td><td><input type="number" name="balancedue" value="" readonly="readonly"/></td></tr>
	<tr><td></td><td><input name="submit" type="submit" /></tr>
</table>
</form>
<script>
	$(function(){
		$( "*[name=created]" ).datepicker({changeMonth: true, changeYear: true});
		$( "*[name=sent]" ).datepicker({changeMonth: true, changeYear: true});
		$( "*[name=paid]" ).datepicker({changeMonth: true, changeYear: true});
		$('.licensing input').keyup(function(){
			var price = 0;
			var po_seats = parseInt($('*[name=po_seats]').val());
			var po_initial_price = parseInt($('*[name=po_initial_price]').val());
			var po_subsequent_price = parseInt($('*[name=po_subsequent_price]').val());
			var ex_seats = parseInt($('*[name=ex_seats]').val());
			var ex_initial_price = parseInt($('*[name=ex_initial_price]').val());
			var ex_subsequent_price = parseInt($('*[name=ex_subsequent_price]').val());
			var st_seats = parseInt($('*[name=st_seats]').val());
			var st_initial_price = parseInt($('*[name=st_initial_price]').val());
			var st_subsequent_price = parseInt($('*[name=st_subsequent_price]').val());
			var extra = parseInt($('*[name=extra]').val());
			var tax = parseInt($('*[name=sales_tax]').val());
			if(po_seats > 0) price += po_initial_price;
			if(ex_seats > 0) price += ex_initial_price;
			if(ex_seats > 0) price += st_initial_price;
			if(po_seats > 1) price += po_subsequent_price*(po_seats-1);
			if(ex_seats > 1) price += ex_subsequent_price*(ex_seats-1);
			if(ex_seats > 1) price += st_subsequent_price*(st_seats-1);
			price += extra;
			$('*[name=subtotal]').val(eRound(price,2));
			$('*[name=tax]').val(eRound(price*(tax/100),2));
			$('*[name=balancedue]').val(eRound(price*(1+(tax/100)),2));
		});
		$('.licensing input').change(function(){$('.licensing input').first().keyup();});
		$('.licensing input').first().keyup();
	});
	function eRound(value, places) {
		var multiplier = Math.pow(10, places);
		return (Math.round(value * multiplier) / multiplier);
	}
</script>