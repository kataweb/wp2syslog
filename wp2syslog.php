<?php
/*
Plugin Name: wp2syslog
Plugin URI: https://github.com/kataweb/wp2syslog
Description: It keeps track of wordpress's events and log them to syslog.
Author: psicosi448
Version: 0.2.3
Author URI: http://www.kataweb.it
*/

/*

	wp2syslog -- Global logging facility for WordPress
        (WPsyslog revisited)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation in the Version 3.

*/

function wp2syslogInit() {
  get_role( 'administrator' )->add_cap( 'read_wp2syslog');
  get_role( 'administrator' )->add_cap( 'manage_wp2syslog');

  $wp2syslogStatus = get_option('wp2syslog_init');

  if ( empty($wp2syslogStatus) ) {
    #First execution.. lets go to create configuration default
    $wp2syslog_options = array(
      'coreevents' => 'true',
      'db' => 'false',
      'db_init' => 'false',
      'timeformat' => 'Y-m-d H:i:s',
      'tableheight' => '400px'
    );
    update_option('wp2syslog_options', $wp2syslog_options);

    require_once('define.php');
    update_option('wp2syslog_dotrigger', $wp2syslogTrigger);
    update_option('wp2syslog_init', 'config');
    wp2syslog('wp2syslog', __('wp2syslog was successfully initialised: default options added.', 'wp2syslog'), 2);
  }
  elseif($wp2syslogStatus!='complete'){
    #check if we need and finally create it
    $wp2syslog_options = get_option('wp2syslog_options');

    if($wp2syslog_options['db']=='true' && $wp2syslog_options['db_init']=='false'){

      global $wpdb;
      $query = "DROP TABLE IF EXISTS `{$wpdb->prefix}wp2syslog`";
      $wpdb->query($query);
      
      $query =
      "CREATE TABLE `{$wpdb->prefix}wp2syslog` (
      	`id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      	`time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      	`severity` ENUM( '0', '1', '2', '3', '4', '5' ) NOT NULL DEFAULT '1',
      	`user` VARCHAR(15) CHARACTER SET ASCII COLLATE ascii_general_ci NOT NULL,
      	`module` VARCHAR(30) CHARACTER SET ASCII COLLATE ascii_general_ci NOT NULL,
      	`message` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
      ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
      
      $createtable = $wpdb->query($query);
      if ($createtable === false) {
      	update_option('wp2syslog_init', 'false');
      	echo '<pre>'.mysql_error().'</pre><br />Error creating tables! Aborting.';
      	return false;
      } else {
        $wp2syslog_options['db_init']=='true';
        update_option('wp2syslog_options', $wp2syslog_options);
      	update_option('wp2syslog_init', 'complete');
      	wp2syslog('wp2syslog', __('wp2syslog was completely initialised: database table created.', 'wp2syslog'), 2);
      }
    }
  }
}

function wp2syslogAdminMenu() {
  add_menu_page('wp2syslog', 'wp2syslog', 'read_wp2syslog', 'wp2syslog/wp2syslog_admin_config.php');
  $wp2syslogStatus = get_option('wp2syslog_init');
  if ($wp2syslogStatus=='complete') {
    add_submenu_page('wp2syslog/wp2syslog_admin_config.php', 'wp2syslog', 'Show log', 'manage_wp2syslog', 'wp2syslog/wp2syslog_admin_data.php');
  }
}

function wp2syslog($module, $message, $severity=1, $cut=500, $userid=0, $time=0 ) {

  $wp2syslog_options = get_option('wp2syslog_options');
  global $wpdb;
  
  $module = substr($module, 0, 30);
  $module = $wpdb->escape($module);
  
  $message = ( is_numeric($cut) && 0 < $cut )
  	 ? substr($message, 0, $cut)
  	 : substr($message, 0, 500);
  $message = $wpdb->escape($message);
  $message = htmlspecialchars($message);
	
  $user_name = NULL;
  if ( !$userid || !is_integer($userid) ) {
    $user = wp_get_current_user();
    if ( !empty($user->ID) ) {
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

  $time_mysql = date('Y-m-d H:i:s', $time);
  
  $severity = (int)$severity;
  if (0 > $severity || 5 < $severity)
  	$severity = 1;


  /* Setting remote ip. */
  $remote_ip = NULL;
  if(isset($_SERVER['REMOTE_ADDR']) && strlen($_SERVER['REMOTE_ADDR']) > 4)
  {
      $remote_ip = $_SERVER['REMOTE_ADDR'];
  }
  else
  {
      $remote_ip = "Local";
  }


  /* Setting header block */
  $block_header = NULL;
  if($user_name !== NULL)
  {
      $block_header = '['.$remote_ip.' '.$user_name.']';
  }
  else
  {
      $block_header = '['.$remote_ip.' na]';
  }

  /* Making sure we escape everything. */
  $block_header = htmlspecialchars($block_header);


  /* Getting severity. */
  $severityname = "Info";
  if($severity == 0)      $severityname = "Debug";
  else if($severity == 1) $severityname = "Notice";
  else if($severity == 2) $severityname = "Info";
  else if($severity == 3) $severityname = "Warning";
  else if($severity == 4) $severityname = "Error";
  else if($severity == 5) $severityname = "Critical";

  /* First log via syslog. */
  openlog($module, LOG_PID, LOG_DAEMON);
  syslog(LOG_WARNING, "$block_header ".home_url()." $severityname: $message");
  closelog();

  if($wp2syslog_options['db']=='true'){
    $wp2syslogStatus = get_option('wp2syslog_init');
    if ($wp2syslogStatus!='complete') return true; #UGLY: first activation hasn't already created table

    $query = "
    INSERT INTO `{$wpdb->prefix}wp2syslog` (
    	`time`,
    	`severity`,
    	`user`,
    	`module`,
    	`message`
    )
    VALUES (
    	'$time_mysql',
    	'$severity',
    	'$userid',
    	'$module',
    	'$message'
    )";
    
    $result = $wpdb->query($query);
    
    if ( false === $result) {
      syslog(LOG_WARNING, "$block_header $severityname: MYSQLERROR ".mysql_error());
      return false;
    }
  }
  return true;
}

function wp2syslogAdminHeader() {
  $wp2syslog_options = get_option('wp2syslog_options');
  ?>
  <link rel="stylesheet" href="<?php bloginfo('url') ?>/wp-content/plugins/wp2syslog/wp2syslog.css" type="text/css" media="screen" />
  <style type="text/css">#wp2syslog_data { height: <?php echo $wp2syslog_options['tableheight'] ?>; }</style>
  <?php
}

#function wp2syslogCaps($caps) {
#  $caps[] = 'manage_wp2syslog';
#  $caps[] = 'read_wp2syslog';
#  return $caps;
#}
#add_filter('capabilities_list', 'wp2syslogCaps');

add_action('admin_head', 'wp2syslogAdminHeader');
add_action('init', 'wp2syslogInit');
add_action('admin_menu', 'wp2syslogAdminMenu');

$wp2syslog_options = get_option('wp2syslog_options');
if ( 'true' == $wp2syslog_options['coreevents'])
  include_once(ABSPATH.'/wp-content/plugins/wp2syslog/wp2syslog_events.php');
?>
