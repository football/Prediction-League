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

if ($league <> 0)
{
	$data_rank_matchday = false;
	$index = 0;
	$sql = "SELECT
			r.status,
			r.rank,
			r.user_id,
			u.username,
			r.points,
			IF(r.win=0, '', r.win) AS win
		FROM  " . FOOTB_RANKS . ' AS r
		LEFT Join ' . USERS_TABLE . " AS u ON (r.user_id = u.user_id)
		WHERE r.season = $season 
			AND r.league = $league 
			AND r.matchday = $matchday 
			AND r.status IN (2,3)
		ORDER BY r.rank ASC, LOWER(u.username) ASC";
		
	$result = $db->sql_query($sql);
	while($row = $db->sql_fetchrow($result))
	{
		$index++;
		if (($index <= $config['football_display_ranks']) OR ($row['user_id'] == $user->data['user_id']))
		{
			$data_rank_matchday = true;
			$row_class = (!($index % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
			if ($row['user_id'] == $user->data['user_id'])
			{
				$row_class = 'bg3  row_user';
			}
			$colorstyle = color_style($row['status']);
				
			$template->assign_block_vars('rank', array(
				'RANK' 			=> $row['rank'],
				'ROW_CLASS' 	=> $row_class,
				'USERID' 		=> $row['user_id'],
				'USERNAME' 		=> $row['username'],
				'U_BET_USER'	=> $this->helper->route('football_football_popup', array('popside' => 'bet_popup', 's' => $season, 'l' => $league,
																					'm' => $matchday, 'u' => $row['user_id'])),
				'POINTS' 		=> $row['points'],
				'COLOR_STYLE' 	=> $colorstyle,
				'WIN' 			=> $row['win'],
				)
			);
		}
	}
	$db->sql_freeresult($result);
	$league_info = league_info($season, $league);

	$template->assign_vars(array(
		'S_DISPLAY_RANK_MATCHDAY' 	=> true,
		'S_DATA_RANK_MATCHDAY' 		=> $data_rank_matchday,
		'S_WIN' 					=> ($league_info['win_matchday'] == '0') ? false : true,
		'WIN_NAME' 					=> $config['football_win_name'],
	));
}
