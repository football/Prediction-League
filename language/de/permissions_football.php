<?php
/**
*
* @package language
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

// Adding new football permissions
$lang = array_merge($lang, array(
	'ACP_FOOTBALL'						=> 'Fussball',
	'ACL_U_USE_FOOTBALL'				=> 'Kann Football Extension verwenden',
	'ACL_A_FOOTBALL_CONFIG'				=> 'Kann Tipprundeneinstellung ändern',
	'ACL_A_FOOTBALL_DELETE'				=> 'Kann Spielpläne löschen',
	'ACL_A_FOOTBALL_EDITBETS'			=> 'Kann alle Tipps ansehen, eingeben und ändern',
	'ACL_A_FOOTBALL_PLAN'				=> 'Kann Spielpläne erstellen und ändern',
	'ACL_A_FOOTBALL_RESULTS'			=> 'Kann endgültige Spielergebnisse eingeben, ändern und löschen',
	'ACL_A_FOOTBALL_POINTS'				=> 'Kann Kontodaten eingeben, ändern und löschen',

	'ROLE_ADMIN_FOOTBALL'				=> 'Tipprunden Administrator',
	'ROLE_DESCRIPTION_ADMIN_FOOTBALL'	=> 'Kann endgültige Spielergebnisse eingeben und ändern.',
));

?>