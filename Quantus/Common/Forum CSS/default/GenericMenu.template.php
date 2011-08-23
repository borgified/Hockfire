<?php
// Version: 2.0 RC1; GenericMenu

// This contains the html for the side bar of the admin center, which is used for all admin pages.
function template_generic_menu_sidebar_above()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// This is the main table - we need it so we can keep the content to the right of it.
	echo '
		<table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top: 0; clear: left;"><tr>
			<td width="150" valign="top" style="width: 23ex; padding-right: 10px; padding-bottom: 10px;">
				<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor">';

	// What one are we rendering?
	$context['cur_menu_id'] = isset($context['cur_menu_id']) ? $context['cur_menu_id'] + 1 : 1;
	$menu_context = &$context['menu_data_' . $context['cur_menu_id']];

	// For every section that appears on the sidebar...
	$firstSection = true;
	foreach ($menu_context['sections'] as $section)
	{
		// Show the section header - and pump up the line spacing for readability.
		echo '
					<tr>
						<td class="catbg">', $section['title'];

		if ($firstSection && !empty($menu_context['can_toggle_drop_down']))
			echo '
						<a href="', $scripturl, '?action=', $menu_context['current_action'], ';area=', $menu_context['current_area'], (!empty($menu_context['current_subsection']) ? ';sa=' . $menu_context['current_subsection'] : ''), $menu_context['extra_parameters'], ';togglebar=0;', $context['session_var'], '=', $context['session_id'], '"><img style="margin: 0 0 0 5px;" src="' , $context['menu_image_path'], '/change_menu2.png" alt="!" /></a>';
		echo '
						</td>
					</tr>
					<tr class="windowbg2">
						<td class="smalltext" style="line-height: 1.3; padding-bottom: 3ex;">';

		// For every area of this section show a link to that area (bold if it's currently selected.)
		foreach ($section['areas'] as $i => $area)
		{
			// Not supposed to be printed?
			if (empty($area['label']))
				continue;

			// Is this the current area, or just some area?
			if ($i == $menu_context['current_area'])
			{
				echo '
							<b><a href="', (isset($area['url']) ? $area['url'] : $scripturl . '?action=' . $menu_context['current_action'] . ';area=' . $i), $menu_context['extra_parameters'], '">', $area['label'], '</a></b><br />';

				if (empty($context['tabs']))
					$context['tabs'] = isset($area['subsections']) ? $area['subsections'] : array();
			}
			else
				echo '
							<a href="', (isset($area['url']) ? $area['url'] : $scripturl . '?action=' . $menu_context['current_action'] . ';area=' . $i), $menu_context['extra_parameters'], '">', $area['label'], '</a><br />';
		}

		echo '
						</td>
					</tr>';

		$firstSection = false;
	}

	// This is where the actual "main content" area for the admin section starts.
	echo '
			</table>
		</td>
		<td valign="top">';

	// If there are any "tabs" setup, this is the place to shown them.
	//!!! Clean this up!
	if (!empty($context['tabs']) && empty($context['force_disable_tabs']))
		template_generic_menu_tabs($menu_context);
}

// Part of the sidebar layer - closes off the main bit.
function template_generic_menu_sidebar_below()
{
	global $context, $settings, $options;

	echo '
			</td>
		</tr>
	</table>';
}

