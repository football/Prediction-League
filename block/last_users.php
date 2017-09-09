<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
   exit;
}

$display_last_users = false;
// Last users
$sql = 'SELECT s.session_user_id
		, u.username
		, u.user_colour
		, u.user_lastvisit
		, MAX(s.session_time) AS session_time
		, IF(MAX(s.session_time) > u.user_lastvisit, MAX(s.session_time), u.user_lastvisit) AS lastvisit
		, IF(MAX(s.session_time) > u.user_lastvisit, MAX(CONCAT(s.session_time,s.session_browser)), "") AS session_browser
	FROM ' . USERS_TABLE . ' AS u
	LEFT JOIN ' . SESSIONS_TABLE . ' AS s ON (u.user_id = s.session_user_id)
	WHERE u.user_lastvisit > 0
	AND u.user_type IN (0,3)
	GROUP BY u.user_id 
	ORDER BY lastvisit DESC';

$result = $db->sql_query_limit($sql, $config['football_display_last_users']);
$first = true;
while ($row = $db->sql_fetchrow($result))
{
	if (!$row['lastvisit'] && $first == true)
	{
		 $display_last_users = false;
	}
	else 
	{
		$display_last_users = true;
		if($row['lastvisit'] > 0)
		{
			$browser = '';
			if (preg_match('/iPad|iPhone|iOS|Opera Mobi|BlackBerry|Android|IEMobile|Symbian/', $row['session_browser'], $match_browser))
			{
				$browser = ' (' . $match_browser[0] . ')';
			}
			$template->assign_block_vars('last_users', array(
				'USER_NAME'			=> get_username_string('full', '', $row['username'], $row['user_colour']) . $browser,
				'LAST_VISIT_DATE'	=> $user->format_date($row['lastvisit']),
			));
		}
	}
	$first = false;
}
$db->sql_freeresult($result);

// Assign specific vars
$template->assign_vars(array(
	'LAST_USERS'			=> sprintf($user->lang['LAST_VISITORS'], $config['football_display_last_users']),
	'S_DISPLAY_LAST_USERS'	=> $display_last_users,
	'S_LAST_USERS'			=> true,
));

?>