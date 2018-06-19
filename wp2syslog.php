<?php
/*
   Plugin Name: wp2syslog
   Plugin URI: https://github.com/kataweb/wp2syslog
   Description: It keeps track of wordpress's events and log them to syslog.
   Author: psicosi448
   Version: 1.0.5
   Author URI: http://www.kataweb.it
 */

/*

   wp2syslog -- Global logging facility for WordPress
   (WPsyslog revisited)

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation in the Version 3.

 */

if (!defined('WP2SYSLOG_PLUGIN_NAME'))
define('WP2SYSLOG_PLUGIN_NAME', trim(dirname(plugin_basename(__FILE__)), '/'));
if (!defined('WP2SYSLOG_PLUGIN_DIR'))
define('WP2SYSLOG_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . WP2SYSLOG_PLUGIN_NAME);
if (!defined('WP2SYSLOG_VERSION_NUM'))
define('WP2SYSLOG_VERSION_NUM', '1.0.5');

require_once(WP2SYSLOG_PLUGIN_DIR.'/wp2syslog_settings.php');
require_once(WP2SYSLOG_PLUGIN_DIR.'/wp2syslog_showlog.php');
require_once(WP2SYSLOG_PLUGIN_DIR.'/wp2syslog_events.php');

class WP2SYSLOG{

		protected static $instance = NULL;

		protected $init_status;

		protected $settings;
		protected $events;

		public function __construct()
		{
				//INIT_STATUS
				$this->init_status = get_option('wp2syslog_init');

				//WP2SYSLOG_SETTINGS
				$this->settings = WP2SYSLOG_SETTINGS::get_instance();

				add_action('wp2syslogInit', array($this,'init'));
		}

		public static function get_instance()
		{
				NULL === self::$instance and self::$instance = new self;

				return self::$instance;
		}

		public function get_Triggers()
		{
				return $this->settings->get_wpCoreEvents();
		}

		function init()
		{
				get_role( 'administrator' )->add_cap( 'read_wp2syslog');
				get_role( 'administrator' )->add_cap( 'manage_wp2syslog');
				add_action( 'admin_menu', array($this,'wp2syslogAdminHeader'));

				$wp2syslog_version_num = get_option('wp2syslog_version_num','0.0.0');
				if($wp2syslog_version_num != WP2SYSLOG_VERSION_NUM)
				{
						if (version_compare($wp2syslog_version_num, '1.0.0', '<'))
						{
								$wpCoreEvents=$this->settings->get_wpCoreEvents();
								unset($wpCoreEvents['publish_post']);
								unset($wpCoreEvents['publish_page']);
								unset($wpCoreEvents['register_post']);
								update_option( 'wp2syslog_dotrigger', $wpCoreEvents );

								update_option('wp2syslog_version_num','1.0.0');
						}
						else
						{
								update_option('wp2syslog_version_num', WP2SYSLOG_VERSION_NUM);
						}
						
						//Force a config state and init() again ..
						$this->set_init_status('config');
						$this->init();
						return;
				}

				$check_options=$this->settings->get_options();
				if($this->init_status=='config' && $check_options['db']=='true' )
				{ 
						//OK, let's go to create or upgrade wp2syslog table
						$this->wp2syslog('wp2syslog', __('wp2syslog is going to create table ..', 'wp2syslog'), 2);

						$this->create_table();
						$this->set_init_status('complete');
						$this->wp2syslog('wp2syslog', 'wp2syslog was completely initialised: database table created or upgraded.', 2);
				} else {
						//var_dump("this->init_status: ".$this->init_status);
						//var_dump("INIT: ".get_option('wp2syslog_init'));
						//var_dump("DB: ".$this->settings->get_options()['db']);
				}

				//WP2SYSLOG_EVENTS
				if ('true' == $check_options['coreevents'])
				{
						$this->events = WP2SYSLOG_EVENTS::get_instance();
				}
		}

		private function create_table()
		{
				global $wpdb;
				$charset_collate = $wpdb->get_charset_collate();
				$query =
						"CREATE TABLE {$wpdb->prefix}wp2syslog (
						id BIGINT NOT NULL AUTO_INCREMENT,
						   time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
						   severity ENUM( '0', '1', '2', '3', '4', '5' ) NOT NULL DEFAULT '1',
						   user VARCHAR(15) CHARACTER SET ASCII COLLATE ascii_general_ci NOT NULL,
						   module VARCHAR(30) CHARACTER SET ASCII COLLATE ascii_general_ci NOT NULL,
						   message TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
						   clientip INT UNSIGNED NOT NULL,
						   useragent VARCHAR(500) CHARACTER SET ASCII COLLATE ascii_general_ci NOT NULL DEFAULT 'na',
						   PRIMARY KEY  (id)
								   ) ENGINE = MYISAM $charset_collate;";

				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta($query);
		}

		private function set_init_status($new_init_status)
		{
				$this->init_status=$new_init_status;
				update_option('wp2syslog_init', $this->init_status);
		}

		function wp2syslog($module, $message, $severity=1, $cut=500, $userid=0, $time=0)
		{
				$useragent=filter_var($_SERVER['HTTP_USER_AGENT'],FILTER_SANITIZE_STRING|FILTER_SANITIZE_MAGIC_QUOTES);
				$useragent=strlen($useragent)
						? $useragent
						: 'na';

				$clientip=ip2long($_SERVER['REMOTE_ADDR']);

				$module = substr($module, 0, 30);
				$module = esc_html($module);

				$message = ( is_numeric($cut) && 0 < $cut )
						? substr($message, 0, $cut)
						: substr($message, 0, 500);
				$message = esc_html($message);
				$message = htmlspecialchars($message);

				$user_name = NULL;
				if ( !$userid || !is_integer($userid) )
				{
						$user = wp_get_current_user();
						if ( !empty($user->ID) )
						{
								$userid = $user->ID;
								$user_name = $user->user_login;
						}
						else {
								$userid = preg_replace( '|[^0-9\.]|', '', preg_quote($_SERVER['REMOTE_ADDR'], '|') );
						}
				}
				else
				{
						$user_info = get_userdata($userid);
						$user_name = $user_info->user_login;
				}

				$time = (int)$time;
				if (1181677869 > $time || 2147483647 < $time)
						$time = date('U');

				/* Getting severity. */
				$severity = (int)$severity;
				switch($severity)
				{
						case 0:
								$severityname = "Debug";
								break;
						case 1:
								$severityname = "Notice";
								break;
						case 2:
								$severityname = "Info";
								break;
						case 3:
								$severityname = "Warning";
								break;
						case 4:
								$severityname = "Error";
								break;
						case 5:
								$severityname = "Critical";
								break;
						default:
								$severity = 0;
								$severityname = "UnkwownSeverityCode";
				}

				$block_header = ($user_name !== NULL)
						? "[".long2ip($clientip)." $user_name]"
						: "[".long2ip($clientip)." na]";
				$block_header = htmlspecialchars($block_header);

				/* First log via syslog. */
				openlog($module, LOG_PID, LOG_DAEMON);
				syslog(LOG_WARNING, "$block_header ".home_url()." $severityname: $message");
				closelog();

				/* Then write a record into table. */
				$check_options=$this->settings->get_options();
				if($check_options['db']=='true' && $this->init_status=='complete')
				{
						global $wpdb;
						$result=$wpdb->insert(
										"{$wpdb->prefix}wp2syslog", #tablename
										array(
												'time' => current_time( 'mysql' ),
												'severity' => $severity,
												'user' => $userid,
												'module' => $module,
												'message' => $message,
												'clientip' => ip2long($_SERVER['REMOTE_ADDR']),
												'useragent' => $useragent
											 )
										);

						if ( false === $result)
						{
								syslog(LOG_WARNING, "$block_header $severityname: MYSQLERROR {$wpdb->last_error}");
								return false;
						}
				}
				return true;
		}

		function wp2syslogAdminHeader()
		{
				if ($this->init_status=='complete')
				{
						add_menu_page('Wp2syslog - Show Log', 'Show Log', 'read_wp2syslog', 'showlog','wp2syslog_showlog',plugin_dir_url( __FILE__ ) .'icon.png');
				}
		}

}

add_action('init', array(WP2SYSLOG::get_instance(),'init'));
