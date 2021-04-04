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

$sql = 'SELECT *
		FROM ' . FOOTB_LEAGUES . " 
		WHERE season = $season 
			AND league = $league";
if( !$result = $db->sql_query($sql) )
{
	trigger_error('NO_LEAGUE');
}
$league_short = $db->sql_fetchfield('league_name_short');
$db->sql_freeresult($result);
$export_file = $league_short . '_'. $season. '_Tipps.csv';
$path_attachment = './../../files/' . $export_file;
$newline = "\r\n";

if (!isset($_POST['send']))
{
	header('Pragma: no-cache');
	header("Content-Type: text/csv; name=\"$export_file\"");
	header("Content-disposition: attachment; filename=$export_file");

//	header('Content-Type: text/x-csv');
//	header('Expires: ' . gmdate('D, d M Y H:i:m') . ' GMT');
//	header('Content-Disposition: attachment; filename='. $export_file);
	$phpbb_root_path = './../';
}
else
{
	$phpbb_root_path = './../../';
}

$sql_users = 'SELECT DISTINCT
				b.user_id,
				u.username
				FROM ' . FOOTB_BETS . ' AS b
				LEFT JOIN ' . USERS_TABLE . " AS u ON (u.user_id = b.user_id)
				WHERE b.season = $season AND b.league = $league
				ORDER BY b.user_id ASC";

$sql_results = "SELECT
				m.match_no,
				DATE_FORMAT(m.match_datetime,'%d.%m.%Y') AS match_time,
				m.matchday,
				m.formula_home,
				m.formula_guest,
				t1.team_id AS hid,
				t2.team_id AS gid,
				t1.team_name_short AS team_home,
				t2.team_name_short AS team_guest,
				m.status,
				m.goals_home,
				m.goals_guest
				FROM " . FOOTB_MATCHES . ' AS m
				LEFT JOIN ' . FOOTB_TEAMS . ' AS t1 ON (t1.season = m.season AND t1.league = m.league AND t1.team_id=m.team_id_home)
				LEFT JOIN ' . FOOTB_TEAMS . " AS t2 ON (t2.season = m.season AND t2.league = m.league AND t2.team_id=m.team_id_guest)
				WHERE m.season = $season AND m.league = $league
				ORDER BY m.match_no ASC";

$sql_bets = "SELECT
				m.matchday,
				m.match_no,
				b.user_id,
				IF(m.status > 0, b.goals_home, '') AS bet_home,
				IF(m.status > 0, b.goals_guest, '') AS bet_guest
				FROM " . FOOTB_MATCHES . ' AS m
				LEFT JOIN ' . FOOTB_BETS . " AS b ON (b.season = m.season AND b.league = m.league AND b.match_no = m.match_no)
				WHERE m.season = $season AND m.league = $league
				ORDER BY m.matchday ASC, m.match_no ASC, b.user_id ASC";

if(!$result_users = $db->sql_query($sql_users))
{
	trigger_error('NO_USER');
}
$rows_users = $db->sql_fetchrowset($result_users);
$count_user = sizeof($rows_users);
$db->sql_freeresult($result_users);
$j = 0;
$column = array();
foreach ($rows_users as $row_user)
{
	$column[(8 + (3 * ($j)))] = str_replace("\"", "\"\"", $row_user['username']);
	$lastcolumn = 8 + (3 * ($j));
	$bet_column[$row_user['user_id']] = $lastcolumn;
	$j++;
}
$export_row_users = "\"\";\"\";\"\";\"\";\"\";\"\";";
for($j = 8; $j <= $lastcolumn; $j = $j + 3)
{
	$export_row_users .= "\"\";\"\";\"" . $column[$j] . "\"";
	if($j != $lastcolumn)
	{
		$export_row_users .= ';';
	}
}
$export_row_users .= $newline;

if( !$result_results = $db->sql_query($sql_results) )
{
	trigger_error('NO_RESULTS');
}
$rows_results = $db->sql_fetchrowset($result_results);
$count_results = sizeof($rows_results);
$db->sql_freeresult($result_results);

