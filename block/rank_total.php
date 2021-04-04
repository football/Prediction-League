<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB') OR !defined('IN_FOOTBALL'))
{
	exit;
}

if ($league <> 0)
{
	$sql = 'SELECT
				r.matchday AS last_matchday
			FROM '. FOOTB_RANKS . " AS r
			WHERE r.season = $season 
				AND r.league = $league 
				AND r.status IN (2,3)
			ORDER BY r.matchday DESC";
			
	$result = $db->sql_query_limit($sql, 1);
	if($row = $db->sql_fetchrow($result))
	{
		$last_matchday = $row['last_matchday'];
		$db->sql_freeresult($result);
		$rank_matchday = ($last_matchday < $matchday) ? $last_matchday : $matchday;

		$sql = 'SELECT
					r.rank_total AS rank,
					r.user_id,
					u.username,
					u.user_colour,
					r.status AS status,
					r.points_total AS points,
					r.win_total AS win
				FROM '. FOOTB_RANKS . ' AS r
				LEFT JOIN '. USERS_TABLE . " AS u ON (r.user_id = u.user_id)
				WHERE r.season = $season 
					AND r.league = $league 
					AND r.matchday = $rank_matchday 
					AND r.status IN (2,3)
				ORDER BY points DESC, LOWER(u.username) ASC";
				
		$result = $db->sql_query($sql);

		$index = 0;
		while($row = $db->sql_fetchrow($result))
		{
			$index++;
			$data_rank_total = true;
			if (($index <= $config['football_display_ranks']) OR ($row['user_id'] == $user->data['user_id']))
			{
				$row_class = (!($index % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
				if ($row['user_id'] == $user->data['user_id'])
				{
					$row_class = 'bg3  row_user';
				}
				$colorstyle = color_style($row['status']);

				$template->assign_block_vars("ranktotal", array(
					'RANK' 			=> $row['rank'],
					'ROW_CLASS' 	=> $row_class,
					'USERID' 		=> $row['user_id'],
					'USERNAME' 		=> $row['username'],
					'U_PROFILE'		=> get_username_string('profile', $row['user_id'], $row['username'], $row['user_colour']),
					'URL' 			=> $phpbb_root_path . "profile.php?mode=viewprofile&u=" . $row['user_id'],
					'POINTS' 		=> $row['points'],
					'COLOR_STYLE'	=> $colorstyle,
					'WIN' 			=> $row['win'] ,
					)
				);
			}
		}
		$db->sql_freeresult($result);
	}
	else
	{
		$data_rank_total = false;
	}
	$league_info = league_info($season, $league);

	$template->assign_vars(array(
		'S_DISPLAY_RANK_TOTAL' 	=> true,
		'S_DATA_RANK_TOTAL' 	=> $data_rank_total,
		'S_WIN' 				=> ($league_info['win_matchday'] == '0' and $league_info['win_season'] == '0') ? false : true,
		'WIN_NAME' 				=> $config['football_win_name'],
		)
	);
}
?>