// This contains the html for the side bar of the admin center, which is used for all admin pages.
function template_generic_menu_dropdown_above()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Which menu are we rendering?
	$context['cur_menu_id'] = isset($context['cur_menu_id']) ? $context['cur_menu_id'] + 1 : 1;
	$menu_context = &$context['menu_data_' . $context['cur_menu_id']];

	echo '
	<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/menu.js"></script>';

	if (!empty($menu_context['can_toggle_drop_down']))
		echo '
		<div id="menu_toggle">
			<a href="', $scripturl, '?action=', $menu_context['current_action'], ';area=', $menu_context['current_area'], (!empty($menu_context['current_subsection']) ? ';sa=' . $menu_context['current_subsection'] : ''), $menu_context['extra_parameters'], ';togglebar=1;', $context['session_var'], '=', $context['session_id'], '"><img style="margin: 0 2px 0 2px;" src="' , $context['menu_image_path'], '/change_menu.png" alt="*" /></a>
		</div>';

	echo '
	<div id="adm_container">
		<ul class="admin_menu" id="dropdown_menu_', $context['cur_menu_id'], '">';

	// Main areas first.
	$s = 0;
	foreach ($menu_context['sections'] as $section)
	{
		$s ++;
		$is_last = $s == count($menu_context['sections']);
		
		if ($section['id'] == $menu_context['current_section'])
		{
			echo '
			<li class="chosen', $is_last ? ' last' : '', '"><h4>', $section['title'] , '</h4>
				<ul>';
		}
		else
			echo '
			<li', $is_last ? ' class="last"' : '', '><h4>', $section['title'] , '</h4>
				<ul>';

		// For every area of this section show a link to that area (bold if it's currently selected.)
		foreach ($section['areas'] as $i => $area)
		{
			// Not supposed to be printed?
			if (empty($area['label']))
				continue;

			echo '
					<li>';

			// Is this the current area, or just some area?
			if ($i == $menu_context['current_area'])
			{
				echo '
						<a class="chosen', !empty($area['subsections']) ? ' subsection' : '' , '" href="', (isset($area['url']) ? $area['url'] : $scripturl . '?action=' . $menu_context['current_action'] . ';area=' . $i), $menu_context['extra_parameters'], '">' , $area['icon'] , $area['label'], '</a>';

				if (empty($context['tabs']))
					$context['tabs'] = isset($area['subsections']) ? $area['subsections'] : array();
			}
			else
				echo '
						<a href="', (isset($area['url']) ? $area['url'] : $scripturl . '?action=' . $menu_context['current_action'] . ';area=' . $i), $menu_context['extra_parameters'], '"', !empty($area['subsections']) ? ' class="subsection"' : '' , '>' , $area['icon'] , $area['label'] , '</a>';

			// Is there any subsections?
			if (!empty($area['subsections']))
			{
				echo '
						<ul>';

				foreach ($area['subsections'] as $sa => $sub)
				{
					if (!empty($sub['disabled']))
						continue;

					echo '
							<li>';

					$url = isset($sub['url']) ? $sub['url'] : (isset($area['url']) ? $area['url'] : $scripturl . '?action=' . $menu_context['current_action'] . ';area=' . $i) . ';sa=' . $sa;

					echo '
								<a ', !empty($sub['selected']) ? 'class="chosen" ' : '', 'href="', $url, $menu_context['extra_parameters'], '">' , $sub['label'], '</a>';

					echo '
							</li>';
				}

				echo '
						</ul>';
			}

			echo '
					</li>';
		}
		echo '
				</ul>
			</li>';
	}

	echo '
		</ul></div>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var menuHandle = new smfMenu("dropdown_menu_', $context['cur_menu_id'], '");
		// ]]></script>';

	// This is the main table - we need it so we can keep the content to the right of it.
	echo '
		<table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top: 0; clear: both;"><tr>
			<td valign="top">';

	// It's possible that some pages have their own tabs they wanna force...
	if (!empty($context['tabs']))
		template_generic_menu_tabs($menu_context);
}

// Part of the admin layer - used with admin_above to close the table started in it.
function template_generic_menu_dropdown_below()
{
	global $context, $settings, $options;

	echo '
			</td>
		</tr>
	</table>';
}

