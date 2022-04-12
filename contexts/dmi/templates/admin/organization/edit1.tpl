<!--{$subnav}-->

<form action="." method="post">
<input type="hidden" name="do" value="admin.organization.edit2"/>
<input type="hidden" name="id" value="<!--{$org->id}-->"/>
<table style="width:100%;">
	<tr><th style="width:220px;"></th><th></th></tr>
	<tr><td>Name:</td><td><input type="text" name="name" value="<!--{$org->name}-->"/></td></tr>
	<tr><td>Short Name (Permalink):</td><td><input type="text" name="short"  maxlength="32" value="<!--{$org->short}-->"/></td></tr>
	<tr><td>Description</td><td><textarea name="description"><!--{$org->description}--></textarea></td></tr>
	<tr><td>Owner Account:</td><td>Leave The Same: <input type="radio" name="contact" id="newBox" value="<!--{$org->owner->id}-->" checked="checked"/> <!--{$org->owner->realname}--> (<!--{$org->owner->username}-->)</td></tr>
	<tr><td></td><td>New User: <input type="radio" name="contact" id="newBox" value="new"/> Username: <input type="text" name="account_username" style="width:2in;" maxlength="16"/> Password: <input type="text" name="account_password" style="width:2in;" maxlength="16"/></td></tr>
	<tr><td></td><td><div id="selector"></div><!--{include file='list/contact.tpl'}--></td></tr>
	<tr><td colspan="2"><strong>Invoicing</strong></td></tr>
	<tr><td>Customer ID:</td><td><input type="text" name="customer_id" value="<!--{$org->customer_id}-->"/></td></tr>
	<tr><td>Purchase Order:</td><td><input type="text" name="purchase_order" value="<!--{$org->purchase_order}-->"/></td></tr>
	<tr><td>Billing Name:</td><td><input type="text" name="billing_name" value="<!--{$org->billing_name}-->"/></td></tr>
	<tr><td>Street1:</td><td><input type="text" name="street1" value="<!--{$org->street1}-->"/></td></tr>
	<tr><td>Street2:</td><td><input type="text" name="street2" value="<!--{$org->street2}-->"/></td></tr>
	<tr><td>City:</td><td><input type="text" name="city" value="<!--{$org->city}-->"/></td></tr>
	<tr><td>State:</td><td><input type="text" name="state" value="<!--{$org->state}-->"/></td></tr>
	<tr><td>Zip:</td><td><input type="text" name="zip" value="<!--{$org->zip}-->"/></td></tr>
	<tr><td>Phone:</td><td><input type="text" name="phone" value="<!--{$org->phone}-->"/></td></tr>
	<tr><td colspan="2"><strong>Licensing</strong></td></tr>
	<tr><td>Power Users:</td><td><table class="licensing">
		<tr><td>Seats:</td><td><input type="number" name="po_seats" value="<!--{$org->po_seats}-->"/></td></tr>
		<tr><td>Part Number:</td><td><input type="text" name="po_part_number" value="<!--{$org->po_part_number}-->"/></td></tr>
		<tr><td>Initial price per user:</td><td><input type="number" name="po_initial_price" value="<!--{$org->po_initial_price}-->"/></td></tr>
		<tr><td>Subsequent price per user:</td><td><input type="number" name="po_subsequent_price" value="<!--{$org->po_subsequent_price}-->"/></td></tr>
		<tr><td>Initial disk space per user:</td><td><input type="number" name="po_initial_disk_space" value="<!--{$org->po_initial_disk_space}-->"/></td></tr>
		<tr><td>Subsequent disk space per user:</td><td><input type="number" name="po_subsequent_disk_space" value="<!--{$org->po_subsequent_disk_space}-->"/></td></tr>
	</table></td></tr>
	<tr><td>Executive Users:</td><td><table class="licensing">
		<tr><td>Seats:</td><td><input type="number" name="ex_seats" value="<!--{$org->ex_seats}-->"/></td></tr>
		<tr><td>Part Number:</td><td><input type="text" name="ex_part_number" value="<!--{$org->ex_part_number}-->"/></td></tr>
		<tr><td>Initial price per user:</td><td><input type="number" name="ex_initial_price" value="<!--{$org->ex_initial_price}-->"/></td></tr>
		<tr><td>Subsequent price per user:</td><td><input type="number" name="ex_subsequent_price" value="<!--{$org->ex_subsequent_price}-->"/></td></tr>
		<tr><td>Initial disk space per user:</td><td><input type="number" name="ex_initial_disk_space" value="<!--{$org->ex_initial_disk_space}-->"/></td></tr>
		<tr><td>Subsequent disk space per user:</td><td><input type="number" name="ex_subsequent_disk_space" value="<!--{$org->ex_subsequent_disk_space}-->"/></td></tr>
	</table></td></tr>
	<tr><td>Staff Users:</td><td><table class="licensing">
		<tr><td>Seats:</td><td><input type="number" name="st_seats" value="<!--{$org->st_seats}-->"/></td></tr>
		<tr><td>Part Number:</td><td><input type="text" name="st_part_number" value="<!--{$org->st_part_number}-->"/></td></tr>
		<tr><td>Initial price per user:</td><td><input type="number" name="st_initial_price" value="<!--{$org->st_initial_price}-->"/></td></tr>
		<tr><td>Subsequent price per user:</td><td><input type="number" name="st_subsequent_price" value="<!--{$org->st_subsequent_price}-->"/></td></tr>
		<tr><td>Initial disk space per user:</td><td><input type="number" name="st_initial_disk_space" value="<!--{$org->st_initial_disk_space}-->"/></td></tr>
		<tr><td>Subsequent disk space per user:</td><td><input type="number" name="st_subsequent_disk_space" value="<!--{$org->st_subsequent_disk_space}-->"/></td></tr>
	</table></td></tr>
	<tr class="licensing"><td>Extra Cost:</td><td><input type="number" name="extra" value="<!--{$org->extra}-->" /></td></tr>
	<tr class="licensing"><td>Sales Tax Percentage:</td><td><input type="number" name="sales_tax" value="<!--{$org->sales_tax}-->" /></td></tr>
	<tr><td>First Payment Due:</td><td><input type="text" name="paymentstartdate" value="<!--{$org->paymentstartdate}-->"/></td></tr>
	<tr><td>Recurring Payment Interval:</td><td><input type="text" name="paymentterm" value="<!--{$org->paymentterm}-->"/></td></tr>
	<tr><td>Payments Passed:</td><td><input type="number" name="paymentspassed" value="<!--{$org->paymentspassed}-->"/></td></tr>
	<tr><td style="padding-top:12pt;">Total disk space allocation:</td><td style="padding-top:12pt;"><input type="number" name="totaldisk" value="" readonly="readonly"/></td></tr>
	<tr><td>Current disk space used:</td><td><input type="number" name="currentdisk" value="0" readonly="readonly"/></td></tr>
	<tr><td>Sub Total:</td><td><input type="number" name="subtotal" value="" readonly="readonly"/></td></tr>
	<tr><td>Tax:</td><td><input type="number" name="tax" value="" readonly="readonly"/></td></tr>
	<tr><td>Balance Due:</td><td><input type="number" name="balancedue" value="" readonly="readonly"/></td></tr>
	<tr><td></td><td><input name="submit" type="submit" /></tr>
