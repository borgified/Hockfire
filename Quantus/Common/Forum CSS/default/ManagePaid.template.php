<?php
// Version: 2.0 RC1; ManagePaid

// The template for adding or editing a subscription.
function template_modify_subscription()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Javascript for the duration stuff.
	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[

			function toggleDuration(toChange)
			{
				if (toChange == \'fixed\')
				{
					document.getElementById("fixed_area").style.display = "inline";
					document.getElementById("flexible_area").style.display = "none";
				}
				else
				{
					document.getElementById("fixed_area").style.display = "none";
					document.getElementById("flexible_area").style.display = "inline";

				}
			}
		// ]]></script>';

	echo '
	<form action="', $scripturl, '?action=admin;area=paidsubscribe;sa=modify;sid=', $context['sub_id'], '" method="post">
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['paid_' . $context['action_type'] . '_subscription'], '</td>
			</tr>';

	if (!empty($context['disable_groups']))
		echo '
			<tr class="windowbg">
				<td colspan="2">
					<span class="smalltext alert">', $txt['paid_mod_edit_note'], '</span>
				</td>
			</tr>';

	echo '
			<tr class="windowbg2">
				<td align="right">', $txt['paid_mod_name'], ':</td>
				<td><input type="text" name="name" value="', $context['sub']['name'], '" size="30" /></td>
			</tr><tr class="windowbg2" valign="top">
				<td align="right">', $txt['paid_mod_desc'], ':</td>
				<td>
					<textarea name="desc" rows="3" cols="40">', $context['sub']['desc'], '</textarea></td>
			</tr><tr class="windowbg2">
				<td width="50%" align="right"><label for="repeatable_check">', $txt['paid_mod_repeatable'], '</label>:</td>
				<td><input type="checkbox" name="repeatable" id="repeatable_check"', empty($context['sub']['repeatable']) ? '' : ' checked="checked"', ' class="check" /></td>
			</tr><tr class="windowbg2">
				<td width="50%" align="right"><label for="activated_check">', $txt['paid_mod_active'], '</label>:<br /><span class="smalltext">', $txt['paid_mod_active_desc'], '</span></td>
				<td><input type="checkbox" name="active" id="activated_check"', empty($context['sub']['active']) ? '' : ' checked="checked"', ' class="check" /></td>
			</tr><tr class="windowbg2">
				<td align="center" colspan="2">
					<hr />
				</td>
			</tr><tr class="windowbg2">
				<td align="right">', $txt['paid_mod_prim_group'], ':<br /><span class="smalltext">', $txt['paid_mod_prim_group_desc'], '</span></td>
				<td>
					<select name="prim_group" ', !empty($context['disable_groups']) ? 'disabled="disabled"' : '', '>
						<option value="0" ', $context['sub']['prim_group'] == 0 ? 'selected="selected"' : '', '>', $txt['paid_mod_no_group'], '</option>';

	// Put each group into the box.
	foreach ($context['groups'] as $id => $name)
		echo '
						<option value="', $id, '" ', $context['sub']['prim_group'] == $id ? 'selected="selected"' : '', '>', $name, '</option>';

	echo '
					</select>
				</td>
			</tr><tr class="windowbg2" valign="top">
				<td align="right">', $txt['paid_mod_add_groups'], ':<br /><span class="smalltext">', $txt['paid_mod_add_groups_desc'], '</span></td>
				<td>';

	// Put a checkbox in for each group
	foreach ($context['groups'] as $id => $name)
		echo '
						<label for="addgroup_', $id, '"><input type="checkbox" id="addgroup_', $id, '" name="addgroup[', $id, ']"', in_array($id, $context['sub']['add_groups']) ? ' checked="checked"' : '', ' ', !empty($context['disable_groups']) ? ' disabled="disabled"' : '', ' class="check" />&nbsp;<span class="smalltext">', $name, '</span></label><br />';

	echo '
				</td>
			</tr><tr class="windowbg2">
				<td align="right">', $txt['paid_mod_reminder'], ':<br /><span class="smalltext">', $txt['paid_mod_reminder_desc'], '</span></td>
				<td><input type="text" name="reminder" value="', $context['sub']['reminder'], '" size="6" /></td>
			</tr><tr class="windowbg2" valign="top">
				<td align="right">
					', $txt['paid_mod_email'], ':<br />
					<span class="smalltext">', $txt['paid_mod_email_desc'], '</span>
				</td>
				<td>
					<textarea name="emailcomplete" rows="6" cols="40">', $context['sub']['email_complete'], '</textarea>
				</td>
			</tr><tr class="windowbg2">
				<td align="center" colspan="2">
					<hr />
				</td>
			</tr><tr class="windowbg">
				<td colspan="2" align="left">
					<input type="radio" name="duration_type" id="duration_type_fixed" value="fixed" ', empty($context['sub']['duration']) || $context['sub']['duration'] == 'fixed' ? 'checked="checked"' : '', ' class="check" onclick="toggleDuration(\'fixed\');" />
					<b>', $txt['paid_mod_fixed_price'], '</b>
				</td>
			</tr><tr class="windowbg2">
				<td align="left" colspan="2">
					<div id="fixed_area" ', empty($context['sub']['duration']) || $context['sub']['duration'] == 'fixed' ? '' : 'style="display: none;"', '>
						<table border="0" cellspacing="0" cellpadding="4" align="center" width="100%">
							<tr class="windowbg2">
								<td align="right" width="50%">', $txt['paid_cost'], ' (', str_replace('%1.2f', '', $modSettings['paid_currency_symbol']), '):</td>
								<td><input type="text" name="cost" value="', empty($context['sub']['cost']['fixed']) ? '0' : $context['sub']['cost']['fixed'], '" size="4" /></td>
							</tr><tr class="windowbg2">
								<td align="right">', $txt['paid_mod_span'], ':</td>
								<td>
									<input type="text" name="span_value" value="', $context['sub']['span']['value'], '" size="4" />
									<select name="span_unit">
										<option value="D" ', $context['sub']['span']['unit'] == 'D' ? 'selected="selected"' : '', '>', $txt['paid_mod_span_days'], '</option>
										<option value="W" ', $context['sub']['span']['unit'] == 'W' ? 'selected="selected"' : '', '>', $txt['paid_mod_span_weeks'], '</option>
										<option value="M" ', $context['sub']['span']['unit'] == 'M' ? 'selected="selected"' : '', '>', $txt['paid_mod_span_months'], '</option>
										<option value="Y" ', $context['sub']['span']['unit'] == 'Y' ? 'selected="selected"' : '', '>', $txt['paid_mod_span_years'], '</option>
									</select>
								</td>
							</tr>
						</table>
					</div>
				</td>
			</tr><tr class="windowbg">
				<td colspan="2" align="left">
					<input type="radio" name="duration_type" id="duration_type_flexible" value="flexible" ', !empty($context['sub']['duration']) && $context['sub']['duration'] == 'flexible' ? 'checked="checked"' : '', ' class="check" onclick="toggleDuration(\'flexible\');" />
					<b>', $txt['paid_mod_flexible_price'], '</b>
				</td>
			</tr><tr class="windowbg2">
				<td align="left" colspan="2">
					<div id="flexible_area" ', !empty($context['sub']['duration']) && $context['sub']['duration'] == 'flexible' ? '' : 'style="display: none;"', '>
						<table border="0" cellspacing="0" cellpadding="4" align="center" width="100%">';

	//!! Removed until implemented
	if (!empty($sdflsdhglsdjgs))
		echo '
							<tr class="windowbg2" valign="top">
								<td width="50%" align="right"><label for="allow_partial_check">', $txt['paid_mod_allow_partial'], '</label>:<br /><span class="smalltext">', $txt['paid_mod_allow_partial_desc'], '</span></td>
								<td><input type="checkbox" name="allow_partial" id="allow_partial_check"', empty($context['sub']['allow_partial']) ? '' : ' checked="checked"', ' class="check" /></td>
							</tr>';

	echo '
							<tr class="windowbg2">
								<td colspan="2" width="100%">
									<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%" class="tborder">
										<tr class="titlebg">
											<td colspan="2">', $txt['paid_mod_price_breakdown'], '</td>
										</tr>
										<tr class="windowbg2">
											<td colspan="2"><span class="smalltext">', $txt['paid_mod_price_breakdown_desc'], '</span></td>
										</tr>
										<tr class="titlebg">
											<td width="70%">', $txt['paid_duration'], '</td>
											<td align="center">', $txt['paid_cost'], ' (', preg_replace('~%[df\.\d]+~', '', $modSettings['paid_currency_symbol']), ')</td>
										</tr>
										<tr class="windowbg2">
											<td>', $txt['paid_per_day'], ':</td>
											<td align="center"><input type="text" name="cost_day" value="', empty($context['sub']['cost']['day']) ? '0' : $context['sub']['cost']['day'], '" size="5" /></td>
										</tr>
										<tr class="windowbg2">
											<td>', $txt['paid_per_week'], ':</td>
											<td align="center"><input type="text" name="cost_week" value="', empty($context['sub']['cost']['week']) ? '0' : $context['sub']['cost']['week'], '" size="5" /></td>
										</tr>
										<tr class="windowbg2">
											<td>', $txt['paid_per_month'], ':</td>
											<td align="center"><input type="text" name="cost_month" value="', empty($context['sub']['cost']['month']) ? '0' : $context['sub']['cost']['month'], '" size="5" /></td>
										</tr>
										<tr class="windowbg2">
											<td>', $txt['paid_per_year'], ':</td>
											<td align="center"><input type="text" name="cost_year" value="', empty($context['sub']['cost']['year']) ? '0' : $context['sub']['cost']['year'], '" size="5" /></td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</div>
				</td>
			</tr><tr class="windowbg2">
				<td align="right" colspan="2">
					<input type="submit" name="save" value="', $txt['paid_settings_save'], '" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';

}

function template_delete_subscription()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<form action="', $scripturl, '?action=admin;area=paidsubscribe;sa=modify;sid=', $context['sub_id'], ';delete" method="post">
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['paid_delete_subscription'], '</td>
			</tr><tr class="windowbg2">
				<td colspan="2">
					', $txt['paid_mod_delete_warning'], '
				</td>
			</tr><tr class="windowbg2">
				<td align="center" colspan="2">
					<input type="submit" name="delete_confirm" value="', $txt['paid_delete_subscription'], '" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';

}

