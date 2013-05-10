<?php

$wp2syslogDoTrigger = get_option('wp2syslog_dotrigger');

$wp2syslog_eventTriggered=array_fill_keys($wp2syslogDoTrigger,false);

add_action('activated_plugin', 'wp2syslog_activated_plugin', 50);
function wp2syslog_activated_plugin($arg='null', $arg2='null') {
  $sitewide=( $arg2 ===true )?' sitewide':'';
  wp2syslog('wp2syslog', __("Plugin $arg has been activated$sitewide."), 3);
}

add_action('deactivated_plugin', 'wp2syslog_deactivated_plugin', 50);
function wp2syslog_deactivated_plugin($arg='null', $arg2='null') {
	wp2syslog('wp2syslog', __("Plugin $arg has been deactivated."), 3);
}

add_action('add_attachment', 'wp2syslog_add_attachment', 50);
function wp2syslog_add_attachment($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['add_attachment']) {
		$name = $arg;
		if ( is_numeric($arg) ) {
			$postdata = get_post($arg);
			$name = $postdata->post_title;
		}
		wp2syslog('core', sprintf( __('Attachment added. Id: #%1$s, name: %2$.', 'wp2syslog'), $arg, $name ), 2);
		$wp2syslog_eventTriggered['add_attachment'] = true;
	}
}

add_action('delete_attachment', 'wp2syslog_delete_attachment', 50);
function wp2syslog_delete_attachment($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['delete_attachment']) {
		wp2syslog('core', sprintf( __('Attachment deleted. Id: #%s.', 'wp2syslog'), $arg ), 2);
		$wp2syslog_eventTriggered['delete_attachment'] = true;
	}
}

add_action('edit_attachment', 'wp2syslog_edit_attachment', 50);
function wp2syslog_edit_attachment($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['edit_attachment']) {
		$name = $arg;
		if ( is_numeric($arg) ) {
			$postdata = get_post($arg);
			$name = $postdata->post_title;
		}
		wp2syslog('core', sprintf( __('Attachment updated. Id: #%1$s, name: %2$s.', 'wp2syslog'), $arg, $name ), 2);
		$wp2syslog_eventTriggered['edit_attachment'] = true;
	}
}

add_action('create_category', 'wp2syslog_create_category', 50);
function wp2syslog_create_category($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['create_category']) {
		$name = $arg;
		if ( is_numeric($arg) ) {
			$name = get_cat_name($arg);
		}
		wp2syslog('core', sprintf( __('Category created. Id: #%1$s, name: %2$s.', 'wp2syslog'), $arg, $name ), 2);
		$wp2syslog_eventTriggered['create_category'] = true;
	}
}

add_action('delete_category', 'wp2syslog_delete_category', 50);
function wp2syslog_delete_category($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['delete_category']) {
		wp2syslog('core', sprintf( __('Category deleted. Id: #%s.', 'wp2syslog'), $arg ), 2);
		$wp2syslog_eventTriggered['delete_category'] = true;
	}
}

add_action('delete_post', 'wp2syslog_delete_post', 50);
function wp2syslog_delete_post($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['delete_post']) {
		wp2syslog('core', sprintf( __('Post deleted. Id: #%s.', 'wp2syslog'), $arg ), 2);
		$wp2syslog_eventTriggered['delete_post'] = true;
	}
}

add_action('save_post', 'wp2syslog_save_post', 50);
function wp2syslog_save_post($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['save_post']) {
		$name = $arg;
		if ( is_numeric($arg) ) {
			$postdata = get_post($arg);
			$name = $postdata->post_title;
		}
		wp2syslog('core', sprintf( __('Post saved. Id: #%1$s, name: %2$s.', 'wp2syslog'), $arg, $name ), 1);
		$wp2syslog_eventTriggered['save_post'] = true;
	}
}

add_action('private_to_published', 'wp2syslog_private_to_published', 50);
function wp2syslog_private_to_published($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['private_to_published']) {
		$name = $arg;
		if ( is_numeric($arg) ) {
			$postdata = get_post($arg);
			$name = $postdata->post_title;
		}
		wp2syslog('core', sprintf( __('Post state changed from private to published. Id: #%1$s, name: %2$s.', 'wp2syslog'), $arg, $name ), 1);
		$wp2syslog_eventTriggered['private_to_published'] = true;
	}
}

add_action('publish_post', 'wp2syslog_publish_post', 50);
function wp2syslog_publish_post($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['publish_post']) {
		$name = $arg;
		if ( is_numeric($arg) ) {
			$postdata = get_post($arg);
			$name = $postdata->post_title;
		}
		wp2syslog('core', sprintf( __('Post published (or edited). Id: #%1$s, name: %2$s.', 'wp2syslog'), $arg, $name ), 2);
		$wp2syslog_eventTriggered['publish_post'] = true;
	}
}

