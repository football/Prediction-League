<?php
/**
*
* info_acp_football.php [en]
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}


if (empty($lang) || !is_array($lang))
{
	$lang = array();
}


// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'ACP_FOOTBALL'							=> 'Football',
	'ACP_FOOTBALL_MANAGEMENT'				=> 'Manage Prediction League',
	'ACP_FOOTBALL_OPERATION'				=> 'Match operations',
	'ACP_FOOTBALL_MANAGE'					=> 'Manage fixture list',
	'ACP_FOOTBALL_CONFIGURATION'			=> 'Prediction League configuration',
	'ACP_FOOTBALL_SETTINGS'					=> 'Prediction League settings',
	'ACP_FOOTBALL_SETTINGS_EXPLAIN'			=> 'Here you can carry out some basic settings of the Prediction League, give it a suitable name and description and define a football side announcement and other values. ',
	'DISABLE_FOOTBALL'						=> 'Deactivate Prediction League',
	'DISABLE_FOOTBALL_EXPLAIN'				=> 'You can disable the Prediction League for all users. If you wanted, you can display a short message (up to 255 signs). ',
	'DISPLAY_RANKS'							=> 'Number of indicated User in total ranking',
	'DISPLAY_RANKS_EXPLAIN'					=> 'Announcement of users in the ranking lists. The ownrank is suspended if necessary below. ',
	'FOOTBALL_CODE'							=> 'Prediction League Code',
	'FOOTBALL_CODE_EXPLAIN'					=> 'Access code to the Prediction League, e.g., for cronjobs. ',
	'FOOTBALL_FULLSCREEN'					=> 'Prediction League pages in fullscreen mode',
	'FOOTBALL_FULLSCREEN_EXPLAIN'			=> 'Herewith you can, unlike the forum, display Prediction League pages with minimal margins. ',
	'FOOTBALL_HEADER_ENABLE'				=> 'Use header image per league',
	'FOOTBALL_HEADER_ENABLE_EXPLAIN'		=> 'Hereby you can define whether each league the associated image to be displayed in the header. ',
	'FOOTBALL_INFO'							=> 'Show Prediction Leagues information',
	'FOOTBALL_INFO_EXPLAIN'					=> 'Here you can switch on short information (up to 255 signs) above the phpBB3 Football sides. ',
	'FOOTBALL_NAME'							=> 'Name of the Prediction League',
	'FOOTBALL_NAME_EXPLAIN'					=> 'Name used in the Prediction League-menu, as link to Prediction League home side. ',
	'FOOTBALL_OVERRIDE_STYLE'				=> 'Override style',
	'FOOTBALL_OVERRIDE_STYLE_EXPLAIN'		=> 'Replaces style in Prediction League with the default Prediction League style. ',
	'FOOTBALL_MENU'							=> ' Show Prediction League menu',
	'FOOTBALL_MENU_EXPLAIN'					=> 'Display the Prediction League dropdown menu in the navigation bar? ',
	'FOOTBALL_STYLE'						=> 'Default Prediction League style',
	'FOOTBALL_UPDATE_SOURCE'				=> 'Link to the update source',
	'FOOTBALL_UPDATE_SOURCE_EXPLAIN'		=> 'Update link. If empty, <var>http://football.bplaced.net/football/xml/football_xml_season.php</var> is automatically selected. ',
	'FOOTBALL_UPDATE_CODE'					=> 'Update Code',
	'FOOTBALL_UPDATE_CODE_EXPLAIN'			=> 'Only with this code, updates can be downloaded from this page. ',
	'GUEST_VIEW'							=> 'Prediction League for guests visible',
	'GUEST_VIEW_EXPLAIN'					=> 'Should guests be able to see the Prediction League?',
	'USER_VIEW'								=> 'Prediction League only for participants visible',
	'USER_VIEW_EXPLAIN'						=> 'Should the Prediction League only to participants be visible?',
	'HOST_TIMEZONE'							=> 'Host Timezone',
	'HOST_TIMEZONE_EXPLAIN'					=> 'Difference to the Board time zone if your Host in another time zone stands, so that the tip delivery correctly functions. ',
	'LEFT_COLUMN'							=> 'Left column width in pixels',
	'LEFT_COLUMN_EXPLAIN'					=> 'Optimum width 180 pixels. This value should not be fell short. ',
	'PREDICTION_LEAGUE'						=> 'Prediction League',
	'RIGHT_COLUMN'							=> 'Right column width in pixels',
	'RIGHT_COLUMN_EXPLAIN'					=> 'Optimally 180 pixels. This value is redifined by external insertions if necessary. ',
	'USERS_PAGE'							=> 'User per side',
	'USERS_PAGE_EXPLAIN'					=> 'Number of Users displayed in ranking list and all bets. ',
	'WIN_NAME'								=> 'Win',
	'WIN_NAME_EXPLAIN'						=> 'The name or currency you want to disaply for the wins in the Prediction League',
	'ANNOUNCEMENT_TOPIC'					=> 'Release Announcement',
	'CURRENT_VERSION'						=> 'Current Version',
	'DOWNLOAD_LATEST'						=> 'Download Latest Version',
	'LATEST_VERSION'						=> 'Latest Version',
	'NOT_UP_TO_DATE'						=> '%s is not up to date',
	'RELEASE_ANNOUNCEMENT'					=> 'Annoucement Topic',
	'UP_TO_DATE'							=> '%s is up to date',
	'FOOTBALL_VERSION_CHECK'				=> 'Prediction League Extension Version Check',
	'ACP_FOOTBALL_FEATURES'					=> 'Prediction League features',
	'ACP_FOOTBALL_FEATURES_EXPLAIN'			=> 'Here you can activate some functions of the Prediction League or deactivate',
	'AUTO'									=> 'Automatic',
	'BANK'									=> 'Manage Prediction League account',
	'BANK_EXPLAIN'							=> 'Should Prediction League accounts be maintained with bets and profits?',
	'FOOTBALL_BREADCRUMB'					=> 'Show Prediction League breadcrumb',
	'FOOTBALL_BREADCRUMB_EXPLAIN'			=> 'Display a Breadcrumb-Link to Prediction League? ',
	'FOOTBALL_SEASON_START'					=> 'Start in season',
	'FOOTBALL_SEASON_START_EXPLAIN'			=> 'Start in this or in the auto-detected season if one is not already selected. ',
	'FOOTBALL_REMEMBER_ENABLE'				=> 'Enable cron job for Email reminder',
	'FOOTBALL_REMEMBER_ENABLE_EXPLAIN'		=> 'Here you can specify whether a reminder should be sent 1 day ago predictions.',
	'FOOTBALL_REMEMBER_NEXT_RUN'			=> 'Next Cronjoblauf for Email reminder',
	'FOOTBALL_REMEMBER_NEXT_RUN_EXPLAIN'	=> ' Here you can specify when the cron job for the Email reminder will be recalled. After running the cron job for the following day will be rescheduled on same hour.',
	'FOOTBALL_SIDE'							=> 'Display Prediction league sidebar',
	'FOOTBALL_SIDE_EXPLAIN'					=> 'Display the Prediction league side bar with the menu items? ',
	'FOUNDER_DELETE'						=> 'Only foundation members may remove',
	'FOUNDER_DELETE_EXPLAIN'				=> 'Remove of Prediction League data like seasons, leagues, teams, matchdays and schedules only to foundation members limit. ',
	'RESULTS_AT_TIME'						=> 'Input of final match results only after playing',
	'RESULTS_AT_TIME_EXPLAIN'				=> 'Release input of the final match results in the Adminbereich only after playing. The input of temporary match results by the users is independent from this. ',
	'SAME_ALLOWED'							=> 'All bets on a matchday immediately',
	'SAME_ALLOWED_EXPLAIN'					=> 'Identical bets (grannys tip) with all matches of a matchday permit or forbid. ',
	'ULT_POINTS'							=> 'Integrate Ultimate Points',
	'ULT_POINTS_EXPLAIN'					=> 'Should it be possible to let settle the points or profits in Ultimate Points? In addition Ultimate Points must be installed. ',
	'ULT_POINTS_FACTOR'						=> 'Ultimate Points Factor',
	'ULT_POINTS_FACTOR_EXPLAIN'				=> 'Factor on matchday points to be credit in Ultimate Points. ',
	'UP_NONE'								=> 'None',
	'UP_POINTS'								=> 'Points',
	'UP_WINS'								=> 'Wins',
	'VIEW_BETS'								=> 'Display bets immediately',
	'VIEW_BETS_EXPLAIN'						=> 'Should all bets be indicated immediately? If no, then they are indicated only after delivery end. ',
	'VIEW_CURRENT'							=> 'Display current league on start',
	'VIEW_CURRENT_EXPLAIN'					=> 'Display league with next game when no league selected? Otherwise starts on first league',
	'VIEW_TENDENCIES'						=> 'Display tendencies immediately',
	'VIEW_TENDENCIES_EXPLAIN'				=> 'Should alltendencies be indicated immediately? If no, then they are indicated only after delivery end. ',
	'WIN_HITS02'							=> 'Winnings direct hit evaluation with away points',
	'WIN_HITS02_EXPLAIN'					=> 'Should winnings of the direct hit evaluation with away points be indicated? If no, must be made sure that in addition no winnings were deposited in the existing leagues. ',
	'ACP_FOOTBALL_MENU'						=> 'Menu settings',
	'ACP_FOOTBALL_MENU_EXPLAIN'				=> 'Here you can deposit community references as a Menueinträge for the Prediction League menu. You can choose the Menutext, besides, freely, give another name aslo to the link to the community. ',
	'MENU_DESC1'							=> 'Caption Link 1',
	'MENU_DESC1_EXPLAIN'					=> 'The caption of the links may no impermissible special characters and be no greater than 20 characters long. ',
	'MENU_DESC2'							=> 'Caption Link 2',
	'MENU_DESC3'							=> 'Caption Link 3',
	'MENU_LINK1'							=> 'Http-Link 1',
	'MENU_LINK1_EXPLAIN'					=> 'Please enter http links here. ',
	'MENU_LINK2'							=> 'Http-Link 2',
	'MENU_LINK3'							=> 'Http-Link 3',
	'MENU_NO_LINK'							=> 'No community link deposits',
	'ACP_FOOTBALL_USERGUIDE'				=> 'Football guide',
	'ACP_FOOTBALL_USERGUIDE_EXPLAIN'		=> 'Hier findest du Hilfe für die Einstellungen des phpBB3 Football MODs.<br />Wenn du Fragen hast, dann schaue bitte immer zuerst hier!',
	'LOG_FOOTBALL_FEATURES'					=> '<strong>Prediction League features changed</strong>',
	'LOG_FOOTBALL_MENU'						=> '<strong>Prediction League menu changed</strong>',
	'LOG_FOOTBALL_SETTINGS'					=> '<strong>Prediction League settings changed</strong>',
	'LOG_FOOTBALL_MSG_TEST'					=> 'Call on %s.',
	'LOG_FOOTBALL_MSG_TEST_TRAVEL'			=> 'Call with time travel to %s.',
	'LOG_FOOTBALL_REMEMBER_CRON'			=> 'Cronjob Football remember running %s',
	'LOG_FOOTBALL_REMEMBER_CRON_TEST'		=> 'Cronjob Football remember test call %s',
	'LOG_PL_BACKUP'							=> '<strong>Prediction League backup</strong>',
	'FOOTBALL_REMEMBER_SUBJECT'				=> 'Please bet %1$s %2$d. matchday',
	'FOOTBALL_REMEMBER_SUBJECT_BOARD'		=> 'Sent reminder mails %1$s %2$d',
	'FOOTBALL_REMEMBER_ERROR_EMAIL'			=> '%1$s reminder email to: %2$d failed',
	'FOOTBALL_REMEMBER_ERROR_EMAIL_BOARD'	=> '%1$s packing list reminder email to: %2$d failed',
	'FOOTBALL_REMEMBER_NOBODY'				=> ' 	Nobody needs to be reminded.',
	'FOOTBALL_REMEMBER_NO_DELIVERY'			=> 'No forthcoming predictions in the period.',
	'FOOTBALL_REMEMBER_SEND'				=> '%1$s reminder email to: %2$d',
));
