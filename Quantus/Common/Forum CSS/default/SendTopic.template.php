<?php
// Version: 2.0 RC1; SendTopic

//------------------------------------------------------------------------------
/*	This template contains two humble sub templates - main. Its job is pretty
	simple: it collects the information we need to actually send the topic.

	The main sub template gets shown from:
		'?action=emailuser;sa=sendtopic;topic=##.##'
	And should submit to:
		'?action=emailuser;sa=sendtopic;topic=' . $context['current_topic'] . '.' . $context['start']
	It should send the following fields:
		y_name: sender's name.
		y_email: sender's email.
		comment: any additional comment.
		r_name: receiver's name.
		r_email: receiver's email address.
		send: this just needs to be set, as by the submit button.
		sc: the session id, or $context['session_id'].

	The report sub template gets shown from:
		'?action=reporttm;topic=##.##;msg=##'
	It should submit to:
		'?action=reporttm;topic=' . $context['current_topic'] . '.' . $context['start']
	It only needs to send the following fields:
		comment: an additional comment to give the moderator.
		sc: the session id, or $context['session_id'].
*/

// This is where we get information about who they want to send the topic to, etc.
function template_main()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<form action="', $scripturl, '?action=emailuser;sa=sendtopic;topic=', $context['current_topic'], '.', $context['start'], '" method="post" accept-charset="', $context['character_set'], '">
			<table width="400" cellpadding="3" cellspacing="0" border="0" class="tborder" align="center">
				<tr class="titlebg">
					<td align="left" colspan="2">
						<img src="', $settings['images_url'], '/email_sm.gif" alt="" />
						', $context['page_title'], '
					</td>
				</tr>';

	// Just show all the input boxes, in a line...
	echo '
				<tr class="windowbg">
					<td align="right"><b>', $txt['sendtopic_sender_name'], ':</b></td>
					<td align="left"><input type="text" name="y_name" size="24" maxlength="40" value="', $context['user']['name'], '" /></td>
				</tr>
				<tr class="windowbg">
					<td align="right"><b>', $txt['sendtopic_sender_email'], ':</b></td>
					<td align="left"><input type="text" name="y_email" size="24" maxlength="50" value="', $context['user']['email'], '" /></td>
				</tr>
				<tr class="windowbg">
					<td align="right"><b>', $txt['sendtopic_comment'], ':</b></td>
					<td align="left"><input type="text" name="comment" size="24" maxlength="100" /></td>
				</tr>
				<tr class="windowbg">
					<td align="center" colspan="2"><hr width="100%" size="1" class="hrcolor" /></td>
				</tr>
				<tr class="windowbg">
					<td align="right"><b>', $txt['sendtopic_receiver_name'], ':</b></td>
					<td align="left"><input type="text" name="r_name" size="24" maxlength="40" /></td>
				</tr>
				<tr class="windowbg">
					<td align="right"><b>', $txt['sendtopic_receiver_email'], ':</b></td>
					<td align="left"><input type="text" name="r_email" size="24" maxlength="50" /></td>
				</tr>
				<tr class="windowbg">
					<td align="center" colspan="2"><br /><input type="submit" name="send" value="', $txt['sendtopic_send'], '" /></td>
				</tr>
			</table>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>';
}

// Send an email to a user!
function template_custom_email()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<form action="', $scripturl, '?action=emailuser;sa=email" method="post" accept-charset="', $context['character_set'], '">
			<table width="60%" cellpadding="3" cellspacing="0" border="0" class="tborder" align="center">
				<tr class="titlebg">
					<td align="left" colspan="2">
						<img src="', $settings['images_url'], '/email_sm.gif" alt="" />
						', $context['page_title'], '
					</td>
				</tr>
				<tr class="windowbg">
					<td width="30%"><b>', $txt['sendtopic_receiver_name'], ':</b></td>
					<td>', $context['recipient']['link'], '</td>
				</tr>';

	// Can the user see the persons email?
	if ($context['can_view_receipient_email'])
		echo '
				<tr class="windowbg">
					<td width="30%"><b>', $txt['sendtopic_receiver_email'], ':</b></td>
					<td>', $context['recipient']['email_link'], '</td>
				</tr>
				<tr class="windowbg">
					<td colspan="2">
						<hr />
					</td>
				</tr>';

	// If it's a guest we need their details.
	if ($context['user']['is_guest'])
		echo '
				<tr class="windowbg" valign="top">
					<td width="30%"><b>', $txt['sendtopic_sender_name'], ':</b></td>
					<td><input type="text" name="y_name" size="24" maxlength="40" value="', $context['user']['name'], '" /></td>
				</tr>
				<tr class="windowbg" valign="top">
					<td width="30%">
						<b>', $txt['sendtopic_sender_email'], ':</b>
						<div class="smalltext">', $txt['send_email_disclosed'], '</div>
					</td>
					<td><input type="text" name="y_email" size="24" maxlength="50" value="', $context['user']['email'], '" /></td>
				</tr>';
	// Otherwise show the user that we know their email.
	else
		echo '
				<tr class="windowbg" valign="top">
					<td width="30%">
						<b>', $txt['sendtopic_sender_email'], ':</b>
						<div class="smalltext">', $txt['send_email_disclosed'], '</div>
					</td>
					<td><i>', $context['user']['email'], '</i></td>
				</tr>';

	echo '
				<tr class="windowbg">
					<td width="30%"><b>', $txt['send_email_subject'], ':</b></td>
					<td><input type="text" name="email_subject" size="50" maxlength="100" /></td>
				</tr>
				<tr class="windowbg" valign="top">
					<td width="30%">
						<b>', $txt['message'], ':</b>
					</td>
					<td>
						<textarea name="email_body" rows="10" cols="20" style="width: 90%;"></textarea>
					</td>
				</tr>
				<tr class="windowbg">
					<td align="center" colspan="2"><br /><input type="submit" name="send" value="', $txt['sendtopic_send'], '" /></td>
				</tr>
			</table>';

	foreach ($context['form_hidden_vars'] as $key => $value)
		echo '
			<input type="hidden" name="', $key, '" value="', $value, '" />';

	echo '
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>';
}

function template_report()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<form action="', $scripturl, '?action=reporttm;topic=', $context['current_topic'], '.', $context['start'], '" method="post" accept-charset="', $context['character_set'], '">
		<input type="hidden" name="msg" value="' . $context['message_id'] . '" />
		<table border="0" width="80%" cellspacing="0" class="tborder" align="center" cellpadding="4">
			<tr class="titlebg">
				<td>', $txt['report_to_mod'], '</td>
			</tr><tr class="windowbg">
				<td style="padding-bottom: 3ex;" align="center">
					<div style="margin-top: 1ex; margin-bottom: 3ex;" align="left">', $txt['report_to_mod_func'], '</div>
					', $txt['enter_comment'], ': <input type="text" name="comment" size="50" />
					<input type="submit" name="submit" value="', $txt['rtm10'], '" style="margin-left: 1ex;" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';
}

?>