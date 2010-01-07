<?php
// Copyright (C) 2002-2008  Paul Yasi (paul at citrusdb.org)
// read the README file for more information

/*----------------------------------------------------------------------------*/
// Check for authorized accesss
/*----------------------------------------------------------------------------*/
if(constant("INDEX_CITRUS") <> 1){
	echo "You must be logged in to run this.  Goodbye.";
	exit;	
}

if (!defined("INDEX_CITRUS")) {
	echo "You must be logged in to run this.  Goodbye.";
        exit;
}

echo "<h3>$l_refund</h3>
<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"include/CalendarPopup.js\"></SCRIPT>
	<SCRIPT LANGUAGE=\"JavaScript\">
	var cal = new CalendarPopup();
	</SCRIPT>
";

//Include the billing functions
//include('include/billing.inc.php');

//GET Variables
if (!isset($base->input['billingdate'])) { $base->input['billingdate'] = ""; }
if (!isset($base->input['organization_id'])) { $base->input['organization_id'] = ""; }
if (!isset($base->input['passphrase'])) { $base->input['passphrase'] = ""; }

$submit = $base->input['submit'];
$billingdate = $base->input['billingdate'];
$organization_id = $base->input['organization_id'];
$passphrase = $base->input['passphrase'];

// make sure the user is in a group that is allowed to run this

