<?php
class WP2SYSLOG_SETTINGS{

		protected static $instance = NULL;

		private $options;
		private $wpCoreEvents;

		public function __construct()
		{
				if( false === get_option( 'wp2syslog_options' ) )
				{
						$options= array(
										'coreevents'=>'true',
										'db'=>'false',
										'timeformat'=>'Y-m-d H:i:s',
										'tableheight'=>'600px'
									   );
						add_option( 'wp2syslog_options', $options);
				}
				if( false === get_option( 'wp2syslog_dotrigger' ) )
				{
						$wpCoreEvents = array(
										'activated_plugin' => 'true',
										'add_attachment' => 'true',
										'comment_flood_trigger' => 'true',
										'comment_id_not_found' => 'true',
										'comment_post' => 'true',
										'create_category' => 'true',
										'deactivated_plugin' => 'true',
										'delete_category' => 'true',
										'delete_post' => 'true',
										'delete_user' => 'true',
										'edit_attachment' => 'true',
										'edit_comment' => 'true',
										'generate_rewrite_rules' => 'true',
										'personal_options_update' => 'true',
										'pingback_post' => 'true',
										'private_to_published' => 'true',
										'profile_update' => 'true',
										'retrieve_password' => 'true',
										'save_post' => 'true',
										'switch_theme' => 'true',
										'user_register' => 'true',
										'wp_login_failed' => 'true',
										'wp_login' => 'true',
										'wp_logout' => 'true',
										'wp_set_comment_status' => 'true',
										'xmlrpc_publish_post' => 'true'
												);
						add_option( 'wp2syslog_dotrigger', $wpCoreEvents );
				}
				$this->init();

				add_action('wp2syslog_logIntoDB',array($this,'logIntoDB'));
				add_action('wp2syslog_logCoreEvents',array($this,'logCoreEvents'));
		}

		public static function get_instance()
		{
				NULL === self::$instance and self::$instance = new self;

				return self::$instance;
		}

		public function init()
		{
				$this->options=get_option('wp2syslog_options');
				$this->wpCoreEvents=get_option('wp2syslog_dotrigger');
		}

		public function get_wpCoreEvents()
		{
				return $this->wpCoreEvents;
		}

		public function get_options()
		{
				return $this->options;
		}

		public function logCoreEvents()
		{
				$this->options['coreevents']='true';
				update_option('wp2syslog_options',$this->options);
		}

		public function logIntoDB()
		{
				$this->options['db']='true';
				update_option('wp2syslog_options',$this->options);
		}

		public function wp2syslog_add_admin_menu()
		{

				add_options_page( 'Wp2syslog - Options', 'Wp2syslog', 'manage_options', 'Wp2syslog', array($this,'wp2syslog_options_page') );
				if($this->options['coreevents']==='true')
				{
						add_options_page( 'Wp2syslog - Configure core events', 'Wp2syslog Events', 'manage_options', 'Wp2syslogEvents', array($this,'wp2syslog_core_page') );
				}
		}

		public function wp2syslog_settings_init()
		{
				//For first run ...
				register_setting( 'WP2SYSLOGoptions', 'wp2syslog_options', array($this,'wp2syslog_validateOptions'));
				add_settings_section(
								'wp2syslog_options_section',
								__( 'WP2SYSLOG options', 'wordpress' ),
								array($this,'wp2syslog_settings_section_callback'),
								'WP2SYSLOGoptions'
								);

				add_settings_field(
								'coreevents',
								__( 'Log Wordpress core events', 'wordpress' ),
								array($this,'wp2syslog_checkbox_core_render'),
								'WP2SYSLOGoptions',
								'wp2syslog_options_section'
								);
				add_settings_field(
								'db',
								__( 'Log events into a db table', 'wordpress' ),
								array($this,'wp2syslog_checkbox_db_render'),
								'WP2SYSLOGoptions',
								'wp2syslog_options_section'
								);
				add_settings_field(
								'timeformat',
								'Time format in log table (in date() format):',
								array($this,'wp2syslog_text_timeformat_render'),
								'WP2SYSLOGoptions',
								'wp2syslog_options_section'
								);
				add_settings_field(
								'tableheight',
								'Height of the log data table, in pixels:',
								array($this,'wp2syslog_text_tableheight_render'),
								'WP2SYSLOGoptions',
								'wp2syslog_options_section'
								);

				register_setting( 'WP2SYSLOGoptionsCORE', 'wp2syslog_dotrigger', array($this,'wp2syslog_validateCore') );
				add_settings_section(
								'wp2syslog_optionsCORE_section',
								'Wordpress Core actions',
								array($this,'wp2syslog_settings_COREsection_callback'),
								'WP2SYSLOGoptionsCORE'
								);

				foreach($this->wpCoreEvents as $action_name=>$default_value)
				{
						add_settings_field(
										"$action_name",
										"$action_name",
										array($this,'wp2syslog_checkbox_actions_render'),
										'WP2SYSLOGoptionsCORE',
										'wp2syslog_optionsCORE_section',
										$action_name
										);
				}
		}

