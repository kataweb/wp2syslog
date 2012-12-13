<div class="wrap">
<?php

if ( !current_user_can('manage_wp2syslog') )
	echo '<p>'.__('Sorry, this is not for you.', 'wp2syslog').'</p>';
else {

global $wpdb;
require_once('define.php');

echo '<form action="'.$_SERVER['REQUEST_URI'].'" method="post" id="wp2syslog_options">';

if ( 'true' === issetor($_POST['wp2syslog_update_options'] )) {

  $wp2syslog_newoptions = array(
                               'coreevents',
                               'db',
                               'db_init'
                              );
  foreach ($wp2syslog_newoptions as $key)
    $wp2syslog_newoptions[$key] = ('on' == issetor($_POST["wp2syslog_$key"])) ? 'true' : 'false';
  
  $wp2syslog_newoptions['timeformat'] = $wpdb->escape(issetor($_POST['wp2syslog_timeformat']));
  $wp2syslog_newoptions['tableheight'] = $wpdb->escape(issetor($_POST['wp2syslog_tableheight']));
  
  foreach ($wp2syslogTrigger as $key=>$value)
    $wp2syslogTrigger[$key] = ('on' == issetor($_POST["wp2syslog_trigger_$key"])) ? 'true' : 'false';

  $wp2syslog_options = get_option('wp2syslog_options');
  $wp2syslog_trigger = get_option('wp2syslog_dotrigger');
  
  $wp2syslog_trigger_diff1 = array_diff($wp2syslog_trigger, $wp2syslogTrigger);
  $wp2syslog_trigger_diff2 = array_diff($wp2syslogTrigger, $wp2syslog_trigger);
  if ( !empty($wp2syslog_trigger_diff1) || !empty($wp2syslog_trigger_diff2) )
  	wp2syslog('wp2syslog', __('The set of core events to log has been modified.', 'wp2syslog'), 1);
  
  update_option('wp2syslog_options', $wp2syslog_newoptions);
  update_option('wp2syslog_dotrigger', $wp2syslogTrigger);
  
  wp2syslog('wp2syslog', __('wp2syslog configuration has been changed.', 'wp2syslog'), 2);
}

$wp2syslog_options = get_option('wp2syslog_options');
$wp2syslog_trigger = get_option('wp2syslog_dotrigger');


echo '<fieldset>';

echo '<h2>'.__('DB options', 'wp2syslog').'</h2>';

echo '<p><input type="checkbox" name="wp2syslog_db" id="wp2syslog_db"'.($wp2syslog_options['db'] != 'false' ? 'checked="checked"' : '').' />&nbsp;<label for="wp2syslog_db"><strong>'.__('Log events into a db table', 'wp2syslog').'</strong></label><br /></p>';
echo '<p class="blocklabel"><label for="wp2syslog_timeformat">'.__('<strong>Time format</strong> in log table (in <code><a href="http://php.net/date">date()</a></code> format):', 'wp2syslog').'</label> <input type="text" name="wp2syslog_timeformat" id="wp2syslog_timeformat" value="'.$wp2syslog_options['timeformat'].'" size="12"></p>';

echo '<p class="blocklabel"><label for="wp2syslog_tableheight">'.__('<strong>Height of the log data table</strong>, in pixels:', 'wp2syslog').'</label> <input type="text" name="wp2syslog_tableheight" id="wp2syslog_tableheight" value="'.$wp2syslog_options['tableheight'].'" size="5"></p>';

echo '<h2>'.__('Core configuration', 'wp2syslog').'</h2>';
echo '<p><strong>'.__('Note: Please respect your user&#039;s privacy and log only data that you really need.', 'wp2syslog').'</strong></p>';
echo '<p><input type="checkbox" name="wp2syslog_coreevents" id="wp2syslog_coreevents"'.($wp2syslog_options['coreevents'] != 'false' ? 'checked="checked"' : '').' />&nbsp;<label for="wp2syslog_coreevents"><strong>'.__('Log Wordpress core events', 'wp2syslog').'</strong></label><br /></p>';

foreach ($wp2syslog_trigger as $key=>$value)
echo "<input type=\"checkbox\" name=\"wp2syslog_trigger_$key\" id=\"wp2syslog_trigger_$key\"".($value != 'false' ? 'checked="checked"' : '')." />&nbsp;<label for=\"wp2syslog_trigger_$key\"><code>$key</code></label><br />";

echo '<p><input type="hidden" name="wp2syslog_update_options" value="true" /><input type="submit" value="'.__('Save wp2syslog options &raquo;', 'wp2syslog').'" /></p>';
echo '</form>';
}
?>
</div>
