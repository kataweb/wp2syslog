<?php

function walk_POST(&$post_value, $key){
		if(array_key_exists($key,$_POST)){
				$post_value=$_POST[$key];
		}
}

function wp2syslogAssembleRequestData()
{

		$allowed = array(
						'wp2syslog_filter_search',
						'wp2syslog_filter_andor1',
						'wp2syslog_filter_modules',
						'wp2syslog_filter_andor2',
						'wp2syslog_filter_levels',
						'wp2syslog_filter_andor3',
						'wp2syslog_filter_users',
						'wp2syslog_filter_andor4',
						'wp2syslog_filter_clientip',
						'wp2syslog_filter_andor6',
						'wp2syslog_filter_timestart',
						'wp2syslog_filter_timeend',
						'wp2syslog_filter_orderby',
						'wp2syslog_filter_order',
						'wp2syslog_filter_limit',
						);
		$post_values=array_fill_keys($allowed, ''); //set null value for all keys..
		array_walk($post_values,'walk_POST');       //.. and fill array vith _POST values

		//VALIDATE..
		$search=filter_var($post_values['wp2syslog_filter_search'],FILTER_SANITIZE_STRING|FILTER_SANITIZE_MAGIC_QUOTES);

		$andor1=filter_var($post_values['wp2syslog_filter_andor1'],FILTER_SANITIZE_STRING);
		$andor1 = ('OR' === $post_values['wp2syslog_filter_andor1']) 
				? 'OR' 
				: 'AND';
		$andor2=filter_var($post_values['wp2syslog_filter_andor2'],FILTER_SANITIZE_STRING);
		$andor2 = ('OR' === $post_values['wp2syslog_filter_andor2'])
				? 'OR' 
				: 'AND';
		$andor3=filter_var($post_values['wp2syslog_filter_andor3'],FILTER_SANITIZE_STRING);
		$andor3 = ('OR' === $post_values['wp2syslog_filter_andor3']) 
				? 'OR' 
				: 'AND';
		$andor4=filter_var($post_values['wp2syslog_filter_andor4'],FILTER_SANITIZE_STRING);
		$andor4 = ('OR' === $post_values['wp2syslog_filter_andor4']) 
				? 'OR' 
				: 'AND';
		$andor6=filter_var($post_values['wp2syslog_filter_andor6'],FILTER_SANITIZE_STRING);
		$andor6 = ('OR' === $post_values['wp2syslog_filter_andor6']) 
				? 'OR' 
				: 'AND';

		$modules=$post_values['wp2syslog_filter_modules'];
		if ( is_array($modules) )
		{
				foreach ($modules as $k=>$v){
						$modules[$k]=filter_var($v,FILTER_SANITIZE_STRING);
				}
		} else {
				$modules = 'all';
		}

		$severityLevels=$post_values['wp2syslog_filter_levels'];
		if ( is_array($severityLevels) )
		{
				foreach ($severityLevels as $k=>$v){
						$v=filter_var($v,FILTER_SANITIZE_NUMBER_INT);
						$severityLevels[$k] = (0 <= $v && 5 >= $v ) 
								? $v
								: '10';
				}
		} else {
				$severityLevels = 'all';
		}

		// Which users shall be displayed?
		$users=$post_values['wp2syslog_filter_users'];
		if ( is_array($users) )
		{
				foreach ($users as $k=>$v){
						$v=filter_var($v,FILTER_SANITIZE_NUMBER_INT);
						$users[$k] = (0 < $v) 
								? $v 
								: '0';
				}
		} else{
				$users = 'all';
		}

		$clientip=$post_values['wp2syslog_filter_clientip'];
		if ( is_array($clientip) )
		{
				foreach ($clientip as $k=>$v){
						$v=filter_var($v,FILTER_SANITIZE_NUMBER_INT);
						$severityLevels[$k] = (0 <= $v && 5 >= $v ) 
								? $v
								: '10';
				}
		} else {
				$clientip = 'all';
		}

		// Where should the timeframe start?
		$timeStart=$post_values['wp2syslog_filter_timestart'];
		$timeStart = preg_replace('|[^0-9:\s-]|', '', $timeStart);
		$timeStart = strtotime($timeStart);
		if (1181677869 > $timeStart || 2147483647 < $timeStart)
				$timeStart = 1181677869;

		// Where should the timeframe end?
		$timeEnd=$post_values['wp2syslog_filter_timeend'];
		$timeEnd = preg_replace('|[^0-9:\s-]|', '', $timeEnd);
		$timeEnd = strtotime($timeEnd);
		if (1181677869 > $timeEnd || 2147483647 < $timeEnd)
				$timeEnd = 2147483647;

		$orderby = preg_replace('|[^a-z]|', '', $post_values['wp2syslog_filter_orderby']);
		$orderby = ('time' == $orderby || 'module' == $orderby || 'severity' == $orderby || 'user' == $orderby)
				? $orderby
				: 'id';

		$order = preg_replace('|[^a-z]|', '', $post_values['wp2syslog_filter_order']);
		$order=$post_values['wp2syslog_filter_order'];
		$order = ('ASC' == $order) 
				? 'ASC' 
				: 'DESC';

		// How many entries shall be displayed at most?
		$limit=filter_var($post_values['wp2syslog_filter_limit'],FILTER_SANITIZE_NUMBER_INT);
		$limit = (1 > $limit || 5000 < $limit)
				? 100
				: $limit;

		return compact(
						'search',
						'andor1',
						'modules',
						'andor2',
						'severityLevels',
						'andor3',
						'users',
						'andor4',
						'clientip',
						'andor6',
						'timeStart',
						'timeEnd',
						'orderby',
						'order',
						'limit'
					  );
}
function wp2syslogFetchLogData($query_parameters)
{

		extract($query_parameters);

		global $wpdb;
		$search = ( !empty($search) )
				? "`message` LIKE '%$search%'"
				: '';

		// What modules shall be displayed?
		if ( is_array($modules) )
		{
				foreach ($modules as $k=>$v)
						$modules[$k] = "'$v'";
				$modules = implode(', ', $modules);
				$modules = "`module` IN ($modules)";
		}
		else
				$modules = '';

		// What severity levels shall be displayed?
		if ( is_array($severityLevels) )
		{
				foreach ($severityLevels as $k=>$v)
						$severityLevels[$k] = "'$v'";
				$severityLevels = implode(', ', $severityLevels);
				$severityLevels = "`severity` IN ($severityLevels)";
		} else
				$severityLevels = '';

		// Which users shall be displayed?
		if ( is_array($users) )
		{
				foreach ($users as $k=>$v)
						$users[$k] = "'$v'";
				$users = implode(', ', $users);
				$users = "`user` IN ($users)";
		} else
				$users = '';

		// Which IP shall be displayed?
		if ( is_array($clientip) )
		{
				foreach ($clientip as $k=>$v)
						$clientip[$k] = "'$v'";
				$clientip = implode(', ', $clientip);
				$clientip = "`clientip` IN ($clientip)";
		} else
				$clientip = '';

		$timeStart = date('Y-m-d H:i:s', (int)$timeStart);
		$timeStart = "`time` > '$timeStart'";

		$timeEnd = date('Y-m-d H:i:s', (int)$timeEnd);
		$timeEnd = "`time` < '$timeEnd'";

		$orderby = "ORDER BY `$orderby`";
		$limit = "LIMIT $limit";

		$andor1 = ( !empty($search) ) ? $andor1 : '';
		$andor2 = ( !empty($modules) ) ? $andor2 : '';
		$andor3 = ( !empty($severityLevels) ) ? $andor3 : '';
		$andor4 = ( !empty($clientip) ) ? $andor4 : '';
		$andor6 = ( !empty($users) ) ? $andor6 : '';

		$query =
				"SELECT * FROM {$wpdb->prefix}wp2syslog ".
				"WHERE $search $andor1 ($modules $andor2 ($severityLevels $andor3 ($users $andor6 ($clientip $andor4 ($timeStart AND $timeEnd))))) ".
				"$orderby $order $limit";

		//echo $query;

		$result = $wpdb->get_results($query);
		return $result;
}

