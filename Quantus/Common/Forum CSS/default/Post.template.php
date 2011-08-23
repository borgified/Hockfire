<?php
// Version: 2.0 RC1; Post

// The main template for the post page.
function template_main()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// Start the javascript... and boy is there a lot.
	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[';

	// Start with message icons - and any missing from this theme.
	echo '
			var icon_urls = {';
	foreach ($context['icons'] as $icon)
		echo '
				"', $icon['value'], '": "', $icon['url'], '"', $icon['is_last'] ? '' : ',';
	echo '
			};';

	// The actual message icon selector.
	echo '
			function showimage()
			{
				document.images.icons.src = icon_urls[document.forms.postmodify.icon.options[document.forms.postmodify.icon.selectedIndex].value];
			}';

	// A function needed to discern HTML entities from non-western characters.
	echo '
			function saveEntities()
			{
				var textFields = ["subject", "', $context['post_box_name'], '", "guestname", "evtitle", "question"];
				for (i in textFields)
					if (document.forms.postmodify.elements[textFields[i]])
						document.forms.postmodify[textFields[i]].value = document.forms.postmodify[textFields[i]].value.replace(/&#/g, "&#38;#");
				for (var i = document.forms.postmodify.elements.length - 1; i >= 0; i--)
					if (document.forms.postmodify.elements[i].name.indexOf("options") == 0)
						document.forms.postmodify.elements[i].value = document.forms.postmodify.elements[i].value.replace(/&#/g, "&#38;#");
			}';

	// Code for showing and hiding additional options.
	if (!empty($settings['additional_options_collapsable']))
		echo '
			var currentSwap = false;
			function swapOptions()
			{
				document.getElementById("postMoreExpand").src = smf_images_url + "/" + (currentSwap ? "collapse.gif" : "expand.gif");
				document.getElementById("postMoreExpand").alt = currentSwap ? "-" : "+";

				document.getElementById("postMoreOptions").style.display = currentSwap ? "" : "none";

				if (document.getElementById("postAttachment"))
					document.getElementById("postAttachment").style.display = currentSwap ? "" : "none";
				if (document.getElementById("postAttachment2"))
					document.getElementById("postAttachment2").style.display = currentSwap ? "" : "none";

				if (typeof(document.forms.postmodify) != "undefined")
					document.forms.postmodify.additional_options.value = currentSwap ? "1" : "0";

				currentSwap = !currentSwap;
			}';

	// If this is a poll - use some javascript to ensure the user doesn't create a poll with illegal option combinations.
	if ($context['make_poll'])
		echo '
			function pollOptions()
			{
				var expire_time = document.getElementById("poll_expire");

				if (isEmptyText(expire_time) || expire_time.value == 0)
				{
					document.forms.postmodify.poll_hide[2].disabled = true;
					if (document.forms.postmodify.poll_hide[2].checked)
						document.forms.postmodify.poll_hide[1].checked = true;
				}
				else
					document.forms.postmodify.poll_hide[2].disabled = false;
			}

			var pollOptionNum = 0, pollTabIndex;
			function addPollOption()
			{
				if (pollOptionNum == 0)
				{
					for (var i = 0; i < document.forms.postmodify.elements.length; i++)
						if (document.forms.postmodify.elements[i].id.substr(0, 8) == "options-")
						{
							pollOptionNum++;
							pollTabIndex = document.forms.postmodify.elements[i].tabIndex;
						}
				}
				pollOptionNum++

				setOuterHTML(document.getElementById("pollMoreOptions"), \'<br /><label for="options-\' + pollOptionNum + \'">', $txt['option'], ' \' + pollOptionNum + \'<\' + \'/label>: <input type="text" name="options[\' + pollOptionNum + \']" id="options-\' + pollOptionNum + \'" value="" size="25" tabindex="\' + pollTabIndex + \'" /><span id="pollMoreOptions"><\' + \'/span>\');
			}';

	// If we are making a calendar event we want to ensure we show the current days in a month etc... this is done here.
	if ($context['make_event'])
		echo '
			var monthLength = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

			function generateDays()
			{
				var dayElement = document.getElementById("day"), yearElement = document.getElementById("year"), monthElement = document.getElementById("month");
				var days, selected = dayElement.selectedIndex;

				monthLength[1] = yearElement.options[yearElement.selectedIndex].value % 4 == 0 ? 29 : 28;
				days = monthLength[monthElement.value - 1];

				while (dayElement.options.length)
					dayElement.options[0] = null;

				for (i = 1; i <= days; i++)
					dayElement.options[dayElement.length] = new Option(i, i);

				if (selected < days)
					dayElement.selectedIndex = selected;
			}';

	// End of the javascript, start the form and display the link tree.
	echo '
		// ]]></script>

		<form action="', $scripturl, '?action=', $context['destination'], ';', empty($context['current_board']) ? '' : 'board=' . $context['current_board'], '" method="post" accept-charset="', $context['character_set'], '" name="postmodify" id="postmodify" onsubmit="', ($context['becomes_approved'] ? '' : 'alert(\'' . $txt['js_post_will_require_approval'] . '\');'), 'submitonce(this);saveEntities();" enctype="multipart/form-data" style="margin: 0;">
			<table width="100%" align="center" cellpadding="0" cellspacing="3">
				<tr>
					<td valign="bottom" colspan="2">
						', theme_linktree(), '
					</td>
				</tr>
			</table>';

	// If the user wants to see how their message looks - the preview table is where it's at!
	echo '
		<div id="preview_section"', isset($context['preview_message']) ? '' : ' style="display: none;"', '>
			<table border="0" width="100%" cellspacing="1" cellpadding="3" class="bordercolor" align="center" style="table-layout: fixed;">
				<tr class="titlebg">
					<td id="preview_subject">', empty($context['preview_subject']) ? '' : $context['preview_subject'], '</td>
				</tr>
				<tr class="windowbg">
					<td class="post" width="100%" id="preview_body">
						', empty($context['preview_message']) ? str_repeat('<br />', 5) : $context['preview_message'], '
					</td>
				</tr>
			</table><br />
		</div>';

	if ($context['make_event'] && (!$context['event']['new'] || !empty($context['current_board'])))
		echo '
			<input type="hidden" name="eventid" value="', $context['event']['id'], '" />';

	// Start the main table.
	echo '
			<table border="0" width="100%" align="center" cellspacing="1" cellpadding="3" class="bordercolor">
				<tr class="titlebg">
					<td>', $context['page_title'], '</td>
				</tr>
				<tr>
					<td class="windowbg">', isset($context['current_topic']) ? '
						<input type="hidden" name="topic" value="' . $context['current_topic'] . '" />' : '', '
						<table border="0" cellpadding="3" width="100%">';

	// If an error occurred, explain what happened.
	echo '
							<tr', empty($context['post_error']['messages']) ? ' style="display: none"' : '', ' id="errors">
								<td></td>
								<td align="left">
									<div style="padding: 0px; font-weight: bold;', empty($context['error_type']) || $context['error_type'] != 'serious' ? ' display: none;' : '', '" id="error_serious">
										', $txt['error_while_submitting'], '
									</div>
									<div class="error" style="margin: 1ex 0 2ex 3ex;" id="error_list">
										', empty($context['post_error']['messages']) ? '' : implode('<br />', $context['post_error']['messages']), '
									</div>
								</td>
							</tr>';

	// If this won't be approved let them know!
	if (!$context['becomes_approved'])
	{
		echo '
							<tr>
								<td></td>
								<td align="left">
									<i>', $txt['wait_for_approval'], '</i>
									<input type="hidden" name="not_approved" value="1" />
								</td>
							</tr>';
	}

	// If it's locked, show a message to warn the replyer.
	echo '
							<tr', $context['locked'] ? '' : ' style="display: none"', ' id="lock_warning">
								<td></td>
								<td align="left">
									', $txt['topic_locked_no_reply'], '
								</td>
							</tr>';

	// Guests have to put in their name and email...
	if (isset($context['name']) && isset($context['email']))
	{
		echo '
							<tr>
								<td align="right" style="font-weight: bold;"', isset($context['post_error']['long_name']) || isset($context['post_error']['no_name']) || isset($context['post_error']['bad_name']) ? ' class="error"' : '', ' id="caption_guestname">
									', $txt['name'], ':
								</td>
								<td>
									<input type="text" name="guestname" size="25" value="', $context['name'], '" tabindex="', $context['tabindex']++, '" />
								</td>
							</tr>';

		if (empty($modSettings['guest_post_no_email']))
			echo '
							<tr>
								<td align="right" style="font-weight: bold;"', isset($context['post_error']['no_email']) || isset($context['post_error']['bad_email']) ? ' class="error"' : '', ' id="caption_email">
									', $txt['email'], ':
								</td>
								<td>
									<input type="text" name="email" size="25" value="', $context['email'], '" tabindex="', $context['tabindex']++, '" />
								</td>
							</tr>';
	}

	// Is visual verification enabled?
	if ($context['require_verification'])
	{
		echo '
							<tr>
								<td align="right" valign="top"', !empty($context['post_error']['need_qr_verification']) ? ' class="error"' : '', '>
									<b>', $txt['verification'], ':</b>
								</td>
								<td>
									', template_control_verification($context['visual_verification_id'], 'all'), '
								</td>
							</tr>';
	}

	// Are you posting a calendar event?
	if ($context['make_event'])
	{
		echo '
							<tr>
								<td align="right" style="font-weight: bold;"', isset($context['post_error']['no_event']) ? ' class="error"' : '', ' id="caption_evtitle">
									', $txt['calendar_event_title'], '
								</td>
								<td class="smalltext">
									<input type="text" name="evtitle" maxlength="30" size="30" value="', $context['event']['title'], '" tabindex="', $context['tabindex']++, '" />
								</td>
							</tr><tr>
								<td></td>
								<td class="smalltext">
									<input type="hidden" name="calendar" value="1" />', $txt['calendar_year'], '&nbsp;
									<select name="year" id="year" tabindex="', $context['tabindex']++, '" onchange="generateDays();">';

		// Show a list of all the years we allow...
		for ($year = $modSettings['cal_minyear']; $year <= $modSettings['cal_maxyear']; $year++)
			echo '
										<option value="', $year, '"', $year == $context['event']['year'] ? ' selected="selected"' : '', '>', $year, '</option>';

		echo '
									</select>&nbsp;
									', $txt['calendar_month'], '&nbsp;
									<select name="month" id="month" onchange="generateDays();">';

		// There are 12 months per year - ensure that they all get listed.
		for ($month = 1; $month <= 12; $month++)
			echo '
										<option value="', $month, '"', $month == $context['event']['month'] ? ' selected="selected"' : '', '>', $txt['months'][$month], '</option>';

		echo '
									</select>&nbsp;
									', $txt['calendar_day'], '&nbsp;
									<select name="day" id="day">';

		// This prints out all the days in the current month - this changes dynamically as we switch months.
		for ($day = 1; $day <= $context['event']['last_day']; $day++)
			echo '
										<option value="', $day, '"', $day == $context['event']['day'] ? ' selected="selected"' : '', '>', $day, '</option>';

		echo '
									</select>
								</td>
							</tr>';

		// If events can span more than one day then allow the user to select how long it should last.
		if (!empty($modSettings['cal_allowspan']))
		{
			echo '
							<tr>
								<td align="right"><b>', $txt['calendar_numb_days'], '</b></td>
								<td class="smalltext">
									<select name="span">';

			for ($days = 1; $days <= $modSettings['cal_maxspan']; $days++)
				echo '
										<option value="', $days, '"', $days == $context['event']['span'] ? ' selected="selected"' : '', '>', $days, '</option>';

			echo '
									</select>
								</td>
							</tr>';
		}

		// If this is a new event let the user specify which board they want the linked post to be put into.
		if ($context['event']['new'] && $context['is_new_post'])
		{
			echo '
							<tr>
								<td align="right"><b>', $txt['calendar_post_in'], '</b></td>
								<td class="smalltext">
									<select name="board">';
			foreach ($context['event']['categories'] as $category)
			{
				echo '
										<optgroup label="', $category['name'], '">';
				foreach ($category['boards'] as $board)
					echo '
											<option value="', $board['id'], '"', $board['selected'] ? ' selected="selected"' : '', '>', $board['child_level'] > 0 ? str_repeat('==', $board['child_level'] - 1) . '=&gt;' : '', ' ', $board['name'], '</option>';
				echo '
										</optgroup>';
			}
			echo '
									</select>
								</td>
							</tr>';
		}
	}

	// Now show the subject box for this post.
	echo '
							<tr>
								<td align="right" style="font-weight: bold;"', isset($context['post_error']['no_subject']) ? ' class="error"' : '', ' id="caption_subject">
									', $txt['subject'], ':
								</td>
								<td>
									<input type="text" name="subject"', $context['subject'] == '' ? '' : ' value="' . $context['subject'] . '"', ' tabindex="', $context['tabindex']++, '" size="80" maxlength="80" />
								</td>
							</tr>
							<tr>
								<td align="right">
									<b>', $txt['message_icon'], ':</b>
								</td>
								<td>
									<select name="icon" id="icon" onchange="showimage()">';

	// Loop through each message icon allowed, adding it to the drop down list.
	foreach ($context['icons'] as $icon)
		echo '
										<option value="', $icon['value'], '"', $icon['value'] == $context['icon'] ? ' selected="selected"' : '', '>', $icon['name'], '</option>';

	echo '
									</select>
									<img src="', $context['icon_url'], '" name="icons" hspace="15" alt="" />
								</td>
							</tr>';

	// If this is a poll then display all the poll options!
	if ($context['make_poll'])
	{
		echo '
							<tr>
								<td align="right" style="font-weight: bold;"', isset($context['post_error']['no_question']) ? ' class="error"' : '', ' id="caption_question">
									', $txt['poll_question'], ':
								</td>
								<td align="left">
									<input type="text" name="question" value="', isset($context['question']) ? $context['question'] : '', '" tabindex="', $context['tabindex']++, '" size="80" />
								</td>
							</tr>
							<tr>
								<td align="right"></td>
								<td>';

		// Loop through all the choices and print them out.
		foreach ($context['choices'] as $choice)
		{
			echo '
									<label for="options-', $choice['id'], '">', $txt['option'], ' ', $choice['number'], '</label>: <input type="text" name="options[', $choice['id'], ']" id="options-', $choice['id'], '" value="', $choice['label'], '" tabindex="', $context['tabindex']++, '" size="25" />';

			if (!$choice['is_last'])
				echo '<br />';
		}

		echo '
									<span id="pollMoreOptions"></span> <a href="#" onclick="addPollOption(); return false;">(', $txt['poll_add_option'], ')</a>
								</td>
							</tr>
							<tr>
								<td align="right"><b>', $txt['poll_options'], ':</b></td>
								<td class="smalltext"><input type="text" name="poll_max_votes" size="2" value="', $context['poll_options']['max_votes'], '" /> ', $txt['poll_max_votes'], '</td>
							</tr>
							<tr>
								<td align="right"></td>
								<td class="smalltext">', $txt['poll_run'], ' <input type="text" id="poll_expire" name="poll_expire" size="2" value="', $context['poll_options']['expire'], '" onchange="pollOptions();" /> ', $txt['poll_run_days'], '</td>
							</tr>
							<tr>
								<td align="right"></td>
								<td class="smalltext">
									<label for="poll_change_vote"><input type="checkbox" id="poll_change_vote" name="poll_change_vote"', !empty($context['poll_options']['change_vote']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['poll_do_change_vote'], '</label>';

		if ($context['poll_options']['guest_vote_enabled'])
			echo '
									<br /><label for="poll_guest_vote"><input type="checkbox" id="poll_guest_vote" name="poll_guest_vote"', !empty($context['poll_options']['guest_vote']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['poll_guest_vote'], '</label>';

		echo '
								</td>
							</tr>
							<tr>
								<td align="right"></td>
								<td class="smalltext">
									<input type="radio" name="poll_hide" value="0"', $context['poll_options']['hide'] == 0 ? ' checked="checked"' : '', ' class="check" /> ', $txt['poll_results_anyone'], '<br />
									<input type="radio" name="poll_hide" value="1"', $context['poll_options']['hide'] == 1 ? ' checked="checked"' : '', ' class="check" /> ', $txt['poll_results_voted'], '<br />
									<input type="radio" name="poll_hide" value="2"', $context['poll_options']['hide'] == 2 ? ' checked="checked"' : '', empty($context['poll_options']['expire']) ? ' disabled="disabled"' : '', ' class="check" /> ', $txt['poll_results_expire'], '<br />
									<br />
								</td>
							</tr>';
	}

	// Show the actual posting area...
	if ($context['show_bbc'])
	{
		echo '
							<tr>
								<td align="right"></td>
								<td valign="middle">
									', template_control_richedit($context['post_box_name'], 'bbc'), '
								</td>
							</tr>';
	}

	// What about smileys?
	if (!empty($context['smileys']['postform']))
		echo '
							<tr>
								<td align="right"></td>
								<td valign="middle">
									', template_control_richedit($context['post_box_name'], 'smileys'), '
								</td>
							</tr>';

	echo '
							<tr>
								<td valign="top" align="right"></td>
								<td>
									', template_control_richedit($context['post_box_name'], 'message'), '
								</td>
							</tr>';

	// If this message has been edited in the past - display when it was.
	if (isset($context['last_modified']))
		echo '
									<tr>
										<td valign="top" align="right">
											<b>', $txt['last_edit'], ':</b>
										</td>
										<td>
											', $context['last_modified'], '
										</td>
									</tr>';

	// If the admin has enabled the hiding of the additional options - show a link and image for it.
	if (!empty($settings['additional_options_collapsable']))
		echo '
									<tr>
										<td colspan="2" style="padding-left: 5ex;">
											<a href="javascript:swapOptions();"><img src="', $settings['images_url'], '/expand.gif" alt="+" id="postMoreExpand" /></a> <a href="javascript:swapOptions();"><b>', $txt['post_additionalopt'], '</b></a>
										</td>
									</tr>';

	// Display the check boxes for all the standard options - if they are available to the user!
	echo '
									<tr>
										<td></td>
										<td>
											<div id="postMoreOptions">
												<table width="80%" cellpadding="0" cellspacing="0" border="0">
													<tr>
														<td class="smalltext">', $context['can_notify'] ? '<input type="hidden" name="notify" value="0" /><label for="check_notify"><input type="checkbox" name="notify" id="check_notify"' . ($context['notify'] || !empty($options['auto_notify']) ? ' checked="checked"' : '') . ' value="1" class="check" /> ' . $txt['notify_replies'] . '</label>' : '', '</td>
														<td class="smalltext">', $context['can_lock'] ? '<input type="hidden" name="lock" value="0" /><label for="check_lock"><input type="checkbox" name="lock" id="check_lock"' . ($context['locked'] ? ' checked="checked"' : '') . ' value="1" class="check" /> ' . $txt['lock_topic'] . '</label>' : '', '</td>
													</tr>
													<tr>
														<td class="smalltext"><label for="check_back"><input type="checkbox" name="goback" id="check_back"' . ($context['back_to_topic'] || !empty($options['return_to_post']) ? ' checked="checked"' : '') . ' value="1" class="check" /> ' . $txt['back_to_topic'] . '</label></td>
														<td class="smalltext">', $context['can_sticky'] ? '<input type="hidden" name="sticky" value="0" /><label for="check_sticky"><input type="checkbox" name="sticky" id="check_sticky"' . ($context['sticky'] ? ' checked="checked"' : '') . ' value="1" class="check" /> ' . $txt['sticky_after'] . '</label>' : '', '</td>
													</tr>
													<tr>
														<td class="smalltext"><label for="check_smileys"><input type="checkbox" name="ns" id="check_smileys"', $context['use_smileys'] ? '' : ' checked="checked"', ' value="NS" class="check" /> ', $txt['dont_use_smileys'], '</label></td>', '
														<td class="smalltext">', $context['can_move'] ? '<input type="hidden" name="move" value="0" /><label for="check_move"><input type="checkbox" name="move" id="check_move" value="1" class="check" ' . (!empty($context['move']) ? 'checked="checked" ' : '') . '/> ' . $txt['move_after2'] . '</label>' : '', '</td>
													</tr>
													<tr>
														<td class="smalltext">', $context['can_announce'] && $context['is_first_post'] ? '<label for="check_announce"><input type="checkbox" name="announce_topic" id="check_announce" value="1" class="check" ' . (!empty($context['announce']) ? 'checked="checked" ' : '') . '/> ' . $txt['announce_topic'] . '</label>' : '', '</td>
														<td class="smalltext">', $context['show_approval'] ? '<label for="approve"><input type="checkbox" name="approve" id="approve" value="2" class="check" ' . ($context['show_approval'] === 2 ? 'checked="checked"' : '') . ' /> ' . $txt['approve_this_post'] . '</label>' : '', '</td>
													</tr>
												</table>
											</div>
										</td>
									</tr>';

	// If this post already has attachments on it - give information about them.
	if (!empty($context['current_attachments']))
	{
		echo '
							<tr id="postAttachment">
								<td align="right" valign="top">
									<b>', $txt['attached'], ':</b>
								</td>
								<td class="smalltext">
									<input type="hidden" name="attach_del[]" value="0" />
									', $txt['uncheck_unwatchd_attach'], ':<br />';
		foreach ($context['current_attachments'] as $attachment)
			echo '
									<input type="checkbox" name="attach_del[]" value="', $attachment['id'], '"', empty($attachment['unchecked']) ? ' checked="checked"' : '', ' class="check" /> ', $attachment['name'], (empty($attachment['approved']) ? ' (' . $txt['awaiting_approval'] . ')' : ''), '<br />';
		echo '
									<br />
								</td>
							</tr>';
	}

	// Is the user allowed to post any additional ones? If so give them the boxes to do it!
	if ($context['can_post_attachment'])
	{
		echo '
							<tr id="postAttachment2">
								<td align="right" valign="top">
									<b>', $txt['attach'], ':</b>
								</td>
								<td class="smalltext">
									<input type="file" size="48" name="attachment[]" />';

		// Show more boxes only if they aren't approaching their limit.
		if ($context['num_allowed_attachments'] > 1)
			echo '
									<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
										var allowed_attachments = ', $context['num_allowed_attachments'], ' - 1;

										function addAttachment()
										{
											if (allowed_attachments <= 0)
												return alert("', $txt['more_attachments_error'], '");

											setOuterHTML(document.getElementById("moreAttachments"), \'<br /><input type="file" size="48" name="attachment[]" /><span id="moreAttachments"><\' + \'/span>\');
											allowed_attachments = allowed_attachments - 1;

											return true;
										}
									// ]]></script>
									<span id="moreAttachments"></span> <a href="#" onclick="addAttachment(); return false;">(', $txt['more_attachments'], ')</a><br />
									<noscript><input type="file" size="48" name="attachment[]" /><br /></noscript>';
		else
			echo '
									<br />';

		// Show some useful information such as allowed extensions, maximum size and amount of attachments allowed.
		if (!empty($modSettings['attachmentCheckExtensions']))
			echo '
									', $txt['allowed_types'], ': ', $context['allowed_extensions'], '<br />';

		if (!empty($context['attachment_restrictions']))
			echo '
									', $txt['attach_restrictions'], ' ', implode(', ', $context['attachment_restrictions']), '.<br />';

		if (!$context['can_post_attachment_unapproved'])
			echo '
									<span class="alert">', $txt['attachment_requires_approval'], '</span><br />';

		echo '
								</td>
							</tr>';
	}

	// Finally, the submit buttons.
	echo '
							<tr>
								<td align="center" colspan="2">
									<span class="smalltext"><br />', $txt['shortcuts'], '</span><br />
									', template_control_richedit($context['post_box_name'], 'buttons');

	// Option to delete an event if user is editing one.
	if ($context['make_event'] && !$context['event']['new'])
		echo '
									<input type="submit" name="deleteevent" value="', $txt['event_delete'], '" onclick="return confirm(\'', $txt['event_delete_confirm'], '\');" />';

	echo '
								</td>
							</tr>
							<tr>
								<td colspan="2"></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>';

	// Assuming this isn't a new topic pass across the number of replies when the topic was created.
	if (isset($context['num_replies']))
		echo '
			<input type="hidden" name="num_replies" value="', $context['num_replies'], '" />';

	echo '
			<input type="hidden" name="additional_options" value="', $context['show_additional_options'] ? 1 : 0, '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />
		</form>';

	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[';

	// The functions used to preview a posts without loading a new page.
	echo '
			var current_board = ', empty($context['current_board']) ? 'null' : $context['current_board'], ';
			var make_poll = ', $context['make_poll'] ? 'true' : 'false', ';
			var txt_preview_title = "', $txt['preview_title'], '";
			var txt_preview_fetch = "', $txt['preview_fetch'], '";
			function previewPost()
			{
				', $context['browser']['is_firefox'] ? '
				// Firefox doesn\'t render <marquee> that have been put it using javascript
				if (document.forms.postmodify.elements["' . $context['post_box_name'] . '"].value.indexOf("[move]") != -1)
				{
					return submitThisOnce(document.forms.postmodify);
				}' : '', '
				if (window.XMLHttpRequest)
				{
					// Opera didn\'t support setRequestHeader() before 8.01.
					if (typeof(window.opera) != "undefined")
					{
						var test = new XMLHttpRequest();
						if (typeof(test.setRequestHeader) != "function")
							return submitThisOnce(document.forms.postmodify);
					}
					// !!! Currently not sending poll options and option checkboxes.
					var i, x = new Array();
					var textFields = ["subject", "', $context['post_box_name'], '", "icon", "guestname", "email", "evtitle", "question", "topic"];
					var numericFields = [
						"board", "topic", "num_replies",
						"eventid", "calendar", "year", "month", "day",
						"poll_max_votes", "poll_expire", "poll_change_vote", "poll_hide"
					];
					var checkboxFields = [
						"ns",
					];


					for (i in textFields)
						if (document.forms.postmodify.elements[textFields[i]])
						{
							// Handle the WYSIWYG editor.
							if (textFields[i] == "', $context['post_box_name'], '" && editorHandle', $context['post_box_name'], ' && editorHandle', $context['post_box_name'], '.bRichTextEnabled)
								x[x.length] = "message_mode=1&" + textFields[i] + "=" + editorHandle', $context['post_box_name'], '.getText(false).replace(/&#/g, "&#38;#").php_to8bit().php_urlencode();
							else
								x[x.length] = textFields[i] + "=" + document.forms.postmodify[textFields[i]].value.replace(/&#/g, "&#38;#").php_to8bit().php_urlencode();
						}
					for (i in numericFields)
						if (document.forms.postmodify.elements[numericFields[i]] && typeof(document.forms.postmodify[numericFields[i]].value) != "undefined")
							x[x.length] = numericFields[i] + "=" + parseInt(document.forms.postmodify.elements[numericFields[i]].value);
					for (i in checkboxFields)
						if (document.forms.postmodify.elements[checkboxFields[i]] && document.forms.postmodify.elements[checkboxFields[i]].checked)
							x[x.length] = checkboxFields[i] + "=" + document.forms.postmodify.elements[checkboxFields[i]].value;

					sendXMLDocument(smf_scripturl + "?action=post2" + (current_board ? ";board=" + current_board : "") + (make_poll ? ";poll" : "") + ";preview;xml", x.join("&"), onDocSent);

					document.getElementById("preview_section").style.display = "";
					setInnerHTML(document.getElementById("preview_subject"), txt_preview_title);
					setInnerHTML(document.getElementById("preview_body"), txt_preview_fetch);

					return false;
				}
				else
					return submitThisOnce(document.forms.postmodify);
			}
			function onDocSent(XMLDoc)
			{
				if (!XMLDoc)
				{
					document.forms.postmodify.preview.onclick = new function ()
					{
						return true;
					}
					document.forms.postmodify.preview.click();
				}

				// Show the preview section.
				var i, preview = XMLDoc.getElementsByTagName("smf")[0].getElementsByTagName("preview")[0];
				setInnerHTML(document.getElementById("preview_subject"), preview.getElementsByTagName("subject")[0].firstChild.nodeValue);

				var bodyText = "";
				for (i = 0; i < preview.getElementsByTagName("body")[0].childNodes.length; i++)
					bodyText += preview.getElementsByTagName("body")[0].childNodes[i].nodeValue;

				setInnerHTML(document.getElementById("preview_body"), bodyText);
				document.getElementById("preview_body").className = "post";

				// Show a list of errors (if any).
				var errors = XMLDoc.getElementsByTagName("smf")[0].getElementsByTagName("errors")[0];
				var numErrors = errors.getElementsByTagName("error").length, errorList = new Array();
				for (i = 0; i < numErrors; i++)
					errorList[errorList.length] = errors.getElementsByTagName("error")[i].firstChild.nodeValue;
				document.getElementById("errors").style.display = numErrors == 0 ? "none" : "";
				document.getElementById("error_serious").style.display = errors.getAttribute("serious") == 1 ? "" : "none";
				setInnerHTML(document.getElementById("error_list"), numErrors == 0 ? "" : errorList.join("<br />"));

				// Show a warning if the topic has been locked.
				document.getElementById("lock_warning").style.display = errors.getAttribute("topic_locked") == 1 ? "" : "none";

				// Adjust the color of captions if the given data is erroneous.
				var captions = errors.getElementsByTagName("caption"), numCaptions = errors.getElementsByTagName("caption").length;
				for (i = 0; i < numCaptions; i++)
					if (document.getElementById("caption_" + captions[i].getAttribute("name")))
						document.getElementById("caption_" + captions[i].getAttribute("name")).style.color = captions[i].getAttribute("color");

				if (errors.getElementsByTagName("post_error").length == 1)
					document.forms.postmodify.', $context['post_box_name'], '.style.border = "1px solid red";
				else if (document.forms.postmodify.', $context['post_box_name'], '.style.borderColor == "red" || document.forms.postmodify.', $context['post_box_name'], '.style.borderColor == "red red red red")
				{
					if (typeof(document.forms.postmodify.', $context['post_box_name'], '.runtimeStyle) == "undefined")
						document.forms.postmodify.', $context['post_box_name'], '.style.border = null;
					else
						document.forms.postmodify.', $context['post_box_name'], '.style.borderColor = "";
				}

				// Set the new number of replies.
				if (document.forms.postmodify.elements["num_replies"])
					document.forms.postmodify.num_replies.value = XMLDoc.getElementsByTagName("smf")[0].getElementsByTagName("num_replies")[0].firstChild.nodeValue;

				var newPosts = XMLDoc.getElementsByTagName("smf")[0].getElementsByTagName("new_posts")[0] ? XMLDoc.getElementsByTagName("smf")[0].getElementsByTagName("new_posts")[0].getElementsByTagName("post") : {length: 0};
				var numNewPosts = newPosts.length;
				if (numNewPosts != 0)
				{
					var newTable = \'<span id="new_replies"><\' + \'/span><table width="100%" class="windowbg" cellspacing="0" cellpadding="2" align="center" style="table-layout: fixed;">\';
					for (i = 0; i < numNewPosts; i++)
						newTable += \'<tr class="catbg"><td colspan="2" align="left" class="smalltext"><div style="float: right;">', $txt['posted_on'], ': \' + newPosts[i].getElementsByTagName("time")[0].firstChild.nodeValue + \' <img src="\' + smf_images_url + \'/', $context['user']['language'], '/new.gif" alt="', $txt['preview_new'], '" /><\' + \'/div>', $txt['posted_by'], ': \' + newPosts[i].getElementsByTagName("poster")[0].firstChild.nodeValue + \'<\' + \'/td><\' + \'/tr><tr class="windowbg2"><td colspan="2" class="smalltext" id="msg\' + newPosts[i].getAttribute("id") + \'" width="100%"><div align="right" class="smalltext"><a href="#top" onclick="return insertQuoteFast(\\\'\' + newPosts[i].getAttribute("id") + \'\\\');">', $txt['bbc_quote'], '<\' + \'/a><\' + \'/div><div class="post">\' + newPosts[i].getElementsByTagName("message")[0].firstChild.nodeValue + \'<\' + \'/div><\' + \'/td><\' + \'/tr>\';
					newTable += \'<\' + \'/table>\';
					setOuterHTML(document.getElementById("new_replies"), newTable);
				}

				if (typeof(smf_codeFix) != "undefined")
					smf_codeFix();
			}';

	// Now some javascript to hide the additional options on load...
	if (!empty($settings['additional_options_collapsable']) && !$context['show_additional_options'])
		echo '

			swapOptions();';

	echo '
		// ]]></script>';

	// If the user is replying to a topic show the previous posts.
	if (isset($context['previous_posts']) && count($context['previous_posts']) > 0)
	{
		echo '
		<br />
		<br />

		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			function insertQuoteFast(messageid)
			{
				if (window.XMLHttpRequest)
					getXMLDocument(smf_prepareScriptUrl(smf_scripturl) + "action=quotefast;quote=" + messageid + ";', $context['session_var'], '=', $context['session_id'], ';xml;pb=', $context['post_box_name'], ';mode=" + (editorHandle', $context['post_box_name'], '.bRichTextEnabled ? 1 : 0), onDocReceived);
				else
					reqWin(smf_prepareScriptUrl(smf_scripturl) + "action=quotefast;quote=" + messageid + ";', $context['session_var'], '=', $context['session_id'], ';pb=', $context['post_box_name'], ';mode=" + (editorHandle', $context['post_box_name'], '.bRichTextEnabled ? 1 : 0), 240, 90);
				return true;
			}
			function onDocReceived(XMLDoc)
			{
				var text = "";
				for (var i = 0; i < XMLDoc.getElementsByTagName("quote")[0].childNodes.length; i++)
					text += XMLDoc.getElementsByTagName("quote")[0].childNodes[i].nodeValue;
				editorHandle', $context['post_box_name'], '.insertText(text, false, true);
			}
		// ]]></script>

			<table cellspacing="1" cellpadding="0" width="92%" align="center" class="bordercolor">
				<tr>
					<td>
						<table width="100%" class="windowbg" cellspacing="0" cellpadding="2" align="center">
							<tr class="titlebg">
								<td colspan="2">', $txt['topic_summary'], '</td>
							</tr>
						</table>
						<span id="new_replies"></span>
						<table width="100%" class="windowbg" cellspacing="0" cellpadding="2" align="center" style="table-layout: fixed;">';
		foreach ($context['previous_posts'] as $post)
			echo '
							<tr class="catbg">
								<td colspan="2" align="left" class="smalltext">
									<div style="float: right;">', $txt['posted_on'], ': ', $post['time'], $post['is_new'] ? ' <img src="' . $settings['lang_images_url'] . '/new.gif" alt="' . $txt['preview_new'] . '" />' : '', '</div>
									', $txt['posted_by'], ': ', $post['poster'], '
								</td>
							</tr><tr class="windowbg2">
								<td colspan="2" class="smalltext" id="msg', $post['id'], '" width="100%">
									<div align="right" class="smalltext"><a href="#top" onclick="return insertQuoteFast(', $post['id'], ');">', $txt['bbc_quote'], '</a></div>
									<div class="post">', $post['message'], '</div>
								</td>
							</tr>';
		echo '
						</table>
					</td>
				</tr>
			</table>';
	}
}

// The template for the spellchecker.
function template_spellcheck()
{
	global $context, $settings, $options, $txt;

	// The style information that makes the spellchecker look... like the forum hopefully!
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<title>', $txt['spell_check'], '</title>
		<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
		<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/style.css" />
		<style type="text/css">
			body, td
			{
				font-size: small;
				margin: 0;
				background: #f0f0f0;
				color: #000;
				padding: 10px;
			}
			.highlight
			{
				color: red;
				font-weight: bold;
			}
			#spellview
			{
				border-style: outset;
				border: 1px solid black;
				padding: 5px;
				width: 95%;
				height: 314px;
				overflow: auto;
				background: #ffffff;
			}';

	if ($context['browser']['needs_size_fix'])
		echo '
			@import(', $settings['default_theme_url'], '/css/fonts-compat.css);';

	// As you may expect - we need a lot of javascript for this... load it form the separate files.
	echo '
		</style>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var spell_formname = window.opener.spell_formname;
			var spell_fieldname = window.opener.spell_fieldname;
		// ]]></script>
		<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/spellcheck.js"></script>
		<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/script.js"></script>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			', $context['spell_js'], '
		// ]]></script>
	</head>
	<body onload="nextWord(false);">
		<form action="#" method="post" accept-charset="', $context['character_set'], '" name="spellingForm" id="spellingForm" onsubmit="return false;" style="margin: 0;">
			<div id="spellview">&nbsp;</div>
			<table border="0" cellpadding="4" cellspacing="0" width="100%"><tr class="windowbg">
				<td width="50%" valign="top">
					', $txt['spellcheck_change_to'], '<br />
					<input type="text" name="changeto" style="width: 98%;" />
				</td>
				<td width="50%">
					', $txt['spellcheck_suggest'], '<br />
					<select name="suggestions" style="width: 98%;" size="5" onclick="if (this.selectedIndex != -1) this.form.changeto.value = this.options[this.selectedIndex].text;" ondblclick="replaceWord();">
					</select>
				</td>
			</tr></table>
			<div align="right" style="padding: 4px;">
				<input type="button" name="change" value="', $txt['spellcheck_change'], '" onclick="replaceWord();" />
				<input type="button" name="changeall" value="', $txt['spellcheck_change_all'], '" onclick="replaceAll();" />
				<input type="button" name="ignore" value="', $txt['spellcheck_ignore'], '" onclick="nextWord(false);" />
				<input type="button" name="ignoreall" value="', $txt['spellcheck_ignore_all'], '" onclick="nextWord(true);" />
			</div>
		</form>
	</body>
</html>';
}

function template_quotefast()
{
	global $context, $settings, $options, $txt;

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
		<title>', $txt['retrieving_quote'], '</title>
		<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/script.js"></script>
	</head>
	<body>
		', $txt['retrieving_quote'], '
		<div id="temporary_posting_area" style="display: none;"></div>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[';

	if ($context['close_window'])
		echo '
			window.close();';
	else
	{
		// Lucky for us, Internet Explorer has an "innerText" feature which basically converts entities <--> text. Use it if possible ;).
		echo '
			var quote = \'', $context['quote']['text'], '\';
			var stage = document.createElement ? document.createElement("DIV") : document.getElementById("temporary_posting_area");

			if (typeof(DOMParser) != "undefined" && typeof(window.opera) == "undefined")
			{
				var xmldoc = new DOMParser().parseFromString("<temp>" + \'', $context['quote']['mozilla'], '\'.replace(/\n/g, "_SMF-BREAK_").replace(/\t/g, "_SMF-TAB_") + "</temp>", "text/xml");
				quote = xmldoc.childNodes[0].textContent.replace(/_SMF-BREAK_/g, "\n").replace(/_SMF-TAB_/g, "\t");
			}
			else if (typeof(stage.innerText) != "undefined")
			{
				setInnerHTML(stage, quote.replace(/\n/g, "_SMF-BREAK_").replace(/\t/g, "_SMF-TAB_").replace(/</g, "&lt;").replace(/>/g, "&gt;"));
				quote = stage.innerText.replace(/_SMF-BREAK_/g, "\n").replace(/_SMF-TAB_/g, "\t");
			}

			if (typeof(window.opera) != "undefined")
				quote = quote.replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, \'"\').replace(/&amp;/g, "&");

			window.opener.editorHandle', $context['post_box_name'], '.InsertText(quote);

			window.focus();
			setTimeout("window.close();", 400);';
	}
	echo '
		// ]]></script>
	</body>
</html>';
}

function template_announce()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<form action="', $scripturl, '?action=announce;sa=send" method="post" accept-charset="', $context['character_set'], '">
			<table width="600" cellpadding="5" cellspacing="0" border="0" align="center" class="tborder">
				<tr class="titlebg">
					<td>', $txt['announce_title'], '</td>
				</tr><tr class="windowbg">
					<td class="smalltext" style="padding: 2ex;">', $txt['announce_desc'], '</td>
				</tr><tr>
					<td class="windowbg2">
						', $txt['announce_this_topic'], ' <a href="', $scripturl, '?topic=', $context['current_topic'], '.0">', $context['topic_subject'], '</a><br />
					</td>
				</tr><tr>
					<td class="windowbg2">';

	foreach ($context['groups'] as $group)
				echo '
						<label for="who_', $group['id'], '"><input type="checkbox" name="who[', $group['id'], ']" id="who_', $group['id'], '" value="', $group['id'], '" checked="checked" class="check" /> ', $group['name'], '</label> <i>(', $group['member_count'], ')</i><br />';

	echo '
						<br />
						<label for="checkall"><input type="checkbox" id="checkall" class="check" onclick="invertAll(this, this.form);" checked="checked" /> <i>', $txt['check_all'], '</i></label>
					</td>
				</tr><tr>
					<td class="windowbg2" style="padding-bottom: 1ex;" align="center">
						<input type="submit" value="', $txt['post'], '" />
					</td>
				</tr>
			</table>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="hidden" name="topic" value="', $context['current_topic'], '" />
			<input type="hidden" name="move" value="', $context['move'], '" />
			<input type="hidden" name="goback" value="', $context['go_back'], '" />
		</form>';
}

function template_announcement_send()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<form action="' . $scripturl . '?action=announce;sa=send" method="post" accept-charset="', $context['character_set'], '" name="autoSubmit" id="autoSubmit">
			<table width="600" cellpadding="5" cellspacing="0" border="0" align="center" class="tborder">
				<tr class="titlebg">
					<td>
						', $txt['announce_sending'], ' <a href="', $scripturl, '?topic=', $context['current_topic'], '.0" target="_blank" class="new_win">', $context['topic_subject'], '</a>
					</td>
				</tr><tr>
					<td class="windowbg2"><b>', $context['percentage_done'], '% ', $txt['announce_done'], '</b></td>
				</tr><tr>
					<td class="windowbg2" style="padding-bottom: 1ex;" align="center">
						<input type="submit" name="b" value="', $txt['announce_continue'], '" />
					</td>
				</tr>
			</table>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="hidden" name="topic" value="', $context['current_topic'], '" />
			<input type="hidden" name="move" value="', $context['move'], '" />
			<input type="hidden" name="goback" value="', $context['go_back'], '" />
			<input type="hidden" name="start" value="', $context['start'], '" />
			<input type="hidden" name="membergroups" value="', $context['membergroups'], '" />
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

				document.forms.autoSubmit.b.value = "', $txt['announce_continue'], ' (" + countdown + ")";
				countdown--;

				setTimeout("doAutoSubmit();", 1000);
			}
		// ]]></script>';
}

?>