<?php
$wp2syslogTrigger = array(
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
  'edit_comment' => 'true',
  'generate_rewrite_rules' => 'true',
  'personal_options_update' => 'true',
  'pingback_post' => 'true',
  'private_to_published' => 'true',
  'profile_update' => 'true',
  'publish_page' => 'true',
  'publish_post' => 'true',
  'register_post' => 'true',
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

function issetor(&$variable, $or = NULL) {
    return $variable === NULL ? $or : $variable;
}
?>
