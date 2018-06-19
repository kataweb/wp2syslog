<?php
function sanitize_text_or_array_field( $array_or_string ) {
	if( is_string( $array_or_string ) )
	{
		$array_or_string = sanitize_text_field( $array_or_string );
	}
	elseif( is_array($array_or_string) )
	{
		foreach ( $array_or_string as $key => &$value ) {
			if ( is_array( $value ) ) 
			{
				$value = sanitize_text_or_array_field( $value );
			}
			else {
				$value = sanitize_text_field( $value );
			}
		}
	}

	return isset( $array_or_string )? $array_or_string : '';
}

function sanitize_wp2syslog_POST()
{
	foreach( $_POST as $key=>$value )
	{
		switch($key) 
		{
			case 'wp2syslog_filter_do':
			case 'wp2syslog_filter_search':
			case 'wp2syslog_filter_modules':
			case 'wp2syslog_filter_levels':
			case 'wp2syslog_filter_users':
			case 'wp2syslog_filter_clientip':
			case 'wp2syslog_filter_timestart':
			case 'wp2syslog_filter_timeend':
			case 'wp2syslog_filter_orderby':
			case 'wp2syslog_filter_limit':
				$post_values[$key] = sanitize_text_or_array_field($value);
				break;
			case 'wp2syslog_filter_andor1':
			case 'wp2syslog_filter_andor2':
			case 'wp2syslog_filter_andor3':
			case 'wp2syslog_filter_andor4':
			case 'wp2syslog_filter_andor6':
				$post_values[$key] = ('OR' === $value)
					? 'OR'
					: 'AND';
				break;
			case 'wp2syslog_filter_order':
				$post_values[$key] = ('ASC' === $value)
					? 'ASC'
					: 'DESC';
				break;

			default:
				wp_die("Uh ?! Cheating ? [$key]");
		}
	}

	return isset( $post_values )? $post_values : array();
}

function validate_wp2syslog_POST()
{
	//SANITIZE EARLY..
	$post_values=sanitize_wp2syslog_POST();

	//VALIDATE..
	$default_values = array(
			'wp2syslog_filter_search'=>''
			,'wp2syslog_filter_andor1'=>'AND'
			,'wp2syslog_filter_andor2'=>'AND'
			,'wp2syslog_filter_andor3'=>'AND'
			,'wp2syslog_filter_andor4'=>'AND'
			,'wp2syslog_filter_andor6'=>'AND'
			,'wp2syslog_filter_modules'=>'all'
			,'wp2syslog_filter_levels'=>'all'
			,'wp2syslog_filter_users'=>'all'
			,'wp2syslog_filter_clientip'=>'all'
			,'wp2syslog_filter_timestart'=>'1181677869'
			,'wp2syslog_filter_timeend'=>'2147483647'
			,'wp2syslog_filter_limit'=>'100'
			,'wp2syslog_filter_orderby'=>'id'
			,'wp2syslog_filter_order'=>'ASC'
			);
	$post_values=array_merge($default_values,$post_values);

	if ( is_array($post_values['wp2syslog_filter_levels']) )
	{
		foreach ($post_values['wp2syslog_filter_levels'] as $k=>$v){
			$v=filter_var($v,FILTER_SANITIZE_NUMBER_INT);
			$post_values['wp2syslog_filter_levels'][$k] = (0 <= $v && 5 >= $v ) 
				? $v
				: '10';
		}
	}

	if ( is_array($post_values['wp2syslog_filter_users']) )
	{
		foreach ($post_values['wp2syslog_filter_users'] as $k=>$v){
			$v=filter_var($v,FILTER_SANITIZE_NUMBER_INT);
			$post_values['wp2syslog_filter_users'][$k] = (0 < $v) 
				? $v 
				: '0';
		}
	}

	if ( is_array($post_values['wp2syslog_filter_clientip']) )
	{
		foreach ($post_values['wp2syslog_filter_clientip'] as $k=>$v){
			$v=filter_var($v,FILTER_SANITIZE_NUMBER_INT);
			$post_values['wp2syslog_filter_clientip'][$k] = $v;
		}
	}

	// Where should the timeframe start?
	$timeStart = preg_replace('|[^0-9:\s-]|', '', $post_values['wp2syslog_filter_timestart']);
	$post_values['wp2syslog_filter_timestart'] = strtotime($post_values['wp2syslog_filter_timestart']);
	if (1181677869 > $post_values['wp2syslog_filter_timestart'] || 2147483647 < $post_values['wp2syslog_filter_timestart'])
		$post_values['wp2syslog_filter_timestart'] = 1181677869;

	// Where should the timeframe end?
	$post_values['wp2syslog_filter_timeend'] = preg_replace('|[^0-9:\s-]|', '', $post_values['wp2syslog_filter_timeend']);
	$post_values['wp2syslog_filter_timeend'] = strtotime($post_values['wp2syslog_filter_timeend']);
	if (1181677869 > $post_values['wp2syslog_filter_timeend'] || 2147483647 < $post_values['wp2syslog_filter_timeend'])
		$post_values['wp2syslog_filter_timeend'] = 2147483647;

	switch($post_values['wp2syslog_filter_orderby'])
	{
		case 'time':
		case 'module':
		case 'severity':
		case 'user':
			break;
		default:
			$post_values['wp2syslog_filter_orderby']='id';
	}

	// How many entries shall be displayed at most?
	$limit=filter_var($post_values['wp2syslog_filter_limit'],FILTER_SANITIZE_NUMBER_INT);
	$post_values['wp2syslog_filter_limit'] = (1 > $limit || 5000 < $limit)
		? 100
		: $limit;

	return $post_values;
}

