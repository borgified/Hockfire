<?php
// Version: 2.0 RC1; Wireless

// This is the header for WAP 1.1 output. You can view it with ?wap in the URL.
function template_wap_above()
{
	global $context, $settings, $options;

	// Show the xml declaration...
	echo '<?xml version="1.0"?', '>
<!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.1//EN" "http://www.wapforum.org/DTD/wml_1.1.xml">
<wml>
<head>
</head>';
}

// This is the board index (main page) in WAP 1.1.
function template_wap_boardindex()
{
	global $context, $settings, $options, $scripturl;

	// This is the "main" card...
	echo '
	<card id="main" title="', $context['page_title'], '">
		<p><b>', $context['forum_name_html_safe'], '</b><br /></p>';

	// Show an anchor for each category.
	foreach ($context['categories'] as $category)
	{
		// Skip it if it's empty.
		if (!empty($category['boards']))
			echo '
		<p><a href="#c', $category['id'], '">', $category['name'], '</a><br /></p>';
	}

	// Okay, that's it for the main card.
	echo '
	</card>';

	// Now fill out the deck of cards with the boards in each category.
	foreach ($context['categories'] as $category)
	{
		// Begin the card, and make the name available.
		echo '
	<card id="c', $category['id'], '" title="', strip_tags($category['name']), '">
		<p><b>', strip_tags($category['name']), '</b><br /></p>';

		// Now show a link for each board.
		foreach ($category['boards'] as $board)
			echo '
		<p><a href="', $scripturl, '?board=', $board['id'], '.0;wap">', $board['name'], '</a><br /></p>';

		echo '
	</card>';
	}
}

// This is the message index (list of topics in a board) for WAP 1.1.
function template_wap_messageindex()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<card id="main" title="', $context['page_title'], '">
		<p><b>', $context['name'], '</b></p>
		<p>', $txt['pages'], ': ', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';wap">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';wap">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';wap">&gt;</a> <a href="' . $context['links']['last'] . ';wap">&gt;&gt;</a> ' : '', '<br /></p>';

	if (isset($context['boards']) && count($context['boards']) > 0)
	{
		foreach ($context['boards'] as $board)
			echo '
		<p>- <a href="', $scripturl, '?board=', $board['id'], '.0;wap">', $board['name'], '</a><br /></p>';
		echo '
		<p><br /></p>';
	}

	if (!empty($context['topics']))
		foreach ($context['topics'] as $topic)
			echo '
		<p><a href="', $scripturl, '?topic=', $topic['id'], '.0;wap">', $topic['first_post']['subject'], '</a> - ', $topic['first_post']['member']['name'], '<br /></p>';

	echo '
		<p>', $txt['pages'], ': ', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';wap">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';wap">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';wap">&gt;</a> <a href="' . $context['links']['last'] . ';wap">&gt;&gt;</a> ' : '', '</p>
	</card>';
}

function template_wap_display()
{
	global $context, $settings, $options, $txt;

	echo '
	<card id="main" title="', $context['page_title'], '">
		<p><b>' . $context['linktree'][1]['name'] . ' > ' . $context['linktree'][count($context['linktree']) - 2]['name'] . '</b></p>
		<p><b>', $context['subject'], '</b></p>
		<p>', $txt['pages'], ': ', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';wap">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';wap">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';wap">&gt;</a> <a href="' . $context['links']['last'] . ';wap">&gt;&gt;</a> ' : '', '<br /><br /></p>';

	while ($message = $context['get_message']())
	{
		// This is a special modification to the post so it will work on phones:
		$wireless_message = strip_tags(str_replace(array('<blockquote>', '</blockquote>', '<code>', '</code>', '<li>'), array('&gt;&gt;&gt;&gt;', '&lt;&lt;&lt;&lt;', '&gt;&gt;&gt;&gt;', '&lt;&lt;&lt;&lt;', '<br />* '), $message['body']), '<br>');

		echo '
		<p><u>', $message['member']['name'], '</u>:<br /></p>
		<p>', $wireless_message, '<br /><br /></p>';
	}

	echo '
		<p>', $txt['pages'], ': ', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';wap">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';wap">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';wap">&gt;</a> <a href="' . $context['links']['last'] . ';wap">&gt;&gt;</a> ' : '', '</p>
	</card>';
}

function template_wap_login()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<card id="login" title="', $context['page_title'], '">';

	if (isset($context['login_errors']))
		foreach ($context['login_errors'] as $error)
			echo '
			<p><b>', $error, '</b></p>';

	echo '
		<p>', $txt['username'], ':<br />
		<input type="text" name="user" /></p>

		<p>', $txt['password'], ':<br />
		<input type="password" name="passwrd" /></p>

		<p><do type="accept" label="', $txt['login'], '">
			<go method="post" href="', $scripturl, '?action=login2;wap">
				<postfield name="user" value="$user" />
				<postfield name="passwrd" value="$passwrd" />
				<postfield name="cookieneverexp" value="1" />
			</go>
		</do></p>
	</card>';
}

function template_wap_recent()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<card id="recent" title="', $context['page_title'], '">
		<p><b>', $_REQUEST['action'] == 'unread' ? $txt['wireless_recent_unread_posts'] : $txt['wireless_recent_unread_replies'], '</b></p>';

	if (empty($context['topics']))
		echo '
		<p>', $txt['old_posts'], '</p>';
	else
	{
		echo '
			<p>', $txt['pages'], ': ', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';wap">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';wap">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';wap">&gt;</a> <a href="' . $context['links']['last'] . ';wap">&gt;&gt;</a> ' : '', '<br /><br /></p>';
		foreach ($context['topics'] as $topic)
		{
			echo '
			<p><a href="', $scripturl, '?topic=', $topic['id'], '.msg', $topic['new_from'], ';topicseen;imode#new">', $topic['first_post']['subject'], '</a></p>';
		}
	}

	echo '
	</card>';
}

function template_wap_error()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<card id="main" title="', $context['page_title'], '">
		<p><b>', $context['error_title'], '</b></p>
		<p>', $context['error_message'], '</p>
		<p><a href="', $scripturl, '?wap">', $txt['wireless_error_home'], '</a></p>
	</card>';
}

function template_wap_below()
{
	global $context, $settings, $options;

	echo '
</wml>';
}

// The cHTML protocol used for i-mode starts here.
function template_imode_above()
{
	global $context, $settings, $options;

	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD Compact HTML 1.0 Draft//EN">
<html', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
		<title>', $context['page_title'], '</title>
	</head>
	<body>';
}

function template_imode_boardindex()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<table border="0" cellspacing="0" cellpadding="0">
			<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $context['forum_name_html_safe'], '</font></td></tr>';
	$count = 0;
	foreach ($context['categories'] as $category)
	{
		if (!empty($category['boards']) || $category['is_collapsed'])
			echo '
			<tr bgcolor="#b6dbff"><td>', $category['can_collapse'] ? '<a href="' . $scripturl . '?action=collapse;c=' . $category['id'] . ';sa=' . ($category['is_collapsed'] ? 'expand' : 'collapse') . ';imode">' : '', $category['name'], $category['can_collapse'] ? '</a>' : '', '</td></tr>';

		foreach ($category['boards'] as $board)
		{
			$count++;
			echo '
			<tr><td>', $board['new'] ? '<font color="#ff0000">' : '', $count < 10 ? '&#' . (59105 + $count) . ';' : '<b>-</b>', $board['new'] ? '</font>' : ($board['children_new'] ? '<font color="#ff0000">.</font>' : ''), ' <a href="', $scripturl, '?board=', $board['id'], '.0;imode"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $board['name'], '</a></td></tr>';
		}
	}
	echo '
			<tr bgcolor="#6d92aa"><td>', $txt['wireless_options'], '</td></tr>';
	if ($context['user']['is_guest'])
		echo '
			<tr><td><a href="', $scripturl, '?action=login;imode">', $txt['wireless_options_login'], '</a></td></tr>';
	else
	{
		if ($context['allow_pm'])
			echo '
			<tr><td><a href="', $scripturl, '?action=pm;imode">', empty($context['user']['unread_messages']) ? $txt['wireless_pm_inbox'] : sprintf($txt['wireless_pm_inbox_new'], $context['user']['unread_messages']), '</a></td></tr>';
		echo '
			<tr><td><a href="', $scripturl, '?action=unread;imode">', $txt['wireless_recent_unread_posts'], '</a></td></tr>
			<tr><td><a href="', $scripturl, '?action=unreadreplies;imode">', $txt['wireless_recent_unread_replies'], '</a></td></tr>
			<tr><td><a href="', $scripturl, '?action=logout;', $context['session_var'], '=', $context['session_id'], ';imode">', $txt['wireless_options_logout'], '</a></td></tr>';
	}
	echo '
		</table>';
}

