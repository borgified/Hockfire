<?php
// Version: 2.0 RC1; ManageBans

function template_ban_edit()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '<br />
	<table border="0" align="center" cellspacing="1" cellpadding="4" class="tborder" width="60%">
		<tr class="catbg">
			<td>', $context['ban']['is_new'] ? $txt['ban_add_new'] : $txt['ban_edit'] . ' \'' . $context['ban']['name'] . '\'', '</td>
		</tr><tr class="windowbg2">
			<td align="center">
				<form action="', $scripturl, '?action=admin;area=ban;sa=edit" method="post" accept-charset="', $context['character_set'], '" onsubmit="if (this.ban_name.value == \'\') {alert(\'', $txt['ban_name_empty'], '\'); return false;} if (this.partial_ban.checked &amp;&amp; !(this.cannot_post.checked || this.cannot_register.checked || this.cannot_login.checked)) {alert(\'', $txt['ban_restriction_empty'], '\'); return false;}">
					<table cellpadding="4">
						<tr>
							<th align="right">', $txt['ban_name'], ':</th>
							<td align="left"><input type="text" name="ban_name" value="', $context['ban']['name'], '" size="50" /></td>
						</tr><tr>
							<th align="right" valign="top">', $txt['ban_expiration'], ':</th>
							<td align="left"><input type="radio" name="expiration" value="never" id="never_expires" onclick="updateStatus();"', $context['ban']['expiration']['status'] == 'never' ? ' checked="checked"' : '', ' class="check" />&nbsp;&nbsp;<label for="never_expires">', $txt['never'], '</label><br />
							<input type="radio" name="expiration" value="one_day" id="expires_one_day" onclick="updateStatus();"', $context['ban']['expiration']['status'] == 'still_active_but_we_re_counting_the_days' ? ' checked="checked"' : '', ' class="check" />&nbsp;&nbsp;<label for="expires_one_day">', $txt['ban_will_expire_within'], '</label>: <input type="text" name="expire_date" id="expire_date" size="3" value="', $context['ban']['expiration']['days'], '" /> ', $txt['ban_days'], '<br />
							<input type="radio" name="expiration" value="expired" id="already_expired" onclick="updateStatus();"', $context['ban']['expiration']['status'] == 'expired' ? ' checked="checked"' : '', ' class="check" />&nbsp;&nbsp;<label for="already_expired">', $txt['ban_expired'], '</label>
							</td>
						</tr><tr>
							<th align="right" valign="bottom">', $txt['ban_reason'], ':</th>
							<td align="left">
								<div class="smalltext">', $txt['ban_reason_desc'], '</div>
								<input type="text" name="reason" value="', $context['ban']['reason'], '" size="50" />
							</td>
						</tr><tr>
							<th align="right" valign="middle">', $txt['ban_notes'], ':</th>
							<td align="left">
								<div class="smalltext">', $txt['ban_notes_desc'], '</div>
								<textarea name="notes" cols="50" rows="3">', $context['ban']['notes'], '</textarea>
							</td>
						</tr><tr>
							<th align="right" valign="top">', $txt['ban_restriction'], ':</th>
							<td align="left">
								<input type="radio" name="full_ban" id="full_ban" value="1" onclick="updateStatus();"', $context['ban']['cannot']['access'] ? ' checked="checked"' : '', ' class="check" />&nbsp;&nbsp;<label for="full_ban">', $txt['ban_full_ban'], '</label><br />
								<input type="radio" name="full_ban" id="partial_ban" value="0" onclick="updateStatus();"', !$context['ban']['cannot']['access'] ? ' checked="checked"' : '', ' class="check" />&nbsp;&nbsp;<label for="partial_ban">', $txt['ban_partial_ban'], '</label><br />
								&nbsp;&nbsp;&nbsp;<input type="checkbox" name="cannot_post" id="cannot_post" value="1"', $context['ban']['cannot']['post'] ? ' checked="checked"' : '', ' class="check" /> <label for="cannot_post">', $txt['ban_cannot_post'], '</label> (<a href="', $scripturl, '?action=helpadmin;help=ban_cannot_post" onclick="return reqWin(this.href);">?</a>)<br />
								&nbsp;&nbsp;&nbsp;<input type="checkbox" name="cannot_register" id="cannot_register" value="1"', $context['ban']['cannot']['register'] ? ' checked="checked"' : '', ' class="check" /> <label for="cannot_register">', $txt['ban_cannot_register'], '</label><br />
								&nbsp;&nbsp;&nbsp;<input type="checkbox" name="cannot_login" id="cannot_login" value="1"', $context['ban']['cannot']['login'] ? ' checked="checked"' : '', ' class="check" /> <label for="cannot_login">', $txt['ban_cannot_login'], '</label><br />
							</td>
						</tr>';
	if (!empty($context['ban_suggestions']))
	{
		echo '
						<tr>
							<th align="right" valign="top">', $txt['ban_triggers'], ':</th>
							<td>
								<table cellpadding="4">
									<tr>
										<td valign="bottom"><input type="checkbox" name="ban_suggestion[]" id="main_ip_check" value="main_ip" class="check" /></td>
										<td align="left" valign="top">
											', $txt['ban_on_ip'], ':<br />
											<input type="text" name="main_ip" value="', $context['ban_suggestions']['main_ip'], '" size="50" onfocus="document.getElementById(\'main_ip_check\').checked = true;" />
										</td>
									</tr><tr>';
		if (empty($modSettings['disableHostnameLookup']))
			echo '
										<td valign="bottom"><input type="checkbox" name="ban_suggestion[]" id="hostname_check" value="hostname" class="check" /></td>
										<td align="left" valign="top">
											', $txt['ban_on_hostname'], ':<br />
											<input type="text" name="hostname" value="', $context['ban_suggestions']['hostname'], '" size="50" onfocus="document.getElementById(\'hostname_check\').checked = true;" />
										</td>
									</tr><tr>';
		echo '
										<td valign="bottom"><input type="checkbox" name="ban_suggestion[]" id="email_check" value="email" class="check" /></td>
										<td align="left" valign="top">
											', $txt['ban_on_email'], ':<br />
											<input type="text" name="email" value="', $context['ban_suggestions']['email'], '" size="50" onfocus="document.getElementById(\'email_check\').checked = true;" />
										</td>
									</tr><tr>
										<td valign="bottom"><input type="checkbox" name="ban_suggestion[]" id="user_check" value="user" class="check" /></td>
										<td align="left" valign="top">
											', $txt['ban_on_username'], ':<br />';
		if (empty($context['ban_suggestions']['member']['id']))
			echo '
											<input type="text" name="user" id="user" value="" size="40" />';
		else
			echo '
											', $context['ban_suggestions']['member']['link'], '
											<input type="hidden" name="bannedUser" value="', $context['ban_suggestions']['member']['id'], '" />';
		echo '
										</td>
									</tr>';
		if (!empty($context['ban_suggestions']['message_ips']))
		{
			echo '
									<tr>
										<th align="left" colspan="2"><br />', $txt['ips_in_messages'], ':</th>
									</tr>';
			foreach ($context['ban_suggestions']['message_ips'] as $ip)
				echo '
									<tr>
										<td><input type="checkbox" name="ban_suggestion[ips][]" value="', $ip, '" class="check" /></td>
										<td align="left">', $ip, '</td>
									</tr>';
		}
		if (!empty($context['ban_suggestions']['error_ips']))
		{
			echo '
									<tr>
										<th align="left" colspan="2"><br />', $txt['ips_in_errors'], ':</th>
									</tr>';
			foreach ($context['ban_suggestions']['error_ips'] as $ip)
				echo '
									<tr>
										<td><input type="checkbox" name="ban_suggestion[ips][]" value="', $ip, '" class="check" /></td>
										<td align="left">', $ip, '</td>
									</tr>';
		}
		echo '
								</table>
							</td>
						</tr>';
	}
	echo '
						<tr>
							<td colspan="2" align="right"><input type="submit" name="', $context['ban']['is_new'] ? 'add_ban' : 'modify_ban', '" value="', $context['ban']['is_new'] ? $txt['ban_add'] : $txt['ban_modify'], '" /></td>
						</tr>
					</table>', $context['ban']['is_new'] ? '<br />
					' . $txt['ban_add_notes'] : '', '
					<input type="hidden" name="old_expire" value="', $context['ban']['expiration']['days'], '" />
					<input type="hidden" name="bg" value="', $context['ban']['id'], '" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>';
	if (!$context['ban']['is_new'] && empty($context['ban_suggestions']))
	{
		echo '
		<tr>
			<td align="center" style="padding: 0px;">
				<form action="', $scripturl, '?action=admin;area=ban;sa=edit" method="post" accept-charset="', $context['character_set'], '" style="padding: 0px;margin: 0px;" onsubmit="return confirm(\'', $txt['ban_remove_selected_triggers_confirm'], '\');">
					<table cellpadding="4" cellspacing="1" width="100%"><tr class="titlebg">
						<td width="65%" align="left">', $txt['ban_banned_entity'], '</td>
						<td width="15%" align="center">', $txt['ban_hits'], '</td>
						<td width="15%" align="center">', $txt['ban_actions'], '</td>
						<td width="5%" align="center"><input type="checkbox" onclick="invertAll(this, this.form, \'ban_items\');" class="check" /></td>
					</tr>';
		if (empty($context['ban_items']))
			echo '
					<tr class="windowbg2"><td colspan="4">(', $txt['ban_no_triggers'], ')</td></tr>';
		else
		{
			foreach ($context['ban_items'] as $ban_item)
			{
				echo '
						<tr class="windowbg2" align="left">
							<td>';
				if ($ban_item['type'] == 'ip')
					echo '<b>', $txt['ip'], ':</b>&nbsp;', $ban_item['ip'];
				elseif ($ban_item['type'] == 'hostname')
					echo '<b>', $txt['hostname'], ':</b>&nbsp;', $ban_item['hostname'];
				elseif ($ban_item['type'] == 'email')
					echo '<b>', $txt['email'], ':</b>&nbsp;', $ban_item['email'];
				elseif ($ban_item['type'] == 'user')
					echo '<b>', $txt['username'], ':</b>&nbsp;', $ban_item['user']['link'];
				echo '
						</td>
						<td class="windowbg" align="center">', $ban_item['hits'], '</td>
						<td class="windowbg" align="center"><a href="', $scripturl, '?action=admin;area=ban;sa=edittrigger;bg=', $context['ban']['id'], ';bi=', $ban_item['id'], '">', $txt['ban_edit_trigger'], '</a></td>
						<td align="center" class="windowbg2"><input type="checkbox" name="ban_items[]" value="', $ban_item['id'], '" class="check" /></td>
					</tr>';
			}
		}
		echo '
					<tr class="catbg3">
						<td colspan="4" align="right">
							<div style="float: left;">
								[<a href="', $scripturl, '?action=admin;area=ban;sa=edittrigger;bg=', $context['ban']['id'], '"><b>', $txt['ban_add_trigger'], '</b></a>]
							</div>
							<input type="submit" name="remove_selection" value="', $txt['ban_remove_selected_triggers'], '" />
							</div>
						</td>
					</tr>
					</table>
					<input type="hidden" name="bg" value="', $context['ban']['id'], '" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>';

	}
	echo '
	</table>
	<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/suggest.js?rc1"></script>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		function updateStatus()
		{
			document.getElementById("expire_date").disabled = !document.getElementById("expires_one_day").checked;
			document.getElementById("cannot_post").disabled = document.getElementById("full_ban").checked;
			document.getElementById("cannot_register").disabled = document.getElementById("full_ban").checked;
			document.getElementById("cannot_login").disabled = document.getElementById("full_ban").checked;
		}
		add_load_event(updateStatus);

		var oAddMemberSuggest = new smc_AutoSuggest({
			sSelf: \'oAddMemberSuggest\',
			sSessionId: \'', $context['session_id'], '\',
			sSuggestId: \'user\',
			sControlId: \'user\',
			sSearchType: \'member\',
			sTextDeleteItem: \'', $txt['autosuggest_delete_item'], '\',
			bItemList: false
		});

		function onUpdateName(oAutoSuggest)
		{
			document.getElementById(\'user_check\').checked = true;
			return true;
		}
		oAddMemberSuggest.registerCallback(\'onBeforeUpdate\', \'onUpdateName\');
	// ]]></script>';
}

function template_ban_edit_trigger()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<form action="', $scripturl, '?action=admin;area=ban;sa=edit" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" align="center" cellspacing="0" cellpadding="4" class="tborder" width="60%">
			<tr class="titlebg">
				<td>', $context['ban_trigger']['is_new'] ? $txt['ban_add_trigger'] : $txt['ban_edit_trigger_title'], '</td>
			</tr>
			<tr class="windowbg">
				<td align="center">
					<table cellpadding="4">
						<tr>
							<td valign="bottom"><input type="radio" name="bantype" value="ip_ban"', $context['ban_trigger']['ip']['selected'] ? ' checked="checked"' : '', ' /></td>
							<td align="left" valign="top">
								', $txt['ban_on_ip'], ':<br />
								<input type="text" name="ip" value="', $context['ban_trigger']['ip']['value'], '" size="50" onfocus="selectRadioByName(this.form.bantype, \'ip_ban\');" />
							</td>
						</tr><tr>';
				if (empty($modSettings['disableHostnameLookup']))
				echo '
							<td valign="bottom"><input type="radio" name="bantype" value="hostname_ban"', $context['ban_trigger']['hostname']['selected'] ? ' checked="checked"' : '', ' /></td>
							<td align="left" valign="top">
								', $txt['ban_on_hostname'], ':<br />
								<input type="text" name="hostname" value="', $context['ban_trigger']['hostname']['value'], '" size="50" onfocus="selectRadioByName(this.form.bantype, \'hostname_ban\');" />
							</td>
						</tr><tr>';
				echo '
							<td valign="bottom"><input type="radio" name="bantype" value="email_ban"', $context['ban_trigger']['email']['selected'] ? ' checked="checked"' : '', ' /></td>
							<td align="left" valign="top">
								', $txt['ban_on_email'], ':<br />
								<input type="text" name="email" value="', $context['ban_trigger']['email']['value'], '" size="50" onfocus="selectRadioByName(this.form.bantype, \'email_ban\');" />
							</td>
						</tr><tr>
							<td valign="bottom"><input type="radio" name="bantype" value="user_ban"', $context['ban_trigger']['banneduser']['selected'] ? ' checked="checked"' : '', ' /></td>
							<td align="left" valign="top">
								', $txt['ban_on_username'], ':<br />
								<input type="text" name="user" id="user" value="', $context['ban_trigger']['banneduser']['value'], '" size="50" onfocus="selectRadioByName(this.form.bantype, \'user_ban\');" />
							</td>
						</tr><tr>
							<td colspan="2" align="right"><br />
								<input type="submit" name="', $context['ban_trigger']['is_new'] ? 'add_new_trigger' : 'edit_trigger', '" value="', $context['ban_trigger']['is_new'] ? $txt['ban_add_trigger_submit'] : $txt['ban_edit_trigger_submit'], '" />
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<input type="hidden" name="bi" value="' . $context['ban_trigger']['id'] . '" />
		<input type="hidden" name="bg" value="' . $context['ban_trigger']['group'] . '" />
		<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
	</form>
	<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/suggest.js?rc1"></script>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var oAddMemberSuggest = new smc_AutoSuggest({
			sSelf: \'oAddMemberSuggest\',
			sSessionId: \'', $context['session_id'], '\',
			sSuggestId: \'username\',
			sControlId: \'user\',
			sSearchType: \'member\',
			sTextDeleteItem: \'', $txt['autosuggest_delete_item'], '\',
			bItemList: false
		});

		function onUpdateName(oAutoSuggest)
		{
			selectRadioByName(oAutoSuggest.oTextHandle.form.bantype, \'user_ban\');
			return true;
		}
		oAddMemberSuggest.registerCallback(\'onBeforeUpdate\', \'onUpdateName\');
	// ]]></script>';
}

?>