// Add or edit an existing subscriber.
function template_modify_user_subscription()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Some quickly stolen javascript from Post, could do with being more efficient :)
	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var monthLength = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

			function generateDays(offset)
			{
				var days = 0, selected = 0;
				var dayElement = document.getElementById("day" + offset), yearElement = document.getElementById("year" + offset), monthElement = document.getElementById("month" + offset);

				monthLength[1] = 28;
				if (yearElement.options[yearElement.selectedIndex].value % 4 == 0)
					monthLength[1] = 29;

				selected = dayElement.selectedIndex;
				while (dayElement.options.length)
					dayElement.options[0] = null;

				days = monthLength[monthElement.value - 1];

				for (i = 1; i <= days; i++)
					dayElement.options[dayElement.length] = new Option(i, i);

				if (selected < days)
					dayElement.selectedIndex = selected;
			}
		// ]]></script>';

	echo '
	<form action="', $scripturl, '?action=admin;area=paidsubscribe;sa=modifyuser;sid=', $context['sub_id'], ';lid=', $context['log_id'], '" method="post">
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="60%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">
						', $txt['paid_' . $context['action_type'] . '_subscription'], ' - ', $context['current_subscription']['name'], '
						', empty($context['sub']['username']) ? '' : ' (' . $txt['user'] . ': ' . $context['sub']['username'] . ')', '
				</td>
			</tr>';

	// Do we need a username?
	if ($context['action_type'] == 'add')
		echo '
			<tr class="windowbg2">
				<td align="right"><b>', $txt['paid_username'], ':</b><br /><span class="smalltext">', $txt['one_username'], '</span></td>
				<td>
					<input type="text" name="name" id="name_control" value="', $context['sub']['username'], '" size="30" />
				</td>
			</tr>';

	echo '
			<tr class="windowbg2" valign="top">
				<td width="50%" align="right"><b>', $txt['start_date_and_time'], ':</b></td>
				<td>
					<select name="year" id="year" onchange="generateDays(\'\');">';

	// Show a list of all the years we allow...
	for ($year = 2005; $year <= 2030; $year++)
		echo '
						<option value="', $year, '"', $year == $context['sub']['start']['year'] ? ' selected="selected"' : '', '>', $year, '</option>';

	echo '
					</select>&nbsp;
					', (isset($txt['calendar_month']) ? $txt['calendar_month'] : $txt['calendar_month']), '&nbsp;
					<select name="month" id="month" onchange="generateDays(\'\');">';

	// There are 12 months per year - ensure that they all get listed.
	for ($month = 1; $month <= 12; $month++)
		echo '
						<option value="', $month, '"', $month == $context['sub']['start']['month'] ? ' selected="selected"' : '', '>', $txt['months'][$month], '</option>';

	echo '
					</select>&nbsp;
					', (isset($txt['calendar_day']) ? $txt['calendar_day'] : $txt['calendar_day']), '&nbsp;
					<select name="day" id="day">';

	// This prints out all the days in the current month - this changes dynamically as we switch months.
	for ($day = 1; $day <= $context['sub']['start']['last_day']; $day++)
		echo '
						<option value="', $day, '"', $day == $context['sub']['start']['day'] ? ' selected="selected"' : '', '>', $day, '</option>';

	echo '
					</select><br />
					', $txt['hour'], ':<input type="text" name="hour" value="', $context['sub']['start']['hour'], '" size="2" />
					', $txt['minute'], ':<input type="text" name="minute" value="', $context['sub']['start']['min'], '" size="2" />
				</td>
			</tr><tr class="windowbg2" valign="top">
				<td width="50%" align="right"><b>', $txt['end_date_and_time'], ':</b></td>
				<td>
					<select name="yearend" id="yearend" onchange="generateDays(\'end\');">';

	// Show a list of all the years we allow...
	for ($year = 2005; $year <= 2030; $year++)
		echo '
						<option value="', $year, '"', $year == $context['sub']['end']['year'] ? ' selected="selected"' : '', '>', $year, '</option>';

	echo '
					</select>&nbsp;
					', (isset($txt['calendar_month']) ? $txt['calendar_month'] : $txt['calendar_month']), '&nbsp;
					<select name="monthend" id="monthend" onchange="generateDays(\'end\');">';

	// There are 12 months per year - ensure that they all get listed.
	for ($month = 1; $month <= 12; $month++)
		echo '
						<option value="', $month, '"', $month == $context['sub']['end']['month'] ? ' selected="selected"' : '', '>', $txt['months'][$month], '</option>';

	echo '
					</select>&nbsp;
					', (isset($txt['calendar_day']) ? $txt['calendar_day'] : $txt['calendar_day']), '&nbsp;
					<select name="dayend" id="dayend">';

	// This prints out all the days in the current month - this changes dynamically as we switch months.
	for ($day = 1; $day <= $context['sub']['end']['last_day']; $day++)
		echo '
						<option value="', $day, '"', $day == $context['sub']['end']['day'] ? ' selected="selected"' : '', '>', $day, '</option>';

	echo '
					</select><br />
					', $txt['hour'], ':<input type="text" name="hourend" value="', $context['sub']['end']['hour'], '" size="2" />
					', $txt['minute'], ':<input type="text" name="minuteend" value="', $context['sub']['end']['min'], '" size="2" />
				</td>
			</tr><tr class="windowbg2">
				<td align="right"><b>', $txt['paid_status'], ':</b></td>
				<td>
					<select name="status">
						<option value="0" ', $context['sub']['status'] == 0 ? 'selected="selected"' : '', '>', $txt['paid_finished'], '</option>
						<option value="1" ', $context['sub']['status'] == 1 ? 'selected="selected"' : '', '>', $txt['paid_active'], '</option>
					</select>
				</td>
			</tr><tr class="windowbg2">
				<td align="right" colspan="2">
					<input type="submit" name="save_sub" value="', $txt['paid_settings_save'], '" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>
	<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/suggest.js?rc1"></script>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var oAddMemberSuggest = new smc_AutoSuggest({
			sSelf: \'oAddMemberSuggest\',
			sSessionId: \'', $context['session_id'], '\',
			sSuggestId: \'name_subscriber\',
			sControlId: \'name_control\',
			sSearchType: \'member\',
			sTextDeleteItem: \'', $txt['autosuggest_delete_item'], '\',
			bItemList: false
		});
	// ]]></script>';

	if (!empty($context['pending_payments']))
	{
		echo '
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="60%" class="tborder">
			<tr class="titlebg">
				<td colspan="3">
					', $txt['pending_payments'], '
				</td>
			</tr>
			<tr class="windowbg2">
				<td colspan="3" class="smalltext">
					', $txt['pending_payments_desc'], '
				</td>
			</tr>
			<tr class="titlebg">
				<td width="70%">
					', $txt['pending_payments_value'], '
				</td>
				<td></td>
				<td></td>
			</tr>';

		foreach ($context['pending_payments'] as $id => $payment)
		{
			echo '
			<tr class="windowbg">
				<td>
					', $payment['desc'], '
				</td>
				<td align="center">
					<a href="', $scripturl, '?action=admin;area=paidsubscribe;sa=modifyuser;lid=', $context['log_id'], ';pending=', $id, ';accept">', $txt['pending_payments_accept'], '</a>
				</td>
				<td align="center">
					<a href="', $scripturl, '?action=admin;area=paidsubscribe;sa=modifyuser;lid=', $context['log_id'], ';pending=', $id, ';remove">', $txt['pending_payments_remove'], '</a>
				</td>
			</tr>';
		}

		echo '
		</table>';
	}
}