function wp2syslog_showlog(){
		global $wpdb;
		$wp2syslogRequest=wp2syslogAssembleRequestData();
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
				<input type="text" name="wp2syslog_filter_search" id="wp2syslog_filter_search" size="10" value="<?php echo $wp2syslogRequest['search'] ?>" />
				</p>
				</div>

				<div class="inputsection">
				<p>
				<input type="radio" name="wp2syslog_filter_andor1" value="AND" id="wp2syslog_filter_andor1_and"<?php echo ($wp2syslogRequest['andor1'] == 'AND' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_andor1_and"><?php echo __('and', 'wp2syslog') ?></label><br />
				<input type="radio" name="wp2syslog_filter_andor1" value="OR" id="wp2syslog_filter_andor1_or"<?php echo ($wp2syslogRequest['andor1'] == 'OR' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_andor1_or"><?php echo __('or', 'wp2syslog') ?></label>
				</p>
				<p>
				<label for="wp2syslog_filter_modules"><strong><?php echo __('Modules:', 'wp2syslog') ?></strong></label><br />
				<select size="6" name="wp2syslog_filter_modules[]" id="wp2syslog_filter_modules" multiple="multiple">
				<?php
				foreach ($wp2syslogLoggedModules as $loggedModule) : if ( !empty($loggedModule) ) :
				$selected = ( is_array($wp2syslogRequest['modules']) && in_array($loggedModule, $wp2syslogRequest['modules']) ? ' selected="selected"' : '' );
		echo '<option value="'.$loggedModule.'" '.$selected.'>'.$loggedModule.'</option>';
		endif; endforeach;
		?>
				</select>
				</p>
				</div>

				<div class="inputsection">
				<p>
				<input type="radio" name="wp2syslog_filter_andor2" value="AND" id="wp2syslog_filter_andor2_and"<?php echo ($wp2syslogRequest['andor2'] == 'AND' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_andor2_and"><?php echo __('and', 'wp2syslog') ?></label><br />
				<input type="radio" name="wp2syslog_filter_andor2" value="OR" id="wp2syslog_filter_andor2_or"<?php echo ($wp2syslogRequest['andor2'] == 'OR' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_andor2_or"><?php echo __('or', 'wp2syslog') ?></label>
				</p>
				<p>
				<label for="wp2syslog_filter_levels"><strong><?php echo __('Severity levels:', 'wp2syslog') ?></strong></label><br />
				<select size="6" name="wp2syslog_filter_levels[]" id="wp2syslog_filter_levels" multiple="multiple">
				<option value="0"<?php echo (is_array($wp2syslogRequest['severityLevels']) && in_array('0', $wp2syslogRequest['severityLevels']) ? ' selected="selected"' : ''); ?>><?php echo __('Debug', 'wp2syslog') ?></option>
				<option value="1"<?php echo (is_array($wp2syslogRequest['severityLevels']) && in_array('1', $wp2syslogRequest['severityLevels']) ? ' selected="selected"' : ''); ?>><?php echo __('Notice', 'wp2syslog') ?></option>
				<option value="2"<?php echo (is_array($wp2syslogRequest['severityLevels']) && in_array('2', $wp2syslogRequest['severityLevels']) ? ' selected="selected"' : ''); ?>><?php echo __('Important', 'wp2syslog') ?></option>
				<option value="3"<?php echo (is_array($wp2syslogRequest['severityLevels']) && in_array('3', $wp2syslogRequest['severityLevels']) ? ' selected="selected"' : ''); ?>><?php echo __('Warning', 'wp2syslog') ?></option>
				<option value="4"<?php echo (is_array($wp2syslogRequest['severityLevels']) && in_array('4', $wp2syslogRequest['severityLevels']) ? ' selected="selected"' : ''); ?>><?php echo __('Error', 'wp2syslog') ?></option>
				<option value="5"<?php echo (is_array($wp2syslogRequest['severityLevels']) && in_array('5', $wp2syslogRequest['severityLevels']) ? ' selected="selected"' : ''); ?>><?php echo __('Panic', 'wp2syslog') ?></option>
				</select>
				</p>
				</div>

				<div class="inputsection">
				<p>
				<input type="radio" name="wp2syslog_filter_andor3" value="AND" id="wp2syslog_filter_andor3_and"<?php echo ($wp2syslogRequest['andor3'] == 'AND' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_andor3_and"><?php echo __('and', 'wp2syslog') ?></label><br />
				<input type="radio" name="wp2syslog_filter_andor3" value="OR" id="wp2syslog_filter_andor3_or"<?php echo ($wp2syslogRequest['andor3'] == 'OR' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_andor3_or"><?php echo __('or', 'wp2syslog') ?></label>
				</p>
				<p>
				<label for="wp2syslog_filter_users"><strong><?php echo __('Users:', 'wp2syslog') ?></strong></label><br />
				<select size="6" name="wp2syslog_filter_users[]" id="wp2syslog_filter_users" multiple="multiple">
				<?php
				foreach ($wp2syslogSystemUsers as $systemUser) :
				$selected = (is_array($wp2syslogRequest['users']) && in_array($systemUser->id, $wp2syslogRequest['users']) ? ' selected="selected"' : '');
		echo '<option value="'.$systemUser->id.'" '.$selected.'>'.$systemUser->user_login.'</option>';
		endforeach;
		?>
				</select>
				</p>
				</div>

				<div class="inputsection">
				<p>
				<input type="radio" name="wp2syslog_filter_andor4" value="AND" id="wp2syslog_filter_andor4_and"<?php echo ($wp2syslogRequest['andor4'] == 'AND' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_andor4_and"><?php echo __('and', 'wp2syslog') ?></label><br />
				<input type="radio" name="wp2syslog_filter_andor4" value="OR" id="wp2syslog_filter_andor4_or"<?php echo ($wp2syslogRequest['andor4'] == 'OR' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_andor4_or"><?php echo __('or', 'wp2syslog') ?></label>
				</p>
				<p>
				<label for="wp2syslog_filter_clientip"><strong><?php echo __('Client IP:', 'wp2syslog') ?></strong></label><br />
				<select size="6" name="wp2syslog_filter_clientip[]" id="wp2syslog_filter_clientip" multiple="multiple">
				<?php
				foreach ($wp2syslogClientsIP as $clientip) : if ( !empty($clientip) ) :
				$selected = (is_array($wp2syslogRequest['clientip']) && in_array($clientip, $wp2syslogRequest['clientip']) ? ' selected="selected"' : '');
		echo '<option value="'.$clientip.'" '.$selected.'>'.long2ip($clientip).'</option>';
		endif; endforeach;
		?>
				</select>
				</p>
				</div>

				<div class="inputsection">
				<p>
				<input type="radio" name="wp2syslog_filter_andor6" value="AND" id="wp2syslog_filter_andor6_and"<?php echo ($wp2syslogRequest['andor6'] == 'AND' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_andor6_and"><?php echo __('and', 'wp2syslog') ?></label><br />
				<input type="radio" name="wp2syslog_filter_andor6" value="OR" id="wp2syslog_filter_andor6_or"<?php echo ($wp2syslogRequest['andor6'] == 'OR' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_andor6_or"><?php echo __('or', 'wp2syslog') ?></label>
				</p>
				<p>
				<label for="wp2syslog_filter_timestart"><strong><?php echo __('Start Time:', 'wp2syslog') ?></strong></label><br />
				<input type="text" name="wp2syslog_filter_timestart" id="wp2syslog_filter_timestart" size="11" value="<?php echo (1181677869 != $wp2syslogRequest['timeStart']) ? date('Y-m-d H:i:s', $wp2syslogRequest['timeStart']) : '' ?>" />
				</p>
				<p>
				<label for="wp2syslog_filter_timeend"><strong><?php echo __('End Time:', 'wp2syslog') ?></strong></label><br />
				<input type="text" name="wp2syslog_filter_timeend" id="wp2syslog_filter_timeend" size="11" value="<?php echo (2147483647 != $wp2syslogRequest['timeEnd']) ? date('Y-m-d H:i:s', $wp2syslogRequest['timeEnd']) : '' ?>" />
				</p>
				<p><?php echo __('(Format:<br /><code><a href="http://php.net/date">Y-m-d H:i:s</a></code>)', 'wp2syslog') ?></p>
				</div>

				<div class="inputsection">
				<p>
				<strong><?php echo __('Sort by:', 'wp2syslog') ?></strong><br />
				<input type="radio" name="wp2syslog_filter_orderby" value="id" id="wp2syslog_filter_orderby_id"<?php echo (array_key_exists('orderby',$wp2syslogRequest) && $wp2syslogRequest['orderby'] == 'id') ? ' checked="checked"' : '' ?> />&nbsp;<label for="wp2syslog_filter_orderby_id"><?php echo __('event ID', 'wp2syslog') ?></label><br />
				<input type="radio" name="wp2syslog_filter_orderby" value="time" id="wp2syslog_filter_orderby_time"<?php echo (array_key_exists('orderby',$wp2syslogRequest) && $wp2syslogRequest['orderby'] == 'time') ? ' checked="checked"' : '' ?> />&nbsp;<label for="wp2syslog_filter_orderby_time"><?php echo __('time', 'wp2syslog') ?></label><br />
				<input type="radio" name="wp2syslog_filter_orderby" value="module" id="wp2syslog_filter_orderby_module"<?php echo (array_key_exists('orderby',$wp2syslogRequest) && $wp2syslogRequest['orderby'] == 'module') ? ' checked="checked"' : '' ?> />&nbsp;<label for="wp2syslog_filter_orderby_module"><?php echo __('module', 'wp2syslog') ?></label><br />
				<input type="radio" name="wp2syslog_filter_orderby" value="severity" id="wp2syslog_filter_orderby_severity"<?php echo (array_key_exists('orderby',$wp2syslogRequest) && $wp2syslogRequest['orderby'] == 'severity') ? ' checked="checked"' : '' ?> />&nbsp;<label for="wp2syslog_filter_orderby_severity"><?php echo __('severity', 'wp2syslog') ?></label><br />
				<input type="radio" name="wp2syslog_filter_orderby" value="user" id="wp2syslog_filter_orderby_user"<?php echo (array_key_exists('orderby',$wp2syslogRequest) && $wp2syslogRequest['orderby'] == 'user') ? ' checked="checked"' : '' ?> />&nbsp;<label for="wp2syslog_filter_orderby_user"><?php echo __('user', 'wp2syslog') ?></label>
				</p>
				<p>
				<strong><?php echo __('Order:', 'wp2syslog') ?></strong><br />
				<input type="radio" name="wp2syslog_filter_order" value="DESC" id="wp2syslog_filter_order_desc"<?php echo ($wp2syslogRequest['order'] == 'DESC' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_order_desc"><?php echo __('desecending', 'wp2syslog') ?></label><br />
				<input type="radio" name="wp2syslog_filter_order" value="ASC" id="wp2syslog_filter_order_asc"<?php echo ($wp2syslogRequest['order'] == 'ASC' ? ' checked="checked"' : '') ?> />&nbsp;<label for="wp2syslog_filter_order_asc"><?php echo __('ascending', 'wp2syslog') ?></label><br />
				</p>
				<p>
				<label for="wp2syslog_filter_limit"><strong><?php echo __('Max. Results:', 'wp2syslog') ?></strong></label><br />
				<input type="text" name="wp2syslog_filter_limit" id="wp2syslog_filter_limit" size="10" value="<?php echo $wp2syslogRequest['limit'] ?>" />
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
