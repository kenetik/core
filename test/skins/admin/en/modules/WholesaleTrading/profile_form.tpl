<tbody IF="isShowWholesalerFields()">
<tr>
    <td colspan="4">&nbsp;</td>
</tr>
<tr valign="middle">
    <td colspan="4"><b>Tax registration details (wholesalers only)</b><br><hr size="1" noshade></td>
</tr>
<tr  IF="xlite.config.WholesaleTrading.WholesalerFieldsTaxId" valign="middle">
    <td align="right">Sales Permit/Tax ID#</td>
    <td>&nbsp;</td>
    <td>
	<input name="tax_id" size="32" value="{tax_id}">
	</td>
</tr>
<tr IF="xlite.config.WholesaleTrading.WholesalerFieldsVat" valign="middle">
    <td align="right">VAT Registration number</td>
    <td>&nbsp;</td>
    <td>
	<input name="vat_number" size="32" value="{vat_number}">
	</td>
</tr>
<tr IF="xlite.config.WholesaleTrading.WholesalerFieldsGst" valign="middle">
    <td align="right">GST Registration number</td>
    <td>&nbsp;</td>
    <td>
	<input name="gst_number" size="32" value="{gst_number}">
	</td>
</tr>
<tr IF="xlite.config.WholesaleTrading.WholesalerFieldsPst" valign="middle">
    <td align="right">PST Registration number</td>
    <td>&nbsp;</td>
    <td>
	<input name="pst_number" size="32" value="{pst_number}">
	</td>
</tr>
</tbody>
