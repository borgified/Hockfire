<?php
// Version: 2.0 RC1; ManageMembers

function template_search_members()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<form action="', $scripturl, '?action=admin;area=viewmembers" method="post" accept-charset="', $context['character_set'], '">
		<input type="hidden" name="sa" value="query" /><div class="tborder">
		<table width="100%" cellpadding="4" cellspacing="0" class="windowbg">
			<tr class="titlebg">
				<td colspan="5">', $txt['search_for'], ':</td>
			</tr>
			<tr>
				<td colspan="5" align="right"><span class="smalltext">(', $txt['wild_cards_allowed'], ')</span></td>
			</tr><tr>
				<th align="right">', $txt['member_id'], ':</th>
				<td align="center">
					<select name="types[mem_id]">
						<option value="--">&lt;</option>
						<option value="-">&lt;=</option>
						<option value="=" selected="selected">=</option>
						<option value="+">&gt;=</option>
						<option value="++">&gt;</option>
					</select>
				</td>
				<td align="left"><input type="text" name="mem_id" value="" size="6" /></td>
				<th align="right">', $txt['username'], ':</th>
				<td align="left"><input type="text" name="membername" value="" /> </td>
			</tr><tr>
				<th align="right">', $txt['age'], ':</th>
				<td align="center">
					<select name="types[age]">
						<option value="--">&lt;</option>
						<option value="-">&lt;=</option>
						<option value="=" selected="selected">=</option>
						<option value="+">&gt;=</option>
						<option value="++">&gt;</option>
					</select>
				</td>
				<td align="left"><input type="text" name="age" value="" size="6" /></td>
				<th align="right">', $txt['email_address'], ':</th>
				<td align="left"><input type="text" name="email" value="" /></td>
			</tr><tr>
				<th align="right">', $txt['member_postcount'], ':</th>
				<td align="center">
					<select name="types[posts]">
						<option value="--">&lt;</option>
						<option value="-">&lt;=</option>
						<option value="=" selected="selected">=</option>
						<option value="+">&gt;=</option>
						<option value="++">&gt;</option>
					</select>
				</td>
				<td align="left"><input type="text" name="posts" value="" size="6" /></td>
				<th align="right">', $txt['website'], ':</th>
				<td align="left"><input type="text" name="website" value="" /></td>
			</tr><tr>
				<th align="right">', $txt['date_registered'], ':</th>
				<td align="center">
					<select name="types[reg_date]">
						<option value="--">&lt;</option>
						<option value="-">&lt;=</option>
						<option value="=" selected="selected">=</option>
						<option value="+">&gt;=</option>
						<option value="++">&gt;</option>
					</select>
				</td>
				<td align="left"><input type="text" name="reg_date" value="" /> <span class="smalltext">', $txt['date_format'], '</span></td>
				<th align="right">', $txt['location'], ':</th>
				<td align="left"><input type="text" name="location" value="" /></td>
			</tr><tr>
				<th align="right">', $txt['viewmembers_online'], ':</th>
				<td align="center">
					<select name="types[last_online]">
						<option value="--">&lt;</option>
						<option value="-">&lt;=</option>
						<option value="=" selected="selected">=</option>
						<option value="+">&gt;=</option>
						<option value="++">&gt;</option>
					</select>
				</td>
				<td align="left"><input type="text" name="last_online" value="" /> <span class="smalltext">', $txt['date_format'], '</span></td>
				<th align="right">', $txt['ip_address'], ':</th>
				<td align="left"><input type="text" name="ip" value="" /></td>
			</tr><tr>
				<th align="right">', $txt['gender'], ':</th>
				<td align="left" colspan="2">
					<label for="gender-0"><input type="checkbox" name="gender[]" value="0" id="gender-0" checked="checked" class="check" /> ', $txt['undefined_gender'], '</label>&nbsp;&nbsp;
					<label for="gender-1"><input type="checkbox" name="gender[]" value="1" id="gender-1" checked="checked" class="check" /> ', $txt['male'], '</label>&nbsp;&nbsp;
					<label for="gender-2"><input type="checkbox" name="gender[]" value="2" id="gender-2" checked="checked" class="check" /> ', $txt['female'], '</label>
				</td>
				<th align="right">', $txt['messenger_address'], ':</th>
				<td align="left"><input type="text" name="messenger" value="" /></td>
			</tr><tr>
				<th align="right">', $txt['activation_status'], ':</th>
				<td align="left" colspan="2">
					<label for="activated-0"><input type="checkbox" name="activated[]" value="1" id="activated-0" checked="checked" class="check" /> ', $txt['activated'], '</label>&nbsp;&nbsp;
					<label for="activated-1"><input type="checkbox" name="activated[]" value="0" id="activated-1" checked="checked" class="check" /> ', $txt['not_activated'], '</label>
				</td>
			</tr>
		</table></div>

		<table width="100%" cellpadding="0" cellspacing="0" class="tborder" style="margin-top: 2ex;">
			<tr class="catbg3">
				<td colspan="2" height="28"><b>', $txt['member_part_of_these_membergroups'], ':</b></td>
			</tr>
			<tr>
				<td class="windowbg" width="50%" valign="top">
					<table width="100%" cellpadding="3" cellspacing="1" border="0" >
						<tr class="titlebg">
							<th>', $txt['membergroups'], '</th>
							<th width="40">', $txt['primary'], '</th>
							<th width="40">', $txt['additional'], '</th>
						</tr>';

			foreach ($context['membergroups'] as $membergroup)
				echo '
						<tr class="windowbg2">
							<td>', $membergroup['name'], '</td>
							<td align="center">
								<input type="checkbox" name="membergroups[1][]" value="', $membergroup['id'], '" checked="checked" class="check" />
							</td>
							<td align="center">
								', $membergroup['can_be_additional'] ? '<input type="checkbox" name="membergroups[2][]" value="' . $membergroup['id'] . '" checked="checked" class="check" />' : '', '
							</td>
						</tr>';

			echo '
						<tr class="windowbg2">
							<td><em>', $txt['check_all'], '</em></td>
							<td align="center"><input type="checkbox" onclick="invertAll(this, this.form, \'membergroups[1]\');" checked="checked" class="check" /></td>
							<td align="center"><input type="checkbox" onclick="invertAll(this, this.form, \'membergroups[2]\');" checked="checked" class="check" /></td>
						</tr>
					</table>
				</td>
				<td class="windowbg" valign="top">

					<table width="100%" cellpadding="3" cellspacing="1" border="0">
						<tr class="titlebg">
							<th colspan="2">', $txt['membergroups_postgroups'], '</th>
						</tr>';

			foreach ($context['postgroups'] as $postgroup)
				echo '
						<tr class="windowbg2">
							<td>', $postgroup['name'], '</td>
							<td width="40" align="center">
								<input type="checkbox" name="postgroups[]" value="', $postgroup['id'], '" checked="checked" class="check" />
							</td>
						</tr>';

			echo '
						<tr class="windowbg2">
							<td><em>', $txt['check_all'], '</em></td>
							<td align="center"><input type="checkbox" onclick="invertAll(this, this.form, \'postgroups[]\');" checked="checked" class="check" /></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>

		<div align="center" style="margin: 2ex;"><input type="submit" value="', $txt['search'], '" /></div>
	</form>';
}