add_action('publish_page', 'wp2syslog_publish_page', 50);
function wp2syslog_publish_page($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['publish_page']) {
		$name = $arg;
		if ( is_numeric($arg) ) {
			$postdata = get_post($arg);
			$name = $postdata->post_title;
		}
		wp2syslog('core', sprintf( __('Page published (or edited). Id: #%1$s, name: %2$s.', 'wp2syslog'), $arg, $name ), 2);
		$wp2syslog_eventTriggered['publish_page'] = true;
	}
}

add_action('xmlrpc_publish_post', 'wp2syslog_xmlrpc_publish_post', 50);
function wp2syslog_xmlrpc_publish_post($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['xmlrpc_publish_post']) {
		$name = $arg;
		if ( is_numeric($arg) ) {
			$postdata = get_post($arg);
			$name = $postdata->post_title;
		}
		wp2syslog('core', sprintf( __('Page published via XMLRPC. Id: #%1$s, name: %2$s.', 'wp2syslog'), $arg, $name ), 2);
		$wp2syslog_eventTriggered['xmlrpc_publish_post'] = true;
	}
}

add_action('comment_id_not_found', 'wp2syslog_comment_id_not_found', 50);
function wp2syslog_comment_id_not_found($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['comment_id_not_found']) {
		wp2syslog('core', __('Trying to display the comment form of a non-existent post.', 'wp2syslog'), 3);
		$wp2syslog_eventTriggered['comment_id_not_found'] = true;
	}
}

add_action('comment_flood_trigger', 'wp2syslog_comment_flood_trigger', 50);
function wp2syslog_comment_flood_trigger($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['comment_flood_trigger']) {
		wp2syslog('core', __('Comment flood attempt.', 'wp2syslog'), 3);
		$wp2syslog_eventTriggered['comment_flood_trigger'] = true;
	}
}

add_action('comment_post', 'wp2syslog_comment_post', 50);
function wp2syslog_comment_post($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['comment_post']) {
		$name = $arg;
		if ( is_numeric($arg) ) {
			$commentdata = get_comment($arg);
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
		wp2syslog('core', sprintf( __('Comment posted. Comment Id: #%1$s, name: %2$s. Post Id: #%3$s, name: %4$s. Comment status: %5$s.', 'wp2syslog'), $arg, $name, $commentdata->comment_post_ID, $posttitle, $commentstatus ), 1);
		$wp2syslog_eventTriggered['comment_post'] = true;
	}
}

add_action('edit_comment', 'wp2syslog_edit_comment', 50);
function wp2syslog_edit_comment($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['edit_comment']) {
		$name = $arg;
		if ( is_numeric($arg) ) {
			$commentdata = get_comment($arg);
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
		wp2syslog('core', sprintf( __('Comment updated. Comment Id: #%1$s, name: %2$s. Post Id: #%3$s, name: %4$s. Comment status is %5$s.', 'wp2syslog'), $arg, $name, $commentdata->comment_post_ID, $posttitle, $commentstatus ), 1);
		$wp2syslog_eventTriggered['edit_comment'] = true;
	}
}

add_action('pingback_post', 'wp2syslog_pingback_post', 50);
function wp2syslog_pingback_post($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['pingback_post']) {
		$url = $name = $arg;
		if ( is_numeric($arg) ) {
			$commentdata = get_comment($arg);
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
		wp2syslog('core', sprintf( __('Comment via pingback posted. From <a href="%1$s">%1$s</a> to post Id: #%2$s, name: %3$s. Saved as comment #%4$s. Comment status is %5$s.', 'wp2syslog'), $url, $commentdata->comment_post_ID, $posttitle, $arg, $commentstatus ), 1);
		$wp2syslog_eventTriggered['pingback_post'] = true;
	}
}

add_action('wp_set_comment_status', 'wp2syslog_wp_set_comment_status', 50);
function wp2syslog_wp_set_comment_status($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['wp_set_comment_status']) {
		$name = $arg;
		if ( is_numeric($arg) ) {
			$commentdata = get_comment($arg);
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
		wp2syslog('core', sprintf( __('Comment status changed. Comment Id: #%1$s by %2$s on post Id: #%3$s, name: %4$s. New status: %5$s.', 'wp2syslog'), $arg, $name, $commentdata->comment_post_ID, $posttitle, $commentstatus ), 1);
		$wp2syslog_eventTriggered['wp_set_comment_status'] = true;
	}
}