function template_imode_messageindex()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<table border="0" cellspacing="0" cellpadding="0">
			<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $context['name'], '</font></td></tr>';

	if (!empty($context['boards']))
	{
		echo '
		<tr bgcolor="#b6dbff"><td>', $txt['parent_boards'], '</td></tr>';
		foreach ($context['boards'] as $board)
			echo '
		<tr><td>', $board['new'] ? '<font color="#ff0000">-</font> ' : ($board['children_new'] ? '-<font color="#ff0000">.</font>' : '- '), '<a href="', $scripturl, '?board=', $board['id'], '.0;imode">', $board['name'], '</a></td></tr>';
	}

	$count = 0;
	if (!empty($context['topics']))
	{
		echo '
			<tr bgcolor="#b6dbff"><td>', $txt['topics'], '</td></tr>
			<tr><td>', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';imode">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';imode">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';imode">&gt;</a> <a href="' . $context['links']['last'] . ';imode">&gt;&gt;</a> ' : '', '</td></tr>';
		foreach ($context['topics'] as $topic)
		{
			$count++;
			echo '
			<tr><td>', $count < 10 ? '&#' . (59105 + $count) . '; ' : '', '<a href="', $scripturl, '?topic=', $topic['id'], '.0;imode"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $topic['first_post']['subject'], '</a>', $topic['new'] && $context['user']['is_logged'] ? ' [<a href="' . $scripturl . '?topic=' . $topic['id'] . '.msg' . $topic['new_from'] . ';imode#new">' . $txt['new'] . '</a>]' : '', '</td></tr>';
		}
	}
	echo '
			<tr bgcolor="#b6dbff"><td>', $txt['wireless_navigation'], '</td></tr>
			<tr><td>&#59115; <a href="', $context['links']['up'], ';imode" accesskey="0">', $txt['wireless_navigation_up'], '</a></td></tr>', !empty($context['links']['next']) ? '
			<tr><td>&#59104; <a href="' . $context['links']['next'] . ';imode" accesskey="#">' . $txt['wireless_navigation_next'] . '</a></td></tr>' : '', !empty($context['links']['prev']) ? '
			<tr><td><b>[*]</b> <a href="' . $context['links']['prev'] . ';imode" accesskey="*">' . $txt['wireless_navigation_prev'] . '</a></td></tr>' : '', $context['can_post_new'] ? '
			<tr><td><a href="' . $scripturl . '?action=post;board=' . $context['current_board'] . '.0;imode">' . $txt['start_new_topic'] . '</a></td></tr>' : '', '
		</table>';
}

function template_imode_display()
{
	global $context, $settings, $options, $scripturl, $board, $txt;

	echo '
		<table border="0" cellspacing="0" cellpadding="0">
			<tr bgcolor="#b6dbff"><td>' . $context['linktree'][1]['name'] . ' > ' . $context['linktree'][count($context['linktree']) - 2]['name'] . '</td></tr>
			<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $context['subject'], '</font></td></tr>
			<tr><td>', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';imode">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';imode">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';imode">&gt;</a> <a href="' . $context['links']['last'] . ';imode">&gt;&gt;</a> ' : '', '</td></tr>';
	while ($message = $context['get_message']())
	{
		// This is a special modification to the post so it will work on phones:
		$wireless_message = strip_tags(str_replace(array('<blockquote>', '</blockquote>', '<code>', '</code>', '<li>'), array('&gt;&gt;&gt;&gt;', '&lt;&lt;&lt;&lt;', '&gt;&gt;&gt;&gt;', '&lt;&lt;&lt;&lt;', '<br />* '), $message['body']), '<br>');

		echo '
			<tr><td>', $message['first_new'] ? '
				<a name="new"></a>' : '',
				$context['wireless_moderate'] && $message['member']['id'] ? '<a href="' . $scripturl . '?action=profile;u=' . $message['member']['id'] . ';imode">' . $message['member']['name'] . '</a>' : '<b>' . $message['member']['name'] . '</b>', ':
				', ((empty($context['wireless_more']) && $message['can_modify']) || !empty($context['wireless_moderate']) ? '[<a href="' . $scripturl . '?action=post;msg=' . $message['id'] . ';topic=' . $context['current_topic'] . '.' . $context['start'] . ';' . $context['session_var'] . '=' . $context['session_id'] . ';imode">' . $txt['wireless_display_edit'] . '</a>]' : ''), '<br />
				', $wireless_message, '
			</td></tr>';
	}
	echo '
			<tr bgcolor="#b6dbff"><td>', $txt['wireless_navigation'], '</td></tr>
			<tr><td>&#59115; <a href="', $context['links']['up'], ';imode" accesskey="0">', $txt['wireless_navigation_index'], '</a></td></tr>', !empty($context['links']['next']) ? '
			<tr><td><a href="' . $context['links']['next'] . ';imode' . $context['wireless_moderate'] . '" accesskey="#">' . $txt['wireless_navigation_next'] . '</a></td></tr>' : '', !empty($context['links']['prev']) ? '
			<tr><td><a href="' . $context['links']['prev'] . ';imode' . $context['wireless_moderate'] . '" accesskey="*">' . $txt['wireless_navigation_prev'] . '</a></td></tr>' : '', $context['can_reply'] ? '
			<tr><td><a href="' . $scripturl . '?action=post;topic=' . $context['current_topic'] . '.' . $context['start'] . ';imode">' . $txt['reply'] . '</a></td></tr>' : '';

	if (!empty($context['wireless_more']) && empty($context['wireless_moderate']))
		echo '
			<tr><td><a href="', $scripturl, '?topic=', $context['current_topic'], '.', $context['start'], ';moderate;imode">', $txt['wireless_display_moderate'], '</a></td></tr>';
	elseif (!empty($context['wireless_moderate']))
	{
		if ($context['can_sticky'])
			echo '
				<tr><td><a href="', $scripturl, '?action=sticky;topic=', $context['current_topic'], '.', $context['start'], ';', $context['session_var'], '=', $context['session_id'], ';imode">', $txt['wireless_display_' . ($context['is_sticky'] ? 'unsticky' : 'sticky')], '</a></td></tr>';
		if ($context['can_lock'])
			echo '
				<tr><td><a href="', $scripturl, '?action=lock;topic=', $context['current_topic'], '.', $context['start'], ';', $context['session_var'], '=', $context['session_id'], ';imode">', $txt['wireless_display_' . ($context['is_locked'] ? 'unlock' : 'lock')], '</a></td></tr>';
	}

	echo '
		</table>';
}

function template_imode_post()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// !!! $modSettings['guest_post_no_email']
	echo '
		<form action="', $scripturl, '?action=', $context['destination'], ';board=', $context['current_board'], '.0;imode" method="post">
			<table border="0" cellspacing="0" cellpadding="0">';
	
	if ($context['locked'])
			echo '
				<tr><td>' . $txt['topic_locked_no_reply'] . '</td></tr>';
	
	if (isset($context['name']) && isset($context['email']))
	{
		echo '
				<tr><td>', isset($context['post_error']['long_name']) || isset($context['post_error']['no_name']) ? '<font color="#cc0000">' . $txt['username'] . '</font>' : $txt['username'], ':</td></tr>
				<tr><td><input type="text" name="guestname" value="', $context['name'], '" /></td></tr>';
	
		if (empty($modSettings['guest_post_no_email']))
			echo '
				<tr><td>', isset($context['post_error']['no_email']) || isset($context['post_error']['bad_email']) ? '<font color="#cc0000">' . $txt['email'] . '</font>' : $txt['email'], ':</td></tr>
				<tr><td><input type="text" name="email" value="', $context['email'], '" /></td></tr>';
	}

	// !!! Needs a more specific imode template.
	if ($context['require_verification'])
		echo '
				<tr><td>', !empty($context['post_error']['need_qr_verification']) ? '<font color="#cc0000">' . $txt['verification'] . '</font>' : $txt['verification'], ':</td></tr>
				<tr><td>', template_control_verification($context['visual_verification_id'], 'all'), '</td></tr>';

	echo '
				<tr><td>', isset($context['post_error']['no_subject']) ? '<font color="#FF0000">' . $txt['subject'] . '</font>' : $txt['subject'], ':</td></tr>
				<tr><td><input type="text" name="subject"', $context['subject'] == '' ? '' : ' value="' . $context['subject'] . '"', ' maxlength="80" /></td></tr>
				<tr><td>', isset($context['post_error']['no_message']) || isset($context['post_error']['long_message']) ? '<font color="#ff0000">' . $txt['message'] . '</font>' : $txt['message'], ':</td></tr>
				<tr><td><textarea name="message" rows="3" cols="20">', $context['message'], '</textarea></td></tr>
				<tr><td>
					<input type="submit" name="post" value="', $context['submit_label'], '" />
					<input type="hidden" name="icon" value="wireless" />
					<input type="hidden" name="goback" value="', $context['back_to_topic'] || !empty($options['return_to_post']) ? '1' : '0', '" />
					<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />', isset($context['current_topic']) ? '
					<input type="hidden" name="topic" value="' . $context['current_topic'] . '" />' : '', '
					<input type="hidden" name="notify" value="', $context['notify'] || !empty($options['auto_notify']) ? '1' : '0', '" />
				</td></tr>
				<tr><td>
					&#59115; ', !empty($context['current_topic']) ? '<a href="' . $scripturl . '?topic=' . $context['current_topic'] . '.new;imode">' . $txt['wireless_navigation_topic'] . '</a>' : '<a href="' . $scripturl . '?board=' . $context['current_board'] . '.0;imode" accesskey="0">' . $txt['wireless_navigation_index'] . '</a>', '
				</td></tr>
			</table>
		</form>';
}

