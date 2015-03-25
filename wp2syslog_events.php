<?php

class WP2SYSLOG_EVENTS{

		protected static $instance = NULL;

		protected $logger;

		function __construct(){
				$this->logger=WP2SYSLOG::get_instance();

				foreach($this->logger->get_Triggers() as $event=>$value)
				{
						if($value==='true')
						{
								add_action($event, array($this,"wp2syslog_$event"));
						}
				}
		}

		public static function get_instance()
		{
				NULL === self::$instance and self::$instance = new self;

				return self::$instance;
		}

		function wp2syslog_activated_plugin($plugin, $network_wide=false)
		{
				$sitewide=( $network_wide === true )
						?' sitewide'
						:'';
				$this->logger->wp2syslog('wpcore', __("Plugin $plugin has been activated$sitewide."), 3);
		}

		function wp2syslog_deactivated_plugin($plugins, $silent=false, $network_wide = null )
		{

				$this->logger->wp2syslog('wpcore', __("Plugin $plugins has been deactivated."), 3);
		}

		function wp2syslog_add_attachment($attachment_id)
		{
				if ( is_numeric($attachment_id) )
				{
						$data = get_post($attachment_id);
				}
				$this->logger->wp2syslog('wpcore', sprintf( __('Attachment added. Id: #%1$s, name: %2$s.', 'wp2syslog'), $attachment_id, $data->post_title ), 2);
		}

		function wp2syslog_edit_attachment($attachment_id)
		{
				if ( is_numeric($attachment_id) )
				{
						$data = get_post($attachment_id);
				}
				$this->logger->wp2syslog('wpcore', sprintf( __('Attachment updated. Id: #%1$s, name: %2$s.', 'wp2syslog'), $attachment_id, $data->post_title ), 2);
		}

		function wp2syslog_create_category($term_id)
		{
				if ( is_numeric($term_id) )
				{
						$name = get_cat_name($term_id);
				}
				$this->logger->wp2syslog('wpcore', sprintf( __('Category created. Id: #%1$s, name: %2$s.', 'wp2syslog'), $term_id, $name ), 2);
		}

		function wp2syslog_delete_category($term_id)
		{
				if ( is_numeric($term_id) )
				{
						$name = get_cat_name($term_id);
				}
				$this->logger->wp2syslog('wpcore', sprintf( __('Category deleted. Id: #%1$s.', 'wp2syslog'), $term_id, $name ), 2);
		}

		function wp2syslog_delete_post($ID)
		{
				if ( is_numeric($ID) )
				{
						$postdata = get_post($ID);
				}
				$this->logger->wp2syslog('wpcore', sprintf( __('%1$s deleted. Id: #%2$s, title: %3$s.', 'wp2syslog'), $postdata->post_type, $ID, $postdata->post_title ), 2);
		}

		function wp2syslog_save_post($ID)
		{
				if ( is_numeric($ID) )
				{
						$postdata = get_post($ID);
				}
				$this->logger->wp2syslog('wpcore', sprintf( __('%1$s saved. Id: #%2$s, status: %3$s, title: %4$s.', 'wp2syslog'), $postdata->post_type, $ID, $postdata->post_status, $postdata->post_title ), 2);
		}

		function wp2syslog_private_to_published($post_id)
		{
				if ( is_numeric($post_id) )
				{
						$postdata = get_post($post_id);
				}
				$this->logger->wp2syslog('wpcore', sprintf( __('Post state changed from private to published. Id: #%1$s, name: %2$s.', 'wp2syslog'), $post_id, $postdata->post_title ), 1);
		}

		function wp2syslog_xmlrpc_publish_post($post_id)
		{
				if ( is_numeric($post_id) )
				{
						$postdata = get_post($post_id);
				}
				$this->logger->wp2syslog('wpcore', sprintf( __('Page published via XMLRPC. Id: #%1$s, title: %2$s.', 'wp2syslog'), $post_id, $postdata->post_title ), 2);
		}

