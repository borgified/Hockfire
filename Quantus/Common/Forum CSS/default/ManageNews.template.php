<?php
// Version: 2.0 RC1; ManageNews

// Form for editing current news on the site.
function template_edit_news()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=admin;area=news;sa=editnews" method="post" accept-charset="', $context['character_set'], '" name="postmodify" id="postmodify">
			<table width="85%" cellpadding="3" cellspacing="0" border="0" align="center" class="tborder">
				<tr class="titlebg">
					<th width="50%">', $txt['admin_edit_news'], '</th>
					<th align="left" width="45%">', $txt['preview'], '</th>
					<th align="center" width="5%"><input type="checkbox" class="check" onclick="invertAll(this, this.form);" /></th>
				</tr>';

	// Loop through all the current news items so you can edit/remove them.
	foreach ($context['admin_current_news'] as $admin_news)
		echo '
				<tr class="windowbg2">
					<td align="center">
						<div style="margin-bottom: 2ex;"><textarea rows="3" cols="65" name="news[]" style="width: 85%;">', $admin_news['unparsed'], '</textarea></div>
					</td><td align="left" valign="top">
						<div style="overflow: auto; width: 100%; height: 10ex;">', $admin_news['parsed'], '</div>
					</td><td align="center">
						<input type="checkbox" name="remove[]" value="', $admin_news['id'], '" class="check" />
					</td>
				</tr>';

	// This provides an empty text box to add a news item to the site.
	echo '
				<tr class="windowbg2">
					<td align="center">
						<div id="moreNewsItems"></div><div id="moreNewsItems_link" style="display: none;"><a href="javascript:void(0);" onclick="addNewsItem(); return false;">', $txt['editnews_clickadd'], '</a></div>
						<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
							document.getElementById("moreNewsItems_link").style.display = "";

							function addNewsItem()
							{
								setOuterHTML(document.getElementById("moreNewsItems"), \'<div style="margin-bottom: 2ex;"><textarea rows="3" cols="65" name="news[]" style="width: 85%;"><\' + \'/textarea><\' + \'/div><div id="moreNewsItems"><\' + \'/div>\');
							}
						// ]]></script>
						<noscript>
							<div style="margin-bottom: 2ex;"><textarea rows="3" cols="65" style="width: 85%;" name="news[]"></textarea></div>
						</noscript>
					</td>
					<td colspan="2" valign="bottom" align="right" style="padding: 1ex;">
						<input type="submit" name="save_items" value="', $txt['save'], '" /> <input type="submit" name="delete_selection" value="', $txt['editnews_remove_selected'], '" onclick="return confirm(\'', $txt['editnews_remove_confirm'], '\');" />
					</td>
				</tr>
			</table>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>';
}