function template_admin_browse()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	template_show_list('approve_list');

	// If we have lots of outstanding members try and make the admin's life easier.
	if ($context['approve_list']['total_num_items'] > 20)
	{
		echo '
	<form action="', $scripturl, '?action=admin;area=viewmembers" method="post" accept-charset="', $context['character_set'], '" name="postFormOutstanding" id="postFormOutstanding" onsubmit="return onOutstandingSubmit();">
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			function onOutstandingSubmit()
			{
				if (document.forms.postFormOutstanding.todo.value == "")
					return;

				var message = "";
				if (document.forms.postFormOutstanding.todo.value.indexOf("delete") != -1)
					message = "', $txt['admin_browse_w_delete'], '";
				else if (document.forms.postFormOutstanding.todo.value.indexOf("reject") != -1)
					message = "', $txt['admin_browse_w_reject'], '";
				else if (document.forms.postFormOutstanding.todo.value == "remind")
					message = "', $txt['admin_browse_w_remind'], '";
				else
					message = "', $context['browse_type'] == 'approve' ? $txt['admin_browse_w_approve'] : $txt['admin_browse_w_activate'], '";

				if (confirm(message + " ', $txt['admin_browse_outstanding_warn'], '"))
					return true;
				else
					return false;
			}
		// ]]></script>
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="100%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['admin_browse_outstanding'], '</td>
			</tr>
			<tr class="windowbg2">
				<td align="left" width="50%">
					', $txt['admin_browse_outstanding_days_1'], ':
				</td>
				<td align="left">
					<input type="text" name="time_passed" value="14" maxlength="4" size="3" /> ', $txt['admin_browse_outstanding_days_2'], '.
				</td>
			</tr>
			<tr class="windowbg2">
				<td align="left" width="50%">
					', $txt['admin_browse_outstanding_perform'], ':
				</td>
				<td align="left">
					<select name="todo">
						', $context['browse_type'] == 'activate' ? '
						<option value="ok">' . $txt['admin_browse_w_activate'] . '</option>' : '', '
						<option value="okemail">', $context['browse_type'] == 'approve' ? $txt['admin_browse_w_approve'] : $txt['admin_browse_w_activate'], ' ', $txt['admin_browse_w_email'], '</option>', $context['browse_type'] == 'activate' ? '' : '
						<option value="require_activation">' . $txt['admin_browse_w_approve_require_activate'] . '</option>', '
						<option value="reject">', $txt['admin_browse_w_reject'], '</option>
						<option value="rejectemail">', $txt['admin_browse_w_reject'], ' ', $txt['admin_browse_w_email'], '</option>
						<option value="delete">', $txt['admin_browse_w_delete'], '</option>
						<option value="deleteemail">', $txt['admin_browse_w_delete'], ' ', $txt['admin_browse_w_email'], '</option>', $context['browse_type'] == 'activate' ? '
						<option value="remind">' . $txt['admin_browse_w_remind'] . '</option>' : '', '
					</select>
				</td>
			</tr>
			<tr class="windowbg2">
				<td align="right" colspan="2">
					<input type="submit" value="', $txt['admin_browse_outstanding_go'], '" />
					<input type="hidden" name="type" value="', $context['browse_type'], '" />
					<input type="hidden" name="sort" value="', $context['approve_list']['sort']['id'], '" />
					<input type="hidden" name="start" value="', $context['approve_list']['start'], '" />
					<input type="hidden" name="orig_filter" value="', $context['current_filter'], '" />
					<input type="hidden" name="sa" value="approve" />', !empty($context['approve_list']['sort']['desc']) ? '
					<input type="hidden" name="desc" value="1" />' : '', '
				</td>
			</tr>
		</table>
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';
	}
}

?>