function template_imode_login()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=login2;imode" method="post">
			<table border="0" cellspacing="0" cellpadding="0">
				<tr bgcolor="#b6dbff"><td>', $txt['login'], '</td></tr>';
	if (isset($context['login_errors']))
		foreach ($context['login_errors'] as $error)
		echo '
				<tr><td><b><font color="#ff00000">', $error, '</b></td></tr>';
	echo '
				<tr><td>', $txt['username'], ':</td></tr>
				<tr><td><input type="text" name="user" size="10" /></td></tr>
				<tr><td>', $txt['password'], ':</td></tr>
				<tr><td><input type="password" name="passwrd" size="10" /></td></tr>
				<tr><td><input type="submit" value="', $txt['login'], '" /><input type="hidden" name="cookieneverexp" value="1" /></td></tr>
				<tr bgcolor="#b6dbff"><td>', $txt['wireless_navigation'], '</td></tr>
				<tr><td>[0] <a href="', $scripturl, '?imode" accesskey="0">', $txt['wireless_navigation_up'], '</a></td></tr>
			</table>
		</form>';
}

function template_imode_pm()
{
	global $context, $settings, $options, $scripturl, $txt, $user_info;

	if ($_REQUEST['action'] == 'findmember')
	{
		echo '
		<form action="', $scripturl, '?action=findmember;', $context['session_var'], '=', $context['session_id'], ';imode" method="post">
			<table border="0" cellspacing="0" cellpadding="0">
				<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $txt['wireless_pm_search_member'], '</font></td></tr>
				<tr bgcolor="#b6dbff"><td>', $txt['find_members'], '</td></tr>
				<tr><td>
					<b>', $txt['wireless_pm_search_name'], ':</b>
					<input type="text" name="search" value="', isset($context['last_search']) ? $context['last_search'] : '', '" />', empty($_REQUEST['u']) ? '' : '
					<input type="hidden" name="u" value="' . $_REQUEST['u'] . '" />', '
				</td></tr>
				<tr><td><input type="submit" value="', $txt['search'], '" /></td></tr>';
		if (!empty($context['last_search']))
		{
			echo '
				<tr bgcolor="#b6dbff"><td>', $txt['find_results'], '</td></tr>';
			if (empty($context['results']))
				echo '
				<tr bgcolor="#b6dbff"><td>[-] ', $txt['find_no_results'], '</tr></td>';
			else
			{
				echo '
				<tr bgcolor="#b6dbff"><td>', empty($context['links']['prev']) ? '' : '<a href="' . $context['links']['first'] . ';imode">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';imode">&lt;</a> ', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', empty($context['links']['next']) ? '' : ' <a href="' . $context['links']['next'] . ';imode">&gt;</a> <a href="' . $context['links']['last'] . ';imode">&gt;&gt;</a> ', '</tr></td>';
				$count = 0;
				foreach ($context['results'] as $result)
				{
					$count++;
					echo '
				<tr bgcolor="#b6dbff"><td>
					', $count < 10 ? '&#' . (59105 + $count) . '; ' : '', '<a href="', $scripturl, '?action=pm;sa=send;u=', empty($_REQUEST['u']) ? $result['id'] : $_REQUEST['u'] . ',' . $result['id'], ';imode"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $result['name'], '</a>
				</tr></td>';
				}
			}
		}
		echo '
				<tr bgcolor="#b6dbff"><td>', $txt['wireless_navigation'], '</tr></td>
				<tr><td>[0] <a href="', $context['links']['up'], ';imode" accesskey="0">', $txt['wireless_navigation_up'], '</a></tr></td>';
		if (!empty($context['results']))
			echo empty($context['links']['next']) ? '' : '
				<tr><td>[#] <a href="' . $context['links']['next'] . ';imode" accesskey="#">' . $txt['wireless_navigation_next'] . '</a></tr></td>', empty($context['links']['prev']) ? '' : '
				<tr><td>[*] <a href="' . $context['links']['prev'] . ';imode" accesskey="*">' . $txt['wireless_navigation_prev'] . '</a></tr></td>';
		echo '
			</table>
		</form>';
	}
	elseif (!empty($_GET['sa']))
	{
		echo '
				<table border="0" cellspacing="0" cellpadding="0">';
		if ($_GET['sa'] == 'addbuddy')
		{
			echo '
					<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $txt['wireless_pm_add_buddy'], '</font></td></tr>
					<tr bgcolor="#b6dbff"><td>', $txt['wireless_pm_select_buddy'], '</td></tr>';
			$count = 0;
			foreach ($context['buddies'] as $buddy)
			{
				$count++;
				if ($buddy['selected'])
					echo '
					<tr><td>[-] <span style="color: gray">', $buddy['name'], '</span></tr></td>';
				else
					echo '
					<tr><td>
						', $count < 10 ? '&#' . (59105 + $count) . '; ' : '', '<a href="', $buddy['add_href'], ';imode"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $buddy['name'], '</a>
					</tr></td>';
			}
			echo '
					<tr bgcolor="#b6dbff"><td>', $txt['wireless_navigation'], '</tr></td>
					<tr><td>[0] <a href="', $context['pm_href'], ';imode" accesskey="0">', $txt['wireless_navigation_up'], '</a></tr></td>
				</table>';
		}
		if ($_GET['sa'] == 'send' || $_GET['sa'] == 'send2')
		{
			echo '
				<form action="', $scripturl, '?action=pm;sa=send2;imode" method="post">
					<table border="0" cellspacing="0" cellpadding="0">
						<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $txt['new_message'], '</tr></td>', empty($context['post_error']['messages']) ? '' : '
						<tr><td><font color="#ff0000">' . implode('<br />', $context['post_error']['messages']) . '</font></tr></td>', '
						<tr><td>
							<b>', $txt['pm_to'], ':</b> ';
			if (empty($context['recipients']['to']))
				echo $txt['wireless_pm_no_recipients'];
			else
			{
				$to_names = array();
				foreach ($context['recipients']['to'] as $to)
					$to_names[] = $to['name'];
				echo implode(', ', $to_names);
			}
			echo '
				', empty($_REQUEST['u']) ? '' : '<input type="hidden" name="u" value="' . implode(',', $_REQUEST['u']) . '" />', '<br />
							<a href="', $scripturl, '?action=findmember', empty($_REQUEST['u']) ? '' : ';u=' . implode(',', $_REQUEST['u']), ';', $context['session_var'], '=', $context['session_id'], ';imode">', $txt['wireless_pm_search_member'], '</a>', empty($user_info['buddies']) ? '' : '<br />
							<a href="' . $scripturl . '?action=pm;sa=addbuddy' . (empty($_REQUEST['u']) ? '' : ';u=' . implode(',', $_REQUEST['u'])) . ';imode">' . $txt['wireless_pm_add_buddy'] . '</a>', '
						</tr></td>
						<tr><td>
							<b>', $txt['subject'], ':</b> <input type="text" name="subject" value="', $context['subject'], '" />
						</tr></td>
						<tr><td>
							<b>', $txt['message'], ':</b><br />
							<textarea name="message" rows="3" cols="20">', $context['message'], '</textarea>
						</tr></td>
						<tr><td>
							<input type="submit" value="', $txt['send_message'], '" />
							<input type="hidden" name="outbox" value="', $context['copy_to_outbox'] ? '1' : '0', '" />
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />
							<input type="hidden" name="replied_to" value="', !empty($context['quoted_message']['id']) ? $context['quoted_message']['id'] : 0, '" />
							<input type="hidden" name="folder" value="', $context['folder'], '" />
						</tr></td>';
			if ($context['reply'])
				echo '
						<tr bgcolor="#b6dbff"><td>', $txt['wireless_pm_reply_to'], '</tr></td>
						<tr><td><b>', $context['quoted_message']['subject'], '</b></tr></td>
						<tr><td>', $context['quoted_message']['body'], '</tr></td>';
			echo '
						<tr bgcolor="#b6dbff"><td>', $txt['wireless_navigation'], '</tr></td>
						<tr><td>[0] <a href="', $scripturl, '?action=pm;imode" accesskey="0">', $txt['wireless_navigation_up'], '</a></tr></td>
					</table>
				</form>';
		}
	}
	elseif (empty($_GET['pmsg']))
	{
		echo '
		<table border="0" cellspacing="0" cellpadding="0">
			<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $context['current_label_id'] == -1 ? $txt['wireless_pm_inbox'] : $txt['pm_current_label'] . ': ' . $context['current_label'], '</td></tr>
			<tr><td>', empty($context['links']['prev']) ? '' : '<a href="' . $context['links']['first'] . ';imode">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';imode">&lt;</a> ', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', empty($context['links']['next']) ? '' : ' <a href="' . $context['links']['next'] . ';imode">&gt;</a> <a href="' . $context['links']['last'] . ';imode">&gt;&gt;</a> ', '</tr></td>';
		$count = 0;
		while ($message = $context['get_pmessage']())
		{
			$count++;
			echo '
			<tr><td>
				', $count < 10 ? '&#' . (59105 + $count) . '; ' : '', '<a href="', $scripturl, '?action=pm;start=', $context['start'], ';pmsg=', $message['id'], ';l=', $context['current_label_id'], ';imode"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $message['subject'], ' <i>', $txt['wireless_pm_by'], '</i> ', $message['member']['name'], '</a>
			</td></tr>';
		}

		if ($context['currently_using_labels'])
		{
			$labels = array();
			ksort($context['labels']);
			foreach ($context['labels'] as $label)
				$labels[] = '<a href="' . $scripturl . '?action=pm;l=' . $label['id'] . ';imode">' . $label['name'] . '</a>' . (!empty($label['unread_messages']) ? ' (' . $label['unread_messages'] . ')' : '');
			echo '
			<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $txt['pm_labels'], '</font></td></tr>
			<tr><td>
				', implode(', ', $labels), '
			</td></tr>';
		}
		echo '
			<tr bgcolor="#b6dbff"><td>', $txt['wireless_navigation'], '</tr></td>
			<tr><td>[0] <a href="', $scripturl, '?imode" accesskey="0">', $txt['wireless_navigation_up'], '</a></tr></td>', empty($context['links']['next']) ? '' : '
			<tr><td>[#] <a href="' . $context['links']['next'] . ';imode" accesskey="#">' . $txt['wireless_navigation_next'] . '</a></tr></td>', empty($context['links']['prev']) ? '' : '
			<tr><td>[*] <a href="' . $context['links']['prev'] . ';imode" accesskey="*">' . $txt['wireless_navigation_prev'] . '</a></tr></td>', $context['can_send_pm'] ? '
			<tr><td><a href="' . $scripturl . '?action=pm;sa=send;imode">' . $txt['new_message'] . '</a></tr></td>' : '', '
		</table>';
	}
	else
	{
		$message = $context['get_pmessage']();
		$wireless_message = strip_tags(str_replace(array('<blockquote>', '</blockquote>', '<code>', '</code>', '<li>'), array('&gt;&gt;&gt;&gt;', '&lt;&lt;&lt;&lt;', '&gt;&gt;&gt;&gt;', '&lt;&lt;&lt;&lt;', '<br />* '), $message['body']), '<br>');

		echo '
		<table border="0" cellspacing="0" cellpadding="0">
			<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $message['subject'], '</td></tr>
			<tr bgcolor="#b6dbff"><td>
				<b>', $txt['wireless_pm_by'], ':</b> ', $message['member']['name'], '<br />
				<b>', $txt['on'], ':</b> ', $message['time'], '
			</td></tr>
			<tr><td>
				', $message['body'], '
			</td></tr>
			<tr bgcolor="#b6dbff"><td>', $txt['wireless_navigation'], '</tr></td>
			<tr><td>[0] <a href="', $scripturl, '?action=pm;start=', $context['start'], ';l=', $context['current_label_id'], ';imode" accesskey="0">', $txt['wireless_navigation_up'], '</a></tr></td>';
			if ($context['can_send_pm'])
				echo '
			<tr><td><a href="', $scripturl, '?action=pm;sa=send;pmsg=', $message['id'], ';u=', $message['member']['id'], ';reply;imode">', $txt['wireless_pm_reply'], '</a></tr></td>
		</table>';
	}
}

function template_imode_recent()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<table border="0" cellspacing="0" cellpadding="0">
			<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $_REQUEST['action'] == 'unread' ? $txt['wireless_recent_unread_posts'] : $txt['wireless_recent_unread_replies'], '</tr></td>';

	$count = 0;
	if (empty($context['topics']))
		echo '
			<tr><td>', $txt['old_posts'], '</td></tr>';
	else
	{
		echo '
			<tr><td>', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';imode">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';imode">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';imode">&gt;</a> <a href="' . $context['links']['last'] . ';imode">&gt;&gt;</a> ' : '', '</td></tr>';
		foreach ($context['topics'] as $topic)
		{
			$count++;
			echo '
			<tr><td>', $count < 10 ? '&#' . (59105 + $count) . '; ' : '', '<a href="', $scripturl, '?topic=', $topic['id'], '.msg', $topic['new_from'], ';topicseen;imode#new"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $topic['first_post']['subject'], '</a></td></tr>';
		}
	}
	echo '
			<tr bgcolor="#b6dbff"><td>', $txt['wireless_navigation'], '</td></tr>
			<tr><td>[0] <a href="', $context['links']['up'], '?imode" accesskey="0">', $txt['wireless_navigation_up'], '</a></td></tr>', !empty($context['links']['next']) ? '
			<tr><td>[#] <a href="' . $context['links']['next'] . ';imode" accesskey="#">' . $txt['wireless_navigation_next'] . '</a></td></tr>' : '', !empty($context['links']['prev']) ? '
			<tr><td>[*] <a href="' . $context['links']['prev'] . ';imode" accesskey="*">' . $txt['wireless_navigation_prev'] . '</a></td></tr>' : '', '
		</table>';
}

function template_imode_error()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<table border="0" cellspacing="0" cellpadding="0">
			<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $context['error_title'], '</font></td></tr>
			<tr><td>', $context['error_message'], '</td></tr>
			<tr class="windowbg"><td>[0] <a href="', $scripturl, '?imode" accesskey="0">', $txt['wireless_error_home'], '</a></td></tr>
		</table>';
}

function template_imode_profile()
{
	global $context, $settings, $options, $scripturl, $board, $txt;

	echo '
		<table border="0" cellspacing="0" cellpadding="0">
			<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $txt['summary'], ' - ', $context['member']['name'], '</font></td></tr>
			<tr><td>
				<b>', $txt['name'], ':</b> ', $context['member']['name'], '
			</td></tr>
			<tr><td>
				<b>', $txt['position'], ': </b>', (!empty($context['member']['group']) ? $context['member']['group'] : $context['member']['post_group']), '
			</td></tr>
			<tr><td>
				<b>', $txt['lastLoggedIn'], ':</b> ', $context['member']['last_login'], '
			</td></tr>';

	if (!empty($context['member']['bans']))
	{
		echo '
			<tr><td>
				<font color="red"><b>', $txt['user_banned_by_following'], ':</b></font>';

		foreach ($context['member']['bans'] as $ban)
				echo '
				<br />', $ban['explanation'], '';

		echo '
			</td></tr>';
	}

	echo '

			<tr bgcolor="#b6dbff"><td>', $txt['additional_info'], '</td></tr>';

	if (!$context['user']['is_owner'] && $context['can_send_pm'])
		echo '
			<tr><td><a href="', $scripturl, '?action=pm;sa=send;u=', $context['id_member'], ';imode">', $txt['wireless_profile_pm'], '.</a></td></tr>';

	if (!$context['user']['is_owner'] && !empty($context['can_edit_ban']))
		echo '
			<tr><td><a href="', $scripturl, '?action=admin;area=ban;sa=add;u=', $context['id_member'], ';imode">', $txt['profileBanUser'], '.</a></td></tr>';

	echo '
			<tr><td><a href="', $scripturl, '?imode">', $txt['wireless_error_home'], '.</a></td></tr>';

	echo '
		</table>';
}

function template_imode_ban_edit()
{
	global $context, $settings, $options, $scripturl, $board, $txt, $modSettings;

	echo '
	<form action="', $scripturl, '?action=admin;area=ban;sa=add;imode" method="post">
		<table border="0" cellspacing="0" cellpadding="0">
			<tr bgcolor="#6d92aa"><td><font color="#ffffff">', $context['ban']['is_new'] ? $txt['ban_add_new'] : $txt['ban_edit'] . ' \'' . $context['ban']['name'] . '\'', '</font></td></tr>
			<tr><td>
				<b>', $txt['ban_name'], ': </b>
				<input type="text" name="ban_name" value="', $context['ban']['name'], '" size="20" />
			</td></tr>
			<tr><td>
				<b>', $txt['ban_expiration'], ': </b><br />
				<input type="radio" name="expiration" value="never" ', $context['ban']['expiration']['status'] == 'never' ? ' checked="checked"' : '', ' class="check" /> ', $txt['never'], '<br />
				<input type="radio" name="expiration" value="one_day" ', $context['ban']['expiration']['status'] == 'still_active_but_we_re_counting_the_days' ? ' checked="checked"' : '', ' class="check" /> ', $txt['ban_will_expire_within'], ' <input type="text" name="expire_date" size="3" value="', $context['ban']['expiration']['days'], '" /> ', $txt['ban_days'], '<br />
				<input type="radio" name="expiration" value="expired" ', $context['ban']['expiration']['status'] == 'expired' ? ' checked="checked"' : '', ' class="check" /> ', $txt['ban_expired'], '<br />
			</td></tr>
			<tr><td>
				<b>', $txt['ban_reason'], ': </b>
				<input type="text" name="reason" value="', $context['ban']['reason'], '" size="20" />
			</td></tr>
			<tr><td>
				<b>', $txt['ban_notes'], ': </b><br />
				<textarea name="notes" cols="20" rows="3">', $context['ban']['notes'], '</textarea>
			</td></tr>
			<tr><td>
				<b>', $txt['ban_restriction'], ': </b><br />
				<input type="checkbox" name="full_ban" value="1"', $context['ban']['cannot']['access'] ? ' checked="checked"' : '', ' class="check" /> ', $txt['ban_full_ban'], '<br />
				<input type="checkbox" name="cannot_post" value="1"', $context['ban']['cannot']['post'] ? ' checked="checked"' : '', ' class="check" /> ', $txt['ban_cannot_post'], '<br />
				<input type="checkbox" name="cannot_register" value="1"', $context['ban']['cannot']['register'] ? ' checked="checked"' : '', ' class="check" /> ', $txt['ban_cannot_register'], '<br />
				<input type="checkbox" name="cannot_login" value="1"', $context['ban']['cannot']['login'] ? ' checked="checked"' : '', ' class="check" /> ', $txt['ban_cannot_login'], '
			</td></tr>';

	if (!empty($context['ban_suggestions']))
	{
		echo '
			<tr bgcolor="#b6dbff"><td>', $txt['ban_triggers'], '</td></tr>
			<tr><td>
				<input type="checkbox" name="ban_suggestion[]" value="main_ip" class="check" /> <b>', $txt['wireless_ban_ip'], ':</b><br />
				&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="main_ip" value="', $context['ban_suggestions']['main_ip'], '" size="20" />
			</td></tr>';

		if (empty($modSettings['disableHostnameLookup']))
			echo '
			<tr><td>
				<input type="checkbox" name="ban_suggestion[]" value="hostname" class="check" /> <b>', $txt['wireless_ban_hostname'], ':</b><br />
				&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="hostname" value="', $context['ban_suggestions']['hostname'], '" size="20" />
			</td></tr>';

		echo '
			<tr><td>
				<input type="checkbox" name="ban_suggestion[]" value="email" class="check" /> <b>', $txt['wireless_ban_email'], ':</b><br />
				&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="email" value="', $context['ban_suggestions']['email'], '" size="20" />
			</td></tr>
			<tr><td>
				<input type="checkbox" name="ban_suggestion[]" value="user" class="check" /> <b>', $txt['ban_on_username'], ':</b><br />';

		if (empty($context['ban_suggestions']['member']['id']))
			echo '
				&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="user" value="" size="20" />';
		else
			echo '
				&nbsp;&nbsp;&nbsp;&nbsp;', $context['ban_suggestions']['member']['name'], '
				<input type="hidden" name="bannedUser" value="', $context['ban_suggestions']['member']['id'], '" />';

		echo '
			</td></tr>';
	}

	echo '
			<tr><td><input type="submit" name="', $context['ban']['is_new'] ? 'add_ban' : 'modify_ban', '" value="', $context['ban']['is_new'] ? $txt['ban_add'] : $txt['ban_modify'], '" /></td></tr>
			<tr bgcolor="#b6dbff"><td>', $txt['wireless_additional_info'], '</td></tr>
			<tr><td><a href="', $scripturl, '?imode">', $txt['wireless_error_home'], '.</a></td></tr>';

	echo '
		</table>
		<input type="hidden" name="old_expire" value="', $context['ban']['expiration']['days'], '" />
		<input type="hidden" name="bg" value="', $context['ban']['id'], '" />
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';
}

function template_imode_below()
{
	global $context, $settings, $options;

	echo '
	</body>
</html>';
}

// XHTMLMP (XHTML Mobile Profile) templates used for WAP 2.0 start here
function template_wap2_above()
{
	global $context, $settings, $options;

	echo '<?xml version="1.0" encoding="', $context['character_set'], '"?', '>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<title>', $context['page_title'], '</title>
		<link rel="stylesheet" href="', $settings['default_theme_url'], '/css/wireless.css" type="text/css" />
	</head>
	<body>';
}

function template_wap2_boardindex()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<p class="catbg">', $context['forum_name_html_safe'], '</p>';

	$count = 0;
	foreach ($context['categories'] as $category)
	{
		if (!empty($category['boards']) || $category['is_collapsed'])
			echo '
		<p class="titlebg">', $category['can_collapse'] ? '<a href="' . $scripturl . '?action=collapse;c=' . $category['id'] . ';sa=' . ($category['is_collapsed'] ? 'expand' : 'collapse') . ';wap2">' : '', $category['name'], $category['can_collapse'] ? '</a>' : '', '</p>';

		foreach ($category['boards'] as $board)
		{
			$count++;
			echo '
		<p class="windowbg">', $board['new'] ? '<span class="updated">' : '', $count < 10 ? '[' . $count . '' : '[-', $board['children_new'] && !$board['new'] ? '<span class="updated">' : '', '] ', $board['new'] || $board['children_new'] ? '</span>' : '', '<a href="', $scripturl, '?board=', $board['id'], '.0;wap2"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $board['name'], '</a></p>';
		}
	}

	echo '
		<p class="titlebg">', $txt['wireless_options'], '</p>';
	if ($context['user']['is_guest'])
		echo '
		<p class="windowbg"><a href="', $scripturl, '?action=login;wap2">', $txt['wireless_options_login'], '</a></p>';
	else
	{
		if ($context['allow_pm'])
			echo '
			<p class="windowbg"><a href="', $scripturl, '?action=pm;wap2">', empty($context['user']['unread_messages']) ? $txt['wireless_pm_inbox'] : sprintf($txt['wireless_pm_inbox_new'], $context['user']['unread_messages']), '</a></p>';
		echo '
		<p class="windowbg"><a href="', $scripturl, '?action=unread;wap2">', $txt['wireless_recent_unread_posts'], '</a></p>
		<p class="windowbg"><a href="', $scripturl, '?action=unreadreplies;wap2">', $txt['wireless_recent_unread_replies'], '</a></p>
		<p class="windowbg"><a href="', $scripturl, '?action=logout;', $context['session_var'], '=', $context['session_id'], ';wap2">', $txt['wireless_options_logout'], '</a></p>';
	}
}

function template_wap2_messageindex()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<p class="catbg">', $context['name'], '</p>';

	if (!empty($context['boards']))
	{
		echo '
		<p class="titlebg">', $txt['parent_boards'], '</p>';
		foreach ($context['boards'] as $board)
			echo '
		<p class="windowbg">', $board['new'] ? '<span class="updated">[-] </span>' : ($board['children_new'] ? '[-<span class="updated">] </span>' : '[-] '), '<a href="', $scripturl, '?board=', $board['id'], '.0;wap2">', $board['name'], '</a></p>';
	}

	$count = 0;
	if (!empty($context['topics']))
	{
		echo '
		<p class="titlebg">', $txt['topics'], '</p>
		<p class="windowbg">', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';wap2">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';wap2">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';wap2">&gt;</a> <a href="' . $context['links']['last'] . ';wap2">&gt;&gt;</a> ' : '', '</p>';
		foreach ($context['topics'] as $topic)
		{
			$count++;
			echo '
		<p class="windowbg">', $count < 10 ? '[' . $count . '] ' : '', '<a href="', $scripturl, '?topic=', $topic['id'], '.0;wap2"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $topic['first_post']['subject'], '</a>', $topic['new'] && $context['user']['is_logged'] ? ' [<a href="' . $scripturl . '?topic=' . $topic['id'] . '.msg' . $topic['new_from'] . ';wap2#new" class="new">' . $txt['new'] . '</a>]' : '', '</p>';
		}
	}
	echo '
		<p class="titlebg">', $txt['wireless_navigation'], '</p>
		<p class="windowbg">[0] <a href="', $context['links']['up'], ';wap2" accesskey="0">', $txt['wireless_navigation_up'], '</a></p>', !empty($context['links']['next']) ? '
		<p class="windowbg">[#] <a href="' . $context['links']['next'] . ';wap2" accesskey="#">' . $txt['wireless_navigation_next'] . '</a></p>' : '', !empty($context['links']['prev']) ? '
		<p class="windowbg">[*] <a href="' . $context['links']['prev'] . ';wap2" accesskey="*">' . $txt['wireless_navigation_prev'] . '</a></p>' : '', $context['can_post_new'] ? '
		<p class="windowbg"><a href="' . $scripturl . '?action=post;board=' . $context['current_board'] . '.0;wap2">' . $txt['start_new_topic'] . '</a></p>' : '';
}

function template_wap2_display()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<p class="titlebg">' . $context['linktree'][1]['name'] . ' > ' . $context['linktree'][count($context['linktree']) - 2]['name'] . '</p>
		<p class="catbg">', $context['subject'], '</p>
		<p class="windowbg">', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';wap2">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';wap2">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';wap2">&gt;</a> <a href="' . $context['links']['last'] . ';wap2">&gt;&gt;</a> ' : '', '</p>';
	$alternate = true;
	while ($message = $context['get_message']())
	{
		// This is a special modification to the post so it will work on phones:
		$wireless_message = strip_tags(str_replace(array('<blockquote>', '</blockquote>', '<code>', '</code>', '<li>'), array('&gt;&gt;&gt;&gt;', '&lt;&lt;&lt;&lt;', '&gt;&gt;&gt;&gt;', '&lt;&lt;&lt;&lt;', '<br />* '), $message['body']), '<br>');

		echo $message['first_new'] ? '
		<a name="new"></a>' : '', '
		<p class="windowbg', $alternate ? '' : '2', '">
			', $context['wireless_moderate'] && $message['member']['id'] ? '<a href="' . $scripturl . '?action=profile;u=' . $message['member']['id'] . ';wap2">' . $message['member']['name'] . '</a>' : '<b>' . $message['member']['name'] . '</b>', ':
			', ((empty($context['wireless_more']) && $message['can_modify']) || !empty($context['wireless_moderate']) ? '[<a href="' . $scripturl . '?action=post;msg=' . $message['id'] . ';topic=' . $context['current_topic'] . '.' . $context['start'] . ';' . $context['session_var'] . '=' . $context['session_id'] . ';wap2">' . $txt['wireless_display_edit'] . '</a>]' : ''), '<br />
			', $wireless_message, '
		</p>';
		$alternate = !$alternate;
	}
	echo '
		<p class="titlebg">', $txt['wireless_navigation'], '</p>
		<p class="windowbg">[0] <a href="', $context['links']['up'], ';wap2" accesskey="0">', $txt['wireless_navigation_index'], '</a></p>', !empty($context['links']['next']) ? '
		<p class="windowbg">[#] <a href="' . $context['links']['next'] . ';wap2' . $context['wireless_moderate'] . '" accesskey="#">' . $txt['wireless_navigation_next'] . '</a></p>' : '', !empty($context['links']['prev']) ? '
		<p class="windowbg">[*] <a href="' . $context['links']['prev'] . ';wap2' . $context['wireless_moderate'] . '" accesskey="*">' . $txt['wireless_navigation_prev'] . '</a></p>' : '', $context['can_reply'] ? '
		<p class="windowbg"><a href="' . $scripturl . '?action=post;topic=' . $context['current_topic'] . '.' . $context['start'] . ';wap2">' . $txt['reply'] . '</a></p>' : '';

	if (!empty($context['wireless_more']) && empty($context['wireless_moderate']))
		echo '
		<p class="windowbg"><a href="', $scripturl, '?topic=', $context['current_topic'], '.', $context['start'], ';moderate;wap2">', $txt['wireless_display_moderate'], '</a></p>';
	elseif (!empty($context['wireless_moderate']))
	{
		if ($context['can_sticky'])
			echo '
				<p class="windowbg"><a href="', $scripturl, '?action=sticky;topic=', $context['current_topic'], '.', $context['start'], ';', $context['session_var'], '=', $context['session_id'], ';wap2">', $txt['wireless_display_' . ($context['is_sticky'] ? 'unsticky' : 'sticky')], '</a></p>';
		if ($context['can_lock'])
			echo '
				<p class="windowbg"><a href="', $scripturl, '?action=lock;topic=', $context['current_topic'], '.', $context['start'], ';', $context['session_var'], '=', $context['session_id'], ';wap2">', $txt['wireless_display_' . ($context['is_locked'] ? 'unlock' : 'lock')], '</a></p>';
	}
}

function template_wap2_login()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=login2;wap2" method="post">
			<p class="catbg">', $txt['login'], '</p>';
	if (isset($context['login_errors']))
		foreach ($context['login_errors'] as $error)
			echo '
			<p class="windowbg" style="color: #ff0000;"><b>', $error, '</b></p>';
	echo '
			<p class="windowbg">', $txt['username'], ':</p>
			<p class="windowbg"><input type="text" name="user" size="10" /></p>
			<p class="windowbg">', $txt['password'], ':</p>
			<p class="windowbg"><input type="password" name="passwrd" size="10" /></p>
			<p class="windowbg"><input type="submit" value="', $txt['login'], '" /><input type="hidden" name="cookieneverexp" value="1" /></p>
			<p class="catbg">', $txt['wireless_navigation'], '</p>
			<p class="windowbg">[0] <a href="', $scripturl, '?wap2" accesskey="0">', $txt['wireless_navigation_up'], '</a></p>
		</form>';
}

