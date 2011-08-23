<?php
// Version: 2.0 RC1; Reminder

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<br />
	<form action="', $scripturl, '?action=reminder;sa=picktype" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" width="450" cellspacing="0" cellpadding="4" align="center" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['authentication_reminder'], '</td>
			</tr><tr class="windowbg">
				<td colspan="2" class="smalltext">', $txt['password_reminder_desc'], '</td>
			</tr><tr class="windowbg2">
				<td width="40%"><b>', $txt['user_email'], ':</b></td>
				<td><input type="text" name="user" size="30" /></td>
			</tr><tr class="windowbg2">
				<td colspan="2" align="center"><input type="submit" value="', $txt['reminder_continue'], '" /></td>
			</tr>
		</table>
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';
}

function template_reminder_pick()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<br />
	<form action="', $scripturl, '?action=reminder;sa=picktype" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" width="450" cellspacing="0" cellpadding="4" align="center" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['authentication_reminder'], '</td>
			</tr><tr class="windowbg2">
				<td colspan="2">
					<b>', $txt['authentication_options'], ':</b>
				</td>
			</tr><tr class="windowbg2">
				<td width="2%">
					<input type="radio" name="reminder_type" id="reminder_type_email" value="email" checked="checked" class="check" />
				</td>
				<td>
					<label for="reminder_type_email">', $txt['authentication_' . $context['account_type'] . '_email'], '</label>
				</td>
			</tr><tr class="windowbg2">
				<td width="2%">
					<input type="radio" name="reminder_type" id="reminder_type_secret" value="secret" class="check" />
				</td>
				<td>
					<label for="reminder_type_secret">', $txt['authentication_' . $context['account_type'] . '_secret'], '</label>
				</td>
			</tr><tr class="windowbg2">
				<td colspan="2" align="center"><input type="submit" value="', $txt['reminder_continue'], '" /></td>
			</tr>
		</table>
		<input type="hidden" name="uid" value="', $context['current_member']['id'], '" />
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';
}

function template_sent()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<br />
		<table border="0" width="80%" cellspacing="0" cellpadding="4" class="tborder" align="center">
			<tr class="titlebg">
				<td>' . $context['page_title'] . '</td>
			</tr><tr>
				<td class="windowbg" align="left" cellpadding="3" style="padding-top: 3ex; padding-bottom: 3ex;">
					' . $context['description'] . '
				</td>
			</tr>
		</table>';
}

function template_set_password()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	echo '
	<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/register.js"></script>
	<br />
	<form action="', $scripturl, '?action=reminder;sa=setpassword2" name="reminder_form" id="reminder_form" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" width="440" cellspacing="0" cellpadding="4" class="tborder" align="center">
			<tr class="titlebg">
				<td colspan="2">', $context['page_title'], '</td>
			</tr><tr class="windowbg">
				<td width="45%">
					<b>', $txt['choose_pass'], ': </b>
				</td>
				<td valign="top">
					<input type="password" name="passwrd1" id="smf_autov_pwmain" size="22" />
					<span id="smf_autov_pwmain_div" style="display: none;">
						<img id="smf_autov_pwmain_img" src="', $settings['images_url'], '/icons/field_invalid.gif" alt="*" />
					</span>
				</td>
			</tr><tr class="windowbg">
				<td width="45%"><b>', $txt['verify_pass'], ': </b></td>
				<td>
					<input type="password" name="passwrd2" id="smf_autov_pwverify" size="22" />
					<span id="smf_autov_pwverify_div" style="display: none;">
						<img id="smf_autov_pwverify_img" src="', $settings['images_url'], '/icons/field_invalid.gif" alt="*" />
					</span>
				</td>
			</tr><tr class="windowbg">
				<td colspan="2" align="right"><input type="submit" value="', $txt['save'], '" /></td>
			</tr>
		</table>
		<input type="hidden" name="code" value="', $context['code'], '" />
		<input type="hidden" name="u" value="', $context['memID'], '" />
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
	var regTextStrings = {
		"password_short": "', $txt['registration_password_short'], '",
		"password_reserved": "', $txt['registration_password_reserved'], '",
		"password_numbercase": "', $txt['registration_password_numbercase'], '",
		"password_no_match": "', $txt['registration_password_no_match'], '",
		"password_valid": "', $txt['registration_password_valid'], '"
	};
	var verificationHandle = new smfRegister("reminder_form", ', empty($modSettings['password_strength']) ? 0 : $modSettings['password_strength'], ', regTextStrings);
// ]]></script>';
}

function template_ask()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	echo '
	<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/register.js"></script>
	<br />
	<form action="', $scripturl, '?action=reminder;sa=secret2" method="post" accept-charset="', $context['character_set'], '" name="creator" id="creator">
		<table border="0" width="440" cellspacing="0" cellpadding="4" class="tborder" align="center">
			<tr class="titlebg">
				<td colspan="2">', $txt['authentication_reminder'], '</td>
			</tr><tr class="windowbg">
				<td colspan="2" class="smalltext" style="padding: 2ex;">', $context['account_type'] == 'password' ? $txt['enter_new_password'] : $txt['openid_secret_reminder'], '</td>
			</tr><tr class="windowbg2">
				<td width="45%"><b>', $txt['secret_question'], ':</b></td>
				<td>', $context['secret_question'], '</td>
			</tr><tr class="windowbg2">
				<td width="45%"><b>', $txt['secret_answer'], ':</b> </td>
				<td><input type="text" name="secret_answer" size="22" /></td>';

	if ($context['account_type'] == 'password')
		echo '

			</tr><tr class="windowbg2">
				<td width="45%">
					<b>', $txt['choose_pass'], ': </b>
				</td>
				<td valign="top">
					<input type="password" name="passwrd1" id="smf_autov_pwmain" size="22" />
					<span id="smf_autov_pwmain_div" style="display: none;">
						<img id="smf_autov_pwmain_img" src="', $settings['images_url'], '/icons/field_invalid.gif" alt="*" />
					</span>
				</td>
			</tr><tr class="windowbg2">
				<td width="45%">
					<b>', $txt['verify_pass'], ': </b>
				</td>
				<td>
					<input type="password" name="passwrd2" id="smf_autov_pwverify" size="22" />
					<span id="smf_autov_pwverify_div" style="display: none;">
						<img id="smf_autov_pwverify_img" src="', $settings['images_url'], '/icons/field_valid.gif" alt="*" />
					</span>
				</td>';

	echo '

			</tr><tr class="windowbg2">
				<td colspan="2" align="right" style="padding: 1ex;"><input type="submit" value="', $txt['save'], '" /></td>
			</tr>
		</table>

		<input type="hidden" name="uid" value="', $context['remind_user'], '" />
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';

	if ($context['account_type'] == 'password')
		echo '
<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
	var regTextStrings = {
		"password_short": "', $txt['registration_password_short'], '",
		"password_reserved": "', $txt['registration_password_reserved'], '",
		"password_numbercase": "', $txt['registration_password_numbercase'], '",
		"password_no_match": "', $txt['registration_password_no_match'], '",
		"password_valid": "', $txt['registration_password_valid'], '"
	};
	var verificationHandle = new smfRegister("creator", ', empty($modSettings['password_strength']) ? 0 : $modSettings['password_strength'], ', regTextStrings);
// ]]></script>';

}

?>