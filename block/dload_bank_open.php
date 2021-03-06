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

$this->user->add_lang_ext('football/football', 'info_acp_bank');

// Check Prediction League authorisation 
if ( !$this->auth->acl_get('u_use_football') )
{
	trigger_error('NO_AUTH_VIEW');
}

$action='';

if (!$season)
{
	redirect($this->helper->route('football_football_controller', array('side' => 'bank', 's' => $season)));
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
		// Grab the members points
		$sql = 'SELECT 
				u.username, 
				p.season, 
				p.league, 
				round(sum(if(p.points_type IN (' . POINTS_BET . ',' . POINTS_PAID . '), p.points * -1.0, p.points)),2) as saldo 
			FROM ' . FOOTB_POINTS . ' AS p
			JOIN ' . USERS_TABLE . " AS u ON (u.user_id = p.user_id)
			WHERE p.season <= $season
			GROUP BY p.season, p.league, u.username
			HAVING saldo <> 0.00
			ORDER BY u.username, p.season, p.league";

		if(!$result = $db->sql_query($sql))
		{
			trigger_error('NO_SEASON');
		}
		$user_rows = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);
		$newline = "\r\n";
		$csv_data= '';
		$csv_data .= sprintf($user->lang['SEASON']) . ' ' . $season. $newline;
		$csv_data .= sprintf($user->lang['NAME']) . ';' . sprintf($user->lang['SEASON']) . ';' . sprintf($user->lang['LEAGUE']) . ';Saldo;' . $newline;

		$last_username = '';
		$sum_saldo = 0.0;
		foreach ($user_rows as $user_row)
		{
			if ($last_username != '' AND $last_username != $user_row['username'])
			{
				$csv_data .= str_replace("\"", "\"\"", $last_username) . ';Summe;;' . 
						str_replace('.', ',', $sum_saldo) . ';' . $newline;
				$sum_saldo = 0.0;
			}
			$csv_data .= str_replace("\"", "\"\"", $user_row['username']) . ';' . 
						$user_row['season'] . ';' .
						$user_row['league'] . ';' .
						str_replace('.', ',', $user_row['saldo']) . ';' . $newline;
			$sum_saldo += $user_row['saldo'];
			$last_username = $user_row['username'];
		}
		if ($last_username != '')
		{
			$csv_data .= str_replace("\"", "\"\"", $last_username) . ';Summe;;' . 
					str_replace('.', ',', $sum_saldo) . ';' . $newline;
		}
		// Output the csv file
		$filename = $season. '_bank.csv';
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