function template_wap2_post()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
		<form action="', $scripturl, '?action=', $context['destination'], ';board=', $context['current_board'], '.0;wap2" method="post">
			<p class="titlebg">', $context['page_title'], '</p>';
			
	if ($context['locked'])
		echo '
			<p class="windowbg">
				' . $txt['topic_locked_no_reply'] . '
			</p>';

	if (isset($context['name']) && isset($context['email']))
	{
		echo '
			<p class="windowbg"' . (isset($context['post_error']['long_name']) || isset($context['post_error']['no_name']) ? ' style="color: #ff0000"' : '') . '>
				' . $txt['username'] . ': <input type="text" name="guestname" value="' . $context['name'] . '" />
			</p>';
	
		if (empty($modSettings['guest_post_no_email']))
			echo '
			<p class="windowbg"' . (isset($context['post_error']['no_email']) || isset($context['post_error']['bad_email']) ? ' style="color: #ff0000"' : '') . '>
				' . $txt['email'] . ': <input type="text" name="email" value="' . $context['email'] . '" />
			</p>';
	}

	if ($context['require_verification'])
		echo '
			<p class="windowbg"', !empty($context['post_error']['need_qr_verification']) ? ' style="color: #ff0000"' : '', '>
				' . $txt['verification'] . ': ', template_control_verification($context['visual_verification_id'], 'all'), '
			</p>';

	echo '
			<p class="windowbg"', isset($context['post_error']['no_subject']) ? ' style="color: #ff0000"' : '', '>
				', $txt['subject'], ': <input type="text" name="subject"', $context['subject'] == '' ? '' : ' value="' . $context['subject'] . '"', ' maxlength="80" />
			</p>
			<p class="windowbg"', isset($context['post_error']['no_message']) || isset($context['post_error']['long_message']) ? ' style="color: #ff0000;"' : '', '>
				', $txt['message'], ': <br />
				<textarea name="message" rows="3" cols="20">', $context['message'], '</textarea>
			</p>
			<p class="windowbg">
				<input type="submit" name="post" value="', $context['submit_label'], '" />
				<input type="hidden" name="icon" value="wireless" />
				<input type="hidden" name="goback" value="', $context['back_to_topic'] || !empty($options['return_to_post']) ? '1' : '0', '" />
				<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />', isset($context['current_topic']) ? '
				<input type="hidden" name="topic" value="' . $context['current_topic'] . '" />' : '', '
				<input type="hidden" name="notify" value="', $context['notify'] || !empty($options['auto_notify']) ? '1' : '0', '" />
			</p>
			<p class="windowbg">[0] ', !empty($context['current_topic']) ? '<a href="' . $scripturl . '?topic=' . $context['current_topic'] . '.new;wap2">' . $txt['wireless_navigation_topic'] . '</a>' : '<a href="' . $scripturl . '?board=' . $context['current_board'] . '.0;wap2" accesskey="0">' . $txt['wireless_navigation_index'] . '</a>', '</p>
		</form>';
}

