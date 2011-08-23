<?php
// Version: 2.0 RC1; MoveTopic

// Show an interface for selecting which board to move a post to.
function template_main()
{
	global $context, $settings, $options, $txt, $scripturl;

	theme_linktree();

	echo '
	<form action="', $scripturl, '?action=movetopic2;topic=', $context['current_topic'], '.0" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);">
		<table border="0" width="400" cellspacing="0" cellpadding="4" align="center" class="tborder">
			<tr class="titlebg">
				<td>', $txt['move_topic'], '</td>
			</tr><tr>
				<td class="windowbg" valign="middle" align="center" style="padding-bottom: 1ex; padding-top: 2ex;">
					<b>', $txt['move_to'], ':</b> <select name="toboard">';

	foreach ($context['categories'] AS $category)
	{
		echo '
						<optgroup label="', $category['name'], '">';
		foreach ($category['boards'] as $board)
			echo '
							<option value="', $board['id'], '"', $board['selected'] ? ' selected="selected"' : '', $board['id'] == $context['current_board'] ? ' disabled="disabled"' : '', '>', $board['child_level'] > 0 ? str_repeat('==', $board['child_level']-1) . '=&gt;' : '', $board['name'], '</option>';
		echo '
						</optgroup>';
	}

	echo '
					</select><br />
					<br />
					<label for="reset_subject"><input type="checkbox" name="reset_subject" id="reset_subject" onclick="document.getElementById(\'subjectArea\').style.display = this.checked ? \'block\' : \'none\';" class="check" /> ', $txt['moveTopic2'], '.</label><br />
					<div id="subjectArea" style="display: none; margin-top: 1ex; margin-bottom: 2ex;">
						', $txt['moveTopic3'], ': <input type="text" name="custom_subject" size="30" value="', $context['subject'], '" /><br />
						<label for="enforce_subject"><input type="checkbox" name="enforce_subject" id="enforce_subject" class="check" /> ', $txt['moveTopic4'], '.</label>
					</div>';

	// Disable the reason textarea when the postRedirect checkbox is unchecked...
	echo '
					<label for="postRedirect"><input type="checkbox" name="postRedirect" id="postRedirect" ', $context['is_approved'] ? 'checked="checked"' : '', ' onclick="', $context['is_approved'] ? '' : 'if (this.checked && !confirm(\'' . $txt['move_topic_unapproved_js'] . '\')) return false; ', 'document.getElementById(\'reasonArea\').style.display = this.checked ? \'block\' : \'none\';" class="check" /> ', $txt['moveTopic1'], '.</label><br />
					<div id="reasonArea" style="margin-top: 1ex;', $context['is_approved'] ? '' : 'display: none;', '">
						', $txt['moved_why'], '<br />
						<textarea name="reason" rows="3" cols="40">', $txt['movetopic_default'], '</textarea><br />
					</div>
					<br />
					<input type="submit" value="', $txt['move_topic'], '" onclick="return submitThisOnce(this);" accesskey="s" />
				</td>
			</tr>
		</table>';

	if ($context['back_to_topic'])
		echo '
		<input type="hidden" name="goback" value="1" />';

	echo '
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />
	</form>';
}

?>