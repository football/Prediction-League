<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

$this->user->add_lang_ext('football/football', 'info_acp_bank');

// Check Prediction League authorisation 
if ( !$this->auth->acl_get('u_use_football') )
{
	trigger_error('NO_AUTH_VIEW');
}

$action='';
$phpbb_root_path = './../../';

if (!$season OR !$league)
{
	redirect($this->helper->route('football_football_controller', array('side' => 'bank', 's' => $season, 'l' => $league)));
}
else
{
	$season_info = season_info($season);
	if (!sizeof($season_info))
	{
		$error_message = sprintf($user->lang['NO_SEASON']);
		trigger_error($error_message);
	}
	else
	{
		$league_info = league_info($season, $league);
		if (!sizeof($league_info))
		{
			$error_message = sprintf($user->lang['NO_LEAGUE']);
			trigger_error($error_message);
		}
		else
		{
			$bet_points = $league_info['bet_points'];
			$league_name =$league_info['league_name'];
			$league_short =$league_info['league_name_short'];

			$user_points = '';
			global $phpbb_extension_manager;
			if ($phpbb_extension_manager->is_enabled('dmzx/ultimatepoints') && $config['points_enable'])
			{
				$user_points = 'u.user_points,';
			}
			else
			{
				$user_points = "0.00 AS user_points,";
			}

			// Grab the members points
			$sql = "SELECT 
					b.user_id,
					u.username, 
					$user_points
					$bet_points AS bet_points,
					SUM(IF(p.points_type = " . POINTS_BET . ', IF(p.cash = 0, p.points, 0.00), 0.00)) AS no_cash_bet_points,
					SUM(IF(p.points_type = ' . POINTS_DEPOSITED . ', IF(p.cash = 0, p.points, 0.00), 0.00)) AS no_cash_deposit,
					SUM(IF(p.points_type IN (' . POINTS_MATCHDAY . ',' . POINTS_SEASON . ',' . POINTS_MOST_HITS . ',' . POINTS_MOST_HITS_AWAY . '), 
							IF(p.cash = 0, p.points, 0.00),
							0.00)) AS no_cash_wins,
					SUM(IF(p.points_type = ' . POINTS_PAID . ', IF(p.cash = 0, p.points, 0.00), 0.00)) AS no_cash_paid,
					SUM(IF(p.points_type = ' . POINTS_DEPOSITED . ', p.points, 0.00)) AS deposit,
					IF(SUM(IF(p.points_type IN (' . POINTS_BET . ',' . POINTS_PAID . '), p.points, p.points * -1.0)) > 0,
						SUM(IF(p.points_type IN (' . POINTS_BET . ',' . POINTS_PAID . '), p.points, p.points * -1.0)), 0.00) AS new_deposit,
					SUM(IF(p.points_type IN (' . POINTS_MATCHDAY . ',' . POINTS_SEASON . ',' . POINTS_MOST_HITS . ',' . POINTS_MOST_HITS_AWAY . '),
							p.points, 0.00)) AS wins,
					SUM(IF(p.points_type = ' . POINTS_PAID . ', p.points, 0.00)) AS paid,
					IF(SUM(IF(p.points_type IN (' . POINTS_BET . ',' . POINTS_PAID . '), p.points * -1.0, p.points)) > 0,
						SUM(IF(p.points_type IN (' . POINTS_BET . ',' . POINTS_PAID . '), p.points * -1.0, p.points)), 0.00) AS new_pay
				FROM ' . FOOTB_BETS . ' AS b
				JOIN ' . USERS_TABLE . ' AS u ON (u.user_id = b.user_id)
				LEFT JOIN ' . FOOTB_POINTS . " AS p ON (p.season = $season AND p.league = $league AND p.user_id = b.user_id)
				WHERE b.season = $season
					AND b.league = $league
					AND b.match_no = 1
				GROUP BY b.user_id
				ORDER BY u.username ASC";
				
			if(!$result = $db->sql_query($sql))
			{
				trigger_error('NO_LEAGUE');
			}
			$user_rows = $db->sql_fetchrowset($result);
			$db->sql_freeresult($result);
			$newline = "\r\n";
			$csv_data= '';
			$csv_data .= $league_name . ' ' . sprintf($user->lang['SEASON']) . ' ' . $season. $newline;
			$csv_data .= sprintf($user->lang['NAME']) . ';' . $config['football_win_name'] . ';' . sprintf($user->lang['BET_POINTS']) . ';' . 
						sprintf($user->lang['DEPOSITED']) . ';' . sprintf($user->lang['DEPOSIT']) . ';' . sprintf($user->lang['WINS']) . ';' . 
						sprintf($user->lang['PAID']) . ';' . sprintf($user->lang['PAYOUT']) . ';' . $newline;

			$curr_season = curr_season();			
			foreach ($user_rows as $user_row)
			{
				if ($phpbb_extension_manager->is_enabled('dmzx/ultimatepoints') && $config['points_enable'] && $season == $curr_season)
				{
					$no_cash_bet_points = ($user_row['no_cash_bet_points'] == 0.00) ? '' : ' (' . str_replace('.', ',', $user_row['no_cash_bet_points']) . ')';
					$no_cash_deposit = ($user_row['no_cash_deposit'] == 0.00) ? '' : ' (' . str_replace('.', ',', $user_row['no_cash_deposit']) . ')';
					$no_cash_wins = ($user_row['no_cash_wins'] == 0.00) ? '' : ' (' . str_replace('.', ',', $user_row['no_cash_wins']) . ')';
					$no_cash_paid = ($user_row['no_cash_paid'] == 0.00) ? '' : ' (' . str_replace('.', ',', $user_row['no_cash_paid']) . ')';
				}
				else
				{
					$no_cash_bet_points = '';
					$no_cash_deposit = '';
					$no_cash_wins = '';
					$no_cash_paid = '';
				}
				$csv_data .= str_replace("\"", "\"\"", $user_row['username']) . ';' . 
							str_replace('.', ',', $user_row['user_points']) . ';' .
							str_replace('.', ',', $user_row['bet_points']) . $no_cash_bet_points . ';' .
							str_replace('.', ',', $user_row['deposit']) . $no_cash_deposit . ';' .
							str_replace('.', ',', $user_row['new_deposit']) . ';' .
							str_replace('.', ',', $user_row['wins']) . $no_cash_wins . ';' .
							str_replace('.', ',', $user_row['paid']) . $no_cash_paid . ';' .
							str_replace('.', ',', $user_row['new_pay']) . ';' . $newline;
			}

			// Output the csv file
			$filename = $league_short . '_' . $season . '_bank.csv';
			$fp = fopen('php://output', 'w');
			
			header('Content-Type: application/octet-stream');
			header("Content-disposition: attachment; filename=\"" . basename($filename) . "\"");
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Cache-Control: private', false);
			header('Pragma: public');
			header('Content-Transfer-Encoding: binary');
			
			fwrite($fp, "\xEF\xBB\xBF"); // UTF-8 BOM
			
			fwrite($fp, utf8_decode($csv_data));
			fclose($fp);
			exit_handler();
		}
	}
}