function template_wap2_pm()
{
	global $context, $settings, $options, $scripturl, $txt, $user_info;

	if ($_REQUEST['action'] == 'findmember')
	{
		echo '
				<form action="', $scripturl, '?action=findmember;', $context['session_var'], '=', $context['session_id'], ';wap2" method="post">
					<p class="catbg">', $txt['wireless_pm_search_member'], '</p>
					<p class="titlebg">', $txt['find_members'], '</p>
					<p class="windowbg">
						<b>', $txt['wireless_pm_search_name'], ':</b>
						<input type="text" name="search" value="', isset($context['last_search']) ? $context['last_search'] : '', '" />', empty($_REQUEST['u']) ? '' : '
						<input type="hidden" name="u" value="' . $_REQUEST['u'] . '" />', '
					</p>
					<p class="windowbg"><input type="submit" value="', $txt['search'], '" /></p>
				</form>';
		if (!empty($context['last_search']))
		{
			echo '
				<p class="titlebg">', $txt['find_results'], '</p>';
			if (empty($context['results']))
				echo '
				<p class="windowbg">[-] ', $txt['find_no_results'], '</p>';
			else
			{
				echo '
				<p class="windowbg">', empty($context['links']['prev']) ? '' : '<a href="' . $context['links']['first'] . ';wap2">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';wap2">&lt;</a> ', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', empty($context['links']['next']) ? '' : ' <a href="' . $context['links']['next'] . ';wap2">&gt;</a> <a href="' . $context['links']['last'] . ';wap2">&gt;&gt;</a> ', '</p>';
				$count = 0;
				foreach ($context['results'] as $result)
				{
					$count++;
					echo '
				<p class="windowbg">
					[', $count < 10 ? $count : '-', '] <a href="', $scripturl, '?action=pm;sa=send;u=', empty($_REQUEST['u']) ? $result['id'] : $_REQUEST['u'] . ',' . $result['id'], ';wap2"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $result['name'], '</a>
				</p>';
				}
			}
		}
		echo '
				<p class="titlebg">', $txt['wireless_navigation'], '</p>
				<p class="windowbg">[0] <a href="', $context['links']['up'], ';wap2" accesskey="0">', $txt['wireless_navigation_up'], '</a></p>';
		if (!empty($context['results']))
			echo empty($context['links']['next']) ? '' : '
			<p class="windowbg">[#] <a href="' . $context['links']['next'] . ';wap2" accesskey="#">' . $txt['wireless_navigation_next'] . '</a></p>', empty($context['links']['prev']) ? '' : '
			<p class="windowbg">[*] <a href="' . $context['links']['prev'] . ';wap2" accesskey="*">' . $txt['wireless_navigation_prev'] . '</a></p>';
	}
	elseif (!empty($_GET['sa']))
	{
		if ($_GET['sa'] == 'addbuddy')
		{
			echo '
					<p class="catbg">', $txt['wireless_pm_add_buddy'], '</p>
					<p class="titlebg">', $txt['wireless_pm_select_buddy'], '</p>';
			$count = 0;
			foreach ($context['buddies'] as $buddy)
			{
				$count++;
				if ($buddy['selected'])
					echo '
					<p class="windowbg">[-] <span style="color: gray">', $buddy['name'], '</span></p>';
				else
					echo '
					<p class="windowbg">
						[', $count < 10 ? $count : '-', '] <a href="', $buddy['add_href'], ';wap2"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $buddy['name'], '</a>
					</p>';
			}
			echo '
					<p class="titlebg">', $txt['wireless_navigation'], '</p>
					<p class="windowbg">[0] <a href="', $context['pm_href'], ';wap2" accesskey="0">', $txt['wireless_navigation_up'], '</a></p>';
		}
		if ($_GET['sa'] == 'send' || $_GET['sa'] == 'send2')
		{
			echo '
				<form action="', $scripturl, '?action=pm;sa=send2;wap2" method="post">
					<p class="catbg">', $txt['new_message'], '</p>', empty($context['post_error']['messages']) ? '' : '
					<p class="windowbg error">' . implode('<br />', $context['post_error']['messages']) . '</p>', '
					<p class="windowbg">
						<b>', $txt['pm_to'], ':</b> ';
			if (empty($context['recipients']['to']))
				echo $txt['wireless_pm_no_recipients'];
			else
			{
				$to_names = array();
				foreach ($context['recipients']['to'] as $to)
					$to_names[] = $to['name'];
				echo implode(', ', $to_names);
			}
			echo '
				', empty($_REQUEST['u']) ? '' : '<input type="hidden" name="u" value="' . implode(',', $_REQUEST['u']) . '" />', '<br />
						<a href="', $scripturl, '?action=findmember', empty($_REQUEST['u']) ? '' : ';u=' . implode(',', $_REQUEST['u']), ';', $context['session_var'], '=', $context['session_id'], ';wap2">', $txt['wireless_pm_search_member'], '</a>', empty($user_info['buddies']) ? '' : '<br />
						<a href="' . $scripturl . '?action=pm;sa=addbuddy' . (empty($_REQUEST['u']) ? '' : ';u=' . implode(',', $_REQUEST['u'])) . ';wap2">' . $txt['wireless_pm_add_buddy'] . '</a>', '
					</p>
					<p class="windowbg">
						<b>', $txt['subject'], ':</b> <input type="text" name="subject" value="', $context['subject'], '" />
					</p>
					<p class="windowbg">
						<b>', $txt['message'], ':</b><br />
						<textarea name="message" rows="3" cols="20">', $context['message'], '</textarea>
					</p>
					<p class="windowbg">
						<input type="submit" value="', $txt['send_message'], '" />
						<input type="hidden" name="outbox" value="', $context['copy_to_outbox'] ? '1' : '0', '" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />
						<input type="hidden" name="replied_to" value="', !empty($context['quoted_message']['id']) ? $context['quoted_message']['id'] : 0, '" />
						<input type="hidden" name="folder" value="', $context['folder'], '" />
					</p>';
			if ($context['reply'])
				echo '
					<p class="titlebg">', $txt['wireless_pm_reply_to'], '</p>
					<p class="windowbg"><b>', $context['quoted_message']['subject'], '</b></p>
					<p class="windowbg">', $context['quoted_message']['body'], '</p>';
			echo '
					<p class="titlebg">', $txt['wireless_navigation'], '</p>
					<p class="windowbg">[0] <a href="', $scripturl, '?action=pm;wap2" accesskey="0">', $txt['wireless_navigation_up'], '</a></p>
				</form>';
		}
	}
	elseif (empty($_GET['pmsg']))
	{
		echo '
			<p class="catbg">', $context['current_label_id'] == -1 ? $txt['wireless_pm_inbox'] : $txt['pm_current_label'] . ': ' . $context['current_label'], '</p>
			<p class="windowbg">', empty($context['links']['prev']) ? '' : '<a href="' . $context['links']['first'] . ';wap2">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';wap2">&lt;</a> ', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', empty($context['links']['next']) ? '' : ' <a href="' . $context['links']['next'] . ';wap2">&gt;</a> <a href="' . $context['links']['last'] . ';wap2">&gt;&gt;</a> ', '</p>';
		$count = 0;
		while ($message = $context['get_pmessage']())
		{
			$count++;
			echo '
			<p class="windowbg">
				[', $count < 10 ? $count : '-', '] <a href="', $scripturl, '?action=pm;start=', $context['start'], ';pmsg=', $message['id'], ';l=', $context['current_label_id'], ';wap2"', $count < 10 ? ' accesskey="' . $count . '"' : '', '>', $message['subject'], ' <i>', $txt['wireless_pm_by'], '</i> ', $message['member']['name'], '</a>
			</p>';
		}

		if ($context['currently_using_labels'])
		{
			$labels = array();
			ksort($context['labels']);
			foreach ($context['labels'] as $label)
				$labels[] = '<a href="' . $scripturl . '?action=pm;l=' . $label['id'] . ';wap2">' . $label['name'] . '</a>' . (!empty($label['unread_messages']) ? ' (' . $label['unread_messages'] . ')' : '');
			echo '
			<p class="catbg">
				', $txt['pm_labels'], '
			</p>
			<p class="windowbg">
				', implode(', ', $labels), '
			</p>';
		}

		echo '
			<p class="titlebg">', $txt['wireless_navigation'], '</p>
			<p class="windowbg">[0] <a href="', $scripturl, '?wap2" accesskey="0">', $txt['wireless_navigation_up'], '</a></p>', empty($context['links']['next']) ? '' : '
			<p class="windowbg">[#] <a href="' . $context['links']['next'] . ';wap2" accesskey="#">' . $txt['wireless_navigation_next'] . '</a></p>', empty($context['links']['prev']) ? '' : '
			<p class="windowbg">[*] <a href="' . $context['links']['prev'] . ';wap2" accesskey="*">' . $txt['wireless_navigation_prev'] . '</a></p>', $context['can_send_pm'] ? '
			<p class="windowbg"><a href="' . $scripturl . '?action=pm;sa=send;wap2">' . $txt['new_message'] . '</a></p>' : '';
	}
	else
	{
		$message = $context['get_pmessage']();
		$wireless_message = strip_tags(str_replace(array('<blockquote>', '</blockquote>', '<code>', '</code>', '<li>'), array('&gt;&gt;&gt;&gt;', '&lt;&lt;&lt;&lt;', '&gt;&gt;&gt;&gt;', '&lt;&lt;&lt;&lt;', '<br />* '), $message['body']), '<br>');
		echo '
			<p class="catbg">', $message['subject'], '</p>
			<p class="titlebg">
				<b>', $txt['wireless_pm_by'], ':</b> ', $message['member']['name'], '<br />
				<b>', $txt['on'], ':</b> ', $message['time'], '
			</p>
			<p class="windowbg">
				', $message['body'], '
			</p>
			<p class="titlebg">', $txt['wireless_navigation'], '</p>
			<p class="windowbg">[0] <a href="', $scripturl, '?action=pm;start=', $context['start'], ';l=', $context['current_label_id'], ';wap2" accesskey="0">', $txt['wireless_navigation_up'], '</a></p>';
			if ($context['can_send_pm'])
				echo '
			<p class="windowbg"><a href="', $scripturl, '?action=pm;sa=send;pmsg=', $message['id'], ';u=', $message['member']['id'], ';reply;wap2">', $txt['wireless_pm_reply'], '</a></p>';
	}
}

function template_wap2_recent()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<p class="catbg">', $_REQUEST['action'] == 'unread' ? $txt['wireless_recent_unread_posts'] : $txt['wireless_recent_unread_replies'], '</p>';

	$count = 0;
	if (empty($context['topics']))
		echo '
			<p class="windowbg">', $txt['old_posts'], '</p>';
	else
	{
		echo '
		<p class="windowbg">', !empty($context['links']['prev']) ? '<a href="' . $context['links']['first'] . ';wap2">&lt;&lt;</a> <a href="' . $context['links']['prev'] . ';wap2">&lt;</a> ' : '', '(', $context['page_info']['current_page'], '/', $context['page_info']['num_pages'], ')', !empty($context['links']['next']) ? ' <a href="' . $context['links']['next'] . ';wap2">&gt;</a> <a href="' . $context['links']['last'] . ';wap2">&gt;&gt;</a> ' : '', '</p>';
		foreach ($context['topics'] as $topic)
		{
			$count++;
			echo '
		<p class="windowbg">', ($count < 10 ? '[' . $count . '] ' : ''), '<a href="', $scripturl, '?topic=', $topic['id'], '.msg', $topic['new_from'], ';topicseen;wap2#new"', ($count < 10 ? ' accesskey="' . $count . '"' : ''), '>', $topic['first_post']['subject'], '</a></p>';
		}
	}
	echo '
		<p class="titlebg">', $txt['wireless_navigation'], '</p>
		<p class="windowbg">[0] <a href="', $context['links']['up'], '?wap2" accesskey="0">', $txt['wireless_navigation_up'], '</a></p>', !empty($context['links']['next']) ? '
		<p class="windowbg">[#] <a href="' . $context['links']['next'] . ';wap2" accesskey="#">' . $txt['wireless_navigation_next'] . '</a></p>' : '', !empty($context['links']['prev']) ? '
		<p class="windowbg">[*] <a href="' . $context['links']['prev'] . ';wap2" accesskey="*">' . $txt['wireless_navigation_prev'] . '</a></p>' : '';
}

function template_wap2_error()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<p class="catbg">', $context['error_title'], '</p>
		<p class="windowbg">', $context['error_message'], '</p>
		<p class="windowbg">[0] <a href="', $scripturl, '?wap2" accesskey="0">', $txt['wireless_error_home'], '</a></p>';
}

function template_wap2_profile()
{
	global $context, $settings, $options, $scripturl, $board, $txt;

	echo '
		<p class="catbg">', $txt['summary'], ' - ', $context['member']['name'], '</p>
		<p class="windowbg"><b>', $txt['name'], ':</b> ', $context['member']['name'], '</p>
		<p class="windowbg"><b>', $txt['position'], ': </b>', (!empty($context['member']['group']) ? $context['member']['group'] : $context['member']['post_group']), '</p>
		<p class="windowbg"><b>', $txt['lastLoggedIn'], ':</b> ', $context['member']['last_login'], '</p>';

	if (!empty($context['member']['bans']))
	{
		echo '
		<p class="titlebg"><b>', $txt['user_banned_by_following'], ':</b></p>';

		foreach ($context['member']['bans'] as $ban)
			echo '
		<p class="windowbg">', $ban['explanation'], '</p>';

	}

	echo '

		<p class="titlebg">', $txt['additional_info'], '</p>';

	if (!$context['user']['is_owner'] && $context['can_send_pm'])
		echo '
		<p class="windowbg"><a href="', $scripturl, '?action=pm;sa=send;u=', $context['id_member'], ';wap2">', $txt['wireless_profile_pm'], '.</a></p>';

	if (!$context['user']['is_owner'] && !empty($context['can_edit_ban']))
		echo '
		<p class="windowbg"><a href="', $scripturl, '?action=admin;area=ban;sa=add;u=', $context['id_member'], ';wap2">', $txt['profileBanUser'], '.</a></p>';

	echo '
		<p class="windowbg"><a href="', $scripturl, '?wap2">', $txt['wireless_error_home'], '.</a></p>';

}

function template_wap2_ban_edit()
{
	global $context, $settings, $options, $scripturl, $board, $txt, $modSettings;

	echo '
	<form action="', $scripturl, '?action=admin;area=ban;sa=add;wap2" method="post">
		<p class="catbg">', $context['ban']['is_new'] ? $txt['ban_add_new'] : $txt['ban_edit'] . ' \'' . $context['ban']['name'] . '\'', '</p>
		<p class="windowbg">
			<b>', $txt['ban_name'], ': </b>
			<input type="text" name="ban_name" value="', $context['ban']['name'], '" size="20" />
		</p>
		<p class="windowbg">
			<b>', $txt['ban_expiration'], ': </b><br />
			<input type="radio" name="expiration" value="never" ', $context['ban']['expiration']['status'] == 'never' ? ' checked="checked"' : '', ' class="check" /> ', $txt['never'], '<br />
			<input type="radio" name="expiration" value="one_day" ', $context['ban']['expiration']['status'] == 'still_active_but_we_re_counting_the_days' ? ' checked="checked"' : '', ' class="check" /> ', $txt['ban_will_expire_within'], ' <input type="text" name="expire_date" size="3" value="', $context['ban']['expiration']['days'], '" /> ', $txt['ban_days'], '<br />
			<input type="radio" name="expiration" value="expired" ', $context['ban']['expiration']['status'] == 'expired' ? ' checked="checked"' : '', ' class="check" /> ', $txt['ban_expired'], '<br />
		</p>
		<p class="windowbg">
			<b>', $txt['ban_reason'], ': </b>
			<input type="text" name="reason" value="', $context['ban']['reason'], '" size="20" />
		</p>
		<p class="windowbg">
			<b>', $txt['ban_notes'], ': </b><br />
			<textarea name="notes" cols="20" rows="3">', $context['ban']['notes'], '</textarea>
		</p>
		<p class="windowbg">
			<b>', $txt['ban_restriction'], ': </b><br />
			<input type="checkbox" name="full_ban" value="1"', $context['ban']['cannot']['access'] ? ' checked="checked"' : '', ' class="check" /> ', $txt['ban_full_ban'], '<br />
			<input type="checkbox" name="cannot_post" value="1"', $context['ban']['cannot']['post'] ? ' checked="checked"' : '', ' class="check" /> ', $txt['ban_cannot_post'], '<br />
			<input type="checkbox" name="cannot_register" value="1"', $context['ban']['cannot']['register'] ? ' checked="checked"' : '', ' class="check" /> ', $txt['ban_cannot_register'], '<br />
			<input type="checkbox" name="cannot_login" value="1"', $context['ban']['cannot']['login'] ? ' checked="checked"' : '', ' class="check" /> ', $txt['ban_cannot_login'], '
		</p>';

	if (!empty($context['ban_suggestions']))
	{
		echo '
		<p class="titlebg">', $txt['ban_triggers'], '</p>
		<p class="windowbg">
			<input type="checkbox" name="ban_suggestion[]" value="main_ip" class="check" /> <b>', $txt['wireless_ban_ip'], ':</b><br />
			&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="main_ip" value="', $context['ban_suggestions']['main_ip'], '" size="20" />
		</p>';

		if (empty($modSettings['disableHostnameLookup']))
			echo '
		<p class="windowbg">
			<input type="checkbox" name="ban_suggestion[]" value="hostname" class="check" /> <b>', $txt['wireless_ban_hostname'], ':</b><br />
			&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="hostname" value="', $context['ban_suggestions']['hostname'], '" size="20" />
		<p>';

		echo '
		<p class="windowbg">
			<input type="checkbox" name="ban_suggestion[]" value="email" class="check" /> <b>', $txt['wireless_ban_email'], ':</b><br />
			&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="email" value="', $context['ban_suggestions']['email'], '" size="20" />
		</p>
		<p class="windowbg">
			<input type="checkbox" name="ban_suggestion[]" value="user" class="check" /> <b>', $txt['ban_on_username'], ':</b><br />';

		if (empty($context['ban_suggestions']['member']['id']))
			echo '
			&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="user" value="" size="20" />';
		else
			echo '
			&nbsp;&nbsp;&nbsp;&nbsp;', $context['ban_suggestions']['member']['name'], '
			<input type="hidden" name="bannedUser" value="', $context['ban_suggestions']['member']['id'], '" />';

		echo '
		</p>';
	}

	echo '

		<p class="windowbg"><input type="submit" name="', $context['ban']['is_new'] ? 'add_ban' : 'modify_ban', '" value="', $context['ban']['is_new'] ? $txt['ban_add'] : $txt['ban_modify'], '" /></p>
		<p class="titlebg">', $txt['wireless_additional_info'], '</p>
		<p class="windowbg"><a href="', $scripturl, '?wap2">', $txt['wireless_error_home'], '.</a></p>';

	echo '
		<input type="hidden" name="old_expire" value="', $context['ban']['expiration']['days'], '" />
		<input type="hidden" name="bg" value="', $context['ban']['id'], '" />
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';
}

function template_wap2_below()
{
	global $context, $settings, $options;

	echo '
	</body>
</html>';
}

?>