<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

// Check Prediction League authorisation 
if ( !$this->auth->acl_get('u_use_football') )
{
	trigger_error('NO_AUTH_VIEW');
}

$action='';

if (!$season OR !$league)
{
	redirect($this->helper->route('football_main_controller', array('side' => 'bet')));
}
else
{
	if (user_is_member($user->data['user_id'], $season, $league))
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
				include($this->football_includes_path . 'export.' . $this->php_ext);
			}
		}
	}
	else
	{
		redirect($this->helper->route('football_main_controller', array('side' => 'bet')));
	}
}

?>
