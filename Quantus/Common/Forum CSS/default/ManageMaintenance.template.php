<?php
// Version: 2.0 RC1; ManageMaintenance

// Template for the database maintenance tasks.
function template_maintain_database()
{
	global $context, $settings, $options, $txt, $scripturl;

	// If maintenance has finished tell the user.
	if (!empty($context['maintenance_finished']))
		echo '
			<div class="windowbg" style="margin: 1ex; padding: 1ex 2ex; border: 1px dashed green; color: green;">
				', sprintf($txt['maintain_done'], $context['maintenance_finished']), '
			</div>';

	echo '
	<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor">
		<tr class="titlebg">
			<td>', $txt['maintain_optimize'], '</td>
		</tr>
		<tr class="windowbg">
			<td>
				<form action="', $scripturl, '?action=admin;area=maintain;sa=database;activity=optimize" method="post" accept-charset="', $context['character_set'], '">
					<p>', $txt['maintain_optimize_info'], '</p>
					<p><input type="submit" value="', $txt['maintain_run_now'], '" /></p>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>
		<tr class="titlebg">
			<td><a href="', $scripturl, '?action=helpadmin;help=maintenance_backup" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['maintain_backup'], '</td>
		</tr>
		<tr class="windowbg">
			<td>
				<form action="', $scripturl, '?action=admin;area=maintain;sa=database;activity=backup" method="post" accept-charset="', $context['character_set'], '">
					<p><label for="struct"><input type="checkbox" name="struct" id="struct" onclick="document.getElementById(\'submitDump\').disabled = !document.getElementById(\'struct\').checked &amp;&amp; !document.getElementById(\'data\').checked;" class="check" checked="checked" /> ', $txt['maintain_backup_struct'], '</label><br />
					<label for="data"><input type="checkbox" name="data" id="data" onclick="document.getElementById(\'submitDump\').disabled = !document.getElementById(\'struct\').checked &amp;&amp; !document.getElementById(\'data\').checked;" checked="checked" class="check" /> ', $txt['maintain_backup_data'], '</label><br />
					<label for="compress"><input type="checkbox" name="compress" id="compress" value="gzip" checked="checked" class="check" /> ', $txt['maintain_backup_gz'], '</label></p>
					<p><input type="submit" value="', $txt['maintain_backup_save'], '" id="submitDump" onclick="return document.getElementById(\'struct\').checked || document.getElementById(\'data\').checked;" /></p>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>';

	// Show an option to convert to UTF-8 if we're not on UTF-8 yet.
	if ($context['convert_utf8'])
	{
		echo '
		<tr class="titlebg">
			<td>', $txt['utf8_title'], '</td>
		</tr>
		<tr class="windowbg">
			<td>
				<form action="', $scripturl, '?action=admin;area=maintain;sa=database;activity=convertutf8" method="post" accept-charset="', $context['character_set'], '">
					<p>', $txt['utf8_introduction'], '</p>
					<p><input type="submit" value="', $txt['maintain_run_now'], '" /></p>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>';
	}

	// We might want to convert entities if we're on UTF-8.
	if ($context['convert_entities'])
	{
		echo '
		<tr class="titlebg">
			<td>', $txt['entity_convert_title'], '</td>
		</tr>
		<tr class="windowbg">
			<td>
				<form action="', $scripturl, '?action=admin;area=maintain;sa=database;activity=convertentities" method="post" accept-charset="', $context['character_set'], '">
					<p>', $txt['entity_convert_introduction'], '</p>
					<p><input type="submit" value="', $txt['maintain_run_now'], '" /></p>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>';
	}

	echo '
	</table>';
}