function wp2syslogFetchLogData($query_parameters)
{
	global $wpdb;
	$search = ( !empty($query_parameters['wp2syslog_filter_search']) )
		? "`message` LIKE '%{$query_parameters['wp2syslog_filter_search']}%'"
		: '';

	// What modules shall be displayed?
	if ( is_array($query_parameters['wp2syslog_filter_modules']) )
	{
		foreach ($query_parameters['wp2syslog_filter_modules'] as $k=>$v)
			$query_parameters['wp2syslog_filter_modules'][$k] = "'$v'";
		$modules = implode(', ', $query_parameters['wp2syslog_filter_modules']);
		$modules = "`module` IN ($modules)";
	}
	else
		$modules = '';

	// What severity levels shall be displayed?
	if ( is_array($query_parameters['wp2syslog_filter_levels']) )
	{
		foreach ($query_parameters['wp2syslog_filter_levels'] as $k=>$v)
			$query_parameters['wp2syslog_filter_levels'][$k] = "'$v'";
		$severityLevels = implode(', ', $query_parameters['wp2syslog_filter_levels']);
		$severityLevels = "`severity` IN ($severityLevels)";
	} else
		$severityLevels = '';

	// Which users shall be displayed?
	if ( is_array($query_parameters['wp2syslog_filter_users']) )
	{
		foreach ($query_parameters['wp2syslog_filter_users'] as $k=>$v)
			$query_parameters['wp2syslog_filter_users'][$k] = "'$v'";
		$users = implode(', ', $query_parameters['wp2syslog_filter_users']);
		$users = "`user` IN ($users)";
	} else
		$users = '';

	// Which IP shall be displayed?
	if ( is_array($query_parameters['wp2syslog_filter_clientip']) )
	{
		foreach ($query_parameters['wp2syslog_filter_clientip'] as $k=>$v)
			$query_parameters['wp2syslog_filter_clientip'][$k] = "'$v'";
		$clientip = implode(', ', $query_parameters['wp2syslog_filter_clientip']);
		$clientip = "`clientip` IN ($clientip)";
	} else
		$clientip = '';

	$timeStart = date('Y-m-d H:i:s', (int)$query_parameters['wp2syslog_filter_timestart']);
	$timeStart = "`time` > '$timeStart'";

	$timeEnd = date('Y-m-d H:i:s', (int)$query_parameters['wp2syslog_filter_timeend']);
	$timeEnd = "`time` < '$timeEnd'";

	$orderby = "ORDER BY `{$query_parameters['wp2syslog_filter_orderby']}`";
	$limit = "LIMIT {$query_parameters['wp2syslog_filter_limit']}";

	$andor1 = ( !empty($search) ) ? $query_parameters['wp2syslog_filter_andor1'] : '';
	$andor2 = ( !empty($modules) ) ? $query_parameters['wp2syslog_filter_andor2'] : '';
	$andor3 = ( !empty($severityLevels) ) ? $query_parameters['wp2syslog_filter_andor3'] : '';
	$andor4 = ( !empty($clientip) ) ? $query_parameters['wp2syslog_filter_andor4'] : '';
	$andor6 = ( !empty($users) ) ? $query_parameters['wp2syslog_filter_andor6'] : '';

	$query =
		"SELECT * FROM {$wpdb->prefix}wp2syslog ".
		"WHERE $search $andor1 ($modules $andor2 ($severityLevels $andor3 ($users $andor6 ($clientip $andor4 ($timeStart AND $timeEnd))))) ".
		"$orderby {$query_parameters['wp2syslog_filter_order']} $limit";

	$result = $wpdb->get_results($query);
	return $result;
}

