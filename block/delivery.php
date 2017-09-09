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
$data_delivery = false;
$user_id = $user->data['user_id'];
$lang_dates = $user->lang['datetime'];
$index = 0;
$local_board_time = time() + ($config['football_time_shift'] * 3600); 
$sql = "SELECT 
			m.season, 
			m.league, 
			m.matchday,
			l.league_name_short, 
			CASE m.matchday_name 
				WHEN '' 
					THEN CONCAT(m.matchday, '." . sprintf($user->lang['MATCHDAY']) . "') 
					ELSE m.matchday_name 
			END AS matchday_name, 
			IF(l.bet_in_time = 0, IF(ma.status = 0, m.delivery_date
												  , IF(ma.status = -1, m.delivery_date_2
																	 , m.delivery_date_3
													  )
									)
								, ma.match_datetime) AS delivery,
			SUM(IF(((b.goals_home = '') OR (b.goals_guest = '')), 0, 1)) AS bets_count,
			COUNT(*) AS matches_count,
			SUM(IF(eb.extra_no > 0, IF(eb.bet = '', 0, 1), 0)) AS extra_bets_count,
			SUM(IF(e.extra_no > 0, 1, 0)) AS extra_count
		FROM " . FOOTB_MATCHDAYS . " AS m
		JOIN " . FOOTB_LEAGUES . " AS l ON(l.season = m.season AND l.league = m.league)
		JOIN " . FOOTB_MATCHES . " AS ma ON (ma.season = m.season AND ma.league = m.league AND ma.matchday = m.matchday AND ma.status = 0)
		JOIN " . FOOTB_BETS . " AS b ON (b.season = m.season AND b.league = m.league AND b.match_no = ma.match_no AND b.user_id = $user_id)
		LEFT JOIN " . FOOTB_EXTRA . " AS e ON (e.season = m.season AND e.league = m.league AND e.matchday = m.matchday  AND e.extra_status = 0)
		LEFT JOIN " . FOOTB_EXTRA_BETS . " AS eb ON (eb.season = m.season AND eb.league = m.league AND eb.extra_no = e.extra_no AND eb.user_id = $user_id)
		WHERE m.status <= 0 
		GROUP BY delivery, m.league
		ORDER BY delivery, m.league";
	
$result = $db->sql_query($sql);
while($row = $db->sql_fetchrow($result) AND $index < 11)
{
	$index++;
	$data_delivery = true;
	$row_class = (!($index % 2)) ? 'bg1 row_light' : 'bg2 row_dark';
		
	$template->assign_block_vars('delivery', array(
		'ROW_CLASS' 	=> $row_class,
		'U_BET_LINK'	=> $this->helper->route('football_main_controller', array('side' => 'bet', 's' =>  $row['season'], 'l' => $row['league'], 'm' => $row['matchday'])),
		'LEAGUE_SHORT' 	=> $row['league_name_short'],
		'MATCHDAY_NAME' => $row['matchday_name'],
		'COLOR'			=> ($row['bets_count'] == $row['matches_count'] && $row['extra_bets_count'] == $row['extra_count']) ? 'green' : 'red',
		'TITLE'			=> ($row['bets_count'] == $row['matches_count']) ? sprintf($user->lang['DELIVERY_READY']) : sprintf($user->lang['DELIVERY_NOT_READY']),
		'DELIVERY' 		=> $lang_dates[date("D", strtotime($row['delivery']))] . date(" d.m.y G:i", strtotime($row['delivery'])),
		)
	);
}
$db->sql_freeresult($result);

$template->assign_vars(array(
	'S_DISPLAY_DELIVERY' 	=> $data_delivery,
	'S_DATA_DELIVERY' 		=> $data_delivery,
	)
);

?>