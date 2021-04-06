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

global $table_prefix;

// Config constants
define('FOOTB_BETS',				$table_prefix . 'footb_bets');
define('FOOTB_EXTRA_BETS',			$table_prefix . 'footb_extra_bets');
define('FOOTB_EXTRA',				$table_prefix . 'footb_extra');
define('FOOTB_LEAGUES',				$table_prefix . 'footb_leagues');
define('FOOTB_MATCHDAYS',			$table_prefix . 'footb_matchdays');
define('FOOTB_MATCHES',				$table_prefix . 'footb_matches');
define('FOOTB_MATCHES_HIST',		$table_prefix . 'footb_matches_hist');
define('FOOTB_POINTS',				$table_prefix . 'footb_points');
define('FOOTB_RANKS',				$table_prefix . 'footb_rank_matchdays');
define('FOOTB_SEASONS',				$table_prefix . 'footb_seasons');
define('FOOTB_TEAMS',				$table_prefix . 'footb_teams');
define('FOOTB_TEAMS_HIST',			$table_prefix . 'footb_teams_hist');
define('BET_KO_90', 1);
define('BET_KO_EXTRATIME', 2);
define('BET_KO_PENALTY', 3);
define('POINTS_BET', 1);
define('POINTS_DEPOSITED', 2);
define('POINTS_MATCHDAY', 3);
define('POINTS_SEASON', 4);
define('POINTS_MOST_HITS', 5);
define('POINTS_MOST_HITS_AWAY', 6);
define('POINTS_PAID', 7);
define('UP_NONE', 0);
define('UP_WINS', 1);
define('UP_POINTS', 2);
define('LEAGUE_CHAMP', 1);
define('LEAGUE_KO', 2);