// Template for the routine maintenance tasks.
function template_maintain_routine()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// If maintenance has finished tell the user.
	if (!empty($context['maintenance_finished']))
		echo '
			<div class="windowbg" style="margin: 1ex; padding: 1ex 2ex; border: 1px dashed green; color: green;">
				', sprintf($txt['maintain_done'], $context['maintenance_finished']), '
			</div>';

	// Starts off with general maintenance procedures.
	echo '
	<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor">
		<tr class="titlebg">
			<td>', $txt['maintain_version'], '</td>
		</tr>
		<tr class="windowbg">
			<td>
				<form action="', $scripturl, '?action=admin;area=maintain;sa=routine;activity=version" method="post" accept-charset="', $context['character_set'], '">
					<p>', $txt['maintain_version_info'], '</p>
					<p><input type="submit" value="', $txt['maintain_run_now'], '" /></p>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>
		<tr class="titlebg">
			<td>', $txt['maintain_errors'], '</td>
		</tr>
		<tr class="windowbg">
			<td>
				<form action="', $scripturl, '?action=admin;area=repairboards" method="post" accept-charset="', $context['character_set'], '">
					<p>', $txt['maintain_errors_info'], '</p>
					<p><input type="submit" value="', $txt['maintain_run_now'], '" /></p>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>
		<tr class="titlebg">
			<td>', $txt['maintain_recount'], '</td>
		</tr>
		<tr class="windowbg">
			<td>
				<form action="', $scripturl, '?action=admin;area=maintain;sa=routine;activity=recount" method="post" accept-charset="', $context['character_set'], '">
					<p>', $txt['maintain_recount_info'], '</p>
					<p><input type="submit" value="', $txt['maintain_run_now'], '" /></p>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>
		<tr class="titlebg">
			<td>', $txt['maintain_logs'], '</td>
		</tr>
		<tr class="windowbg">
			<td>
				<form action="', $scripturl, '?action=admin;area=maintain;sa=routine;activity=logs" method="post" accept-charset="', $context['character_set'], '">
					<p>', $txt['maintain_logs_info'], '</p>
					<p><input type="submit" value="', $txt['maintain_run_now'], '" /></p>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>
		<tr class="titlebg">
			<td>', $txt['maintain_cache'], '</td>
		</tr>
		<tr class="windowbg">
			<td>
				<form action="', $scripturl, '?action=admin;area=maintain;sa=routine;activity=cleancache" method="post" accept-charset="', $context['character_set'], '">
					<p>', $txt['maintain_cache_info'], '</p>
					<p><input type="submit" value="', $txt['maintain_run_now'], '" /></p>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>
	</table>';
}

