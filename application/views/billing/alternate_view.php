<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
[ <a href="<?php echo $this->url_prefix; ?>/index.php/billing/addbilling">
<?php echo lang('addaltbilling');?></a> ]<br>

<table width=720><tr bgcolor="#ddddee">
<?php
foreach ($alternate as $myresult)
{
	$billing_id = $myresult['b_id'];
	$billing_type = $myresult['t_name'];
	$billing_orgname = $myresult['g_org_name'];

	$mystatus = $this->billing_model->billingstatus($billing_id);

	$alternate_billing_id_url = $this->ssl_url_prefix . "/index.php/billing/edit/$billing_id";

	print "<td><b>$billing_orgname</b> &nbsp;<a href=\"$alternate_billing_id_url\">".
	"$billing_id</a></td><td>$billing_type</td><td>$mystatus</td>";

	// check if they are billing or manager and show the maintenance and rerun type links
	if (($userprivileges['manager'] == 'y') OR ($userprivileges['admin'] == 'y')) {
		echo "<td>".
			"<a href=\"$this->url_prefix/index.php/billing/rerun/$billing_id\">".
			lang('rerun')."</a> | ".
			"<a href=\"$this->url_prefix/index.php/billing/invmaint/$billing_id\">".
			lang('invoicemaintenance')."</a> | ".
			"<a href=\"$this->url_prefix/index.php/billing/refund/$billing_id\">".
			lang('refund')."</a>".
			"</td><td>".
			"<form name=status$billing_id style=\"margin-bottom:0;\" method=post>".
			"<select style=\"font-size: 80%;\" name=menu onChange=\"location=document.status".
			"$billing_id.menu.options[document.status$billing_id.menu.selectedIndex].value;\" value=GO>".
			"<option value=\"\">".lang('changestatus')."</option>".
			"<option value=\"$this->url_prefix/index.php/billing/turnoff/$billing_id\">- ".lang('turnoff')."</option>".
			"<option value=\"$this->url_prefix/index.php/billing/waiting/$billing_id\">- ".lang('waiting')."</optoin>".
			"<option value=\"$this->url_prefix/index.php/billing/authorized/$billing_id\">- ".lang('authorized')."</option>".
			"<option value=\"$this->url_prefix/index.php/billing/cancelwfee/$billing_id\">- ".lang('cancelwithfee')."</option>".
			"<option value=\"$this->url_prefix/index.php/billing/collections/$billing_id\">- ".lang('collections')."</option>".    
			"</select></form>".
			"</td><td>".
			"<form name=notice$billing_id style=\"margin-bottom:0;\" method=post>".
			"<select style=\"font-size: 80%;\" name=menu onChange=\"location=document.notice$billing_id.menu.".
			"options[document.notice$billing_id.menu.selectedIndex].value;\" value=GO>".
			"<option value=\"\">".lang('invoiceornotice')."</option>".
			"<option value=\"$this->url_prefix/index.php/billing/createinvoice/$billing_id\">- ".lang('createinvoice')."</option> | ".
			"<option value=\"$this->url_prefix/index.php/billing/cancelnotice/$billing_id\">- ".lang('cancel_notice')."</option> | ".
			"<option value=\"$this->url_prefix/index.php/billing/shutoffnotice/$billing_id\">- ".lang('shutoff_notice')."</option> | ".
			"<option value=\"$this->url_prefix/index.php/billing/collectionsnotice/$billing_id\">- ".lang('collections_notice')."</option>".
			"</select></form>".
			"</td>";
	}
	
	echo "<tr bgcolor=\"#ddddee\">";	
}
?>
</table>
