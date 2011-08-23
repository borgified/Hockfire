<?php
// Version: 2.0 RC1; Errors

// !!!
/*	This template file contains only the sub template fatal_error. It is
	shown when an error occurs, and should show at least a back button and
	$context['error_message'].
*/

// Show an error message.....
function template_fatal_error()
{
	global $context, $settings, $options, $txt;

	echo '
<div>
	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>', $context['error_title'], '</td>
		</tr>
		<tr class="windowbg">
			<td style="padding: 3ex;">
				', $context['error_message'], '
			</td>
		</tr>
	</table>
</div>';

	// Show a back button (using javascript.)
	echo '
<div align="center" style="margin-top: 2ex;"><a href="javascript:history.go(-1)">', $txt['back'], '</a></div>';
}

function template_error_log()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
		<form action="', $scripturl, '?action=admin;area=logs;sa=errorlog', $context['sort_direction'] == 'down' ? ';desc' : '', ';start=', $context['start'], $context['has_filter'] ? $context['filter']['href'] : '', '" method="post" accept-charset="', $context['character_set'], '" onsubmit="if (lastClicked == \'remove_all\' &amp;&amp; !confirm(\'', $txt['sure_about_errorlog_remove'], '\')) return false; else return true;">
			<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
				var lastClicked = "";
			// ]]></script>
			<table border="0" cellspacing="1" cellpadding="5" class="bordercolor" align="center" width="100%">
				<tr>
					<td colspan="2" class="titlebg">
						<a href="', $scripturl, '?action=helpadmin;help=error_log" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['errlog'], '
					</td>
				</tr>
				<tr>
					<td colspan="2" class="windowbg2">
						', $txt['apply_filter_of_type'], ':';

	$error_types = array();
	foreach ($context['error_types'] as $type => $details)
		$error_types[] = ($details['is_selected'] ? '<img src="' . $settings['images_url'] . '/selected.gif" alt="" /> ' : '') . '<a href="' . $details['url'] . '" ' . ($details['is_selected'] ? 'style="font-weight: bold;"' : '') . ' title="' . $details['description'] . '">' . $details['label'] . '</a>';

	echo '
						', implode('&nbsp;|&nbsp;', $error_types), '
					</td>
				</tr>
				<tr>
					<td colspan="2" class="catbg">
						', $txt['pages'], ': ', $context['page_index'], '
					</td>
				</tr>';

	if ($context['has_filter'])
		echo '
				<tr>
					<td colspan="2" class="windowbg2">
						<b>', $txt['applying_filter'], ':</b> ', $context['filter']['entity'], ' ', $context['filter']['value']['html'], ' (<a href="', $scripturl, '?action=admin;area=logs;sa=errorlog', $context['sort_direction'] == 'down' ? ';desc' : '', '">', $txt['clear_filter'], '</a>)
					</td>
				</tr>';

	if (!empty($context['errors']))
		echo '
				<tr>
					<td colspan="2" align="left" class="windowbg2">
						<div style="float: right;"><input type="submit" value="', $txt['remove_selection'], '" onclick="lastClicked = \'remove_selection\';" /> <input type="submit" name="delall" value="', $context['has_filter'] ? $txt['remove_filtered_results'] : $txt['remove_all'], '" onclick="lastClicked = \'remove_all\';" /></div>
						<label for="check_all1"><input type="checkbox" id="check_all1" onclick="invertAll(this, this.form, \'delete[]\'); this.form.check_all2.checked = this.checked;" class="check" /> <b>', $txt['check_all'], '</b></label>
					</td>
				</tr>';

	foreach ($context['errors'] as $error)
	{
		echo '
				<tr>
					<td width="15" align="center" class="windowbg2">
						<input type="checkbox" name="delete[]" value="', $error['id'], '" class="check" />
					</td><td class="windowbg2" width="100%">
						<table width="100%" class="windowbg2" border="0" cellspacing="7" cellpadding="0">
							<tr>
								<td class="windowbg2" width="50%">
									<a href="', $scripturl, '?action=admin;area=logs;sa=errorlog', $context['sort_direction'] == 'down' ? ';desc' : '', ';filter=id_member;value=', $error['member']['id'], '" title="', $txt['apply_filter'], ': ', $txt['filter_only_member'], '"><img src="', $settings['images_url'], '/filter.gif" alt="', $txt['apply_filter'], ': ', $txt['filter_only_member'], '" /></a>
									<b>', $error['member']['link'], '</b>
								</td><td class="windowbg2" width="50%" align="left">
									<a href="', $scripturl, '?action=admin;area=logs;sa=errorlog', $context['sort_direction'] == 'down' ? '' : ';desc', $context['has_filter'] ? $context['filter']['href'] : '', '" title="', $txt['reverse_direction'], '"><img src="', $settings['images_url'], '/sort_', $context['sort_direction'], '.gif" alt="', $txt['reverse_direction'], '" /></a>
									', $error['time'], '
								</td>
							</tr><tr>
								<td class="windowbg2" width="50%">
									<a href="', $scripturl, '?action=admin;area=logs;sa=errorlog', $context['sort_direction'] == 'down' ? ';desc' : '', ';filter=ip;value=', $error['member']['ip'], '" title="', $txt['apply_filter'], ': ', $txt['filter_only_ip'], '"><img src="', $settings['images_url'], '/filter.gif" alt="', $txt['apply_filter'], ': ', $txt['filter_only_ip'], '" /></a>
									<b><a href="', $scripturl, '?action=trackip;searchip=', $error['member']['ip'], '">', $error['member']['ip'], '</a></b>&nbsp;&nbsp;
								</td><td class="windowbg2" width="50%">';

		if ($error['member']['session'] != '')
			echo '
									<a href="', $scripturl, '?action=admin;area=logs;sa=errorlog', $context['sort_direction'] == 'down' ? ';desc' : '', ';filter=session;value=', $error['member']['session'], '" title="', $txt['apply_filter'], ': ', $txt['filter_only_session'], '"><img src="', $settings['images_url'], '/filter.gif" alt="', $txt['apply_filter'], ': ', $txt['filter_only_session'], '" /></a>
									', $error['member']['session'];

		echo '
								</td>
							</tr><tr>
								<td class="windowbg2" width="50%">&nbsp;</td>
								<td class="windowbg2" width="50%">
									<a href="', $scripturl, '?action=admin;area=logs;sa=errorlog', $context['sort_direction'] == 'down' ? ';desc' : '', ';filter=error_type;value=', $error['error_type']['type'], '" title="', $txt['apply_filter'], ': ', $txt['filter_only_type'], '"><img src="', $settings['images_url'], '/filter.gif" alt="', $txt['apply_filter'], ': ', $txt['filter_only_type'], '" /></a>
									', $txt['error_type'], ': ', $error['error_type']['name'], '
								</td>
							</tr><tr>
								<td class="windowbg2" colspan="2">
									<div style="overflow: hidden; width: 100%; white-space: nowrap;">
										<a href="', $scripturl, '?action=admin;area=logs;sa=errorlog', $context['sort_direction'] == 'down' ? ';desc' : '', ';filter=url;value=', $error['url']['href'], '" title="', $txt['apply_filter'], ': ', $txt['filter_only_url'], '"><img src="', $settings['images_url'], '/filter.gif" alt="', $txt['apply_filter'], ': ', $txt['filter_only_url'], '" /></a>
										<a href="', $error['url']['html'], '">', $error['url']['html'], '</a>
									</div>
								</td>
							</tr><tr>
								<td class="windowbg2" colspan="2">
									<div style="float: left;"><a href="', $scripturl, '?action=admin;area=logs;sa=errorlog', $context['sort_direction'] == 'down' ? ';desc' : '', ';filter=message;value=', $error['message']['href'], '" title="', $txt['apply_filter'], ': ', $txt['filter_only_message'], '"><img src="', $settings['images_url'], '/filter.gif" alt="', $txt['apply_filter'], ': ', $txt['filter_only_message'], '" /></a></div>
									<div style="float: left; margin-left: 1ex;">', $error['message']['html'], '</div>
								</td>
							</tr>';
			if (!empty($error['file']))
				echo '
							<tr>
								<td class="windowbg2" colspan="2">
									<div style="float: left;"><a href="', $scripturl, '?action=admin;area=logs;sa=errorlog', $context['sort_direction'] == 'down' ? ';desc' : '', ';filter=file;value=', $error['file']['search'], '" title="', $txt['apply_filter'], ': ', $txt['filter_only_file'], '"><img src="', $settings['images_url'], '/filter.gif" alt="', $txt['apply_filter'], ': ', $txt['filter_only_file'], '" /></a></div>
									<div style="float: left; margin-left: 1ex;">
										', $txt['file'], ': ', $error['file']['link'], '<br />
										', $txt['line'], ': ', $error['file']['line'], '
									</div>
								</td>
							</tr>';
			echo '
						</table>
					</td>
				</tr>';
	}

	if (!empty($context['errors']))
		echo '
				<tr>
					<td colspan="2" class="windowbg2">
						<div style="float: right;"><input type="submit" value="', $txt['remove_selection'], '" onclick="lastClicked = \'remove_selection\';" /> <input type="submit" name="delall" value="', $context['has_filter'] ? $txt['remove_filtered_results'] : $txt['remove_all'], '" onclick="lastClicked = \'remove_all\';" /></div>
						<label for="check_all2"><input type="checkbox" id="check_all2" onclick="invertAll(this, this.form, \'delete[]\'); this.form.check_all1.checked = this.checked;" class="check" /> <b>', $txt['check_all'], '</b></label>
					</td>
				</tr>';
	else
		echo '
				<tr>
					<td colspan="2" class="windowbg2">', $txt['msg_alert_none'], '</td>
				</tr>';

	echo '
				<tr>
					<td colspan="2" class="catbg">
						', $txt['pages'], ': ', $context['page_index'], '
					</td>
				</tr>
			</table><br />';
	if ($context['sort_direction'] == 'down')
		echo '
			<input type="hidden" name="desc" value="1" />';
	echo '
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>';
}

function template_show_file()
{
	global $context, $settings;

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<title>', $context['file_data']['file'], '</title>
		<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
		<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/style.css" />
	</head>
	<body>
		<table border="0" cellpadding="0" cellspacing="3">';
	foreach ($context['file_data']['contents'] as $index => $line)
	{
		$line_num = $index+$context['file_data']['min'];
		$is_target = $line_num == $context['file_data']['target'];
		echo '
			<tr>
				<td align="right"', $is_target ? ' style="font-weight: bold; border: 1px solid black;border-width: 1px 0 1px 1px;">==&gt;' : '>', $line_num , ':</td>
				<td style="white-space: nowrap;', $is_target ? ' border: 1px solid black;border-width: 1px 1px 1px 0;':'','">', $line, '</td>
			</tr>';
	}
	echo '
		</table>
	</body>
</html>';
}

?>