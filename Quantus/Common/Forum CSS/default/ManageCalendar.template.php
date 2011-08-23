<?php
// Version: 2.0 RC1; ManageCalendar

// Editing or adding holidays.
function template_edit_holiday()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Start with javascript for getting the calendar dates right.
	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var monthLength = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

			function generateDays()
			{
				var days = 0, selected = 0;
				var dayElement = document.getElementById("day"), yearElement = document.getElementById("year"), monthElement = document.getElementById("month");

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

	// Show a form for all the holiday information.
	echo '
<form action="', $scripturl, '?action=admin;area=managecalendar;sa=editholiday" method="post" accept-charset="', $context['character_set'], '">
	<table width="60%" cellspacing="0" cellpadding="4" border="0" align="center" class="tborder">
		<tr class="titlebg">
			<td colspan="2">', $context['page_title'], '</td>
		</tr><tr class="windowbg2">
			<td width="25%" align="right">', $txt['holidays_title_label'], ':</td>
			<td><input type="text" name="title" value="', $context['holiday']['title'], '" size="60" maxlength="30" /></td>
		</tr><tr class="windowbg2">
			<td align="right">', $txt['calendar_year'], '</td>
			<td>
				<select name="year" id="year" onchange="generateDays();">
					<option value="0000"', $context['holiday']['year'] == '0000' ? ' selected="selected"' : '', '>', $txt['every_year'], '</option>';
		// Show a list of all the years we allow...
		for ($year = $modSettings['cal_minyear']; $year <= $modSettings['cal_maxyear']; $year++)
			echo '
					<option value="', $year, '"', $year == $context['holiday']['year'] ? ' selected="selected"' : '', '>', $year, '</option>';

		echo '
				</select>&nbsp;
				', $txt['calendar_month'], '&nbsp;
				<select name="month" id="month" onchange="generateDays();">';

		// There are 12 months per year - ensure that they all get listed.
		for ($month = 1; $month <= 12; $month++)
			echo '
					<option value="', $month, '"', $month == $context['holiday']['month'] ? ' selected="selected"' : '', '>', $txt['months'][$month], '</option>';

		echo '
				</select>&nbsp;
				', $txt['calendar_day'], '&nbsp;
				<select name="day" id="day" onchange="generateDays();">';

		// This prints out all the days in the current month - this changes dynamically as we switch months.
		for ($day = 1; $day <= $context['holiday']['last_day']; $day++)
			echo '
				<option value="', $day, '"', $day == $context['holiday']['day'] ? ' selected="selected"' : '', '>', $day, '</option>';

		echo '
			</select>
		</td>
		</tr><tr class="windowbg2">
			<td colspan="2" align="center">';
	if ($context['is_new'])
		echo '
				<input type="submit" value="', $txt['holidays_button_add'], '" />';
	else
		echo '
				<input type="submit" name="edit" value="', $txt['holidays_button_edit'], '" />
				<input type="submit" name="delete" value="', $txt['holidays_button_remove'], '" />
				<input type="hidden" name="holiday" value="', $context['holiday']['id'], '" />';
	echo '
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			</td>
		</tr>
	</table>
</form>';
}

?>