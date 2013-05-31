<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('modules')?></h3>
[ <a href="<?php echo $this->url_prefix?>/index.php/tools/admin/addmodule"><?php echo lang('addmodule')?></a> ]
<p>
<table cellpadding=5 cellspacing=1><tr bgcolor="#eeeeee"><td>
<b><?php echo lang('modulename')?></b></td><td></td></tr>

<?php
foreach ($modules AS $m)
{
	$commonname = $m['commonname'];
	$modulename = $m['modulename'];

	print "<tr bgcolor=\"#eeeeee\"><td>".
		"$commonname</td>".
		"<td><a href=\"$this->url_prefix/index.php/tools/admin/modulepermissions/$modulename\">".
		"[ ".lang('edit')." ".lang('permission')." ]</a></td></tr>";
}
?>
</table>
</body>
</html>