// Template for the member maintenance tasks.
function template_maintain_members()
{
	global $context, $settings, $options, $txt, $scripturl;

	// If maintenance has finished tell the user.
	if (!empty($context['maintenance_finished']))
		echo '
			<div class="windowbg" style="margin: 1ex; padding: 1ex 2ex; border: 1px dashed green; color: green;">
				', sprintf($txt['maintain_done'], $context['maintenance_finished']), '
			</div>';

	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var warningMessage = \'\';
		var membersSwap = false;

		function swapMembers()
		{
			membersSwap = !membersSwap;
			var membersForm = document.getElementById(\'membersForm\');

			document.getElementById("membersIcon").src = smf_images_url + (membersSwap ? "/collapse.gif" : "/expand.gif");
			setInnerHTML(document.getElementById("membersText"), membersSwap ? "', $txt['maintain_members_choose'], '" : "', $txt['maintain_members_all'], '");
			document.getElementById("membersPanel").style.display = (membersSwap ? "block" : "none");

			for (var i = 0; i < membersForm.length; i++)
			{
				if (membersForm.elements[i].type.toLowerCase() == "checkbox")
					membersForm.elements[i].checked = !membersSwap;
			}
		}

		function checkAttributeValidity()
		{
			origText = \'', $txt['reattribute_confirm'], '\';
			valid = true;

			// Do all the fields!
			if (!document.getElementById(\'to\').value)
				valid = false;
			warningMessage = origText.replace(/%member_to%/, document.getElementById(\'to\').value);

			if (document.getElementById(\'type_email\').checked)
			{
				if (!document.getElementById(\'from_email\').value)
					valid = false;
				warningMessage = warningMessage.replace(/%type%/, \'', addcslashes($txt['reattribute_confirm_email'], "'"), '\').replace(/%find%/, document.getElementById(\'from_email\').value);
			}
			else
			{
				if (!document.getElementById(\'from_name\').value)
					valid = false;
				warningMessage = warningMessage.replace(/%type%/, \'', addcslashes($txt['reattribute_confirm_username'], "'"), '\').replace(/%find%/, document.getElementById(\'from_name\').value);
			}

			document.getElementById(\'do_attribute\').disabled = valid ? \'\' : \'disabled\';

			setTimeout("checkAttributeValidity();", 500);
			return valid;
		}
		setTimeout("checkAttributeValidity();", 500);
	// ]]></script>

	<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor">
		<tr class="titlebg">
			<td>', $txt['maintain_reattribute_posts'], '</td>
		</tr>
		<tr class="windowbg">
			<td>
				<form action="', $scripturl, '?action=admin;area=maintain;sa=members;activity=reattribute" method="post" accept-charset="', $context['character_set'], '">
					<p>', $txt['reattribute_guest_posts'], ':<br />
					<label for="type_email"><input type="radio" name="type" id="type_email" value="email" checked="checked" class="check" />', $txt['reattribute_email'], '</label>
					<input type="text" name="from_email" id="from_email" value="" onclick="document.getElementById(\'type_email\').checked = \'checked\'; document.getElementById(\'from_name\').value = \'\';" /><br />
					<label for="type_name"><input type="radio" name="type" id="type_name" value="name" class="check" />', $txt['reattribute_username'], '</label>
					<input type="text" name="from_name" id="from_name" value="" onclick="document.getElementById(\'type_name\').checked = \'checked\'; document.getElementById(\'from_email\').value = \'\';" /></p>
					<p>', $txt['reattribute_current_member'], ': <input type="text" name="to" id="to" value="" /></p>
					<p><input type="checkbox" name="posts" id="posts" checked="checked" class="check" />
					<label for="posts">', $txt['reattribute_increase_posts'], '</label></p>
					<p><input type="submit" id="do_attribute" value="', $txt['reattribute'], '" onclick="if (!checkAttributeValidity()) return false; return confirm(warningMessage);" /></p>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>
		<tr class="titlebg">
			<td><a href="', $scripturl, '?action=helpadmin;help=maintenance_members" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['maintain_members'], '</td>
		</tr>
		<tr class="windowbg">
			<td>
				<form action="', $scripturl, '?action=admin;area=maintain;sa=members;activity=purgeinactive" method="post" accept-charset="', $context['character_set'], '" id="membersForm">
					<p><a name="membersLink"></a>', $txt['maintain_members_since1'], '
					<select name="del_type">
						<option value="activated" selected="selected">', $txt['maintain_members_activated'], '</option>
						<option value="logged">', $txt['maintain_members_logged_in'], '</option>
					</select> ', $txt['maintain_members_since2'], ' <input type="text" name="maxdays" value="30" size="3" />', $txt['maintain_members_since3'], '</p>';

	echo '
					<p><a href="#membersLink" onclick="swapMembers();"><img src="', $settings['images_url'], '/expand.gif" alt="+" id="membersIcon" /></a> <a href="#membersLink" onclick="swapMembers();" id="membersText" style="font-weight: bold;">', $txt['maintain_members_all'], '</a></p>
					<div style="display: none; padding: 3px" id="membersPanel">';

	foreach ($context['membergroups'] as $group)
		echo '
						<label for="groups', $group['id'], '"><input type="checkbox" name="groups[', $group['id'], ']" id="groups', $group['id'], '" checked="checked" class="check" /> ', $group['name'], '</label><br />';

	echo '
					</div>
					<p><input type="submit" value="', $txt['maintain_old_remove'], '" onclick="return confirm(\'', $txt['maintain_members_confirm'], '\');" /></p>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>
	</table>
	<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/suggest.js?rc1"></script>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var oAttributeMemberSuggest = new smc_AutoSuggest({
			sSelf: \'oAttributeMemberSuggest\',
			sSessionId: \'', $context['session_id'], '\',
			sSuggestId: \'attributeMember\',
			sControlId: \'to\',
			sSearchType: \'member\',
			sTextDeleteItem: \'', $txt['autosuggest_delete_item'], '\',
			bItemList: false
		});
	// ]]></script>';
}

// Template for the topic maintenance tasks.
function template_maintain_topics()
{
	global $scripturl, $txt, $context, $settings, $modSettings;

	// If maintenance has finished tell the user.
	if (!empty($context['maintenance_finished']))
		echo '
			<div class="windowbg" style="margin: 1ex; padding: 1ex 2ex; border: 1px dashed green; color: green;">
				', sprintf($txt['maintain_done'], $context['maintenance_finished']), '
			</div>';

	// Bit of javascript for showing which boards to prune in an otherwise hidden list.
	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var rotSwap = false;
			function swapRot()
			{
				rotSwap = !rotSwap;
				var rotForm = document.getElementById(\'rotForm\');

				document.getElementById("rotIcon").src = smf_images_url + (rotSwap ? "/collapse.gif" : "/expand.gif");
				setInnerHTML(document.getElementById("rotText"), rotSwap ? "', $txt['maintain_old_choose'], '" : "', $txt['maintain_old_all'], '");
				document.getElementById("rotPanel").style.display = (rotSwap ? "block" : "none");

				for (var i = 0; i < rotForm.length; i++)
				{
					if (rotForm.elements[i].type.toLowerCase() == "checkbox" && rotForm.elements[i].id != "delete_old_not_sticky")
						rotForm.elements[i].checked = !rotSwap;
				}
			}
		// ]]></script>';

	echo '
	<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor">
		<tr class="titlebg">
			<td><a href="', $scripturl, '?action=helpadmin;help=maintenance_rot" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['maintain_old'], '</td>
		</tr>
		<tr class="windowbg">
			<td>
				<form action="', $scripturl, '?action=admin;area=maintain;sa=topics;activity=pruneold" method="post" accept-charset="', $context['character_set'], '" id="rotForm">';

	// The otherwise hidden "choose which boards to prune".
	echo '
					<a name="rotLink"></a>', $txt['maintain_old_since_days1'], '<input type="text" name="maxdays" value="30" size="3" />', $txt['maintain_old_since_days2'], '<br />
					<div style="padding-left: 3ex;">
						<label for="delete_type_nothing"><input type="radio" name="delete_type" id="delete_type_nothing" value="nothing" class="check" checked="checked" /> ', $txt['maintain_old_nothing_else'], '</label><br />
						<label for="delete_type_moved"><input type="radio" name="delete_type" id="delete_type_moved" value="moved" class="check" /> ', $txt['maintain_old_are_moved'], '</label><br />
						<label for="delete_type_locked"><input type="radio" name="delete_type" id="delete_type_locked" value="locked" class="check" /> ', $txt['maintain_old_are_locked'], '</label><br />
					</div>';

	if (!empty($modSettings['enableStickyTopics']))
		echo '
					<div style="padding-left: 3ex; padding-top: 1ex;">
						<label for="delete_old_not_sticky"><input type="checkbox" name="delete_old_not_sticky" id="delete_old_not_sticky" class="check" checked="checked" /> ', $txt['maintain_old_are_not_stickied'], '</label><br />
					</div>';

	echo '
					<br />
					<a href="#rotLink" onclick="swapRot();"><img src="', $settings['images_url'], '/expand.gif" alt="+" id="rotIcon" /></a> <a href="#rotLink" onclick="swapRot();" id="rotText" style="font-weight: bold;">', $txt['maintain_old_all'], '</a>
					<div style="display: none;" id="rotPanel">
						<table width="100%" cellpadding="3" cellspacing="0" border="0">
							<tr>
								<td valign="top">';

	// This is the "middle" of the list.
	$middle = count($context['categories']) / 2;

	$i = 0;
	foreach ($context['categories'] as $category)
	{
		echo '
									<span style="text-decoration: underline;">', $category['name'], '</span><br />';

		// Display a checkbox with every board.
		foreach ($category['boards'] as $board)
			echo '
									<label for="boards_', $board['id'], '"><input type="checkbox" name="boards[', $board['id'], ']" id="boards_', $board['id'], '" checked="checked" class="check" /> ', str_repeat('&nbsp; ', $board['child_level']), $board['name'], '</label><br />';
		echo '
									<br />';

		// Increase $i, and check if we're at the middle yet.
		if (++$i == $middle)
			echo '
								</td>
								<td valign="top">';
	}

	echo '
								</td>
							</tr>
						</table>
					</div>
					<p><input type="submit" value="', $txt['maintain_old_remove'], '" onclick="return confirm(\'', $txt['maintain_old_confirm'], '\');" /></p>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>
		<tr class="titlebg">
			<td>', $txt['move_topics_maintenance'], '</td>
		</tr>
		<tr class="windowbg">
			<td>
				<form action="', $scripturl, '?action=admin;area=maintain;sa=topics;activity=massmove" method="post" accept-charset="', $context['character_set'], '">
					<p><label for="id_board_from">', $txt['move_topics_from'], ' </label>
					<select name="id_board_from" id="id_board_from">
						<option disabled="disabled">(', $txt['move_topics_select_board'], ')</option>';

	// From board
	foreach ($context['categories'] as $category)
	{
		echo '
						<option disabled="disabled">--------------------------------------</option>
						<option disabled="disabled">', $category['name'], '</option>
						<option disabled="disabled">--------------------------------------</option>';

		foreach ($category['boards'] as $board)
			echo '
						<option value="', $board['id'], '"> ', str_repeat('==', $board['child_level']), '=&gt;&nbsp;', $board['name'], '</option>';
	}

	echo '
					</select>
					<label for="id_board_to">', $txt['move_topics_to'], '</label>
					<select name="id_board_to" id="id_board_to">
						<option disabled="disabled">(', $txt['move_topics_select_board'], ')</option>';

	// To board
	foreach ($context['categories'] as $category)
	{
		echo '
						<option disabled="disabled">--------------------------------------</option>
						<option disabled="disabled">', $category['name'], '</option>
						<option disabled="disabled">--------------------------------------</option>';

		foreach ($category['boards'] as $board)
			echo '
						<option value="', $board['id'], '"> ', str_repeat('==', $board['child_level']), '=&gt;&nbsp;', $board['name'], '</option>';
	}
	echo '
					</select></p>
					<p><input type="submit" value="', $txt['move_topics_now'], '" onclick="if (document.getElementById(\'id_board_from\').options[document.getElementById(\'id_board_from\').selectedIndex].disabled || document.getElementById(\'id_board_from\').options[document.getElementById(\'id_board_to\').selectedIndex].disabled) return false; var confirmText = \'', $txt['move_topics_confirm'] . '\'; return confirm(confirmText.replace(/%board_from%/, document.getElementById(\'id_board_from\').options[document.getElementById(\'id_board_from\').selectedIndex].text.replace(/^=+&gt;&nbsp;/, \'\')).replace(/%board_to%/, document.getElementById(\'id_board_to\').options[document.getElementById(\'id_board_to\').selectedIndex].text.replace(/^=+&gt;&nbsp;/, \'\')));" /></p>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>
	</table>';
}

// Simple template for showing results of our optimization...
function template_optimize()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<div class="tborder">
		<div class="titlebg" style="padding: 4px;">', $txt['maintain_optimize'], '</div>
		<div class="windowbg" style="padding: 4px;">
			', $txt['database_numb_tables'], '<br />
			', $txt['database_optimize_attempt'], '<br />';

	// List each table being optimized...
	foreach ($context['optimized_tables'] as $table)
		echo '
			', sprintf($txt['database_optimizing'], $table['name'], $table['data_freed']), '<br />';

	// How did we go?
	echo '
			<br />', $context['num_tables_optimized'] == 0 ? $txt['database_already_optimized'] : $context['num_tables_optimized'] . ' ' . $txt['database_optimized'];

	echo '
			<br /><br />
			<a href="', $scripturl, '?action=admin;area=maintain">', $txt['maintain_return'], '</a>
		</div>
	</div>';
}

function template_convert_utf8()
{
	global $context, $txt, $settings, $scripturl;

	echo '
		<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder">
			<tr class="titlebg">
				<td>', $txt['utf8_title'], '</td>
			</tr><tr>
				<td class="windowbg2">
					', $txt['utf8_introduction'], '
				</td>
			</tr><tr>
				<td class="windowbg2">
					', $txt['utf8_warning'], '
				</td>
			</tr><tr>
				<td class="windowbg2">
					', $context['charset_about_detected'], isset($context['charset_warning']) ? ' <span class="alert">' . $context['charset_warning'] . '</span>' : '', '<br />
					<br />
				</td>
			</tr><tr>
				<td class="windowbg2" align="center">
					<form action="', $scripturl, '?action=admin;area=maintain;sa=database;activity=convertutf8" method="post" accept-charset="', $context['character_set'], '">
						<table><tr>
							<th align="right">', $txt['utf8_source_charset'], ': </th>
							<td><select name="src_charset">';
	foreach ($context['charset_list'] as $charset)
		echo '
								<option value="', $charset, '"', $charset === $context['charset_detected'] ? ' selected="selected"' : '', '>', $charset, '</option>';
	echo '
							</select></td>
						</tr><tr>
							<th align="right">', $txt['utf8_database_charset'], ': </th>
							<td>', $context['database_charset'], '</td>
						</tr><tr>
							<th align="right">', $txt['utf8_target_charset'], ': </th>
							<td>', $txt['utf8_utf8'], '</td>
						</tr><tr>
							<td colspan="2" align="right"><br />
								<input type="submit" value="', $txt['utf8_proceed'], '" />
							</td>
						</tr></table>
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						<input type="hidden" name="proceed" value="1" />
					</form>
				</td>
			</tr>
		</table>';
}

function template_convert_entities()
{
	global $context, $txt, $settings, $scripturl;

	echo '
		<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder">
			<tr class="titlebg">
				<td>', $txt['entity_convert_title'], '</td>
			</tr><tr>
				<td class="windowbg2">
					', $txt['entity_convert_introduction'], '
				</td>
			</tr>
			<tr>
				<td class="windowbg2" align="center">
					<form action="', $scripturl, '?action=admin;area=maintain;sa=database;activity=convertentities;start=0;', $context['session_var'], '=', $context['session_id'], '" method="post" accept-charset="', $context['character_set'], '">
						<input type="submit" value="', $txt['entity_convert_proceed'], '" />
					</form>
				</td>
			</tr>
		</table>';
}

?>