function template_email_members()
{
	global $context, $settings, $options, $txt, $scripturl;

	// This is some javascript for the simple/advanced toggling stuff.
	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		function toggleAdvanced(mode)
		{
			// What styles are we doing?
			var divStyle = mode ? "" : "none";

			for (var i = 0; i < 20; i++)
				if (document.getElementById("advanced_div_" + i))
					document.getElementById("advanced_div_" + i).style.display = divStyle;

			document.getElementById("gosimple").style.display = divStyle;
			document.getElementById("goadvanced").style.display = mode ? "none" : "";
		}
	// ]]></script>';

	echo '
		<form action="', $scripturl, '?action=admin;area=news;sa=mailingcompose" method="post" accept-charset="', $context['character_set'], '">
			<table width="600" cellpadding="5" cellspacing="0" border="0" align="center" class="tborder">
				<tr class="titlebg">
					<td colspan="2">', $txt['admin_newsletters'], '</td>
				</tr>
				<tr class="windowbg">
					<td colspan="2" class="smalltext" style="padding: 2ex;">', $txt['admin_news_select_recipients'], '</td>
				</tr>
				<tr class="windowbg2" valign="top">
					<td width="50%">
						<b>', $txt['admin_news_select_group'], ':</b>
						<div class="smalltext">', $txt['admin_news_select_group_desc'], '</div>
					</td>
					<td width="50%">';

	foreach ($context['groups'] as $group)
				echo '
						<label for="groups_', $group['id'], '"><input type="checkbox" name="groups[', $group['id'], ']" id="groups_', $group['id'], '" value="', $group['id'], '" checked="checked" class="check" /> ', $group['name'], '</label> <i>(', $group['member_count'], ')</i><br />';

	echo '
						<br />
						<label for="checkAllGroups"><input type="checkbox" id="checkAllGroups" checked="checked" onclick="invertAll(this, this.form, \'groups\');" class="check" /> <i>', $txt['check_all'], '</i></label><br />
					</td>
				</tr>
				<tr class="windowbg2" valign="middle" id="advanced_select_div" style="display: none;">
					<td colspan="2">
						<a href="#" onclick="toggleAdvanced(1); return false;" id="goadvanced"><img src="', $settings['images_url'], '/selected.gif" alt="', $txt['advanced'], '" />&nbsp;<b>', $txt['advanced'], '</b></a>
						<a href="#" onclick="toggleAdvanced(0); return false;" id="gosimple" style="display: none;"><img src="', $settings['images_url'], '/sort_down.gif" alt="', $txt['simple'], '" />&nbsp;<b>', $txt['simple'], '</b></a>
					</td>
				</tr>
				<tr class="windowbg2" valign="top" id="advanced_div_1">
					<td width="50%">
						<b>', $txt['admin_news_select_email'], ':</b>
						<div class="smalltext">', $txt['admin_news_select_email_desc'], '</div>
					</td>
					<td width="50%">
						<textarea name="emails" rows="5" cols="30" style="width: 98%;"></textarea>
					</td>
				</tr>
				<tr class="windowbg2" valign="top" id="advanced_div_2">
					<td width="50%">
						<b>', $txt['admin_news_select_members'], ':</b>
						<div class="smalltext">', $txt['admin_news_select_members_desc'], '</div>
					</td>
					<td width="50%">
						<input type="text" name="members" id="members" value="" size="30" />
						<div id="members_container"></div>
					</td>
				</tr>
				<tr class="windowbg2" valign="top" id="advanced_div_3">
					<td colspan="2">
						<hr />
					</td>
				</tr>
				<tr class="windowbg2" valign="top" id="advanced_div_4">
					<td width="50%">
						<b>', $txt['admin_news_select_excluded_groups'], ':</b>
						<div class="smalltext">', $txt['admin_news_select_excluded_groups_desc'], '</div>
					</td>
					<td width="50%">';

	foreach ($context['groups'] as $group)
				echo '
						<label for="exclude_groups_', $group['id'], '"><input type="checkbox" name="exclude_groups[', $group['id'], ']" id="exclude_groups_', $group['id'], '" value="', $group['id'], '" class="check" /> ', $group['name'], '</label> <i>(', $group['member_count'], ')</i><br />';

	echo '
						<br />
						<label for="checkAllGroupsExclude"><input type="checkbox" id="checkAllGroupsExclude" onclick="invertAll(this, this.form, \'exclude_groups\');" class="check" /> <i>', $txt['check_all'], '</i></label><br />
					</td>
				</tr>
				<tr class="windowbg2" valign="top" id="advanced_div_5">
					<td width="50%">
						<b>', $txt['admin_news_select_excluded_members'], ':</b>
						<div class="smalltext">', $txt['admin_news_select_excluded_members_desc'], '</div>
					</td>
					<td width="50%">
						<input type="text" name="exclude_members" id="exclude_members" value="" size="30" />
						<div id="exclude_members_container"></div>
					</td>
				</tr>
				<tr class="windowbg2" valign="top" id="advanced_div_6">
					<td colspan="2">
						<hr />
					</td>
				</tr>
				<tr class="windowbg2" valign="top" id="advanced_div_7">
					<td width="50%">
						<label for="email_force"><b>', $txt['admin_news_select_override_notify'], ':</b></label>
						<div class="smalltext">', $txt['email_force'], '</div>
					</td>
					<td width="50%">
						<input type="checkbox" name="email_force" id="email_force" value="1" class="check" />
					</td>
				</tr>
				<tr>
					<td colspan="2" class="windowbg2" style="padding-bottom: 1ex;" align="center">
						<input type="submit" value="', $txt['admin_next'], '" />
					</td>
				</tr>
			</table>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>';

	// Make the javascript stuff visible.
	echo '
	<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/suggest.js?rc1"></script>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		document.getElementById("advanced_select_div").style.display = "";
		toggleAdvanced(0);
		var oMemberSuggest = new smc_AutoSuggest({
			sSelf: \'oMemberSuggest\',
			sSessionId: \'', $context['session_id'], '\',
			sSuggestId: \'members\',
			sControlId: \'members\',
			sSearchType: \'member\',
			bItemList: true,
			sPostName: \'member_list\',
			sURLMask: \'action=profile;u=%item_id%\',
			sTextDeleteItem: \'', $txt['autosuggest_delete_item'], '\',
			sItemListContainerId: \'members_container\',
			aListItems: []
		});
		var oExcludeMemberSuggest = new smc_AutoSuggest({
			sSelf: \'oExcludeMemberSuggest\',
			sSessionId: \'', $context['session_id'], '\',
			sSuggestId: \'exclude_members\',
			sControlId: \'exclude_members\',
			sSearchType: \'member\',
			bItemList: true,
			sPostName: \'exclude_member_list\',
			sURLMask: \'action=profile;u=%item_id%\',
			sTextDeleteItem: \'', $txt['autosuggest_delete_item'], '\',
			sItemListContainerId: \'exclude_members_container\',
			aListItems: []
		});
	// ]]></script>';
}

