<?php
// Version: 2.0 RC1; ManageScheduledTasks

// Template for listing all scheduled tasks.
function template_view_scheduled_tasks()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// We completed some tasks?
	if (!empty($context['tasks_were_run']))
		echo '
		<div class="windowbg" style="margin: 1ex; padding: 1ex 2ex; border: 1px dashed green; color: green;">
			', $txt['scheduled_tasks_were_run'], '
		</div>';

	template_show_list('scheduled_tasks');
}

// A template for, you guessed it, editing a task!
function template_edit_scheduled_tasks()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// Starts off with general maintenance procedures.
	echo '
	<form action="', $scripturl, '?action=admin;area=scheduledtasks;sa=taskedit;save;tid=', $context['task']['id'], '" method="post" accept-charset="', $context['character_set'], '">
		<table align="center" width="80%" cellpadding="4" cellspacing="0" border="0" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['scheduled_task_edit'], '</td>
			</tr><tr class="windowbg2">
				<td colspan="2">
					<span class="smalltext">
						<em>', sprintf($txt['scheduled_task_time_offset'], $context['server_time']), '</em>
					</span>
				</td>
			</tr><tr class="windowbg" valign="top">
				<td width="30%">
					<b>', $txt['scheduled_tasks_name'], ':</b>
				</td><td width="70%">
					', $context['task']['name'], '</a><br />
					<span class="smalltext">', $context['task']['desc'], '</span>
				</td>
			</tr><tr class="windowbg">
				<td width="30%">
					<b>', $txt['scheduled_task_edit_interval'], ':</b>
				</td><td width="70%">
					', $txt['scheduled_task_edit_repeat'], '
					<input type="text" name="regularity" value="', empty($context['task']['regularity']) ? 1 : $context['task']['regularity'], '" onchange="if (this.value < 1) this.value = 1;" size="2" maxlength="2" />
					<select name="unit">
						<option value="0">', $txt['scheduled_task_edit_pick_unit'], '</option>
						<option value="0">---------------------</option>
						<option value="m" ', empty($context['task']['unit']) || $context['task']['unit'] == 'm' ? 'selected="selected"' : '', '>', $txt['scheduled_task_reg_unit_m'], '</option>
						<option value="h" ', $context['task']['unit'] == 'h' ? 'selected="selected"' : '', '>', $txt['scheduled_task_reg_unit_h'], '</option>
						<option value="d" ', $context['task']['unit'] == 'd' ? 'selected="selected"' : '', '>', $txt['scheduled_task_reg_unit_d'], '</option>
						<option value="w" ', $context['task']['unit'] == 'w' ? 'selected="selected"' : '', '>', $txt['scheduled_task_reg_unit_w'], '</option>
					</select>
				</td>
			</tr><tr class="windowbg" valign="top">
				<td width="30%">
					<b>', $txt['scheduled_task_edit_start_time'], ':</b><br />
					<span class="smalltext">', $txt['scheduled_task_edit_start_time_desc'], '</span>
				</td><td width="70%">
					<input type="text" name="offset" value="', $context['task']['offset_formatted'], '" size="6" maxlength="5" />
				</td>
			</tr><tr class="windowbg">
				<td width="30%">
					<b>', $txt['scheduled_tasks_enabled'], ':</b>
				</td><td width="70%">
					<input type="checkbox" name="enabled" id="enabled" ', !$context['task']['disabled'] ? 'checked="checked"' : '', ' class="check" />
				</td>
			</tr><tr class="windowbg">
				<td colspan="2" align="center">
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="submit" name="save" value="', $txt['scheduled_tasks_save_changes'], '" />
				</td>
			</tr>
		</table>
	</form>';
}

?>