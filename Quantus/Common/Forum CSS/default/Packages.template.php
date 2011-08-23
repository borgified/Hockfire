<?php
// Version: 2.0 RC1; Packages

function template_main()
{
	global $context, $settings, $options;
}

function template_view_package()
{
	global $context, $settings, $options, $txt, $scripturl, $smcFunc;

	echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td>', $txt['apply_mod'], '</td>
			</tr><tr>
				<td class="windowbg2">';

	if ($context['is_installed'])
		echo '
					<b>', $txt['package_installed_warning1'], '</b><br />
					<br />
					', $txt['package_installed_warning2'], '<br />
					<br />';

	echo '
					', $txt['package_installed_warning3'], '
				</td>
			</tr>
		</table>
		<br />';

	// Do errors exist in the install? If so light them up like a christmas tree.
	if ($context['has_failure'])
	{
		echo '
				<div style="margin: 2ex; padding: 2ex; border: 2px dashed #cc3344; color: black; background-color: #ffe4e9; margin-top: 0;">
					<div style="float: left; width: 2ex; font-size: 2em;" class="alert">!!</div>
						<b style="text-decoration: underline;">', $txt['package_will_fail_title'], '</b><br />
						<div style="padding-left: 6ex;">
							', $txt['package_will_fail_warning'], '
						</div>
					</div>
				</div>';
	}

	if (isset($context['package_readme']))
		echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td>', $txt['package_install_readme'], '</td>
			</tr><tr>
				<td class="windowbg2">', $context['package_readme'], '</td>
			</tr>
		</table>
		<br />';

	echo '
	<form action="', $scripturl, '?action=admin;area=packages;sa=', $context['uninstalling'] ? 'uninstall' : 'install', $context['ftp_needed'] ? '' : '2', ';package=', $context['filename'], ';pid=', $context['install_id'], '" onsubmit="submitonce(this);" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td>', $context['uninstalling'] ? $txt['package_uninstall_actions'] : $txt['install_actions'], '</td>
			</tr>';

	// Are there data changes to be removed?
	if ($context['uninstalling'] && !empty($context['database_changes']))
	{
		echo '
			<tr class="windowbg2">
				<td>
					<label for="do_db_changes"><input type="checkbox" name="do_db_changes" id="do_db_changes" class="check" />', $txt['package_db_uninstall'], '</label> [<a href="#" onclick="return swap_database_changes();">', $txt['package_db_uninstall_details'], '</a>]
				</td>
			</tr>
			<tr class="windowbg2" id="db_changes_div">
				<td>
					', $txt['package_db_uninstall_actions'], ':
					<ul>';

		foreach ($context['database_changes'] as $change)
			echo '
						<li>', $change, '</li>';
		echo '
					</ul>
				</td>
			</tr>';
	}

	echo '
			<tr>
				<td class="catbg">', $context['uninstalling'] ? $txt['package_uninstall_actions'] : $txt['package_install_actions'], ' &quot;', $context['package_name'], '&quot;:</td>
			</tr><tr>
				<td class="windowbg2">';

	if (empty($context['actions']) && empty($context['database_changes']))
		echo '
					<b>', $txt['corrupt_compatable'], '</b>';
	else
	{
		echo '
					', $txt['perform_actions'], '
					<table border="0" cellpadding="3" cellspacing="0" width="100%" style="margin-top: 1ex;">
						<tr>
							<td width="20"></td>
							<td width="30"></td>
							<td><b>', $txt['package_install_type'], '</b></td>
							<td width="50%"><b>', $txt['package_install_action'], '</b></td>
							<td width="20%"><b>', $txt['package_install_desc'], '</b></td>
						</tr>';

		$alternate = true;
		$i = 1;
		$action_num = 1;
		$js_operations = array();
		foreach ($context['actions'] as $packageaction)
		{
			// Did we pass or fail?  Need to now for later on.
			$js_operations[$action_num] = isset($packageaction['failed']) ? $packageaction['failed'] : 0;

			echo '
						<tr class="windowbg', $alternate ? '' : '2', '">
							<td style="padding-right: 2ex;">', isset($packageaction['operations']) ? '<a href="#" onclick="operationElements[' . $action_num . '].toggle(); return false;"><img id="operation_img_' . $action_num . '" src="' . $settings['images_url'] . '/sort_down.gif" alt="*" /></a>' : '', '</td>
							<td style="padding-right: 2ex;">', $i++, '.</td>
							<td style="padding-right: 2ex;">', $packageaction['type'], '</td>
							<td style="padding-right: 2ex;">', $packageaction['action'], '</td>
							<td style="padding-right: 2ex;">', $packageaction['description'], '</td>
						</tr>';

			// Is there water on the knee? Operation!
			if (isset($packageaction['operations']))
			{
				echo '
						<tr id="operation_', $action_num, '">
							<td colspan="5" class="windowbg3">
								<table border="0" cellpadding="3" cellspacing="0" width="100%">';

				// Show the operations.
				$alternate2 = true;
				$operation_num = 1;
				foreach ($packageaction['operations'] as $operation)
				{
					// Determine the possition text.
					$operation_text = $operation['position'] == 'replace' ? 'operation_replace' : ($operation['position'] == 'before' ? 'operation_after' : 'operation_before');

					echo '
									<tr class="windowbg', $alternate2 ? '' : '2', '">
										<td style="padding-right: 2ex;" width="0"></td>
										<td style="padding-right: 2ex;" width="30" class="smalltext"><a href="' . $scripturl . '?action=admin;area=packages;sa=showoperations;operation_key=', $operation['operation_key'], ';package=', $_REQUEST['package'], ';filename=', $operation['filename'], ($operation['is_boardmod'] ? ';boardmod' : ''), (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'uninstall' ? ';reverse' : ''), '" onclick="return reqWin(this.href, 600, 400, false);"><img src="', $settings['default_images_url'], '/admin/package_ops.gif" alt="" /></a></td>
										<td style="padding-right: 2ex;" width="30" class="smalltext">', $operation_num, '.</td>
										<td style="padding-right: 2ex;" width="23%" class="smalltext">', $txt[$operation_text], '</td>
										<td style="padding-right: 2ex;" width="50%" class="smalltext">', $operation['action'], '</td>
										<td style="padding-right: 2ex;" width="20%" class="smalltext">', $operation['description'], !empty($operation['ignore_failure']) ? ' (' . $txt['operation_ignore'] . ')' : '', '</td>
									</tr>';

					$operation_num++;
					$alternate2 = !$alternate2;
				}

				echo '
								</table>
							</td>
						</tr>';

				// Increase it.
				$action_num++;
			}
			$alternate = !$alternate;
		}
					echo '
					</table>
				</td>
			</tr>';

		// What if we have custom themes we can install into? List them too!
		if (!empty($context['theme_actions']))
		{
			echo '
			<tr class="catbg">
				<td colspan="6"><a href="#" onclick="return swap_theme_actions();"><img id="swap_theme_image" src="', $settings['images_url'], '/', (empty($context['themes_locked']) ? 'expand.gif' : 'collapse.gif'), '" /></a> ', $txt['package_other_themes'], '</td>
			</tr>
			<tr>
				<td class="windowbg2" id="custom_changes">
					<table border="0" cellpadding="3" cellspacing="0" width="100%">
						<tr class="windowbg2">
							<td colspan="6">
								<span class="smalltext">', $txt['package_other_themes_desc'], '</span>
							</td>
						</tr>';

			// Loop through each theme and display it's name, and then it's details.
			foreach ($context['theme_actions'] as $id => $theme)
			{
				// Pass?
				$js_operations[$action_num] = !empty($theme['has_failure']);

				echo '
						<tr class="titlebg">
							<td></td>
							<td>';
				if (!empty($context['themes_locked']))
					echo '
								<input type="hidden" name="custom_theme[]" value="', $id, '" />';
				echo '
								<input type="checkbox" name="custom_theme[]" id="custom_theme_', $id, '" value="', $id, '" class="check" onclick="', (!empty($theme['has_failure']) ? 'if (this.form.custom_theme_' . $id . '.checked && !confirm(\'' . $txt['package_theme_failure_warning'] . '\')) return false;' : ''), 'invertAll(this, this.form, \'dummy_theme_', $id, '\', true);" ', !empty($context['themes_locked']) ? 'disabled="disabled" checked="checked"' : '', '/>
							</td>
							<td colspan="3">
								', $theme['name'], '
							</td>
						</tr>';

				foreach ($theme['actions'] as $action)
				{
					echo '
						<tr class="windowbg', $alternate ? '' : '2', '">
							<td style="padding-right: 2ex;">', isset($packageaction['operations']) ? '<a href="#" onclick="operationElements[' . $action_num . '].toggle(); return false;"><img id="operation_img_' . $action_num . '" src="' . $settings['images_url'] . '/sort_down.gif" alt="*" /></a>' : '', '</td>
							<td width="30" style="padding-right: 2ex;">
								<input type="checkbox" name="theme_changes[]" value="', !empty($action['value']) ? $action['value'] : '', '" id="dummy_theme_', $id, '[]" class="check" ', (!empty($action['not_mod']) ? '' : 'disabled="disabled"'), ' ', !empty($context['themes_locked']) ? 'checked="checked"' : '', '/>
							</td>
							<td style="padding-right: 2ex;">', $action['type'], '</td>
							<td width="50%" style="padding-right: 2ex;">', $action['action'], '</td>
							<td width="20%" style="padding-right: 2ex;"><b>', $action['description'], '</b></td>
						</tr>';

					// Is there water on the knee? Operation!
					if (isset($action['operations']))
					{
						echo '
						<tr id="operation_', $action_num, '">
							<td colspan="5" class="windowbg3">
								<table border="0" cellpadding="3" cellspacing="0" width="100%">';

						$alternate2 = true;
						$operation_num = 1;
						foreach ($action['operations'] as $operation)
						{
							// Determine the possition text.
							$operation_text = $operation['position'] == 'replace' ? 'operation_replace' : ($operation['position'] == 'before' ? 'operation_after' : 'operation_before');

							echo '
									<tr class="windowbg', $alternate2 ? '' : '2', '">
										<td style="padding-right: 2ex;" width="0"></td>
										<td style="padding-right: 2ex;" width="30" class="smalltext"><a href="' . $scripturl . '?action=admin;area=packages;sa=showoperations;operation_key=', $operation['operation_key'], ';package=', $_REQUEST['package'], ';filename=', $operation['filename'], ($operation['is_boardmod'] ? ';boardmod' : ''), (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'uninstall' ? ';reverse' : ''), '" onclick="return reqWin(this.href, 600, 400, false);"><img src="', $settings['default_images_url'], '/admin/package_ops.gif" alt="" /></a></td>
										<td style="padding-right: 2ex;" width="30" class="smalltext">', $operation_num, '.</td>
										<td style="padding-right: 2ex;" width="23%" class="smalltext">', $txt[$operation_text], '</td>
										<td style="padding-right: 2ex;" width="50%" class="smalltext">', $operation['action'], '</td>
										<td style="padding-right: 2ex;" width="20%" class="smalltext">', $operation['description'], !empty($operation['ignore_failure']) ? ' (' . $txt['operation_ignore'] . ')' : '', '</td>
									</tr>';
							$operation_num++;
							$alternate2 = !$alternate2;
						}

						echo '
								</table>
							</td>
						</tr>';

						// Increase it.
						$action_num++;
					}
				}

				$alternate = !$alternate;
			}

			echo '
					</table>
				</td>
			</tr>';
		}
	}

	// Are we effectively ready to install?
	if (!$context['ftp_needed'] && (!empty($context['actions']) || !empty($context['database_changes'])))
	{
		echo '
		<tr class="titlebg">
			<td align="right">
				<input type="submit" value="', $context['uninstalling'] ? $txt['package_uninstall_now'] : $txt['package_install_now'], '" onclick="return ', !empty($context['has_failure']) ? '(submitThisOnce(this) &amp;&amp; confirm(\'' . ($context['uninstalling'] ? $txt['package_will_fail_popup_uninstall'] : $txt['package_will_fail_popup']) . '\'))"' : 'submitThisOnce(this)', ';" />
			</td>
		</tr>';
	}
	// If we need ftp information then demand it!
	elseif ($context['ftp_needed'])
	{
		echo '
			<tr>
				<td class="catbg">', $txt['package_ftp_necessary'], '</td>
			</tr><tr>
				<td class="windowbg2">
					', template_control_chmod(), '
				</td>
			</tr>';
	}
		echo '
			</table>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />', (isset($context['form_sequence_number']) && !$context['ftp_needed']) ? '
			<input type="hidden" name="seqnum" value="' . $context['form_sequence_number'] . '" />' : '', '
		</form>';

	// Toggle options.
	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		function packageOperation(uniqueId, initialState)
		{
			// The id of the field.
			this.uid = uniqueId;
			this.operationToggle = new smfToggle(\'operation_\' + uniqueId, initialState);
			this.operationToggle.addToggleImage(\'operation_img_\' + uniqueId, \'/sort_down.gif\', \'/selected.gif\');
			this.operationToggle.addTogglePanel(\'operation_\' + uniqueId);
			this.toggle = toggleOperation;

			function toggleOperation()
			{
				this.operationToggle.toggle();
			}
		}

		var operationElements = new Array();';

		// Operations.
		foreach ($js_operations as $key => $operation)
		{
			echo '
			operationElements[', $key, '] = new packageOperation(', $key, ', false);';

			// Failed?
			if (!$operation)
				echo '
			operationElements[', $key, '].toggle();';
		}

	echo '
	// ]]></script>';

	// Some javascript for collapsing/expanded theme section.
	if (!empty($context['theme_actions']))
		echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var theme_action_area = document.getElementById(\'custom_changes\');
		var swap_theme_image = document.getElementById(\'swap_theme_image\');
		var vis = ', empty($context['themes_locked']) ? 'false' : 'true', ';
		theme_action_area.style.display = vis ? "" : "none";
		function swap_theme_actions()
		{
			vis = !vis;
			theme_action_area.style.display = vis ? "" : "none";
			swap_theme_image.src = "', $settings['images_url'], '/" + (vis ? "collapse" : "expand") + ".gif";
			return false;
		}
	// ]]></script>';

	// And a bit more for database changes.
	if (!empty($context['database_changes']))
		echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var database_changes_area = document.getElementById(\'db_changes_div\');
		var db_vis = false;
		database_changes_area.style.display = "none";
		function swap_database_changes()
		{
			db_vis = !db_vis;
			database_changes_area.style.display = db_vis ? "" : "none";
			return false;
		}
	// ]]></script>';
}

function template_extract_package()
{
	global $context, $settings, $options, $txt, $scripturl;

	if (!empty($context['redirect_url']))
	{
		echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		setTimeout("doRedirect();", ', empty($context['redirect_timeout']) ? '5000' : $context['redirect_timeout'], ');

		function doRedirect()
		{
			window.location = "', $context['redirect_url'], '";
		}
	// ]]></script>';
	}

	echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">';

	if (empty($context['redirect_url']))
	{
		echo '
			<tr class="titlebg">
				<td>', $context['uninstalling'] ? $txt['uninstall'] : $txt['extracting'], '</td>
			</tr>
			<tr>
				<td class="catbg">', $txt['package_installed_extract'], '</td>
			</tr>';
	}
	else
		echo '
			<tr class="titlebg">
				<td>', $txt['package_installed_redirecting'], '</td>
			</tr>';

	echo '
			<tr>
				<td class="windowbg2" width="100%">';

	// If we are going to redirect we have a slightly different agenda.
	if (!empty($context['redirect_url']))
	{
		echo '
					', $context['redirect_text'], '<br /><br />
				</td>
			</tr><tr>
				<td class="windowbg2" width="100%" align="center">
					<a href="', $context['redirect_url'], '">', $txt['package_installed_redirect_go_now'], '</a> | <a href="', $scripturl, '?action=admin;area=packages;sa=browse">', $txt['package_installed_redirect_cancel'], '</a>';
	}
	elseif ($context['uninstalling'])
		echo '
					', $txt['package_uninstall_done'];
	elseif ($context['install_finished'])
	{
		if ($context['extract_type'] == 'avatar')
			echo '
					', $txt['avatars_extracted'];
		elseif ($context['extract_type'] == 'language')
			echo '
					', $txt['language_extracted'];
		else
			echo '
					', $txt['package_installed_done'];
	}
	else
		echo '
					', $txt['corrupt_compatable'];

	echo '
				</td>
			</tr>
		</table>';

	// Show the "restore permissions" screen?
	if (function_exists('template_show_list') && !empty($context['restore_file_permissions']['rows']))
	{
		echo '<br />';
		template_show_list('restore_file_permissions');
	}
}

function template_list()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td>', $txt['list_file'], '</td>
			</tr>
			<tr>
				<td class="catbg">', $txt['files_archive'], ' ', $context['filename'], ':</td>
			</tr><tr>
				<td class="windowbg2" width="100%">
					<ol>';

	foreach ($context['files'] as $fileinfo)
		echo '
						<li><a href="', $scripturl, '?action=admin;area=packages;sa=examine;package=', $context['filename'], ';file=', $fileinfo['filename'], '" title="', $txt['view'], '">', $fileinfo['filename'], '</a> (', $fileinfo['size'], ' ', $txt['package_bytes'], ')</li>';

	echo '
					</ol>
					<a href="', $scripturl, '?action=admin;area=packages">[ ', $txt['back'], ' ]</a>
				</td>
			</tr>
		</table>';
}

function template_examine()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor" style="table-layout: fixed;">
			<tr class="titlebg">
				<td>', $txt['package_examine_file'], '</td>
			</tr>
			<tr>
				<td class="catbg">', $txt['package_file_contents'], ' ', $context['filename'], ':</td>
			</tr><tr>
				<td class="windowbg2" style="width: 100%;">
					<pre style="overflow: auto; width: 100%; padding-bottom: 1ex;">', $context['filedata'], '</pre>

					<a href="', $scripturl, '?action=admin;area=packages;sa=list;package=', $context['package'], '">[ ', $txt['list_files'], ' ]</a>
				</td>
			</tr>
		</table>';
}

function template_view_installed()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td>' . $txt['view_and_remove'] . '</td>
			</tr><tr>
				<td class="windowbg2">';

	if (empty($context['installed_mods']))
	{
		echo '
					<table border="0" cellpadding="1" cellspacing="0" width="100%">
						<tr>
							<td style="padding-bottom: 1ex;">', $txt['no_mods_installed'], '</td>
						</tr>
					</table>';
	}
	else
	{
		echo '
					<table border="0" cellpadding="1" cellspacing="0" width="100%">
						<tr>
							<td width="32"></td>
							<td width="25%">', $txt['mod_name'], '</td>
							<td width="25%">', $txt['mod_version'], '</td>
							<td width="49%"></td>
						</tr>';

		$alt = false;
		foreach ($context['installed_mods'] as $i => $file)
		{
			echo '
						<tr class="', $alt ? 'windowbg' : 'windowbg2', '">
							<td>', ++$i, '.</td>
							<td>', $file['name'], '</td>
							<td>', $file['version'], '</td>
							<td align="right"><a href="', $scripturl, '?action=admin;area=packages;sa=uninstall;package=', $file['filename'], ';pid=', $file['id'], '">[ ', $txt['uninstall'], ' ]</a></td>
						</tr>';
			$alt = !$alt;
		}

		echo '
					</table>
					<br />
					<a href="', $scripturl, '?action=admin;area=packages;sa=flush">[ ', $txt['delete_list'], ' ]</a>';
	}

	echo '
				</td>
			</tr>
		</table>';
}

function template_browse()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings, $forum_version;

	echo '
		<table width="100%" cellspacing="0" cellpadding="4" border="0" class="tborder">
			<tr class="titlebg">
				<td><a href="', $scripturl, '?action=helpadmin;help=latest_packages" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['packages_latest'], '</td>
			</tr>
			<tr>
				<td class="windowbg2" id="packagesLatest">', $txt['packages_latest_fetch'], '</td>
			</tr>
		</table>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			window.smfForum_scripturl = "', $scripturl, '";
			window.smfForum_sessionid = "', $context['session_id'], '";';

	// Make a list of already installed mods so nothing is listed twice ;).
	echo '
			window.smfInstalledPackages = ["', implode('", "', $context['installed_mods']), '"];
			window.smfVersion = "', $context['forum_version'], '";
		// ]]></script>';

	if (empty($modSettings['disable_smf_js']))
		echo '
		<script language="JavaScript" type="text/javascript" src="', $scripturl, '?action=viewsmfile;filename=latest-packages.js"></script>';

	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var tempOldOnload;

			function smfSetLatestPackages()
			{
				if (typeof(window.smfLatestPackages) != "undefined")
					setInnerHTML(document.getElementById("packagesLatest"), window.smfLatestPackages);

				if (tempOldOnload)
					tempOldOnload();
			}
		// ]]></script>';

	// Gotta love IE4, and its hatefulness...
	if ($context['browser']['is_ie4'])
		echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			add_load_event(smfSetLatestPackages);
		// ]]></script>';
	else
		echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			smfSetLatestPackages();
		// ]]></script>';

	echo '
		<br />

		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td>', $txt['browse_packages'], '</td>
			</tr>';

	if (!empty($context['available_mods']))
	{
		echo '
			<tr>
				<td class="catbg">', $txt['modification_package'], '</td>
			</tr><tr>
				<td class="windowbg2">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">
						<tr>
							<td width="32"></td>
							<td width="25%">', $txt['mod_name'], '</td>
							<td width="25%">', $txt['mod_version'], '</td>
							<td width="49%"></td>
						</tr>';

		$alt = false;
		foreach ($context['available_mods'] as $i => $package)
		{
			echo '
						<tr class="', $alt ? 'windowbg2' : 'windowbg', '">
							<td>', ++$i, '.</td>
							<td>', $package['name'], '</td>
							<td>
								', $package['version'];

			if ($package['is_installed'] && !$package['is_newer'])
				echo '
								<img src="', $settings['images_url'], '/icons/package_', $package['is_current'] ? 'installed' : 'old', '.gif" alt="" align="middle" style="margin-left: 2ex;" />';

			echo '
							</td>
							<td align="right">';

			if ($package['can_uninstall'])
				echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=uninstall;package=', $package['filename'], ';pid=', $package['installed_id'], '">[ ', $txt['uninstall'], ' ]</a>';
			elseif ($package['can_upgrade'])
				echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=install;package=', $package['filename'], '">[ ', $txt['package_upgrade'], ' ]</a>';
			elseif ($package['can_install'])
				echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=install;package=', $package['filename'], '">[ ', $txt['mod_apply'], ' ]</a>';

			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=list;package=', $package['filename'], '">[ ', $txt['list_files'], ' ]</a>
								<a href="', $scripturl, '?action=admin;area=packages;sa=remove;package=', $package['filename'], '"', $package['is_installed'] && $package['is_current'] ? ' onclick="return confirm(\'' . $txt['package_delete_bad'] . '\');"' : '', '>[ ', $txt['package_delete'], ' ]</a>
							</td>
						</tr>';
			$alt = !$alt;
		}

		echo '
					</table>
				</td>
			</tr>';
	}

	if (!empty($context['available_avatars']))
	{
		echo '
			<tr>
				<td class="catbg">', $txt['avatar_package'], '</td>
			</tr><tr>
				<td class="windowbg2">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">
						<tr>
							<td width="32"></td>
							<td width="25%">', $txt['mod_name'], '</td>
							<td width="25%">', $txt['mod_version'], '</td>
							<td width="49%"></td>
						</tr>';

		foreach ($context['available_avatars'] as $i => $package)
		{
			echo '
						<tr>
							<td>', ++$i, '.</td>
							<td>', $package['name'], '</td>
							<td>', $package['version'];

			if ($package['is_installed'] && !$package['is_newer'])
				echo '
								<img src="', $settings['images_url'], '/icons/package_', $package['is_current'] ? 'installed' : 'old', '.gif" alt="" align="middle" style="margin-left: 2ex;" />';

			echo '
							</td>
							<td align="right">';

		if ($package['can_uninstall'])
			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=uninstall;package=', $package['filename'], ';pid=', $package['installed_id'], '">[ ', $txt['uninstall'], ' ]</a>';
		elseif ($package['can_upgrade'])
			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=install;package=', $package['filename'], '">[ ', $txt['package_upgrade'], ' ]</a>';
		elseif ($package['can_install'])
			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=install;package=', $package['filename'], '">[ ', $txt['mod_apply'], ' ]</a>';

		echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=list;package=', $package['filename'], '">[ ', $txt['list_files'], ' ]</a>
								<a href="', $scripturl, '?action=admin;area=packages;sa=remove;package=', $package['filename'], '">[ ', $txt['package_delete'], ' ]</a>
							</td>
						</tr>';
		}

		echo '
					</table>
				</td>
			</tr>';
	}

	if (!empty($context['available_languages']))
	{
		echo '
			<tr>
				<td class="catbg">' . $txt['language_package'] . '</td>
			</tr><tr>
				<td class="windowbg2">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">
						<tr>
							<td width="32"></td>
							<td width="25%">' . $txt['mod_name'] . '</td>
							<td width="25%">' . $txt['mod_version'] . '</td>
							<td width="49%"></td>
						</tr>';

		foreach ($context['available_languages'] as $i => $package)
		{
			echo '
						<tr>
							<td>' . ++$i . '.</td>
							<td>' . $package['name'] . '</td>
							<td>' . $package['version'];

			if ($package['is_installed'] && !$package['is_newer'])
				echo '
								<img src="', $settings['images_url'], '/icons/package_', $package['is_current'] ? 'installed' : 'old', '.gif" alt="" align="middle" style="margin-left: 2ex;" />';

			echo '
							</td>
							<td align="right">';

		if ($package['can_uninstall'])
			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=uninstall;package=', $package['filename'], ';pid=', $package['installed_id'], '">[ ', $txt['uninstall'], ' ]</a>';
		elseif ($package['can_upgrade'])
			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=install;package=', $package['filename'], '">[ ', $txt['package_upgrade'], ' ]</a>';
		elseif ($package['can_install'])
			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=install;package=', $package['filename'], '">[ ', $txt['mod_apply'], ' ]</a>';

		echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=list;package=', $package['filename'], '">[ ', $txt['list_files'], ' ]</a>
								<a href="', $scripturl, '?action=admin;area=packages;sa=remove;package=', $package['filename'], '">[ ', $txt['package_delete'], ' ]</a>
							</td>
						</tr>';
		}

		echo '
					</table>
				</td>
			</tr>';
	}

	if (!empty($context['available_other']))
	{
		echo '
			<tr>
				<td class="catbg">' . $txt['unknown_package'] . '</td>
			</tr><tr>
				<td class="windowbg2">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">
						<tr>
							<td width="32"></td>
							<td width="25%">' . $txt['mod_name'] . '</td>
							<td width="25%">' . $txt['mod_version'] . '</td>
							<td width="49%"></td>
						</tr>';

		foreach ($context['available_other'] as $i => $package)
		{
			echo '
						<tr>
							<td>' . ++$i . '.</td>
							<td>' . $package['name'] . '</td>
							<td>' . $package['version'];

			if ($package['is_installed'] && !$package['is_newer'])
				echo '
								<img src="', $settings['images_url'], '/icons/package_', $package['is_current'] ? 'installed' : 'old', '.gif" alt="" align="middle" style="margin-left: 2ex;" />';

			echo '
							</td>
							<td align="right">';

		if ($package['can_uninstall'])
			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=uninstall;package=', $package['filename'], ';pid=', $package['installed_id'], '">[ ', $txt['uninstall'], ' ]</a>';
		elseif ($package['can_upgrade'])
			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=install;package=', $package['filename'], '">[ ', $txt['package_upgrade'], ' ]</a>';
		elseif ($package['can_install'])
			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=install;package=', $package['filename'], '">[ ', $txt['mod_apply'], ' ]</a>';

		echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=list;package=', $package['filename'], '">[ ', $txt['list_files'], ' ]</a>
								<a href="', $scripturl, '?action=admin;area=packages;sa=remove;package=', $package['filename'], '"', $package['is_installed'] ? ' onclick="return confirm(\'' . $txt['package_delete_bad'] . '\');"' : '', '>[ ', $txt['package_delete'], ' ]</a>
							</td>
						</tr>';
		}

		echo '
					</table>
				</td>
			</tr>';
	}

	if (empty($context['available_mods']) && empty($context['available_avatars']) && empty($context['available_languages']) && empty($context['available_other']))
		echo '
			<tr>
				<td class="windowbg2">', $txt['no_packages'], '</td>
			</tr>';

	echo '
		</table>
		<table border="0" width="100%" cellspacing="1" cellpadding="4">
			<tr>
				<td class="smalltext">
					', $txt['package_installed_key'], '
					<img src="', $settings['images_url'], '/icons/package_installed.gif" alt="" align="middle" style="margin-left: 1ex;" /> ', $txt['package_installed_current'], '
					<img src="', $settings['images_url'], '/icons/package_old.gif" alt="" align="middle" style="margin-left: 2ex;" /> ', $txt['package_installed_old'], '
				</td>
				<td class="smalltext" align="right">
					<a href="#" onclick="document.getElementById(\'advanced_box\').style.display = document.getElementById(\'advanced_box\').style.display == \'\' ? \'none\' : \'\'; return false;">', $txt['package_advanced_button'], '</a>
				</td>
			</tr>
		</table>
		<form action="', $scripturl, '?action=admin;area=packages;sa=browse" method="get">
		<table id="advanced_box" width="400" align="right" cellspacing="0" cellpadding="2" class="tborder">
			<tr class="titlebg">
				<td colspan="2">
					', $txt['package_advanced_options'], '
				</td>
			</tr>
			<tr class="windowbg">
				<td colspan="2" class="smalltext">
					', $txt['package_emulate_desc'], '
				</td>
			</tr>
			<tr class="windowbg2">
				<td width="50%">
					<b>', $txt['package_emulate'], ':</b>
					<div class="smalltext">
						<a href="#" onclick="document.getElementById(\'ve\').value = \'', $forum_version, '\'; return false">', $txt['package_emulate_revert'], '</a>
					</div>
				</td>
				<td width="50%">
					<input type="text" name="version_emulate" id="ve" value="', $context['forum_version'], '" size="25" />
				</td>
			</tr>
			<tr class="titlebg">
				<td colspan="2" align="right">
					<input type="submit" value="', $txt['package_apply'], '" />
				</td>
			</tr>
		</table>
			<input type="hidden" name="action" value="admin" />
			<input type="hidden" name="area" value="packages" />
			<input type="hidden" name="sa" value="browse" />
		</form>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			document.getElementById(\'advanced_box\').style.display = "none";
			// ]]></script>';
}

function template_servers()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td>', $txt['download_new_package'], '</td>
			</tr>';

	if ($context['package_download_broken'])
	{
		echo '
			<tr>
				<td class="catbg">', $txt['package_ftp_necessary'], '</td>
			</tr><tr>
				<td class="windowbg2">
					', $txt['package_ftp_why_download'];

		if (!empty($context['package_ftp']['error']))
			echo '
					<div class="bordercolor" style="padding: 1px; margin: 1ex;"><div class="windowbg" style="padding: 1ex;">
						<tt>', $context['package_ftp']['error'], '</tt>
					</div></div>';

		echo '
					<form action="', $scripturl, '?action=admin;area=packages;get" method="post" accept-charset="', $context['character_set'], '">
						<table width="520" cellpadding="0" cellspacing="0" border="0" align="center" style="margin-bottom: 1ex; margin-top: 2ex;">
							<tr>
								<td width="26%" valign="top" style="padding-top: 2px; padding-right: 2ex;"><label for="ftp_server">', $txt['package_ftp_server'], ':</label></td>
								<td style="padding-bottom: 1ex;">
									<div style="float: right; margin-right: 1px;"><label for="ftp_port" style="padding-top: 2px; padding-right: 2ex;">', $txt['package_ftp_port'], ':&nbsp;</label> <input type="text" size="3" name="ftp_port" id="ftp_port" value="', $context['package_ftp']['port'], '" /></div>
									<input type="text" size="30" name="ftp_server" id="ftp_server" value="', $context['package_ftp']['server'], '" style="width: 70%;" />
								</td>
							</tr><tr>
								<td width="26%" valign="top" style="padding-top: 2px; padding-right: 2ex;"><label for="ftp_username">', $txt['package_ftp_username'], ':</label></td>
								<td style="padding-bottom: 1ex;">
									<input type="text" size="50" name="ftp_username" id="ftp_username" value="', $context['package_ftp']['username'], '" style="width: 99%;" />
								</td>
							</tr><tr>
								<td width="26%" valign="top" style="padding-top: 2px; padding-right: 2ex;"><label for="ftp_password">', $txt['package_ftp_password'], ':</label></td>
								<td style="padding-bottom: 1ex;">
									<input type="password" size="50" name="ftp_password" id="ftp_password" style="width: 99%;" />
								</td>
							</tr><tr>
								<td width="26%" valign="top" style="padding-top: 2px; padding-right: 2ex;"><label for="ftp_path">', $txt['package_ftp_path'], ':</label></td>
								<td style="padding-bottom: 1ex;">
									<input type="text" size="50" name="ftp_path" id="ftp_path" value="', $context['package_ftp']['path'], '" style="width: 99%;" />
								</td>
							</tr>
						</table>
						<div align="right" style="margin-right: 1ex;"><input type="submit" value="', $txt['package_proceed'], '" /></div>
					</form>
				</td>
			</tr>';
	}

	echo '
			<tr>
				<td class="catbg">' . $txt['package_servers'] . '</td>
			</tr><tr>
				<td class="windowbg2">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">';
	foreach ($context['servers'] as $server)
		echo '
						<tr>
							<td>
								' . $server['name'] . '
							</td>
							<td>
								<a href="' . $scripturl . '?action=admin;area=packages;get;sa=browse;server=' . $server['id'] . '">[ ' . $txt['package_browse'] . ' ]</a>
							</td>
							<td>
								<a href="' . $scripturl . '?action=admin;area=packages;get;sa=remove;server=' . $server['id'] . '">[ ' . $txt['delete'] . ' ]</a>
							</td>
						</tr>';
	echo '
					</table>
					<br />
				</td>
			</tr><tr>
				<td class="catbg">' . $txt['add_server'] . '</td>
			</tr><tr>
				<td class="windowbg2">
					<form action="' . $scripturl . '?action=admin;area=packages;get;sa=add" method="post" accept-charset="', $context['character_set'], '">
						<table border="0" cellspacing="0" cellpadding="4">
							<tr>
								<td valign="top"><b>' . $txt['server_name'] . ':</b></td>
								<td valign="top"><input type="text" name="servername" size="40" value="SMF" /></td>
							</tr><tr>
								<td valign="top"><b>' . $txt['serverurl'] . ':</b></td>
								<td valign="top"><input type="text" name="serverurl" size="50" value="http://" /></td>
							</tr><tr>
								<td colspan="2"><input type="submit" value="' . $txt['add_server'] . '" /></td>
							</tr>
						</table>
						<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
					</form>
				</td>
			</tr><tr>
				<td class="catbg">', $txt['package_download_by_url'], '</td>
			</tr><tr>
				<td class="windowbg2">
					<form action="', $scripturl, '?action=admin;area=packages;get;sa=download;byurl;', $context['session_var'], '=', $context['session_id'], '" method="post" accept-charset="', $context['character_set'], '">
						<table border="0" cellspacing="0" cellpadding="4">
							<tr>
								<td valign="top"><b>' . $txt['serverurl'] . ':</b></td>
								<td valign="top"><input type="text" name="package" size="50" value="http://" /></td>
							</tr><tr>
								<td valign="top"><b>', $txt['package_download_filename'], ':</b></td>
								<td valign="top">
									<input type="text" name="filename" size="50" /><br />
									<span class="smalltext">', $txt['package_download_filename_info'], '</span>
								</td>
							</tr><tr>
								<td colspan="2"><input type="submit" value="', $txt['download'], '" /></td>
							</tr>
						</table>
					</form>
				</td>
			</tr>
		</table>
		<br />
		<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td>' . $txt['package_upload_title'] . '</td>
			</tr><tr>
				<td class="windowbg2" style="padding: 8px;">
					<form action="' . $scripturl . '?action=admin;area=packages;get;sa=upload" method="post" accept-charset="', $context['character_set'], '" enctype="multipart/form-data" style="margin-bottom: 0;">
						<b>' . $txt['package_upload_select'] . ':</b> <input type="file" name="package" size="38" />
						<div style="margin: 1ex;" align="right"><input type="submit" value="' . $txt['package_upload'] . '" /></div>
						<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
					</form>
				</td>
			</tr>
		</table>';
}

function template_package_confirm()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td>', $context['page_title'], '</td>
			</tr>
			<tr>
				<td width="100%" align="left" valign="middle" class="windowbg2">
					', $context['confirm_message'], '<br />
					<br />
					<a href="', $context['proceed_href'], '">[ ', $txt['package_confirm_proceed'], ' ]</a> <a href="JavaScript:history.go(-1);">[ ', $txt['package_confirm_go_back'], ' ]</a>
				</td>
			</tr>
		</table>';	
}

function template_package_list()
{
	global $context, $settings, $options, $txt, $scripturl, $smcFunc;

	echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td>' . $context['page_title'] . '</td>
			</tr>
			<tr>
				<td width="100%" align="left" valign="middle" class="windowbg2">';

	// No packages, as yet.
	if (empty($context['package_list']))
		echo '
					<ul>
						<li>', $txt['no_packages'], '</li>
					</ul>';
	// List out the packages...
	else
	{
		echo '
					<ul id="package_list">';
		foreach ($context['package_list'] as $i => $packageSection)
		{
			echo '
						<li>
							<h2><a href="#" onclick="ps_', $i, '.toggle(); return false;"><img id="ps_img_', $i, '" src="', $settings['images_url'], '/blank.gif" alt="*" /></a> ', $packageSection['title'], '</h2>';

			if (!empty($packageSection['text']))
				echo '
							<h3>', $packageSection['text'], '</h3>';

			echo '
							<', $context['list_type'], ' id="package_section_', $i, '" class="tborder">';

			$alt = false;

			foreach ($packageSection['items'] as $id => $package)
			{
				echo '
								<li>';
				// Textual message. Could be empty just for a blank line...
				if ($package['is_text'])
					echo '
									', empty($package['name']) ? '&nbsp;' : $package['name'];
				// This is supposed to be a rule..
				elseif ($package['is_line'])
					echo '
									<hr />';
				// A remote link.
				elseif ($package['is_remote'])
				{
					echo '
									<b>', $package['link'], '</b>';
				}
				// A title?
				elseif ($package['is_heading'] || $package['is_title'])
				{
					echo '
									<b>', $package['name'], '</b>';
				}
				// Otherwise, it's a package.
				else
				{
					// 1. Some mod [ Download ].
					echo '
									<h4><a href="#" onclick="ps_', $i, '_pkg_', $id, '.toggle(); return false;"><img id="ps_img_', $i, '_pkg_', $id, '" src="', $settings['images_url'], '/blank.gif" alt="*" /></a> ', $package['can_install'] ? '<b>' . $package['name'] . '</b> <a href="' . $package['download']['href'] . '">[ ' . $txt['download'] . ' ]</a>': $package['name'];

					// Mark as installed and current?
					if ($package['is_installed'] && !$package['is_newer'])
						echo '<img src="', $settings['images_url'], '/icons/package_', $package['is_current'] ? 'installed' : 'old', '.gif" width="12" height="11" align="middle" style="margin-left: 2ex;" alt="', $package['is_current'] ? $txt['package_installed_current'] : $txt['package_installed_old'], '" />';

					echo '
									</h4>
									<ul id="package_section_', $i, '_pkg_', $id, '">';

					// Show the mod type?
					if ($package['type'] != '')
						echo '
										<li>', $txt['package_type'], ':&nbsp; ', $smcFunc['ucwords']($smcFunc['strtolower']($package['type'])), '</li>';
					// Show the version number?
					if ($package['version'] != '')
						echo '
										<li>', $txt['mod_version'], ':&nbsp; ', $package['version'], '</li>';
					// How 'bout the author?
					if (!empty($package['author']) && $package['author']['name'] != '' && isset($package['author']['link']))
						echo '
										<li>', $txt['mod_author'], ':&nbsp; ', $package['author']['link'], '</li>';
					// The homepage....
					if ($package['author']['website']['link'] != '')
						echo '
										<li>', $txt['author_website'], ':&nbsp; ', $package['author']['website']['link'], '</li>';

					// Desciption: bleh bleh!
					// Location of file: http://someplace/.
					echo '
										<li>', $txt['file_location'], ':&nbsp; <a href="', $package['href'], '">', $package['href'], '</a></li>
										<li class="description">', $txt['package_description'], ':&nbsp; ', $package['description'], '</li>
									</ul>';
				}
				$alt = !$alt;
				echo '
								</li>';
			}
			echo '
							</', $context['list_type'], '>
						</li>';
		}
		echo '
					</ul>';

	}

	echo '
				</td>
			</tr>
		</table>
		<table border="0" width="100%" cellspacing="1" cellpadding="4">
			<tr>
				<td class="smalltext">
					', $txt['package_installed_key'], '
					<img src="', $settings['images_url'], '/icons/package_installed.gif" alt="" width="12" height="11" align="middle" style="margin-left: 1ex;" /> ', $txt['package_installed_current'], '
					<img src="', $settings['images_url'], '/icons/package_old.gif" alt="" width="12" height="11" align="middle" style="margin-left: 2ex;" /> ', $txt['package_installed_old'], '
				</td>
			</tr>
		</table>';
		// Now go through and turn off all the sections.
		if (!empty($context['package_list']))
		{
			$section_count = count($context['package_list']);
			echo '
			<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[';
			foreach ($context['package_list'] as $section => $ps)
			{
				echo '

					var ps_', $section, ' = new smfToggle("package_section_', $section, '", false);
					ps_', $section, '.useCookie(0);
					ps_', $section, '.addToggleImage("ps_img_', $section, '", "/upshrink.gif", "/upshrink2.gif");
					ps_', $section, '.addTogglePanel("package_section_', $section, '");
					ps_', $section, '.toggle(', count($ps['items']) == 1 || $section_count == 1 ? 'false' : 'true', ');';

				foreach ($ps['items'] as $id => $package)
				{
					if (!$package['is_text'] && !$package['is_line'] && !$package['is_remote'])
						echo '

						var ps_', $section, '_pkg_', $id, ' = new smfToggle("package_section_', $section, '_pkg_', $id, '", false);
						ps_', $section, '_pkg_', $id, '.useCookie(0);
						ps_', $section, '_pkg_', $id, '.addToggleImage("ps_img_', $section, '_pkg_', $id, '", "/upshrink.gif", "/upshrink2.gif");
						ps_', $section, '_pkg_', $id, '.addTogglePanel("package_section_', $section, '_pkg_', $id, '");
						ps_', $section, '_pkg_', $id, '.toggle()';
				}
			}
			echo '
			// ]]></script>';
		}
}

function template_downloaded()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td>' . $context['page_title'] . '</td>
			</tr>
			<tr>
				<td width="100%" align="left" valign="middle" class="windowbg2">
					' . (!isset($context['package_server']) ? $txt['package_uploaded_successfully'] : $txt['package_downloaded_successfully']) . '<br /><br />
					<table border="0" cellpadding="1" cellspacing="0" width="100%">
						<tr>
							<td valign="middle">' . $context['package']['name'] . '</td>
							<td align="right" valign="middle">
								' . $context['package']['install']['link'] . '
								' . $context['package']['list_files']['link'] . '
							</td>
						</tr>
					</table>
					<br />
					<a href="' . $scripturl . '?action=admin;area=packages;get' . (isset($context['package_server']) ? ';sa=browse;server=' . $context['package_server'] : '') . '">[ ' . $txt['back'] . ' ]</a>
				</td>
			</tr>
		</table>';
}

function template_install_options()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<div class="tborder">
			<div class="titlebg" style="padding: 4px;">', $txt['package_install_options'], '</div>
			<div class="windowbg" style="padding: 1ex;">
				<span class="smalltext">', $txt['package_install_options_ftp_why'], '</span>
			</div>

			<div class="windowbg2" style="padding: 4px;">
				<form action="', $scripturl, '?action=admin;area=packages;sa=options" method="post" accept-charset="', $context['character_set'], '">
					<div style="margin-top: 1ex;"><label for="pack_server" style="padding: 2px 0 0 4pt; float: left; width: 20ex; font-weight: bold;">', $txt['package_install_options_ftp_server'], ':</label> <input type="text" name="pack_server" id="pack_server" value="', $context['package_ftp_server'], '" size="30" /> <label for="pack_port" style="padding-left: 4pt; font-weight: bold;">', $txt['package_install_options_ftp_port'], ':</label> <input type="text" name="pack_port" id="pack_port" size="3" value="', $context['package_ftp_port'], '" /></div>
					<div style="margin-top: 1ex;"><label for="pack_user" style="padding: 2px 0 0 4pt; float: left; width: 20ex; font-weight: bold;">', $txt['package_install_options_ftp_user'], ':</label> <input type="text" name="pack_user" id="pack_user" value="', $context['package_ftp_username'], '" size="30" /></div>
					<br />

					<label for="package_make_backups"><input type="checkbox" name="package_make_backups" id="package_make_backups" value="1" class="check"', $context['package_make_backups'] ? ' checked="checked"' : '', ' /> ', $txt['package_install_options_make_backups'], '</label><br />
					<div align="center" style="padding-top: 2ex; padding-bottom: 1ex;"><input type="submit" name="submit" value="', $txt['save'], '" /></div>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</div>
		</div>';
}