		function wp2syslog_comment_id_not_found($comment_post_ID)
		{
				$this->logger->wp2syslog('wpcore', __('Trying to display the comment form of a non-existent post Id: #%1$s.', 'wp2syslog'), $comment_post_ID, 3);
		}

		function wp2syslog_comment_flood_trigger($arg='null', $arg2='null')
		{
				$this->logger->wp2syslog('wpcore', __('Comment flood attempt.', 'wp2syslog'), 3);
		}

		function wp2syslog_comment_post($comment_ID)
		{
				if ( is_numeric($comment_ID) )
				{
						$commentdata = get_comment($comment_ID);
						$name = $commentdata->comment_author;
						$postdata = get_post($commentdata->comment_post_ID);
						$posttitle = $postdata->post_title;

						if (1 == $commentdata->comment_approved)
								$commentstatus = __('approved', 'wp2syslog');
						elseif (0 == $commentdata->comment_approved)
								$commentstatus = __('not approved', 'wp2syslog');
						elseif ('spam' == $commentdata->comment_approved)
								$commentstatus = __('spam', 'wp2syslog');
						else
								$commentstatus = 'undefined';
				}
				$this->logger->wp2syslog('wpcore', sprintf( __('Comment posted. Comment Id: #%1$s, name: %2$s. Post Id: #%3$s, title: %4$s. Comment status: %5$s.', 'wp2syslog'), $comment_ID, $name, $commentdata->comment_post_ID, $posttitle, $commentstatus ), 1);
		}

		function wp2syslog_edit_comment($comment_ID)
		{
				if ( is_numeric($comment_ID) )
				{
						$commentdata = get_comment($comment_ID);
						$name = $commentdata->comment_author;
						$postdata = get_post($commentdata->comment_post_ID);
						$posttitle = $postdata->post_title;

						if (1 == $commentdata->comment_approved)
								$commentstatus = __('approved', 'wp2syslog');
						elseif (0 == $commentdata->comment_approved)
								$commentstatus = __('not approved', 'wp2syslog');
						elseif ('spam' == $commentdata->comment_approved)
								$commentstatus = __('spam', 'wp2syslog');
						else
								$commentstatus = 'undefined';
				}
				$this->logger->wp2syslog('wpcore', sprintf( __('Comment updated. Comment Id: #%1$s, name: %2$s. Post Id: #%3$s, title: %4$s. Comment status is %5$s.', 'wp2syslog'), $comment_ID, $name, $commentdata->comment_post_ID, $posttitle, $commentstatus ), 1);
		}

		function wp2syslog_pingback_post($comment_id)
		{
				if ( is_numeric($comment_id) )
				{
						$commentdata = get_comment($comment_id);
						$url = $commentdata->comment_author_url;
						$postdata = get_post($commentdata->comment_post_ID);
						$posttitle = $postdata->post_title;

						if (1 == $commentdata->comment_approved)
								$commentstatus = __('approved', 'wp2syslog');
						elseif (0 == $commentdata->comment_approved)
								$commentstatus = __('not approved', 'wp2syslog');
						elseif ('spam' == $commentdata->comment_approved)
								$commentstatus = __('spam', 'wp2syslog');
						else
								$commentstatus = 'undefined';
				}
				$this->logger->wp2syslog('wpcore', sprintf( __('Comment via pingback posted. From <a href="%1$s">%1$s</a> to post Id: #%2$s, name: %3$s. Saved as comment #%4$s. Comment status is %5$s.', 'wp2syslog'), $url, $commentdata->comment_post_ID, $posttitle, $comment_id, $commentstatus ), 1);
		}

