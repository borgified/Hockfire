<?php
// Version: 2.0 RC1; Notify

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
			<table border="0" width="100%" cellspacing="0" cellpadding="3" class="tborder">
				<tr class="titlebg">
					<td>', $txt['notify'], '</td>
				</tr>
				<tr class="windowbg">
					<td>
						', $context['notification_set'] ? $txt['notify_deactivate'] : $txt['notify_request'], '<br />
						<br />
						<b><a href="', $scripturl, '?action=notify;sa=', $context['notification_set'] ? 'off' : 'on', ';topic=', $context['current_topic'], '.', $context['start'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['yes'], '</a> - <a href="', $context['topic_href'], '">', $txt['no'], '</a></b>
					</td>
				</tr>
			</table>';
}

function template_notify_board()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
			<table border="0" width="100%" cellspacing="0" cellpadding="3" class="tborder">
				<tr class="titlebg">
					<td>', $txt['notify'], '</td>
				</tr>
				<tr class="windowbg">
					<td>
						', $context['notification_set'] ? $txt['notifyboard_turnoff'] : $txt['notifyboard_turnon'], '<br />
						<br />
						<b><a href="', $scripturl, '?action=notifyboard;sa=', $context['notification_set'] ? 'off' : 'on', ';board=', $context['current_board'], '.', $context['start'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['yes'], '</a> - <a href="', $context['board_href'], '">', $txt['no'], '</a></b>
					</td>
				</tr>
			</table>';
}

?>