// Template for a user to edit/pick their subscriptions.
function template_user_subscription()
{
	global $context, $txt, $scripturl, $modSettings;

	echo '
	<form action="', $scripturl, '?action=profile;u=', $context['id_member'], ';area=subscriptions;confirm" method="post">
		<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%" class="bordercolor">
			<tr class="titlebg">
				<td colspan="2">
					', $txt['subscriptions'], '
				</td>
			</tr>';

	if (empty($context['subscriptions']))
	{
		echo '
			<tr class="windowbg2">
				<td align="center">', $txt['paid_subs_none'], '</td>
			</tr>';
	}
	else
	{
		echo '
			<tr class="windowbg2">
				<td align="left" colspan="2"><span class="smalltext">', $txt['paid_subs_desc'], '</span></td>
			</tr>';

		// Print out all the subscriptions.
		foreach ($context['subscriptions'] as $id => $subscription)
		{
			// Ignore the inactive ones...
			if (empty($subscription['active']))
				continue;

			echo '
			<tr class="catbg">
				<td colspan="2">', $subscription['name'], '</td>
			</tr>
			<tr class="windowbg2">
				<td colspan="2">', $subscription['desc'], '</td>
			</tr>
			<tr class="windowbg">
				<td align="right" valign="bottom">';

			if (!$subscription['flexible'])
				echo '
					<div style="float: left; height: 100%; margin: 2px;"><b>', $txt['paid_duration'], ':</b> ', $subscription['length'], '</div>';

			if ($context['user']['is_owner'])
			{
				echo '
					<b>', $txt['paid_cost'], ':</b>';

				if ($subscription['flexible'])
				{
					echo '
					<select name="cur[', $subscription['id'], ']">';

					// Print out the costs for this one.
					foreach ($subscription['costs'] as $duration => $value)
						echo '
						<option value="', $duration, '">', sprintf($modSettings['paid_currency_symbol'], $value), '/', $txt[$duration], '</option>';

					echo '
					</select>';
				}
				else
					echo '
					', sprintf($modSettings['paid_currency_symbol'], $subscription['costs']['fixed']);

				echo '
					<input type="submit" name="sub_id[', $subscription['id'], ']" value="', $txt['paid_order'], '" />';
			}
			else
				echo '
					<a href="', $scripturl, '?action=admin;area=paidsubscribe;sa=modifyuser;sid=', $subscription['id'], ';uid=', $context['member']['id'], (empty($context['current'][$subscription['id']]) ? '' : ';lid=' . $context['current'][$subscription['id']]['id']), '">', empty($context['current'][$subscription['id']]) ? $txt['paid_admin_add'] : $txt['paid_edit_subscription'], '</a>';

			echo '
				</td>
			</tr>';
		}

		echo '
		</table>
	</form>
	<br />';
	}

	echo '
		<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%" class="bordercolor">
			<tr class="titlebg">
				<td colspan="4">
					', $txt['paid_current'], '
				</td>
			</tr>
			<tr class="windowbg2">
				<td colspan="4">
					<span class="smalltext">', $txt['paid_current_desc'], '</span>
				</td>
			</tr>
			<tr class="titlebg">
				<td width="30%">', $txt['paid_name'], '</td>
				<td align="center">', $txt['paid_status'], '</td>
				<td align="center">', $txt['start_date'], '</td>
				<td align="center">', $txt['end_date'], '</td>
			</tr>';

	if (empty($context['current']))
		echo '
			<tr class="windowbg">
				<td align="center" colspan="4">
					', $txt['paid_none_yet'], '
				</td>
			</tr>';

	foreach ($context['current'] as $sub)
	{
		if (!$sub['hide'])
			echo '
			<tr class="windowbg">
				<td>
					', (allowedTo('admin_forum') ? '<a href="' . $scripturl . '?action=admin;area=paidsubscribe;sa=modifyuser;lid=' . $sub['id'] . '">' . $sub['name'] . '</a>' : $sub['name']), '
				</td><td>
					<span style="color: ', ($sub['status'] == 2 ? 'green' : ($sub['status'] == 1 ? 'red' : 'orange')), '"><b>', $sub['status_text'], '</b></span>
				</td><td>
					', $sub['start'], '
				</td><td>
					', $sub['end'], '
				</td>
			</tr>';
	}
	echo '
		</table>';
}