// Some code for showing a tabbed view.
function template_generic_menu_tabs(&$menu_context)
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Handy shortcut.
	$tab_context = &$menu_context['tab_data'];


	echo '
				<table border="0" cellspacing="0" cellpadding="4" align="center" width="100%" class="tborder" ' , (isset($settings['use_tabs']) && $settings['use_tabs']) ? '' : 'style="margin-bottom: 2ex;"' , '>
					<tr class="titlebg">
						<td>';
	// Show a help item?
	if (!empty($tab_context['help']))
		echo '
							<a href="', $scripturl, '?action=helpadmin;help=', $tab_context['help'], '" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ';
	echo '
							', $tab_context['title'], '
						</td>
					</tr>
					<tr class="windowbg">';

	// Exactly how many tabs do we have?
	foreach ($context['tabs'] as $id => $tab)
	{
		// Can this not be accessed?
		if (!empty($tab['disabled']))
		{
			$tab_context['tabs'][$id]['disabled'] = true;
			continue;
		}

		// Did this not even exist - or do we not have a label?
		if (!isset($tab_context['tabs'][$id]))
			$tab_context['tabs'][$id] = array('label' => $tab['label']);
		elseif (!isset($tab_context['tabs'][$id]['label']))
			$tab_context['tabs'][$id]['label'] = $tab['label'];

		// Has a custom URL defined in the main admin structure?
		if (isset($tab['url']) && !isset($tab_context['tabs'][$id]['url']))
			$tab_context['tabs'][$id]['url'] = $tab['url'];
		// Any additional paramaters for the url?
		if (isset($tab['add_params']) && !isset($tab_context['tabs'][$id]['add_params']))
			$tab_context['tabs'][$id]['add_params'] = $tab['add_params'];
		// Has it been deemed selected?
		if (!empty($tab['is_selected']))
			$tab_context['tabs'][$id]['is_selected'] = true;
		// Is this the last one?
		if (!empty($tab['is_last']) && !isset($tab_context['override_last']))
			$tab_context['tabs'][$id]['is_last'] = true;
	}

	// Find the selected tab
	foreach ($tab_context['tabs'] as $sa => $tab)
		if (!empty($tab['is_selected']) || (isset($menu_context['current_subsection']) && $menu_context['current_subsection'] == $sa))
		{
			$selected_tab = $tab;
			$tab_context['tabs'][$sa]['is_selected'] = true;
		}

	// Shall we use the tabs?
	if (!empty($settings['use_tabs']))
	{
		echo '
						<td class="smalltext" style="padding: 2ex;">', !empty($selected_tab['description']) ? $selected_tab['description'] : $tab_context['description'], '</td>
					</tr>
				</table>';

		// The admin tabs.
		echo '
				<table cellpadding="0" cellspacing="0" border="0" style="margin-left: 10px;">
					<tr>
						<td class="maintab_first">&nbsp;</td>';

		// Print out all the items in this tab.
		foreach ($tab_context['tabs'] as $sa => $tab)
		{
			if (!empty($tab['disabled']))
				continue;

			if (!empty($tab['is_selected']))
			{
				echo '
						<td class="maintab_active_first">&nbsp;</td>
						<td valign="top" class="maintab_active_back">
							<a href="', (isset($tab['url']) ? $tab['url'] : $scripturl . '?action=' . $menu_context['current_action'] . ';area=' . $menu_context['current_area'] . ';sa=' . $sa), $menu_context['extra_parameters'], isset($tab['add_params']) ? $tab['add_params'] : '', '">' , $tab['label'], '</a>
						</td>
						<td class="maintab_active_last">&nbsp;</td>';
			}
			else
				echo '
						<td valign="top" class="maintab_back">
							<a href="', (isset($tab['url']) ? $tab['url'] : $scripturl . '?action=' . $menu_context['current_action'] . ';area=' . $menu_context['current_area'] . ';sa=' . $sa), $menu_context['extra_parameters'], isset($tab['add_params']) ? $tab['add_params'] : '', '">' , $tab['label'], '</a>
						</td>';
		}

		// the end of tabs
		echo '
						<td class="maintab_last">&nbsp;</td>
					</tr>
				</table><br />';
	}
	// ...if not use the old style
	else
	{
		echo '
						<td align="left"><b>';

		// Print out all the items in this tab.
		foreach ($tab_context['tabs'] as $sa => $tab)
		{
			if (!empty($tab['disabled']))
				continue;

			if (!empty($tab['is_selected']))
			{
				echo '
							<img src="', $settings['images_url'], '/selected.gif" alt="*" /> <b><a href="', (isset($tab['url']) ? $tab['url'] : $scripturl . '?action=' . $menu_context['current_action'] . ';area=' . $menu_context['current_area'] . ';sa=' . $sa), ';', $context['session_var'], '=', $context['session_id'], '">' , $tab['label'], '</a></b>';
			}
			else
				echo '
							<a href="', (isset($tab['url']) ? $tab['url'] : $scripturl . '?action=' . $menu_context['current_action'] . ';area=' . $menu_context['current_area'] . ';sa=' . $sa), ';', $context['session_var'], '=', $context['session_id'], '">' , $tab['label'], '</a>';

			if (empty($tab['is_last']))
				echo ' | ';
		}

		echo '
						</b></td>
					</tr>
					<tr class="windowbg">
						<td class="smalltext" style="padding: 2ex;">', isset($selected_tab['description']) ? $selected_tab['description'] : $tab_context['description'], '</td>
					</tr>
				</table>';
	}
}

?>