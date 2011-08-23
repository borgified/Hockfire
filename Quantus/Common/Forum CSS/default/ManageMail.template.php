<?php
// Version: 2.0 RC1; ManageMail

function template_browse()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<table border="0" align="center" cellspacing="0" cellpadding="4" class="tborder" width="100%">
		<tr class="titlebg">
			<td colspan="2">', $txt['mailqueue_stats'], '</td>
		</tr>
		<tr class="windowbg">
			<td width="30%">', $txt['mailqueue_size'], ':</td>
			<td width="70%">', $context['mail_queue_size'], '</td>
		</tr>
		<tr class="windowbg">
			<td width="30%">', $txt['mailqueue_oldest'], ':</td>
			<td width="70%">', $context['oldest_mail'], '</td>
		</tr>
	</table>
	<br />';

	template_show_list('mail_queue');
}

?>