add_action('switch_theme', 'wp2syslog_switch_theme', 50);
function wp2syslog_switch_theme($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['switch_theme']) {
		wp2syslog('core', sprintf( __('Theme switched to %s.', 'wp2syslog'), $arg ), 3);
		$wp2syslog_eventTriggered['switch_theme'] = true;
	}
}

add_action('delete_user', 'wp2syslog_delete_user', 50);
function wp2syslog_delete_user($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['delete_user']) {
                $user = get_userdata($arg);
		wp2syslog('core', sprintf( __('User deleted. Id: #%s. User login: %s', 'wp2syslog'), $arg, $user->user_login ), 3);
		$wp2syslog_eventTriggered['delete_user'] = true;
	}
}

add_action('retrieve_password', 'wp2syslog_retrieve_password', 50);
function wp2syslog_retrieve_password($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['retrieve_password']) {
                $user = get_user_by( 'login', $arg);
                $user = $user->ID;
                $user = get_the_author_meta('display_name', $user);
		wp2syslog('core', sprintf( __('Password created and sent to user %1$s (%2$s).', 'wp2syslog'), $user, $arg ), 2);
		$wp2syslog_eventTriggered['retrieve_password'] = true;
	}
}

add_action('register_post', 'wp2syslog_register_post', 50);
function wp2syslog_register_post($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['register_post']) {
		wp2syslog('core', __('New user attempt to register.', 'wp2syslog'), 1);
		$wp2syslog_eventTriggered['register_post'] = true;
	}
}

add_action('user_register', 'wp2syslog_user_register', 50);
function wp2syslog_user_register($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['user_register']) {
		$user = get_userdata($arg);
		wp2syslog('core', sprintf( __('New user successfully registered. Name: %1$s (%2$s).', 'wp2syslog'), $user->display_name, $user->user_login ), 3);
		$wp2syslog_eventTriggered['user_register'] = true;
	}
}

add_action('personal_options_update', 'wp2syslog_personal_options_update', 50);
function wp2syslog_personal_options_update($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['personal_options_update']) {
		wp2syslog('core', sprintf( __('User personal options changed. User name: %s.', 'wp2syslog'), $arg ), 1);
		$wp2syslog_eventTriggered['personal_options_update'] = true;
	}
}

add_action('profile_update', 'wp2syslog_profile_update', 50);
function wp2syslog_profile_update($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['profile_update']) {
		$user = get_userdata($arg);
		wp2syslog('core', sprintf( __('User profile changed. User name: %s.', 'wp2syslog'), $user->display_name ), 1);
		$wp2syslog_eventTriggered['profile_update'] = true;
	}
}

add_action('wp_login', 'wp2syslog_wp_login', 50);
function wp2syslog_wp_login($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['wp_login']) {
		$user = get_profile('display_name', $arg);
		$userid = get_profile('id', $arg);

		$wp2syslog_options = get_option('wp2syslog_options');
		wp2syslog('core', sprintf( __('User logged in. User name: %1$s (%2$s).', 'wp2syslog'), $user, $arg ), 2, 500, $userid);
		$wp2syslog_eventTriggered['wp_login'] = true;
	}
}

add_action('wp_login_failed', 'wp2syslog_wp_login_failed', 50);
function wp2syslog_wp_login_failed($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['wp_login_failed']) {
		wp2syslog('core', sprintf( __('User authentication failed. User name: %s.', 'wp2syslog'), $arg ), 2);
		$wp2syslog_eventTriggered['wp_login_failed'] = true;
	}
}

add_action('wp_logout', 'wp2syslog_wp_logout', 50);
function wp2syslog_wp_logout($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['wp_logout']) {
		$user = wp_get_current_user();
		wp2syslog('core', sprintf( __('User logged out. User name: %1$s (%2$s).', 'wp2syslog'), $user->display_name, $user->user_login ), 2);
		$wp2syslog_eventTriggered['wp_logout'] = true;
	}
}

add_action('generate_rewrite_rules', 'wp2syslog_generate_rewrite_rules', 50);
function wp2syslog_generate_rewrite_rules($arg='null', $arg2='null') {
	global $wp2syslog_eventTriggered, $wp2syslogDoTrigger;
	if ('true' === $wp2syslogDoTrigger['generate_rewrite_rules'] ) {
		if ( empty($_POST) )
			wp2syslog('core', __('The rewrite rules have been newly calculated and saved.', 'wp2syslog'), 3); // Permalink page was opened, hence rewrite rules by plugins were inserted
		else
			wp2syslog('core', __('The permalink options and rewrite rules have been modified and saved.', 'wp2syslog'), 3); // Permalink options are saved
		$wp2syslog_eventTriggered['generate_rewrite_rules'] = true;
	}
}

?>
