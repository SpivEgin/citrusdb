<br><br>
<h4><?php echo lang('areyousureturnedoff');?></h4>
<table cellpadding=15 cellspacing=0 border=0 width=720><td align=right width=360>
<form style="margin-bottom:0;" action="<?php echo $this->url_prefix;?>/index.php/billing/saveturnoff" method=POST>
<input type=hidden name=billing_id value=$billing_id>
<input name=save type=submit value=" <?php echo lang('yes');?> " class=smallbutton></form></td>
<td align=left width=360><form style="margin-bottom:0;" action="<?php echo $this->url_prefix;?>/index.php/billing" method=post>
<input name=done type=submit value=" <?php echo lang('no');?>  " class=smallbutton>
<input type=hidden name=load value=billing>
<input type=hidden name=type value=module>
</form></td></table>