function template_control_chmod()
{
	global $context, $settings, $options, $txt, $scripturl;

	// Nothing to do? Brilliant!
	if (empty($context['package_ftp']))
		return false;

	if (empty($context['package_ftp']['form_elements_only']))
	{
		echo '
				', sprintf($txt['package_ftp_why'], 'document.getElementById(\'need_writable_list\').style.display = \'\'; return false;'), '<br />
				<div id="need_writable_list" class="smalltext">
					', $txt['package_ftp_why_file_list'], '
					<ul style="display: inline;">';
		if (!empty($context['notwritable_files']))
			foreach ($context['notwritable_files'] as $file)
				echo '
						<li>', $file, '</li>';

		echo '
					</ul>
				</div>';
	}

	echo '
				<div class="bordercolor" id="ftp_error_div" style="', (!empty($context['package_ftp']['error']) ? '' : 'display:none;'), 'padding: 1px; margin: 1ex;"><div class="windowbg2" id="ftp_error_innerdiv" style="padding: 1ex;">
					<tt id="ftp_error_message">', !empty($context['package_ftp']['error']) ? $context['package_ftp']['error'] : '', '</tt>
				</div></div>';

	if (!empty($context['package_ftp']['destination']))
		echo '
				<form action="', $context['package_ftp']['destination'], '" method="post" accept-charset="', $context['character_set'], '" style="margin: 0;">';

	echo '
					<table width="520" cellpadding="0" cellspacing="0" border="0" align="center" style="margin-bottom: 1ex; margin-top: 2ex;">
						<tr>
							<td width="26%" valign="top" style="padding-top: 2px; padding-right: 2ex;"><label for="ftp_server">', $txt['package_ftp_server'], ':</label></td>
							<td style="padding-bottom: 1ex;">
								<div style="float: right; margin-right: 1px;"><label for="ftp_port" style="padding-top: 2px; padding-right: 2ex;">', $txt['package_ftp_port'], ':&nbsp;</label> <input type="text" size="3" name="ftp_port" id="ftp_port" value="', $context['package_ftp']['port'], '" /></div>
								<input type="text" size="30" name="ftp_server" id="ftp_server" value="', $context['package_ftp']['server'], '" style="width: 70%;" />
							</td>
						</tr><tr>
							<td width="26%" valign="top" style="padding-top: 2px; padding-right: 2ex;"><label for="ftp_username">', $txt['package_ftp_username'], ':</label></td>
							<td style="padding-bottom: 1ex;">
								<input type="text" size="50" name="ftp_username" id="ftp_username" value="', $context['package_ftp']['username'], '" style="width: 98%;" />
							</td>
						</tr><tr>
							<td width="26%" valign="top" style="padding-top: 2px; padding-right: 2ex;"><label for="ftp_password">', $txt['package_ftp_password'], ':</label></td>
							<td style="padding-bottom: 1ex;">
								<input type="password" size="50" name="ftp_password" id="ftp_password" style="width: 98%;" />
							</td>
						</tr><tr>
							<td width="26%" valign="top" style="padding-top: 2px; padding-right: 2ex;"><label for="ftp_path">', $txt['package_ftp_path'], ':</label></td>
							<td style="padding-bottom: 1ex;">
								<input type="text" size="50" name="ftp_path" id="ftp_path" value="', $context['package_ftp']['path'], '" style="width: 98%;" />
							</td>
						</tr>
					</table>';

	if (empty($context['package_ftp']['form_elements_only']))
		echo '

					<div align="right" style="margin: 1ex;">
						<span id="test_ftp_placeholder_full"></span>
						<input type="submit" value="', $txt['package_proceed'], '" />
					</div>';

	if (!empty($context['package_ftp']['destination']))
		echo '
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>';

	// Hide the details of the list.
	if (empty($context['package_ftp']['form_elements_only']))
		echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			document.getElementById(\'need_writable_list\').style.display = \'none\';
		// ]]></script>';

	// Quick generate the test button.
	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		// Generate a "test ftp" button.
		var generatedButton = false;
		function generateFTPTest()
		{
			// Don\'t ever call this twice!
			if (generatedButton)
				return false;
			generatedButton = true;

			// No XML?
			if (!window.XMLHttpRequest || (!document.getElementById("test_ftp_placeholder") && !document.getElementById("test_ftp_placeholder_full")))
				return false;

			var ftpTest = document.createElement("input");
			ftpTest.type = "button";
			ftpTest.onclick = testFTP;

			if (document.getElementById("test_ftp_placeholder"))
			{
				ftpTest.value = "', $txt['package_ftp_test'], '";
				document.getElementById("test_ftp_placeholder").appendChild(ftpTest);
			}
			else
			{
				ftpTest.value = "', $txt['package_ftp_test_connection'], '";
				document.getElementById("test_ftp_placeholder_full").appendChild(ftpTest);
			}
		}
		function testFTP()
		{
			ajax_indicator(true);

			// What we need to post.
			var oPostData = {
				0: "ftp_server",
				1: "ftp_port",
				2: "ftp_username",
				3: "ftp_password",
				4: "ftp_path"
			}

			var sPostData = "";
			for (i = 0; i < 5; i++)
				sPostData = sPostData + (sPostData.length == 0 ? "" : "&") + oPostData[i] + "=" + escape(document.getElementById(oPostData[i]).value);

			// Post the data out.
			sendXMLDocument(smf_prepareScriptUrl(smf_scripturl) + \'action=admin;area=packages;sa=ftptest;xml;', $context['session_var'], '=', $context['session_id'], '\', sPostData, testFTPResults);
		}
		function testFTPResults(oXMLDoc)
		{
			ajax_indicator(false);

			// This assumes it went wrong!
			var wasSuccess = false;
			var message = "', addcslashes($txt['package_ftp_test_failed'], "'"), '";

			var results = oXMLDoc.getElementsByTagName(\'results\')[0].getElementsByTagName(\'result\');
			if (results.length > 0)
			{
				if (results[0].getAttribute(\'success\') == 1)
					wasSuccess = true;
				message = results[0].firstChild.nodeValue;
			}

			document.getElementById("ftp_error_div").style.display = "";
			document.getElementById("ftp_error_div").style.backgroundColor = wasSuccess ? "green" : "red";
			document.getElementById("ftp_error_innerdiv").style.backgroundColor = wasSuccess ? "#DBFDC7" : "#FDBDBD";

			setInnerHTML(document.getElementById("ftp_error_message"), message);
		}
		generateFTPTest();
	// ]]></script>';

	// Make sure the button gets generated last.
	$context['insert_after_template'] .= '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		generateFTPTest();
	// ]]></script>';
}

function template_ftp_required()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<div class="tborder">
			<div class="titlebg" style="padding: 4px;">', $txt['package_ftp_necessary'], '</div>
			<div class="windowbg" style="padding: 4px;">
				', template_control_chmod(), '
			</div>
		</div>';
}

function template_view_operations()
{
	global $context, $txt, $settings;

	// Determine the position text.
	$operation_text = $context['operations']['position'] == 'replace' ? 'operation_replace' : ($context['operations']['position'] == 'before' ? 'operation_after' : 'operation_before');

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<title>', $txt['operation_title'], '</title>
		<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
		<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/style.css" />
		<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/script.js?rc1"></script>
		<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/theme.js?rc1"></script>
	</head>
	<body>
	<div class="tborder" style="width: 100%;">
		<div class="titlebg" style="padding: 6px;">
			', $txt['operation_find'], '
			<a href="javascript:void(0);" onclick="return smfSelectText(\'find_code\', true);" class="smalltext" style="font-weight: normal;">' . $txt['code_select'] . '</a>
		</div>
		<div class="windowbg2" style="padding: 4px;">
			<code id="find_code" style="overflow: auto; max-height: 200px; white-space: pre;">', $context['operations']['position'] == 'end' ? '?&gt;' : $context['operations']['search'], '</code>
		</div>
		<div class="titlebg" style="padding: 6px;">
			', $txt[$operation_text], '
			<a href="javascript:void(0);" onclick="return smfSelectText(\'replace_code\', true);" class="smalltext" style="font-weight: normal;">' . $txt['code_select'] . '</a>
		</div>
		<div class="windowbg2" style="padding: 4px;">
			<code id="replace_code" style="overflow: auto; max-height: 200px; white-space: pre;">', $context['operations']['replace'], '</code>
		</div>
	</div>
	</body>
</html>';

}

function template_file_permissions()
{
	global $txt, $scripturl, $context, $settings;

	// This will handle expanding the selection.
	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var oRadioColors = {
			0: "#D1F7BF",
			1: "#FFBBBB",
			2: "#FDD7AF",
			3: "#C2C6C0",
			4: "#FFFFFF"
		}
		var oRadioValues = {
			0: "read",
			1: "writable",
			2: "execute",
			3: "custom",
			4: "no_change"
		}
		function expandFolder(folderIdent, folderReal)
		{
			// See if it already exists.
			var possibleTags = document.getElementsByTagName("tr");
			var foundOne = false;

			for (i = 0; i < possibleTags.length; i++)
			{
				if (possibleTags[i].id.indexOf("content_" + folderIdent + ":-:") == 0)
				{
					possibleTags[i].style.display = possibleTags[i].style.display == "none" ? "" : "none";
					foundOne = true;
				}
			}

			// Got something then we\'re done.
			if (foundOne)
			{
				return false;
			}
			// Otherwise we need to get the wicked thing.
			else if (window.XMLHttpRequest)
			{
				ajax_indicator(true);
				getXMLDocument(smf_prepareScriptUrl(smf_scripturl) + \'action=admin;area=packages;onlyfind=\' + escape(folderReal) + \';sa=perms;xml;', $context['session_var'], '=', $context['session_id'], '\', onNewFolderReceived);
			}
			// Otherwise reload.
			else
				return true;

			return false;
		}
		function dynamicExpandFolder()
		{
			expandFolder(this.ident, this.path);

			return false;
		}
		function dynamicAddMore()
		{
			ajax_indicator(true);

			getXMLDocument(smf_prepareScriptUrl(smf_scripturl) + \'action=admin;area=packages;fileoffset=\' + (parseInt(this.offset) + ', $context['file_limit'], ') + \';onlyfind=\' + escape(this.path) + \';sa=perms;xml;', $context['session_var'], '=', $context['session_id'], '\', onNewFolderReceived);
		}
		function repeatString(sString, iTime)
		{
			if (iTime < 1)
				return \'\';
			else
				return sString + repeatString(sString, iTime - 1);
		}
		// Create a named element dynamically - thanks to: http://www.thunderguy.com/semicolon/2005/05/23/setting-the-name-attribute-in-internet-explorer/
		function createNamedElement(type, name, customFields)
		{
			var element = null;

			if (!customFields)
				customFields = "";

			// Try the IE way; this fails on standards-compliant browsers
			try
			{
				element = document.createElement("<" + type + \' name="\' + name + \'" \' + customFields + ">");
			}
			catch (e)
			{
			}
			if (!element || element.nodeName != type.toUpperCase())
			{
				// Non-IE browser; use canonical method to create named element
				element = document.createElement(type);
				element.name = name;
			}

			return element;
		}
		// Getting something back?
		function onNewFolderReceived(oXMLDoc)
		{
			ajax_indicator(false);

			var fileItems = oXMLDoc.getElementsByTagName(\'folders\')[0].getElementsByTagName(\'folder\');

			// No folders, no longer worth going further.
			if (fileItems.length < 1)
			{
				if (oXMLDoc.getElementsByTagName(\'roots\')[0].getElementsByTagName(\'root\')[0])
				{
					var rootName = oXMLDoc.getElementsByTagName(\'roots\')[0].getElementsByTagName(\'root\')[0].firstChild.nodeValue;
					var itemLink = document.getElementById(\'link_\' + rootName);

					// Move the children up.
					for (i = 0; i <= itemLink.childNodes.length; i++)
						itemLink.parentNode.insertBefore(itemLink.childNodes[0], itemLink);

					// And remove the link.
					itemLink.parentNode.removeChild(itemLink);
				}
				return false;
			}
			var tableHandle = false;
			var isMore = false;
			var ident = "";
			var my_ident = "";
			var curLevel = 0;

			for (var i = 0; i < fileItems.length; i++)
			{
				if (fileItems[i].getAttribute(\'more\') == 1)
				{
					isMore = true;
					var curOffset = fileItems[i].getAttribute(\'offset\');
				}

				if (fileItems[i].getAttribute(\'more\') != 1 && document.getElementById("insert_div_loc_" + fileItems[i].getAttribute(\'ident\')))
				{
					ident = fileItems[i].getAttribute(\'ident\');
					my_ident = fileItems[i].getAttribute(\'my_ident\');
					curLevel = fileItems[i].getAttribute(\'level\') * 5;
					curPath = fileItems[i].getAttribute(\'path\');

					// Get where we\'re putting it next to.
					tableHandle = document.getElementById("insert_div_loc_" + fileItems[i].getAttribute(\'ident\'));

					var curRow = document.createElement("tr");
					curRow.className = "windowbg";
					curRow.id = "content_" + my_ident;
					curRow.style.display = "";
					var curCol = document.createElement("td");
					curCol.className = "smalltext";
					curCol.width = "40%";

					// This is the name.
					var fileName = document.createTextNode(fileItems[i].firstChild.nodeValue);

					// Start by wacking in the spaces.
					setInnerHTML(curCol, repeatString("&nbsp;", curLevel));

					// Create the actual text.
					if (fileItems[i].getAttribute(\'folder\') == 1)
					{
						var linkData = document.createElement("a");
						linkData.name = "fol_" + my_ident;
						linkData.id = "link_" + my_ident;
						linkData.href = \'#\';
						linkData.path = curPath + "/" + fileItems[i].firstChild.nodeValue;
						linkData.ident = my_ident;
						linkData.onclick = dynamicExpandFolder;

						var folderImage = document.createElement("img");
						folderImage.src = \'', addcslashes($settings['default_images_url'], "\\"), '/board.gif\';
						linkData.appendChild(folderImage);

						linkData.appendChild(fileName);
						curCol.appendChild(linkData);
					}
					else
						curCol.appendChild(fileName);

					curRow.appendChild(curCol);

					// Right, the permissions.
					curCol = document.createElement("td");
					curCol.className = "smalltext";

					var writeSpan = document.createElement("span");
					writeSpan.style.color = fileItems[i].getAttribute(\'writable\') ? "green" : "red";
					setInnerHTML(writeSpan, fileItems[i].getAttribute(\'writable\') ? \'', $txt['package_file_perms_writable'], '\' : \'', $txt['package_file_perms_not_writable'], '\');
					curCol.appendChild(writeSpan);

					if (fileItems[i].getAttribute(\'permissions\'))
					{
						var permData = document.createTextNode("\u00a0(', $txt['package_file_perms_chmod'], ': " + fileItems[i].getAttribute(\'permissions\') + ")");
						curCol.appendChild(permData);
					}

					curRow.appendChild(curCol);

					// Now add the five radio buttons.
					for (j = 0; j < 5; j++)
					{
						curCol = document.createElement("td");
						curCol.style.backgroundColor = oRadioColors[j];
						curCol.align = "center";

						var curInput = createNamedElement("input", "permStatus[" + curPath + "/" + fileItems[i].firstChild.nodeValue + "]", j == 4 ? \'checked="checked"\' : "");
						curInput.type = "radio";
						curInput.checked = "checked";
						curInput.value = oRadioValues[j];

						curCol.appendChild(curInput);
						curRow.appendChild(curCol);
					}

					// Put the row in.
					tableHandle.parentNode.insertBefore(curRow, tableHandle);

					// Put in a new dummy section?
					if (fileItems[i].getAttribute(\'folder\') == 1)
					{
						var newRow = document.createElement("tr");
						newRow.id = "insert_div_loc_" + my_ident;
						newRow.style.display = "none";
						tableHandle.parentNode.insertBefore(newRow, tableHandle);
						var newCol = document.createElement("td");
						newCol.colspan = 2;
						newRow.appendChild(newCol);
					}
				}
			}

			// Is there some more to remove?
			if (document.getElementById("content_" + ident + "_more"))
			{
				document.getElementById("content_" + ident + "_more").parentNode.removeChild(document.getElementById("content_" + ident + "_more"));
			}

			// Add more?
			if (isMore && tableHandle)
			{
				// Create the actual link.
				var linkData = document.createElement("a");
				linkData.href = \'#fol_\' + my_ident;
				linkData.path = curPath;
				linkData.offset = curOffset;
				linkData.onclick = dynamicAddMore;

				linkData.appendChild(document.createTextNode(\'', $txt['package_file_perms_more_files'], '\'));

				curRow = document.createElement("tr");
				curRow.className = "windowbg";
				curRow.id = "content_" + ident + "_more";
				tableHandle.parentNode.insertBefore(curRow, tableHandle);
				curCol = document.createElement("td");
				curCol.className = "smalltext";
				curCol.width = "40%";

				setInnerHTML(curCol, repeatString("&nbsp;", curLevel));
				curCol.appendChild(document.createTextNode(\'\\u00ab \'));
				curCol.appendChild(linkData);
				curCol.appendChild(document.createTextNode(\' \\u00bb\'));

				curRow.appendChild(curCol);
				curCol = document.createElement("td");
				curCol.className = "smalltext";
				curRow.appendChild(curCol);
			}

			// Keep track of it.
			var curInput = createNamedElement("input", "back_look[]");
			curInput.type = "hidden";
			curInput.value = curPath;

			curCol.appendChild(curInput);
		}
	// ]]></script>';

		echo '
	<div style="margin: 2ex; padding: 1ex; border: 1px dashed #C16409; color: black; background-color: #FCDBBA; margin-top: 0;">
		<div>
			<b style="text-decoration: underline;">', $txt['package_file_perms_warning'], ':</b>
			<div class="smalltext">
				<ol style="margin-top: 2px; margin-bottom: 2px">
					', $txt['package_file_perms_warning_desc'], '
				</ol>
			</div>
		</div>
	</div>
	<form action="', $scripturl, '?action=admin;area=packages;sa=perms;', $context['session_var'], '=', $context['session_id'], '" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" width="100%" cellspacing="1" cellpadding="2" class="bordercolor">
			<tr class="titlebg">
				<td colspan="7">', $txt['package_file_perms'], '</td>
			</tr>
			<tr class="catbg">
				<td width="30%" rowspan="2">', $txt['package_file_perms_name'], '</td>
				<td width="30%" rowspan="2">', $txt['package_file_perms_status'], '</td>
				<td colspan="5" align="center">', $txt['package_file_perms_new_status'], '</td>
			</tr>
			<tr class="catbg">
				<td align="center" class="smalltext" width="8%">', $txt['package_file_perms_status_read'], '</td>
				<td align="center" class="smalltext" width="8%">', $txt['package_file_perms_status_write'], '</td>
				<td align="center" class="smalltext" width="8%">', $txt['package_file_perms_status_execute'], '</td>
				<td align="center" class="smalltext" width="8%">', $txt['package_file_perms_status_custom'], '</td>
				<td align="center" class="smalltext" width="8%">', $txt['package_file_perms_status_no_change'], '</td>
			</tr>';

	foreach ($context['file_tree'] as $name => $dir)
	{
		echo '
			<tr class="windowbg2">
				<td width="30%"><strong>';

			if (!empty($dir['type']) && ($dir['type'] == 'dir' || $dir['type'] == 'dir_recursive'))
				echo '
					<img src="', $settings['default_images_url'], '/board.gif" alt="*" />';

			echo '
					', $name, '
				</strong></td>
				<td width="30%">
					<span style="color: ', ($dir['perms']['chmod'] ? 'green' : 'red'), '">', ($dir['perms']['chmod'] ? $txt['package_file_perms_writable'] : $txt['package_file_perms_not_writable']), '</span>
					', ($dir['perms']['perms'] ? '&nbsp;(' . $txt['package_file_perms_chmod'] . ': ' . substr(sprintf('%o', $dir['perms']['perms']), -4) . ')' : ''), '
				</td>
				<td align="center" width="8%" style="background-color: #D1F7BF"><input type="radio" name="permStatus[', $name, ']" value="read" /></td>
				<td align="center" width="8%" style="background-color: #FFBBBB"><input type="radio" name="permStatus[', $name, ']" value="writable" /></td>
				<td align="center" width="8%" style="background-color: #FDD7AF"><input type="radio" name="permStatus[', $name, ']" value="execute" /></td>
				<td align="center" width="8%" style="background-color: #C2C6C0"><input type="radio" name="permStatus[', $name, ']" value="custom" /></td>
				<td align="center" width="8%" style="background-color: #FFFFFF"><input type="radio" name="permStatus[', $name, ']" value="no_change" checked="checked" /></td>
			</tr>';

		if (!empty($dir['contents']))
			template_permission_show_contents($name, $dir['contents'], 1);
	}

	echo '
		</table><br />
		<table border="0" width="100%" cellspacing="0" cellpadding="4" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['package_file_perms_change'], '</td>
			</tr>
			<tr class="windowbg2" valign="top">
				<td width="4%" align="center">
					<input type="radio" name="method" value="individual" checked="checked" id="method_individual" />
				</td>
				<td>
					<label for="method_individual"><b>', $txt['package_file_perms_apply'], '</b></label><br />
					<em class="smalltext">', $txt['package_file_perms_custom'], ': <input type="text" name="custom_value" value="0755" maxlength="4" size="5" />&nbsp;<a href="', $scripturl, '?action=helpadmin;help=chmod_flags" onclick="return reqWin(this.href);" class="help">(?)</a></em>
				</td>
			</tr>
			<tr class="windowbg2" valign="top">
				<td width="4%" align="center">
					<input type="radio" name="method" value="predefined" id="method_predefined" />
				</td>
				<td>
					<label for="method_predefined"><b>', $txt['package_file_perms_predefined'], ':</b></label>
					<select name="predefined" onchange="document.getElementById(\'method_predefined\').checked = \'checked\';">
						<option value="restricted" selected="selected">', $txt['package_file_perms_pre_restricted'], '</option>
						<option value="standard">', $txt['package_file_perms_pre_standard'], '</option>
						<option value="free">', $txt['package_file_perms_pre_free'], '</option>
					</select><br />
					<em class="smalltext">', $txt['package_file_perms_predefined_note'], '</em>
				</td>
			</tr>';

	// Likely to need FTP?
	if (empty($context['ftp_connected']))
		echo '
			<tr class="windowbg2">
				<td colspan="2">
					<hr />
					<div style="width: 530px; padding-left: 10px;">
						', $txt['package_file_perms_ftp_details'], ':
						', template_control_chmod(), '
						<span class="smalltext">', $txt['package_file_perms_ftp_retain'], '</span>
					</div>
				</td>
			</tr>';

	echo '
			<tr class="windowbg2">
				<td colspan="2" align="right">
					<span id="test_ftp_placeholder_full"></span>
					<input type="hidden" name="action_changes" value="1" />
					<input type="submit" value="', $txt['package_file_perms_go'], '" name="go" />
				</td>
			</tr>
		</table>';

	// Any looks fors we've already done?
	foreach ($context['look_for'] as $path)
		echo '
			<input type="hidden" name="back_look[]" value="', $path, '" />';
	echo '
	</form>';
}

function template_permission_show_contents($ident, $contents, $level, $has_more = false)
{
	global $settings, $txt, $scripturl, $context;

	$js_ident = preg_replace('~[^A-Za-z0-9_\-=:]~', ':-:', $ident);
	// Have we actually done something?
	$drawn_div = false;

	foreach ($contents as $name => $dir)
	{
		if (isset($dir['perms']))
		{
			if (!$drawn_div)
			{
				$drawn_div = true;
				echo '
			<div id="', $js_ident, '">';
			}

			$cur_ident = preg_replace('~[^A-Za-z0-9_\-=:]~', ':-:', $ident . '/' . $name);
			echo '
			<tr class="windowbg" id="content_', $cur_ident, '">
				<td class="smalltext" width="30%">' . str_repeat('&nbsp;', $level * 5), '
					', (!empty($dir['type']) && $dir['type'] == 'dir_recursive') || !empty($dir['list_contents']) ? '<a name="fol_' . $cur_ident . '" id="link_' . $cur_ident . '" href="' . $scripturl . '?action=admin;area=packages;sa=perms;find=' . base64_encode($ident . '/' . $name) . ';back_look=' . $context['back_look_data'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '#fol_' . $cur_ident . '" onclick="return expandFolder(\'' . $cur_ident . '\', \'' . addcslashes($ident . '/' . $name, "'\\") . '\');">' : '';

			if (!empty($dir['type']) && ($dir['type'] == 'dir' || $dir['type'] == 'dir_recursive'))
				echo '
					<img src="', $settings['default_images_url'], '/board.gif" alt="*" />';

			echo '
					', $name, '
					', !empty($dir['contents']) ? '</a>' : '', '
				</td>
				<td class="smalltext">
					<span style="color: ', ($dir['perms']['chmod'] ? 'green' : 'red'), '">', ($dir['perms']['chmod'] ? $txt['package_file_perms_writable'] : $txt['package_file_perms_not_writable']), '</span>
					', ($dir['perms']['perms'] ? '&nbsp;(' . $txt['package_file_perms_chmod'] . ': ' . substr(sprintf('%o', $dir['perms']['perms']), -4) . ')' : ''), '
				</td>
				<td align="center" width="8%" style="background-color: #D1F7BF"><input type="radio" name="permStatus[', $ident . '/' . $name, ']" value="read" /></td>
				<td align="center" width="8%" style="background-color: #FFBBBB"><input type="radio" name="permStatus[', $ident . '/' . $name, ']" value="writable" /></td>
				<td align="center" width="8%" style="background-color: #FDD7AF"><input type="radio" name="permStatus[', $ident . '/' . $name, ']" value="execute" /></td>
				<td align="center" width="8%" style="background-color: #C2C6C0"><input type="radio" name="permStatus[', $ident . '/' . $name, ']" value="custom" /></td>
				<td align="center" width="8%" style="background-color: #FFFFFF"><input type="radio" name="permStatus[', $ident . '/' . $name, ']" value="no_change" checked="checked" /></td>
			</tr>
			<tr id="insert_div_loc_' . $cur_ident . '" style="display: none;"><td></td></tr>';

			if (!empty($dir['contents']))
			{
				template_permission_show_contents($ident . '/' . $name, $dir['contents'], $level + 1, !empty($dir['more_files']));

			}
		}
	}

	// We have more files to show?
	if ($has_more)
		echo '
	<tr class="windowbg" id="content_', $js_ident, '_more">
		<td class="smalltext" width="40%">' . str_repeat('&nbsp;', $level * 5), '
			&#171; <a href="' . $scripturl . '?action=admin;area=packages;sa=perms;find=' . base64_encode($ident) . ';fileoffset=', ($context['file_offset'] + $context['file_limit']), ';' . $context['session_var'] . '=' . $context['session_id'] . '#fol_' . preg_replace('~[^A-Za-z0-9_\-=:]~', ':-:', $ident) . '">', $txt['package_file_perms_more_files'], '</a> &#187;
		</td>
		<td colspan="6"></td>
	</tr>';

	if ($drawn_div)
	{
		echo '
	</div>';

		// Hide anything too far down the tree.
		$isFound = false;
		foreach ($context['look_for'] as $tree)
		{
			if (substr($tree, 0, strlen($ident)) == $ident)
				$isFound = true;
		}

		if ($level > 1 && !$isFound)
			echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			expandFolder(\'', $js_ident, '\', \'\');
		// ]]></script>';
	}
}

function template_action_permissions()
{
	global $txt, $scripturl, $context, $settings;

	$countDown = 3;

	echo '
	<form action="', $scripturl, '?action=admin;area=packages;sa=perms;', $context['session_var'], '=', $context['session_id'], '" id="perm_submit" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" align="center" width="60%" cellspacing="" cellpadding="2" class="tborder">
			<tr class="titlebg">
				<td>', $txt['package_file_perms_applying'], '</td>
			</tr>';

	if (!empty($context['skip_ftp']))
		echo '
			<tr class="windowbg">
				<td>
					<div style="border: 2px dashed red; margin: 5px; padding: 4px;">
						', $txt['package_file_perms_skipping_ftp'], '
					</div>
				</td>
			</tr>';

	// How many have we done?
	$remaining_items = count($context['method'] == 'individual' ? $context['to_process'] : $context['directory_list']);
	$progress_message = sprintf($context['method'] == 'individual' ? $txt['package_file_perms_items_done'] : $txt['package_file_perms_dirs_done'], $context['total_items'] - $remaining_items, $context['total_items']);
	$progress_percent = round(($context['total_items'] - $remaining_items) / $context['total_items'] * 100, 1);

	echo '
			<tr class="windowbg">
				<td>
					<div style="padding-left: 20%; padding-right: 20%; margin-top: 1ex;">
						<strong>', $progress_message, '</strong>
						<div style="font-size: 8pt; height: 12pt; border: 1px solid black; background-color: white; padding: 1px; position: relative;">
							<div style="padding-top: ', $context['browser']['is_safari'] || $context['browser']['is_konqueror'] ? '2pt' : '1pt', '; width: 100%; z-index: 2; color: black; position: absolute; text-align: center; font-weight: bold;">', $progress_percent, '%</div>
							<div style="width: ', $progress_percent, '%; height: 12pt; z-index: 1; background-color: #98B8F4;">&nbsp;</div>
						</div>
					</div>
				</td>
			</tr>';

	// Second progress bar for a specific directory?
	if ($context['method'] != 'individual' && !empty($context['total_files']))
	{
		$file_progress_message = sprintf($txt['package_file_perms_files_done'], $context['file_offset'], $context['total_files']);
		$file_progress_percent = round($context['file_offset'] / $context['total_files'] * 100, 1);

		echo '
			<tr class="windowbg">
				<td>
					<div style="padding-left: 20%; padding-right: 20%; margin-top: 1ex;">
						<strong>', $file_progress_message, '</strong>
						<div style="font-size: 8pt; height: 12pt; border: 1px solid black; background-color: white; padding: 1px; position: relative;">
							<div style="padding-top: ', $context['browser']['is_safari'] || $context['browser']['is_konqueror'] ? '2pt' : '1pt', '; width: 100%; z-index: 2; color: black; position: absolute; text-align: center; font-weight: bold;">', $file_progress_percent, '%</div>
							<div style="width: ', $file_progress_percent, '%; height: 12pt; z-index: 1; background-color: #C1FFC1;">&nbsp;</div>
						</div>
					</div>
				</td>
			</tr>';
	}

	echo '
			<tr class="titlebg">
				<td>';

	// Put out the right hidden data.
	if ($context['method'] == 'individual')
		echo '
					<input type="hidden" name="custom_value" value="', $context['custom_value'], '" />
					<input type="hidden" name="totalItems" value="', $context['total_items'], '" />
					<input type="hidden" name="toProcess" value="', base64_encode(serialize($context['to_process'])), '" />';
	else
		echo '
					<input type="hidden" name="predefined" value="', $context['predefined_type'], '" />
					<input type="hidden" name="fileOffset" value="', $context['file_offset'], '" />
					<input type="hidden" name="totalItems" value="', $context['total_items'], '" />
					<input type="hidden" name="dirList" value="', base64_encode(serialize($context['directory_list'])), '" />
					<input type="hidden" name="specialFiles" value="', base64_encode(serialize($context['special_files'])), '" />';

	// Are we not using FTP for whatever reason.
	if (!empty($context['skip_ftp']))
		echo '
					<input type="hidden" name="skip_ftp" value="1" />';

	// Retain state.
	foreach ($context['back_look_data'] as $path)
		echo '
					<input type="hidden" name="back_look[]" value="', $path, '" />';

	echo '
					<input type="hidden" name="method" value="', $context['method'], '" />
					<input type="hidden" name="action_changes" value="1" />
					<input type="submit" name="go" id="cont" value="', $txt['not_done_continue'], '" />
				</td>
			</tr>
		</table>
	</form>';

	// Just the countdown stuff
	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var countdown = ', $countDown, ';
		doAutoSubmit();

		function doAutoSubmit()
		{
			if (countdown == 0)
				document.forms.perm_submit.submit();
			else if (countdown == -1)
				return;

			document.getElementById(\'cont\').value = "', $txt['not_done_continue'], ' (" + countdown + ")";
			countdown--;

			setTimeout("doAutoSubmit();", 1000);
		}
	// ]]></script>';

}

?>