if ($submit) {
	
	//$DB->debug = true;

	/*--------------------------------------------------------------------*/
	// Create the refund data
	/*--------------------------------------------------------------------*/

	/*--------------------------------------------------------------------*/
	// print the credit card refunds to a file
	/*--------------------------------------------------------------------*/

	// select the path_to_ccfile from settings
	$query = "SELECT path_to_ccfile FROM settings WHERE id = '1'";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$ccfileresult = $DB->Execute($query) 
		or die ("$l_queryfailed");
	$myccfileresult = $ccfileresult->fields;
	$path_to_ccfile = $myccfileresult['path_to_ccfile'];	

	// select the info from general to get the path_to_ccfile
	$query = "SELECT ccexportvarorder,exportprefix FROM general 
			WHERE id = '$organization_id'";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$ccvarresult = $DB->Execute($query) 
		or die ("$l_queryfailed");
	$myccvarresult = $ccvarresult->fields;
	$ccexportvarorder = $myccvarresult['ccexportvarorder'];
	$exportprefix = $myccvarresult['exportprefix'];
	
	// convert the $ccexportvarorder &#036; dollar signs back to actual dollar signs and &quot; back to quotes
	$ccexportvarorder = str_replace( "&#036;"           , "$"        , $ccexportvarorder );
	$ccexportvarorder = str_replace( "&quot;"           , "\\\""        , $ccexportvarorder );

	// open the file
	$today = date("Y-m-d");
	$filename = "$path_to_ccfile" . "/" . "$exportprefix" . "refund" . "$today.csv";

	$handle = fopen($filename, 'w') or die ("cannot open $filename"); // open the file

	// query from billing_details the refunds to do
	$query = "SELECT ROUND(SUM(bd.refund_amount),2) AS RefundTotal, 
			b.id b_id, b.name b_name, b.company b_company, 
			b.street b_street, b.city b_city, 
			b.state b_state, b.zip b_zip, 
			b.account_number b_acctnum, 
			b.creditcard_number b_ccnum, b.encrypted_creditcard_number b_enc_num, 
			b.creditcard_expire b_ccexp, 
			b.from_date b_from_date, 
			b.to_date b_to_date, 
			b.payment_due_date b_payment_due_date,  
			bd.invoice_number bd_invoice_number, 
			bd.batch bd_batch   
			FROM billing_details bd
			LEFT JOIN billing b ON bd.billing_id = b.id 
			LEFT JOIN billing_types bt ON bt.id = b.billing_type 
			WHERE bd.refunded <> 'y' AND bd.refund_amount > 0 
			AND bt.method = 'creditcard' 
			AND b.organization_id = '$organization_id' 
			GROUP BY b.id";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) 
		or die ("$l_queryfailed");

	while ($myresult = $result->FetchRow()) {
		$batchid = $myresult['bd_batch'];
		$invoice_number = $myresult['bd_invoice_number'];
		$user = "refund";
		$mydate = $today;
		$mybilling_id = $myresult['b_id'];
		$billing_name = $myresult['b_name'];
		$billing_company = $myresult['b_company'];
		$billing_street =  $myresult['b_street'];
		$billing_city = $myresult['b_city'];
		$billing_state = $myresult['b_state'];
		$billing_zip = $myresult['b_zip'];
		$billing_acctnum = $myresult['b_acctnum'];
		$billing_ccnum = $myresult['b_ccnum'];
		$billing_ccexp = $myresult['b_ccexp'];
		$billing_fromdate = $myresult['b_from_date'];
		$billing_todate = $myresult['b_to_date'];
		$billing_payment_due_date = $myresult['b_payment_due_date'];
		$precisetotal = $myresult['RefundTotal'];
		$encrypted_creditcard_number = $myresult['b_enc_num'];

		// get the absolute value of the total
		$abstotal = abs($precisetotal);

		// write the encrypted_creditcard_number to a temporary file
 		// and decrypt that file to stdout to get the CC
 		// select the path_to_ccfile from settings
 		$query = "SELECT path_to_ccfile FROM settings WHERE id = '1'";
 		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
 		$ccfileresult = $DB->Execute($query) 
 		  or die ("$l_queryfailed");
 		$myccfileresult = $ccfileresult->fields;
 		$path_to_ccfile = $myccfileresult['path_to_ccfile'];
 		
 		// open the file
 		$cipherfilename = "$path_to_ccfile/ciphertext.tmp";
 		$cipherhandle = fopen($cipherfilename, 'w') or die ("cannot open $cipherfilename");
 		
 		// write the ciphertext we want to decrypt into the file
 		fwrite($cipherhandle, $encrypted_creditcard_number);
 		
 		// close the file
 		fclose($cipherhandle);
 		
 		//$gpgcommandline = "echo $passphrase | $gpg_decrypt $cipherfilename";
 		
		// destroy the output array before we use it again
 		unset($decrypted);
 		
		//$gpgresult = exec($gpgcommandline, $decrypted, $errorcode);

		$gpgcommandline = "$gpg_decrypt $cipherfilename";
		$decrypted = decrypt_command($gpgcommandline, $passphrase);

		// if there is a gpg error, stop here
		if (substr($decrypted,0,5) == "error") {
		  die ("Credit Card Encryption Error: $decrypted");
		}
 		 
 		// set the billing_ccnum to the decrypted_creditcard_number
 		$decrypted_creditcard_number = $decrypted;
 		$billing_ccnum = $decrypted_creditcard_number;
				
		// determine the variable export order values
		eval ("\$exportstring = \"$ccexportvarorder\";");

		// print the line in the exported data file
		// don't print them to billing if the amount is less than or equal to zero
		$newline = "\"CREDIT\",$exportstring\n";
		
		fwrite($handle, $newline); // write to the file

		// mark the refunds as refunded
		$query ="UPDATE billing_details 
			SET refunded = 'y' 
			WHERE refunded <> 'y' AND refund_amount > 0 
			AND billing_id = $mybilling_id";		
		$detailresult = $DB->Execute($query) or die ("$l_queryfailed");	

	} // end while
	
	// close the file
	fclose($handle); // close the file

	// log this export activity
	log_activity($DB,$user,0,'export','creditcard',$batchid,'success');

	$myfile = "$exportprefix" . "refund" . "$today.csv";

	echo "$l_wrotefile $filename<br><a href=\"index.php?load=tools/downloadfile&type=dl&filename=$myfile\"><u class=\"bluelink\">$l_download $myfile</u></a><p>";	


}
else {
// ask if they want to process outstanding refunds

  $form_action_url ="$ssl_url_prefix" . "index.php";
  
  echo "<FORM ACTION=\"$form_action_url\" METHOD=\"POST\" name=\"form1\" AUTOCOMPLETE=\"off\">
	<input type=hidden name=load value=refundcc>
	<input type=hidden name=type value=tools>
	<input type=hidden name=refund value=on>
	<table>";
	// print list of organizations to choose from
        $query = "SELECT id,org_name FROM general";
        $DB->SetFetchMode(ADODB_FETCH_ASSOC);
        $result = $DB->Execute($query) or die ("$l_queryfailed");
        echo "<td><b>$l_organizationname</b></td>
                <td><select name=\"organization_id\">
                <option value=\"\">$l_choose</option>";
        while ($myresult = $result->FetchRow()) {
                $myid = $myresult['id'];
                $myorg = $myresult['org_name'];
                echo "<option value=\"$myid\">$myorg</option>";
        }
echo "</select></td><tr>
<td align=right>$l_passphrase:</td><td><input type=password name=passphrase></td><tr>
	<td>$l_processoutstandingrefunds:</td>
	<td><INPUT TYPE=\"SUBMIT\" NAME=\"submit\" value=\"$l_yes\"></td>
	</form></table><br><br><br>";
}

?>