		function wp2syslog_wp_set_comment_status($comment_id)
		{
				$commentdata = get_comment($comment_id);
				if(is_object($commentdata)){
						$name = $commentdata->comment_author;
						$postdata = get_post($commentdata->comment_post_ID);
						$posttitle = $postdata->post_title;

						if (1 == $commentdata->comment_approved)
								$commentstatus = __('approved', 'wp2syslog');
						elseif (0 == $commentdata->comment_approved)
								$commentstatus = __('not approved', 'wp2syslog');
						elseif ('spam' == $commentdata->comment_approved)
								$commentstatus = __('spam', 'wp2syslog');
						else
								$commentstatus = 'undefined';
						$this->logger->wp2syslog('wpcore', sprintf( __('Comment status changed. Comment Id: #%1$s, name: %2$s Post Id: #%3$s, title: %4$s. New status: %5$s.', 'wp2syslog'), $comment_id, $name, $commentdata->comment_post_ID, $posttitle, $commentstatus ), 1);

				}
		}

		function wp2syslog_switch_theme($new_name)
		{
				$this->logger->wp2syslog('wpcore', sprintf( __('Theme switched to %s.', 'wp2syslog'), $new_name ), 3);
		}

		function wp2syslog_delete_user($id, $reassign=NULL)
		{
				$user = get_userdata($id);
				if(is_numeric($reassign))
				{
						$reassign_user=get_userdata($reassign);
				}
				$this->logger->wp2syslog('wpcore', sprintf( __('User deleted. Id: #%s. User login: %s.', 'wp2syslog'), $id, $user->user_login), 3);
		}

		function wp2syslog_retrieve_password($user_login)
		{
				$user = get_user_by( 'login', $user_login);
				$this->logger->wp2syslog('wpcore', sprintf( __('Password created and sent to user %1$s (%2$s).', 'wp2syslog'), $user->display_name, $user->user_login ), 2);
		}

		function wp2syslog_user_register($user_id)
		{
				$user = get_userdata($user_id);
				$this->logger->wp2syslog('wpcore', sprintf( __('New user successfully registered. Name: %1$s (%2$s).', 'wp2syslog'), $user->display_name, $user->user_login), 3);
		}

		function wp2syslog_personal_options_update($user_id)
		{
				$user=get_userdata( $user_id );

				$this->logger->wp2syslog('wpcore', sprintf( __('User personal options changed. User login: %1$s name: %2$s.', 'wp2syslog'), $user->user_login, $user->display_name ), 1);
		}

		function wp2syslog_profile_update($user_id)
		{
				$user = get_userdata($user_id);
				$this->logger->wp2syslog('wpcore', sprintf( __('User profile changed. User name: %1$s (%2$s).', 'wp2syslog'), $user->display_name, $user->user_login), 1);
		}

		function wp2syslog_wp_login($user_login)
		{
				$user=get_user_by('login',$user_login);
				$this->logger->wp2syslog('wpcore', sprintf( __('User logged in. User name: %1$s (%2$s).', 'wp2syslog'), $user->display_name, $user->user_login), 2, 500, $user->ID);
		}

		function wp2syslog_wp_login_failed($username)
		{
				$this->logger->wp2syslog('wpcore', sprintf( __('User authentication failed. User name: %s.', 'wp2syslog'), $username ), 2);
		}

		function wp2syslog_wp_logout()
		{
				$user = wp_get_current_user();
				$this->logger->wp2syslog('wpcore', sprintf( __('User logged out. User name: %1$s (%2$s).', 'wp2syslog'), $user->display_name, $user->user_login ), 2);
		}

		function wp2syslog_generate_rewrite_rules()
		{
				if ( empty($_POST) )
						$this->logger->wp2syslog('wpcore', __('The rewrite rules have been newly calculated and saved.', 'wp2syslog'), 3); // Permalink page was opened, hence rewrite rules by plugins were inserted
				else
						$this->logger->wp2syslog('wpcore', __('The permalink options and rewrite rules have been modified and saved.', 'wp2syslog'), 3); // Permalink options are saved
		}
}