function template_email_members_compose()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<form action="', $scripturl, '?action=admin;area=news;sa=mailingsend" method="post" accept-charset="', $context['character_set'], '">
			<table width="600" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
				<tr class="titlebg">
					<td>
						<a href="', $scripturl, '?action=helpadmin;help=email_members" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['admin_newsletters'], '
					</td>
				</tr><tr class="windowbg">
					<td class="smalltext" style="padding: 2ex;">', $txt['email_variables'], '</td>
				</tr><tr>
					<td class="windowbg2">
						<input type="text" name="subject" size="60" value="', $context['default_subject'], '" /><br />
						<br />
						<textarea cols="70" rows="9" name="message" class="editor">', $context['default_message'], '</textarea><br />
						<br />
						<label for="send_pm"><input type="checkbox" name="send_pm" id="send_pm" class="check" onclick="if (this.checked && ', $context['total_emails'], ' != 0 && !confirm(\'', $txt['admin_news_cannot_pm_emails_js'], '\')) return false; this.form.parse_html.disabled = this.checked; this.form.send_html.disabled = this.checked; " /> ', $txt['email_as_pms'], '</label><br />
						<label for="send_html"><input type="checkbox" name="send_html" id="send_html" class="check" onclick="this.form.parse_html.disabled = !this.checked;" /> ', $txt['email_as_html'], '</label><br />
						<label for="parse_html"><input type="checkbox" name="parse_html" id="parse_html" checked="checked" disabled="disabled" class="check" /> ', $txt['email_parsed_html'], '</label><br />
						<br />
						<div align="center"><input type="submit" value="', $txt['sendtopic_send'], '" /></div>
					</td>
				</tr>
			</table>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="hidden" name="email_force" value="', $context['email_force'], '" />
			<input type="hidden" name="total_emails" value="', $context['total_emails'], '" />
			<input type="hidden" name="max_id_member" value="', $context['max_id_member'], '" />';

	foreach ($context['recipients'] as $key => $values)
		echo '
			<input type="hidden" name="', $key, '" value="', implode(($key == 'emails' ? ';' : ','), $values), '" />';

	echo '
		</form>';
}

function template_email_members_send()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<form action="', $scripturl, '?action=admin;area=news;sa=mailingsend" method="post" accept-charset="', $context['character_set'], '" name="autoSubmit" id="autoSubmit">
			<table width="600" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
				<tr class="titlebg">
					<td>
						<a href="', $scripturl, '?action=helpadmin;help=email_members" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['admin_newsletters'], '
					</td>
				</tr><tr>
					<td class="windowbg2"><b>', $context['percentage_done'], '% ', $txt['email_done'], '</b></td>
				</tr><tr>
					<td class="windowbg2" style="padding-bottom: 1ex;" align="center">
						<input type="submit" name="b" value="', $txt['email_continue'], '" />
					</td>
				</tr>
			</table>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="hidden" name="subject" value="', $context['subject'], '" />
			<input type="hidden" name="message" value="', $context['message'], '" />
			<input type="hidden" name="start" value="', $context['start'], '" />
			<input type="hidden" name="total_emails" value="', $context['total_emails'], '" />
			<input type="hidden" name="max_id_member" value="', $context['max_id_member'], '" />
			<input type="hidden" name="send_pm" value="', $context['send_pm'], '" />
			<input type="hidden" name="send_html" value="', $context['send_html'], '" />
			<input type="hidden" name="parse_html" value="', $context['parse_html'], '" />';

	// All the things we must remember!
	foreach ($context['recipients'] as $key => $values)
		echo '
			<input type="hidden" name="', $key, '" value="', implode(($key == 'emails' ? ';' : ','), $values), '" />';

	echo '
		</form>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var countdown = 2;
			doAutoSubmit();

			function doAutoSubmit()
			{
				if (countdown == 0)
					document.forms.autoSubmit.submit();
				else if (countdown == -1)
					return;

				document.forms.autoSubmit.b.value = "', $txt['email_continue'], ' (" + countdown + ")";
				countdown--;

				setTimeout("doAutoSubmit();", 1000);
			}
		// ]]></script>';
}

?>