</table>
</form>
<script>
	$(function(){
		$( "*[name=paymentstartdate]" ).datepicker({changeMonth: true, changeYear: true});
		$('.licensing input').keyup(function(){
			var price = 0;
			var po_seats = parseFloat($('*[name=po_seats]').val());
			var po_initial_price = parseFloat($('*[name=po_initial_price]').val());
			var po_subsequent_price = parseFloat($('*[name=po_subsequent_price]').val());
			var ex_seats = parseFloat($('*[name=ex_seats]').val());
			var ex_initial_price = parseFloat($('*[name=ex_initial_price]').val());
			var ex_subsequent_price = parseFloat($('*[name=ex_subsequent_price]').val());
			var st_seats = parseFloat($('*[name=st_seats]').val());
			var st_initial_price = parseFloat($('*[name=st_initial_price]').val());
			var st_subsequent_price = parseFloat($('*[name=st_subsequent_price]').val());
			if(po_seats > 0) price += po_initial_price;
			if(ex_seats > 0) price += ex_initial_price;
			if(ex_seats > 0) price += st_initial_price;
			if(po_seats > 1) price += po_subsequent_price*(po_seats-1);
			if(ex_seats > 1) price += ex_subsequent_price*(ex_seats-1);
			if(ex_seats > 1) price += st_subsequent_price*(st_seats-1);
			var extra = parseFloat($('*[name=extra]').val());
			var sales_tax = parseFloat($('*[name=sales_tax]').val());
			price += extra;
			$('*[name=subtotal]').val(eRound(price,2));
			$('*[name=tax]').val(eRound(price*(sales_tax/100),2));
			price *= (1+(sales_tax/100))
			$('*[name=balancedue]').val(eRound(price,2));
			
			var diskspace = 0;
			var po_initial_disk_space = parseFloat($('*[name=po_initial_disk_space]').val());
			var po_subsequent_disk_space = parseFloat($('*[name=po_subsequent_disk_space]').val());
			var ex_initial_disk_space = parseFloat($('*[name=ex_initial_disk_space]').val());
			var ex_subsequent_disk_space = parseFloat($('*[name=ex_subsequent_disk_space]').val());
			var st_initial_disk_space = parseFloat($('*[name=st_initial_disk_space]').val());
			var st_subsequent_disk_space = parseFloat($('*[name=st_subsequent_disk_space]').val());
			
			if(po_seats > 0) diskspace += po_initial_disk_space;
			if(ex_seats > 0) diskspace += ex_initial_disk_space;
			if(ex_seats > 0) diskspace += st_initial_disk_space;
			if(po_seats > 1) diskspace += po_subsequent_disk_space*(po_seats-1);
			if(ex_seats > 1) diskspace += ex_subsequent_disk_space*(ex_seats-1);
			if(ex_seats > 1) diskspace += st_subsequent_disk_space*(st_seats-1);
			
			$('*[name=totaldisk]').val(diskspace);
		});
		$('.licensing input').change(function(){$('.licensing input').first().keyup();});
		$('.licensing input').first().keyup();
	});
	function eRound(value, places) {
		var multiplier = Math.pow(10, places);
		return (Math.round(value * multiplier) / multiplier);
	}
</script>