function wp2syslog_showlog(){
	global $wpdb;
	$wp2syslogRequest=validate_wp2syslog_POST();
	$wp2syslogData = wp2syslogFetchLogData($wp2syslogRequest);
	$wp2syslogLoggedModules = $wpdb->get_col("SELECT DISTINCT `module` FROM `{$wpdb->prefix}wp2syslog`");
	$wp2syslogSystemUsers = $wpdb->get_results("SELECT `id`, `user_login` FROM `{$wpdb->users}`");
	$wp2syslogClientsIP = $wpdb->get_col("SELECT DISTINCT `clientip` FROM `{$wpdb->prefix}wp2syslog`");

	wp_register_style( 'wp2syslogStylesheet', plugins_url('wp2syslog.css', __FILE__) );
	wp_enqueue_style( 'wp2syslogStylesheet' );
	$check_options=get_option('wp2syslog_options');
	$tableheight=filter_var($check_options['tableheight'],FILTER_SANITIZE_NUMBER_INT)."px";

	?>

		<script type="text/javascript">
		//<![CDATA[
		function toggleFilterSection()
		{
			if (document.getElementById('wp2syslog_filter').style.display == 'none')
			{
				document.getElementById('wp2syslog_filter').style.display='block';
				document.getElementById('filter_toggle').firstChild.data='<?php echo __('Hide filter options', 'wp2syslog') ?>';
				document.cookie='wp2syslog_show_filters=true;path=/';
			} else {
				document.getElementById('wp2syslog_filter').style.display='none';
				document.getElementById('filter_toggle').firstChild.data='<?php echo __('Show filter options', 'wp2syslog') ?>';
				document.cookie='wp2syslog_show_filters=false;path=/';
			}
		}
	//]]>
	</script>

		<div class="wrap">
		<strong onclick="toggleFilterSection()" id="filter_toggle"><?php echo (array_key_exists('wp2syslog_show_filters',$_COOKIE) && 'true' === $_COOKIE['wp2syslog_show_filters']) ? __('Hide filter options', 'wp2syslog') : __('Show filter options', 'wp2syslog'); ?></strong>

		<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post" id="wp2syslog_filter" <?php echo (array_key_exists('wp2syslog_show_filters',$_COOKIE) && 'true' === $_COOKIE['wp2syslog_show_filters']) ? '':'style="display: none"'; ?>>
		<p><?php echo __('The <a href="http://en.wikipedia.org/wiki/Boolean_logic#Use_of_parentheses">Boolean logic</a> is <code><strong>searchterm and/or (modules and/or (levels and/or (users and/or (start and end))))</strong></code>.', 'wp2syslog') ?><br /><?php echo __('If a field is left empty, it will be ignored (i.e. not be used to narrow down the result). In the select fields, you can select multiple values (Ctrl+click).', 'wp2syslog') ?></p>
		<div class="inputsection">
		<p>
		<label for="wp2syslog_filter_search"><strong><?php echo __('Search string:', 'wp2syslog') ?></strong></label><br />
		<input type="text" name="wp2syslog_filter_search" id="wp2syslog_filter_search" size="10" value="<?php echo $wp2syslogRequest['wp2syslog_filter_search'] ?>" />
		</p>
		</div>

		<div class="inputsection">
		<p>
		<input type="radio" name="wp2syslog_filter_andor1" value="AND" id="wp2syslog_filter_andor1_and"<?php echo ($wp2syslogRequest['wp2syslog_filter_andor1'] == 'AND' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_andor1_and"><?php echo __('and', 'wp2syslog') ?></label><br />
		<input type="radio" name="wp2syslog_filter_andor1" value="OR" id="wp2syslog_filter_andor1_or"<?php echo ($wp2syslogRequest['wp2syslog_filter_andor1'] == 'OR' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_andor1_or"><?php echo __('or', 'wp2syslog') ?></label>
		</p>
		<p>
		<label for="wp2syslog_filter_modules"><strong><?php echo __('Modules:', 'wp2syslog') ?></strong></label><br />
		<select size="6" name="wp2syslog_filter_modules[]" id="wp2syslog_filter_modules" multiple="multiple">
		<?php
		foreach ($wp2syslogLoggedModules as $loggedModule) : if ( !empty($loggedModule) ) :
		$selected = ( is_array($wp2syslogRequest['wp2syslog_filter_modules']) && in_array($loggedModule, $wp2syslogRequest['wp2syslog_filter_modules']) ? ' selected="selected"' : '' );
	echo '<option value="'.$loggedModule.'" '.$selected.'>'.$loggedModule.'</option>';
	endif; endforeach;
	?>
		</select>
		</p>
		</div>

		<div class="inputsection">
		<p>
		<input type="radio" name="wp2syslog_filter_andor2" value="AND" id="wp2syslog_filter_andor2_and"<?php echo ($wp2syslogRequest['wp2syslog_filter_andor2'] == 'AND' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_andor2_and"><?php echo __('and', 'wp2syslog') ?></label><br />
		<input type="radio" name="wp2syslog_filter_andor2" value="OR" id="wp2syslog_filter_andor2_or"<?php echo ($wp2syslogRequest['wp2syslog_filter_andor2'] == 'OR' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_andor2_or"><?php echo __('or', 'wp2syslog') ?></label>
		</p>
		<p>
		<label for="wp2syslog_filter_levels"><strong><?php echo __('Severity levels:', 'wp2syslog') ?></strong></label><br />
		<select size="6" name="wp2syslog_filter_levels[]" id="wp2syslog_filter_levels" multiple="multiple">
		<option value="0"<?php echo (is_array($wp2syslogRequest['wp2syslog_filter_levels']) && in_array('0', $wp2syslogRequest['wp2syslog_filter_levels']) ? ' selected="selected"' : ''); ?>><?php echo __('Debug', 'wp2syslog') ?></option>
		<option value="1"<?php echo (is_array($wp2syslogRequest['wp2syslog_filter_levels']) && in_array('1', $wp2syslogRequest['wp2syslog_filter_levels']) ? ' selected="selected"' : ''); ?>><?php echo __('Notice', 'wp2syslog') ?></option>
		<option value="2"<?php echo (is_array($wp2syslogRequest['wp2syslog_filter_levels']) && in_array('2', $wp2syslogRequest['wp2syslog_filter_levels']) ? ' selected="selected"' : ''); ?>><?php echo __('Important', 'wp2syslog') ?></option>
		<option value="3"<?php echo (is_array($wp2syslogRequest['wp2syslog_filter_levels']) && in_array('3', $wp2syslogRequest['wp2syslog_filter_levels']) ? ' selected="selected"' : ''); ?>><?php echo __('Warning', 'wp2syslog') ?></option>
		<option value="4"<?php echo (is_array($wp2syslogRequest['wp2syslog_filter_levels']) && in_array('4', $wp2syslogRequest['wp2syslog_filter_levels']) ? ' selected="selected"' : ''); ?>><?php echo __('Error', 'wp2syslog') ?></option>
		<option value="5"<?php echo (is_array($wp2syslogRequest['wp2syslog_filter_levels']) && in_array('5', $wp2syslogRequest['wp2syslog_filter_levels']) ? ' selected="selected"' : ''); ?>><?php echo __('Panic', 'wp2syslog') ?></option>
		</select>
		</p>
		</div>

		<div class="inputsection">
		<p>
		<input type="radio" name="wp2syslog_filter_andor3" value="AND" id="wp2syslog_filter_andor3_and"<?php echo ($wp2syslogRequest['wp2syslog_filter_andor3'] == 'AND' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_andor3_and"><?php echo __('and', 'wp2syslog') ?></label><br />
		<input type="radio" name="wp2syslog_filter_andor3" value="OR" id="wp2syslog_filter_andor3_or"<?php echo ($wp2syslogRequest['wp2syslog_filter_andor3'] == 'OR' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_andor3_or"><?php echo __('or', 'wp2syslog') ?></label>
		</p>
		<p>
		<label for="wp2syslog_filter_users"><strong><?php echo __('Users:', 'wp2syslog') ?></strong></label><br />
		<select size="6" name="wp2syslog_filter_users[]" id="wp2syslog_filter_users" multiple="multiple">
		<?php
		foreach ($wp2syslogSystemUsers as $systemUser) :
		$selected = (is_array($wp2syslogRequest['wp2syslog_filter_users']) && in_array($systemUser->id, $wp2syslogRequest['wp2syslog_filter_users']) ? ' selected="selected"' : '');
	echo '<option value="'.$systemUser->id.'" '.$selected.'>'.$systemUser->user_login.'</option>';
	endforeach;
	?>
		</select>
		</p>
		</div>

		<div class="inputsection">
		<p>
		<input type="radio" name="wp2syslog_filter_andor4" value="AND" id="wp2syslog_filter_andor4_and"<?php echo ($wp2syslogRequest['wp2syslog_filter_andor4'] == 'AND' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_andor4_and"><?php echo __('and', 'wp2syslog') ?></label><br />
		<input type="radio" name="wp2syslog_filter_andor4" value="OR" id="wp2syslog_filter_andor4_or"<?php echo ($wp2syslogRequest['wp2syslog_filter_andor4'] == 'OR' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_andor4_or"><?php echo __('or', 'wp2syslog') ?></label>
		</p>
		<p>
		<label for="wp2syslog_filter_clientip"><strong><?php echo __('Client IP:', 'wp2syslog') ?></strong></label><br />
		<select size="6" name="wp2syslog_filter_clientip[]" id="wp2syslog_filter_clientip" multiple="multiple">
		<?php
		foreach ($wp2syslogClientsIP as $clientip) : if ( !empty($clientip) ) :
		$selected = (is_array($wp2syslogRequest['wp2syslog_filter_clientip']) && in_array($clientip, $wp2syslogRequest['wp2syslog_filter_clientip']) ? ' selected="selected"' : '');
	echo '<option value="'.$clientip.'" '.$selected.'>'.long2ip($clientip).'</option>';
	endif; endforeach;
	?>
		</select>
		</p>
		</div>

		<div class="inputsection">
		<p>
		<input type="radio" name="wp2syslog_filter_andor6" value="AND" id="wp2syslog_filter_andor6_and"<?php echo ($wp2syslogRequest['wp2syslog_filter_andor6'] == 'AND' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_andor6_and"><?php echo __('and', 'wp2syslog') ?></label><br />
		<input type="radio" name="wp2syslog_filter_andor6" value="OR" id="wp2syslog_filter_andor6_or"<?php echo ($wp2syslogRequest['wp2syslog_filter_andor6'] == 'OR' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_andor6_or"><?php echo __('or', 'wp2syslog') ?></label>
		</p>
		<p>
		<label for="wp2syslog_filter_timestart"><strong><?php echo __('Start Time:', 'wp2syslog') ?></strong></label><br />
		<input type="text" name="wp2syslog_filter_timestart" id="wp2syslog_filter_timestart" size="11" value="<?php echo (1181677869 != $wp2syslogRequest['wp2syslog_filter_timestart']) ? date('Y-m-d H:i:s', $wp2syslogRequest['wp2syslog_filter_timestart']) : '' ?>" />
		</p>
		<p>
		<label for="wp2syslog_filter_timeend"><strong><?php echo __('End Time:', 'wp2syslog') ?></strong></label><br />
		<input type="text" name="wp2syslog_filter_timeend" id="wp2syslog_filter_timeend" size="11" value="<?php echo (2147483647 != $wp2syslogRequest['wp2syslog_filter_timeend']) ? date('Y-m-d H:i:s', $wp2syslogRequest['wp2syslog_filter_timeend']) : '' ?>" />
		</p>
		<p><?php echo __('(Format:<br /><code><a href="http://php.net/date">Y-m-d H:i:s</a></code>)', 'wp2syslog') ?></p>
		</div>

		<div class="inputsection">
		<p>
		<strong><?php echo __('Sort by:', 'wp2syslog') ?></strong><br />
		<input type="radio" name="wp2syslog_filter_orderby" value="id" id="wp2syslog_filter_orderby_id"<?php echo (array_key_exists('wp2syslog_filter_orderby',$wp2syslogRequest) && $wp2syslogRequest['wp2syslog_filter_orderby'] == 'id') ? ' checked="checked"' : '' ?> />&nbsp;<label for="wp2syslog_filter_orderby_id"><?php echo __('event ID', 'wp2syslog') ?></label><br />
		<input type="radio" name="wp2syslog_filter_orderby" value="time" id="wp2syslog_filter_orderby_time"<?php echo (array_key_exists('wp2syslog_filter_orderby',$wp2syslogRequest) && $wp2syslogRequest['wp2syslog_filter_orderby'] == 'time') ? ' checked="checked"' : '' ?> />&nbsp;<label for="wp2syslog_filter_orderby_time"><?php echo __('time', 'wp2syslog') ?></label><br />
		<input type="radio" name="wp2syslog_filter_orderby" value="module" id="wp2syslog_filter_orderby_module"<?php echo (array_key_exists('wp2syslog_filter_orderby',$wp2syslogRequest) && $wp2syslogRequest['wp2syslog_filter_orderby'] == 'module') ? ' checked="checked"' : '' ?> />&nbsp;<label for="wp2syslog_filter_orderby_module"><?php echo __('module', 'wp2syslog') ?></label><br />
		<input type="radio" name="wp2syslog_filter_orderby" value="severity" id="wp2syslog_filter_orderby_severity"<?php echo (array_key_exists('wp2syslog_filter_orderby',$wp2syslogRequest) && $wp2syslogRequest['wp2syslog_filter_orderby'] == 'severity') ? ' checked="checked"' : '' ?> />&nbsp;<label for="wp2syslog_filter_orderby_severity"><?php echo __('severity', 'wp2syslog') ?></label><br />
		<input type="radio" name="wp2syslog_filter_orderby" value="user" id="wp2syslog_filter_orderby_user"<?php echo (array_key_exists('wp2syslog_filter_orderby',$wp2syslogRequest) && $wp2syslogRequest['wp2syslog_filter_orderby'] == 'user') ? ' checked="checked"' : '' ?> />&nbsp;<label for="wp2syslog_filter_orderby_user"><?php echo __('user', 'wp2syslog') ?></label>
		</p>
		<p>
		<strong><?php echo __('Order:', 'wp2syslog') ?></strong><br />
		<input type="radio" name="wp2syslog_filter_order" value="DESC" id="wp2syslog_filter_order_desc"<?php echo ($wp2syslogRequest['wp2syslog_filter_order'] == 'DESC' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_order_desc"><?php echo __('desecending', 'wp2syslog') ?></label><br />
		<input type="radio" name="wp2syslog_filter_order" value="ASC" id="wp2syslog_filter_order_asc"<?php echo ($wp2syslogRequest['wp2syslog_filter_order'] == 'ASC' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_order_asc"><?php echo __('ascending', 'wp2syslog') ?></label><br />
		</p>
		<p>
		<label for="wp2syslog_filter_limit"><strong><?php echo __('Max. Results:', 'wp2syslog') ?></strong></label><br />
		<input type="text" name="wp2syslog_filter_limit" id="wp2syslog_filter_limit" size="10" value="<?php echo $wp2syslogRequest['wp2syslog_filter_limit'] ?>" />
		</p>
		</div>
		<div id="wp2syslog_filter_bottom">
		<p><input type="hidden" name="wp2syslog_filter_do" value="true" /><input type="submit" value="<?php echo __('Filter wp2syslog data &raquo;', 'wp2syslog') ?>" /></p>
		</div>
		</form>

		<div id="wp2syslog_data" style="height:<?php echo $tableheight; ?>">
		<table >
		<thead>
		<tr>
		<th scope="col" class="col_id"><?php echo __('ID', 'wp2syslog') ?></th>
		<th scope="col" class="col_time"><?php echo __('Time', 'wp2syslog') ?></th>
		<th scope="col" class="col_module"><?php echo __('Module', 'wp2syslog') ?></th>
		<th scope="col" class="col_severity"><?php echo __('Severity', 'wp2syslog') ?></th>
		<th scope="col" class="col_user"><?php echo __('User', 'wp2syslog') ?></th>
		<th scope="col" class="col_clientip"><?php echo __('ClientIP', 'wp2syslog') ?></th>
		<th scope="col" class="col_useragent"><?php echo __('User Agent', 'wp2syslog') ?></th>
		<th scope="col" class="col_message"><?php echo __('Message', 'wp2syslog') ?></th>
		</tr>
		<thead>
		<?php
		if ( is_array($wp2syslogData) && !empty($wp2syslogData) )
		{
			foreach ($wp2syslogData as $entry)
			{

				$wp2syslogSeverity = array(
						__('Debug', 'wp2syslog'),
						__('Notice', 'wp2syslog'),
						__('Important', 'wp2syslog'),
						__('Warning', 'wp2syslog'),
						__('Error', 'wp2syslog'),
						__('Panic', 'wp2syslog')
						);

				$wp2syslogSeverityCss = array(
						'sev_debug',
						'sev_notice',
						'sev_important',
						'sev_warning',
						'sev_error',
						'sev_panic'
						);
				$check_options=get_option('wp2syslog_options');
				$wp2syslogTime = date( $check_options['timeformat'], ( strtotime($entry->time) ) );

				if ( is_numeric($entry->user) ) 
				{
					$wp2syslogUser = get_userdata($entry->user);
					if($wp2syslogUser)
					{
						$wp2syslogUser = '<a href="'.self_admin_url("user-edit.php?user_id={$wp2syslogUser->ID}").'" title="'.__('View user&#039;s profile', 'wp2syslog').'">'.$wp2syslogUser->display_name.'</a>';
					} 
					else {
						$wp2syslogUser="#$entry->user";
					}

				}
				else {
					$wp2syslogUser = preg_replace('|[^0-9\.]|', '', $entry->user);
				}

				?>
					<tr class="<?php echo $wp2syslogSeverityCss[$entry->severity] ?>">
					<td><?php echo $entry->id ?></td>
					<td><?php echo $wp2syslogTime ?></td>
					<td><?php echo $entry->module ?></td>
					<td><?php echo $wp2syslogSeverity[$entry->severity] ?></td>
					<td><?php echo $wp2syslogUser ?></td>
					<td><?php echo long2ip($entry->clientip) ?></td>
					<td><?php echo $entry->useragent ?></td>
					<td><?php echo $entry->message ?></td>
					</tr>

					<?php
			}
		} else
			echo '<tr><td colspan="7">'.__('No results.', 'wp2syslog').'</td></tr>';
	?>
		</table>
		</div>
		</div>
		<?php

}