		public function wp2syslog_validateOptions( $input )
		{
				foreach($this->options as $option)
				{
						if(!array_key_exists('coreevents',$input))
						{
								$input['coreevents']='false';
						}
						elseif(!array_key_exists('db',$input))
						{
								$input['db']='false';
						}
						elseif(!array_key_exists('tableheight',$input))
						{
								$input['tableheight']='600px';
						}
						$input['tableheight']=filter_var($input['tableheight'],FILTER_SANITIZE_NUMBER_INT);
				}
				$this->options=$input;

				return $input;
		}

		public function wp2syslog_validateCore( $input )
		{
				foreach($this->wpCoreEvents as $action_name=>$value)
				{
						if(!array_key_exists($action_name,$input))
						{
								$input[$action_name]='false';
						}
				}
				ksort($input);
				$this->wpCoreEvents=$input;

				return $input;
		}

		public function wp2syslog_checkbox_actions_render($action_name)
		{
				echo "<input type='checkbox' name='wp2syslog_dotrigger[$action_name]' ".checked( $this->wpCoreEvents[$action_name], 'true', false )."  value='true' />";
		}

		public function wp2syslog_checkbox_core_render()
		{
				echo "<input type='checkbox' name='wp2syslog_options[coreevents]' ".checked( $this->options['coreevents'], 'true',false )."  value='true' />";
		}
		public function wp2syslog_checkbox_db_render()
		{
				echo "<input type='checkbox' name='wp2syslog_options[db]' ".checked( $this->options['db'], 'true',false )."  value='true' />";
		}
		public function wp2syslog_text_timeformat_render()
		{
				echo "<input type='text' name='wp2syslog_options[timeformat]' value='{$this->options['timeformat']}'>";
		}

		public function wp2syslog_text_tableheight_render()
		{
				echo "<input type='text' name='wp2syslog_options[tableheight]' value='{$this->options['tableheight']}'>";
		}

		public function wp2syslog_settings_DBsection_callback()
		{
				echo __( 'DB section description', 'wordpress' );
		}

		public function wp2syslog_settings_section_callback()
		{
				echo 'Description';
		}

		public function wp2syslog_settings_COREsection_callback()
		{
				echo 'Note: Please respect your user\'s privacy and log only data that you really need.';
		}

		public function wp2syslog_core_page()
		{

				?>
						<form action='options.php' method='post'>

						<h2>wp2syslog</h2>

						<?php
						settings_fields( 'WP2SYSLOGoptionsCORE' );
				do_settings_sections( 'WP2SYSLOGoptionsCORE' );
				submit_button();
				?>

						</form>
						<?php

		}
		public function wp2syslog_options_page()
		{

				?>
						<form action='options.php' method='post'>

						<h2>wp2syslog</h2>

						<?php
						settings_fields( 'WP2SYSLOGoptions' );
				do_settings_sections( 'WP2SYSLOGoptions' );
				submit_button();
				?>

						</form>
						<?php
		}
}

add_action('admin_init', array(WP2SYSLOG_SETTINGS::get_instance(),'wp2syslog_settings_init'));
add_action('admin_menu', array(WP2SYSLOG_SETTINGS::get_instance(),'wp2syslog_add_admin_menu'));