if( !$result_bets = $db->sql_query($sql_bets) )
{
	trigger_error('NO_BETS');
}
$rows_bets = $db->sql_fetchrowset($result_bets);
$count_bets = sizeof($rows_bets);
$db->sql_freeresult($result_bets);
$column = array();
$lastcolumn = 0;
$last_match_num = 0;
foreach ($rows_bets as $row_bet)
{
	if ($row_bet['match_no'] == $last_match_num)
	{
		$column[$bet_column[$row_bet['user_id']]] = str_replace("\"", "\"\"", $row_bet['bet_home']);
		$column[$bet_column[$row_bet['user_id']] + 1] = str_replace("\"", "\"\"", $row_bet['bet_guest']);
		$column[$bet_column[$row_bet['user_id']] + 2] = '';
		$lastcolumn = $bet_column[$row_bet['user_id']] + 2;
	}
	else
	{
		if ($lastcolumn > 0) 
		{
			$export_bets[$last_match_num] = '';
			for($j=8; $j<=$lastcolumn; $j++)
			{
				$export_bets[$last_match_num] .= "\"" . $column[$j] . "\"";
				if($j!=$lastcolumn)
				{
					$export_bets[$last_match_num] .= ';';
				}
			}
			$export_bets[$last_match_num] .= $newline;
		}
		$column = array();
		$last_match_num = $row_bet['match_no'];
		$column[$bet_column[$row_bet['user_id']]] = str_replace("\"", "\"\"", $row_bet['bet_home']);
		$column[$bet_column[$row_bet['user_id']] + 1] = str_replace("\"", "\"\"", $row_bet['bet_guest']);
		$column[$bet_column[$row_bet['user_id']] + 2] = '';
		$lastcolumn = $bet_column[$row_bet['user_id']] + 2;
	}
}
$export_bets[$last_match_num] = '';
for($j = 8; $j <= $lastcolumn; $j++)
{
	$export_bets[$last_match_num] .= "\"" . $column[$j] . "\"";
	if($j != $lastcolumn)
	{
		$export_bets[$last_match_num] .= ';';
	}
}
$export_bets[$last_match_num] .= $newline;

$last_matchday = 0;

$export= '';
$export .= 'CSV;'. $league. ';'. $season. $newline;

$i = 0;
foreach ($rows_results as $row_result)
{
	if ($last_matchday != $row_result['matchday'])
	{
		if ($last_matchday != 0)
		{
			$export .= $newline;
			$export .= $newline;
			$export .= $newline;
			$export .= $newline;
			$export .= $newline;
			$export .= $newline;
			$export .= ";;". str_replace("\"", "\"\"", $row_result['match_time']). $newline;
			$export .= $newline;
			$export .= $newline;
			$export .= $newline;
			$export .= $newline;
			$export .= $newline;
		}
		else
		{
			$export .= $newline;
			$export .= $newline;
			$export .= $newline;
			$export .= ";;". str_replace("\"", "\"\"", $row_result['match_time']). $newline;
			$export .= $newline;
			$export .= $newline;
			$export .= $newline;
			$export .= $newline;
			$export .= $newline;
		}
		$export .= $export_row_users;
		$column = array();
		$last_matchday = $row_result['matchday'];
	}
	if (0 == $row_result['hid'])
	{
		$home_info 		= get_team($season, $league, $row_result['match_no'], 'team_id_home', $row_result['formula_home']);
		$home_in_array 	= explode("#",$home_info);
		$homename 		= $home_in_array[3];
	}
	else
	{
		$homename = $row_result['team_home'];
	}
	if (0 == $row_result['gid'])
	{
		$guest_info 	= get_team($season, $league, $row_result['match_no'], 'team_id_guest', $row_result['formula_guest']);
		$guest_in_array = explode("#",$guest_info);
		$guestname 		= $guest_in_array[3];
	}
	else
	{
		$guestname = $row_result['team_guest'];
	}
	$column[0] = str_replace("\"", "\"\"", $homename);
	$column[1] = str_replace("\"", "\"\"", $guestname);

	if ($row_result['status'] == 3)
	{
		$column[2] = str_replace("\"", "\"\"", $row_result['goals_home']);
		$column[4] = str_replace("\"", "\"\"", $row_result['goals_guest']);
	}
	else
	{
		$column[2] = '';
		$column[4] = '';
	}
	$export .= "\"" . $column[0] . "\";\"" . $column[1] . "\";\"" . $column[2] . "\";\"\";\"" . $column[4] . "\";\"\";\"\";\"\";";
	if ($export_bets[$row_result['match_no']] == '') 
	{
		$export .= $newline;
	}
	else
	{
		$export .= $export_bets[$row_result['match_no']];
	}
	$column = array();
	$i++;
}

if (isset($_POST['send']))
{
	$fp = fopen($path_attachment , "b");
	ftruncate ($fp, 0);
	rewind($fp);
	fwrite ($fp, $export);
	fclose($fp);
}
else
{
	echo utf8_decode($export);
	exit;
}
