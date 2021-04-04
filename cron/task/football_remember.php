<?php
/**
*
* @package phpBB Extension - Football Football
* @copyright (c) 2016 football (http://football.bplaced.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace football\football\cron\task;

class football_remember extends \phpbb\cron\task\base
{
	/* @var string phpBB root path */
	protected $root_path;

	/* @var string phpEx */
	protected $php_ext;

	/* @var \phpbb\extension\manager */
	protected $phpbb_extension_manager;

	/* @var \phpbb\path_helper */
	protected $phpbb_path_helper;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\log\log_interface */
	protected $log;

	/* @var \phpbb\user */
	protected $user;

	/**
	* Constructor
	*
	* @param string									$root_path
	* @param string									$php_ext
	* @param \phpbb\extension\manager				$phpbb_extension_manager
	* @param \phpbb\path_helper						$phpbb_path_helper
	* @param \phpbb\db\driver\driver_interface		$db
	* @param \phpbb\config\config					$config
	* @param \phpbb\log\log_interface 				$log
	* @param \phpbb\user							$user
	*/
	public function __construct($root_path, $php_ext, \phpbb\extension\manager $phpbb_extension_manager, \phpbb\path_helper $phpbb_path_helper, \phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\log\log_interface $log, \phpbb\user $user)
	{
		$this->root_path				= $root_path;
		$this->php_ext 					= $php_ext;
		$this->phpbb_extension_manager 	= $phpbb_extension_manager;
		$this->phpbb_path_helper		= $phpbb_path_helper;
		$this->db 						= $db;
		$this->config 					= $config;
		$this->phpbb_log 				= $log;
		$this->user 					= $user;
	}

	/**
	* Runs this cron task.
	*
	* @return null
	*/
	public function run()
	{
		global $request;

		$ext_path = $this->phpbb_path_helper->update_web_root_path($this->phpbb_extension_manager->get_extension_path('football/football', true));
		include($ext_path . 'includes/functions.' . $this->php_ext);
		include($ext_path . 'includes/constants.' . $this->php_ext);

		// Load extension language file
		$this->user->setup();
		$this->user->add_lang_ext('football/football', 'info_acp_football');

		// mode=test ?
		$mode = $request->variable('mode', '');
		$days = $request->variable('days', 0);

		//Mail Settings
		$use_queue 		= false;
		$used_method 	= NOTIFY_EMAIL;
		$priority 		= MAIL_NORMAL_PRIORITY;


		$season = curr_season();
		//Matchdays to close in 24 hours and 24 hours later
		// shift days to test
		$local_board_time = time() + ($days * 86400);

		if ($mode <> 'test')
		{
			// Update next run 
			$run_time = getdate($this->config['football_remember_next_run']);
			$next_run = mktime($run_time['hours'], $run_time['minutes'], 0, date("n"), date("j") + 1, date("Y"));
			$this->config->set('football_remember_next_run', $next_run, true);
		}
		else
		{
			$message = sprintf($this->user->lang['LOG_FOOTBALL_MSG_TEST' . (($days == 0) ? '' : '_TRAVEL')], date("d.m.Y H:i", $local_board_time));
			$this->phpbb_log->add('admin', ANONYMOUS, '', 'LOG_FOOTBALL_REMEMBER_CRON_TEST', false, array($message));
		}

		$sql = 'SELECT
				m.*,
				l.*
				FROM ' . FOOTB_MATCHDAYS . ' AS m
				LEFT JOIN ' . FOOTB_LEAGUES . " AS l ON (l.season = m.season AND l.league = m.league)
				WHERE m.season >= $season AND m.status = 0 
					AND (DATE_SUB(m.delivery_date, INTERVAL '1 23:59' DAY_MINUTE) < FROM_UNIXTIME('$local_board_time'))
					AND (DATE_SUB(m.delivery_date, INTERVAL '1 00:00' DAY_MINUTE) > FROM_UNIXTIME('$local_board_time'))
				GROUP BY m.season, m.league, m.matchday";
		$result = $this->db->sql_query($sql);
		$toclose = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		// If we found matchdays to close, search missing bets and mail them
		foreach ($toclose as $close)
		{
			// prepare some variables
			$first_mail 	= true;
			$season 		= $close['season'];
			$league 		= $close['league'];
			$league_name 	= $close['league_name'];
			$league_short 	= $close['league_name_short'];
			$delivery 		= $close['delivery_date'];
			$matchday 		= $close['matchday'];
			$subject 		= sprintf($this->user->lang['FOOTBALL_REMEMBER_SUBJECT'], $league_short, $matchday);
			$usernames 		= '';

			// find missing users 
			$sql = 'SELECT
					u.user_email AS user_email,
					u.username AS username,
					u.user_id AS userid,
					u.user_lang 
					FROM ' . FOOTB_MATCHES . ' AS m
					LEFT JOIN ' . FOOTB_LEAGUES . ' AS l ON (l.season = m.season AND l.league = m.league)
					LEFT JOIN ' . FOOTB_BETS . ' AS b ON (b.season = m.season AND b.league = m.league AND b.match_no = m.match_no)
					LEFT JOIN ' . PROFILE_FIELDS_DATA_TABLE. ' AS p ON p.user_id = b.user_id
					LEFT JOIN ' . USERS_TABLE. " AS u ON u.user_id = b.user_id
					WHERE m.season = $season AND m.league = $league AND m.matchday = $matchday 
						AND ((b.goals_home = '') OR (b.goals_guest = '')) 
						AND m.status = 0 AND p.pf_footb_rem_f = 1
						AND (l.bet_in_time = 0 OR 
								(l.bet_in_time = 1 
									AND (DATE_SUB(m.match_datetime, INTERVAL '1 23:59' DAY_MINUTE) < FROM_UNIXTIME('$local_board_time'))
									AND (DATE_SUB(m.match_datetime, INTERVAL '1 00:00' DAY_MINUTE) > FROM_UNIXTIME('$local_board_time'))))
					GROUP BY b.user_id
					UNION
					SELECT
					p.pf_footb_email  AS user_email,
					u.username AS username,
					u.user_id AS userid,
					u.user_lang
					FROM " . FOOTB_MATCHES . ' AS m
					LEFT JOIN ' . FOOTB_LEAGUES . ' AS l ON (l.season = m.season AND l.league = m.league)
					LEFT JOIN ' . FOOTB_BETS . ' AS b ON (b.season = m.season AND b.league = m.league AND b.match_no = m.match_no)
					LEFT JOIN ' . PROFILE_FIELDS_DATA_TABLE. ' AS p ON p.user_id = b.user_id
					LEFT JOIN ' . USERS_TABLE. " AS u ON u.user_id = b.user_id
					WHERE m.season = $season AND m.league = $league AND m.matchday = $matchday 
						AND ((b.goals_home = '') OR (b.goals_guest = '')) 
						AND m.status = 0 AND p.pf_footb_rem_s = 1
						AND (l.bet_in_time = 0 OR 
								(l.bet_in_time = 1 
									AND (DATE_SUB(m.match_datetime, INTERVAL '1 23:59' DAY_MINUTE) < FROM_UNIXTIME('$local_board_time'))
									AND (DATE_SUB(m.match_datetime, INTERVAL '1 00:00' DAY_MINUTE) > FROM_UNIXTIME('$local_board_time'))))
					GROUP BY b.user_id
					";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);

			if (!$row)
			{
				$this->db->sql_freeresult($result);
			}
			else
			{

				// Send the messages
				include_once($this->root_path . 'includes/functions_messenger.' . $this->php_ext);
				$messenger = new \messenger($use_queue);
				include_once($this->root_path . 'includes/functions_user.' . $this->php_ext);
				$errored = false;
				$messenger->headers('X-AntiAbuse: Board servername - ' . $this->config['server_name']);
				$messenger->headers('X-AntiAbuse: User_id - ' . ANONYMOUS);
				$messenger->headers('X-AntiAbuse: Username - CRON TASK Football remember');
				$messenger->headers('X-AntiAbuse: User IP - ' . $this->user->ip);
				$messenger->subject(htmlspecialchars_decode($subject));
				$messenger->set_mail_priority($priority);

				do
				{
					// Send the messages
					$used_lang = $row['user_lang'];
					$mail_template_path = $ext_path . 'language/' . $used_lang . '/email/';

					if ($mode <> 'test')
					{
						$messenger->to($row['user_email'], $row['username']);
					}
					else
					{
						// test send to board email
						$messenger->to($this->config['board_email'], $this->config['sitename']);
					}
					$messenger->template('footb_send_remember', $used_lang, $mail_template_path);

					$messenger->assign_vars(array(
						'USERNAME'		=> $row['username'],
						'LEAGUE'		=> $league_name,
						'MATCHDAY'		=> $matchday,
						'DELIVERY'		=> $delivery,
						'CONTACT_EMAIL' => $this->config['board_contact'])
					);

					if ($mode <> 'test')
					{
						if (!($messenger->send($used_method)))
						{
							$message = '<strong>' . sprintf($this->user->lang['FOOTBALL_REMEMBER_ERROR_EMAIL'], $league_short, $row['user_email']) . '</strong>';
							$this->phpbb_log->add('critical', ANONYMOUS, '', 'LOG_ERROR_EMAIL', false, array($message));
							$usernames .= (($usernames != '') ? ', ' : '') . $row['username']. '!';
						}
						else
						{
							$usernames .= (($usernames != '') ? ', ' : '') . $row['username'];
						}
					}
					else
					{
						// Test mode
						if ($first_mail)
						{
							// only send one mail
							if (!($messenger->send($used_method)))
							{
								$message = '<strong>' . sprintf($this->user->lang['FOOTBALL_REMEMBER_ERROR_EMAIL'], $league_short, $row['user_email']) . '</strong>';
								$this->phpbb_log->add('critical', ANONYMOUS, '', 'LOG_ERROR_EMAIL', false, array($message));
								$usernames .= (($usernames != '') ? ', ' : '') . $row['username']. '!';
							}
							else
							{
								$usernames .= (($usernames != '') ? ', ' : '') . $row['username'];
							}
							$first_mail = false;
						}
						else
						{
							$usernames .= (($usernames != '') ? ', ' : '') . $row['username'];
						}
					}
				}
				while ($row = $this->db->sql_fetchrow($result));
				$this->db->sql_freeresult($result);
				
				// Only if mails have already been sent previously 
				if ($usernames <> '')
				{
					// send mail to board administration
					$used_lang = $this->config['default_lang'];
					$mail_template_path = $ext_path . 'language/' . $used_lang . '/email/';
					$subject = sprintf($this->user->lang['FOOTBALL_REMEMBER_SUBJECT_BOARD'], $league_short, $matchday);
					$messenger->to($this->config['board_email'], $this->config['sitename']);
					$messenger->subject(htmlspecialchars_decode($subject));
					$messenger->template('footb_board_remember', $used_lang, $mail_template_path);
					$messenger->assign_vars(array(
						'CONTACT_EMAIL' => $this->config['board_contact'],
						'REMEMBER_LIST'	=> $usernames,
						)
					);

					if (!($messenger->send($used_method)))
					{
						$message = '<strong>' . sprintf($this->user->lang['FOOTBALL_REMEMBER_ERROR_EMAIL_BOARD'], $league_short, $this->config['board_email']) . '</strong>';
						$this->phpbb_log->add('critical', ANONYMOUS, '', 'LOG_ERROR_EMAIL', false, array($message));
					}
					else
					{
						$log_subject = sprintf($this->user->lang['FOOTBALL_REMEMBER_SEND'], $league_short, $usernames) ;
						$this->phpbb_log->add('admin', ANONYMOUS, '', 'LOG_FOOTBALL_REMEMBER_CRON', false, array($log_subject));
					}
				}
				else
				{
					// Log the cronjob run
					$log_subject = sprintf($this->user->lang['FOOTBALL_REMEMBER_NOBODY']) ;
					$this->phpbb_log->add('admin', ANONYMOUS, '', 'LOG_FOOTBALL_REMEMBER_CRON', false, array($log_subject));
				}
			}
		}
		if (sizeof($toclose) == 0)
		{
			// Log the cronjob run
			$log_subject = sprintf($this->user->lang['FOOTBALL_REMEMBER_NO_DELIVERY']) ;
			$this->phpbb_log->add('admin', ANONYMOUS, '', 'LOG_FOOTBALL_REMEMBER_CRON', false, array($log_subject));
		}

		return;
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		return (bool) $this->config['football_remember_enable'];
	}

	/**
	* Returns whether this cron task should run now, because next run time
	* has passed.
	*
	* @return bool
	*/
	public function should_run()
	{
		global $request;
		$mode = $request->variable('mode', '');

		if ($mode <> 'test')
		{
			return $this->config['football_remember_next_run'] < time();
		}
		else
		{
			return true;
		}
	}
}
?>