<!--{$subnav}-->
<div  class="print" style="float:right;">
	<!--{if $user->admin}--><a href="./?do=admin.organization.invoice.edit1&id=62">Edit Invoice</a><!--{/if}-->
	<a href="javascript:window.print();"><img src="media/icons/printer.png" alt="Print"/></a>
</div>
<table id="invoice">
	<tr>
		<td class="invoice_name">SimpleLayers</td>
		<td class="invoice_head">INVOICE</td>
	</tr>
	<tr>
		<td style="padding-top:12pt;">SimpleLayers<br/>
		7435 N. Figueroa St #41403<br/>
		Los Angeles, CA 90041<br/>
		Phone: 855-627-7375<br/>
		Email: info@simplelayers.com</td>
		<td style="text-align:right;font-weight:700;padding-top:12pt;">INVOICE #<!--{$invoice->id}--><br/>
		DATE: <!--{$invoice->created}-->
		</td>
	</tr>
	<tr>
		<td colspan="2" style="font-weight:700;padding-top:12pt;">Billing Address:</td>
	</tr>
	<tr>
		<td colspan="2"><!--{$invoice->billing_name}--><br/>
		<!--{$invoice->street1}--><br/>
		<!--{if $invoice->street2}--><!--{$invoice->street2}--><br/><!--{/if}-->
		<!--{$invoice->city}-->, <!--{$invoice->state}--> <!--{$invoice->zip}--><!--{if $invoice->phone}--><br/>
		<!--{$invoice->phone}--><!--{/if}-->
		</td>
	</tr>
	<!--{if $invoice->comment}-->
	<tr>
		<td colspan="2" style="font-weight:700;padding-top:12pt;">Comments or special instructions:</td>
	</tr>
	<tr>
		<td colspan="2" style="word-break:hyphenate;"><!--{$invoice->comment}--></td>
	</tr>
	<!--{/if}-->
	<tr>
		<td colspan="2" style="font-weight:700;padding-top:12pt;">Term of Service:</td>
	</tr>
	<tr>
		<td colspan="2" style="word-break:hyphenate;"><!--{$invoice->created}--> - <!--{$invoice->tos()}--></td>
	</tr>
	<tr>
		<td colspan="2" style="padding-top:12pt;">
			<table>
				<tr>
					<th>QTY</th>
					<!--<!--{if $invoice->po_part_number || $invoice->ex_part_number || $invoice->st_part_number}-->
						<th>P/N</th>
					<!--{/if}-->-->
					<th>DESCRIPTION</th>
					<th>UNIT PRICE</th>
					<th>AMOUNT</th>
				</tr>
				<!--{if $invoice->po_seats}-->
					<tr>
						<td>1</td>
						<td>Initial Power User Seat</td>
						<td><!--{$invoice->po_initial_price|number_format:2}--></td>
						<td><!--{$invoice->po_initial_price|number_format:2}--></td>
					</tr>
					<!--{if $invoice->po_seats>1}-->
					<tr>
						<td><!--{$invoice->po_seats-1}--></td>
						<td>Subsequent Power User Seat</td>
						<td><!--{$invoice->po_subsequent_price|number_format:2}--></td>
						<td><!--{$invoice->poPrice()|number_format:2}--></td>
					</tr>
					<!--{/if}-->
				<!--{/if}-->
				<!--{if $invoice->ex_seats}-->
					<tr>
						<td>1</td>
						<td>Initial Executive Seat</td>
						<td><!--{$invoice->ex_initial_price|number_format:2}--></td>
						<td><!--{$invoice->ex_initial_price|number_format:2}--></td>
					</tr>
					<!--{if $invoice->ex_seats>1}-->
					<tr>
						<td><!--{$invoice->ex_seats-1}--></td>
						<td>Subsequent Executive Seat</td>
						<td><!--{$invoice->ex_subsequent_price|number_format:2}--></td>
						<td><!--{$invoice->exPrice()|number_format:2}--></td>
					</tr>
					<!--{/if}-->
				<!--{/if}-->
				<!--{if $invoice->st_seats}-->
					<tr>
						<td>1</td>
						<td>Initial Staff Seat</td>
						<td><!--{$invoice->st_initial_price|number_format:2}--></td>
						<td><!--{$invoice->st_initial_price|number_format:2}--></td>
					</tr>
					<!--{if $invoice->st_seats>1}-->
					<tr>
						<td><!--{$invoice->st_seats-1}--></td>
						<td>Subsequent Staff Seat</td>
						<td><!--{$invoice->st_subsequent_price|number_format:2}--></td>
						<td><!--{$invoice->stPrice()|number_format:2}--></td>
					</tr>
					<!--{/if}-->
				<!--{/if}-->
				<!--{if $invoice->extra}-->
					<tr>
						<td>-</td>
						<!--{if $invoice->extra > 0}-->
							<td>Extras</td>
						<!--{else}-->
							<td>Discounts</td>
						<!--{/if}-->
						<td>-</td>
						<td><!--{$invoice->extra|number_format:2}--></td>
					</tr>
				<!--{/if}-->
				<tr>
					<td style="text-align:right;border-left:none;border-bottom:none;" colspan="3" class="subtotal">SUBTOTAL</td>
					<td class="subtotal"><!--{$invoice->subTotal()|number_format:2}--></td>
				</tr>
				<tr>
					<td style="text-align:right;border-left:none;" colspan="3">TAX (<!--{$invoice->sales_tax}-->%)</td>
					<td><!--{$invoice->taxTotal()|number_format:2}--></td>
				</tr>
				<tr>
					<td style="text-align:right;border-left:none;font-weight:700;" colspan="3">TOTAL DUE</td>
					<td style="border-top:1px solid #000;border-bottom:1px solid #000;"><!--{$invoice->total()|number_format:2}--></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" style=";padding-top:12pt;">Make all cheques payable to Cartograph Inc.</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align:center;padding-top:12pt;">If you have any questions concerning this invoice, contact us<br/>855-627-7375 or info@cartograph.com.</td>
	</tr>
</table>
<script>
$(function(){
	$(".print").prependTo("#navRow");
});
</script>