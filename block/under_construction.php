<?php
/**
*
* @package Football
* @version $Id: under_construction.php 1 2010-05-17 22:09:43Z football $
* @copyright (c) 2010 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

$sidename = sprintf($user->lang['UNDER_CONSTRUCTION']);
$template->assign_vars(array(
	'S_SIDENAME' 					=> $sidename,
	'S_DISPLAY_UNDER_CONSTRUCTION' 	=> true,
	)
);