// The "choose payment" dialog.
function template_choose_payment()
{
	global $context, $txt, $modSettings, $scripturl;

	echo '
		<table border="0" cellspacing="1" cellpadding="4" align="center" width="85%" class="bordercolor">
			<tr class="titlebg">
				<td>', $txt['paid_confirm_payment'], '</td>
			</tr>
			<tr class="windowbg2">
				<td class="smalltext">', $txt['paid_confirm_desc'], '</td>
			</tr>
			<tr class="windowbg3">
				<td><b>', $txt['subscription'], ':</b> ', $context['sub']['name'], ' | <b>', $txt['paid_cost'], ':</b> ', $context['cost'], '</td>
			</tr>';

	// Do all the gateway options.
	foreach ($context['gateways'] as $gateway)
	{
		echo '
			<tr class="catbg">
				<td><b>', $gateway['title'], '</b></td>
			</tr>
			<tr class="windowbg2">
				<td>
					', $gateway['desc'], '<br />
					<form action="', $gateway['form'], '" method="post">';

		if (!empty($gateway['javascript']))
			echo '
						<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
							', $gateway['javascript'], '
						// ]]></script>';

		foreach ($gateway['hidden'] as $name => $value)
			echo '
						<input type="hidden" id="', $gateway['id'], '_', $name, '" name="', $name, '" value="', $value, '" />';

		echo '
						<input type="submit" value="', $gateway['submit'], '" style="float: right;" />
					</form>
				</td>
			</tr>';
	}

	echo '
		</table>';
}

// The "thank you" bit...
function template_paid_done()
{
	global $context, $txt, $modSettings, $scripturl;

	echo '
		<table border="0" cellspacing="1" cellpadding="3" align="center" width="60%" class="tborder">
			<tr class="titlebg">
				<td>
					', $txt['paid_done'], '
				</td>
			</tr>
			<tr class="windowbg">
				<td>
					', $txt['paid_done_desc'], '<br />
					<center><a href="', $scripturl, '?action=profile;u=', $context['member']['id'], ';area=subscriptions">', $txt['paid_sub_return'], '</a></center>
				</td>
			</tr>
		</table>';
}

?>