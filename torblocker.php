<?php
	/*
	 * Plugin Name: Tor Blocker
	 * Plugin URI: http://pike.hqpeak.com
	 * Description: Block Tor nodes 
	 * Version: 1.1
	 * Author: HQPeak
	 * Author URI: http://hqpeak.com
	 * License: GPL2
	 */

	/*  Copyright 2015  HQPeak  (email: contact@hqpeak.com)
	
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	*/

	// Globals
	$tor_blocker_options = get_option('torblockersettings');
	$default_version = $tor_blocker_options['default'];
	$deny_list = $tor_blocker_options['deny'];
	$checkbox_options = isset($tor_blocker_options['check'])? $tor_blocker_options['check']:array("check"=>array());
	$time = $tor_blocker_options['time'];
	$msg = isset($tor_blocker_options['custom_msg'])?$tor_blocker_options['custom_msg']:array("custom_msg"=>array("text"=>""));
	$stealth_mode = isset($tor_blocker_options['stealth_mode'])?$tor_blocker_options['stealth_mode']:array("stealth_mode"=>array());
	
	
	// Include styles and scripts
	function scripts_init($page) {
		if( 'settings_page_tor_blocker_menu' != $page ) {
			return;
		}
		
		wp_enqueue_style('logs-style', plugins_url('css/logs.css', __FILE__));
		wp_enqueue_script('torblocker-script', plugins_url('js/tor_blocker_scripts.js', __FILE__), array(), '1.0.0', false);
	}
	
	add_action('admin_enqueue_scripts', 'scripts_init');
	
	
	// Add the plugin settings
	function tor_menu_setting(){
		register_setting('torblockergroup', 'torblockersettings');
	}	
	
	add_action('admin_init', 'tor_menu_setting');
	
	
	// Create plugin options page
	function tor_menu_options() {
			
		if ( !current_user_can('manage_options') )  {
			wp_die( __('You do not have sufficient permissions to access this page.'));
		}
		
		global $wpdb, $tor_blocker_options, $default_version, $checkbox_options, $msg, $stealth_mode;
		
		$active_tab = 'main';
		if ( isset( $_GET['tab'] ) ) {
			$active_tab = $_GET['tab'];
		}
		
		ob_start(); ?>
			<div class="wrap">
				<h1>Tor Blocker Settings</h1>
				<a href="http://pike.hqpeak.com/" target="_blank"><strong>Plugin page</strong></a>
				<br/><br/>
				<h2 class="nav-tab-wrapper">
					<a href="?page=tor_blocker_menu&tab=main" class="nav-tab <?php echo $active_tab == 'main' ? 'nav-tab-active' : ''; ?>">General</a>
					<a href="?page=tor_blocker_menu&tab=logs" class="nav-tab <?php echo $active_tab == 'logs' ? 'nav-tab-active' : ''; ?>">Logs</a>
				</h2>
				<?php if( $active_tab == 'main' ) { ?>
				<form method="post" action="options.php">
					<?php settings_fields('torblockergroup'); ?>					
					<p>		
						<label><big><strong>Update Tor block list:</strong></big></label><br />
						<label><small>Default is free version of the tor exit list service. <a href="http://pike.hqpeak.com/" target="_blank">Learn more</a> or get <a href="http://pike.hqpeak.com/account/" target="_blank">premium service</a> access.</small></label><br />
						<input type="text" name="torblockersettings[default]" value="<?php echo $tor_blocker_options['default']; ?>" size="40" />
					</p>
					<br />
					<p>	
						<label><big><strong>Requests to deny:</strong></big></label><br />
						<label><small>(Here goes all the POST and GET parameters you want to deny [enter them one by one, separated by comma])</small></label><br />
						<textarea name="torblockersettings[deny]" rows="8" cols="60"><?php echo $tor_blocker_options['deny']; ?></textarea>
					</p><br />
					<p>
						<label><big><strong>Requests to allow:</strong></big></label><br />
						<input type="checkbox" name="torblockersettings[check][]" value="visit" <?php echo (in_array('visit', $checkbox_options) ? 'checked' : ''); ?>>Visits&nbsp;&nbsp;
						<label><small>(Tor users can read only public content on the site)</small></label><br />
						<input type="checkbox" name="torblockersettings[check][]" value="comment" <?php echo (in_array('comment', $checkbox_options) ? 'checked' : ''); ?>>Comments&nbsp;&nbsp;
						<label><small>(Tor users can post comments)</small></label><br />
						<input type="checkbox" name="torblockersettings[check][]" value="registration" <?php echo (in_array('registration', $checkbox_options) ? 'checked' : ''); ?>>Registration&nbsp;&nbsp;
						<label><small>(Tor users can register for the site)</small></label><br />
						<input type="checkbox" name="torblockersettings[check][]" value="subscription" <?php echo (in_array('subscription', $checkbox_options) ? 'checked' : ''); ?>>Subscription&nbsp;&nbsp;
						<label><small>(Tor users can subscribe)</small></label><br />
						<input type="checkbox" name="torblockersettings[check][]" value="administration" <?php echo (in_array('administration', $checkbox_options) ? 'checked' : ''); ?>>Administration&nbsp;&nbsp;
						<label><small>(Tor users can access administration panel)</small></label><br />
						<input type="checkbox" name="torblockersettings[check][]" value="request" <?php echo (in_array('request', $checkbox_options) ? 'checked' : ''); ?>>Request&nbsp;&nbsp;
						<label><small>(Tor users can send POST requests)</small></label><br />
						<input type="hidden" name="torblockersettings[time]" value=<?php echo time();?> />
					</p><br />
					<p>
						<label><big><strong>Custom Tor logo message:</strong></big></label><br />
						<input type="checkbox" name="torblockersettings[custom_msg][enabled]" value="enable" <?php echo (in_array('enable', $msg) ? 'checked' : ''); ?>>Enable Tor logo message&nbsp;&nbsp;
						<label><small>(When enabled, a custom message with Tor logo and ip address of the tor user is displayed)</small></label><br />
						<label><small>(Here goes the custom message you want to show to the Tor users)</small></label><br />
						<textarea name="torblockersettings[custom_msg][text]" rows="8" cols="60"><?php echo $msg['text']; ?></textarea>
					</p><br />
					<p>
						<label><big><strong>Tor logs:</strong></big></label><br />
						<input type="checkbox" name="torblockersettings[stealth_mode][]" value="on" <?php echo (in_array('on', $stealth_mode)) ? 'checked' : '' ?>>Enable Stealth Mode logging&nbsp;&nbsp;
						<label><small>(When enabled, all tor user vistis are logged in database)</small></label><br />
					</p>
					<p class="submit">
						<input type="submit" name="submit" class="button-primary" value="Save Changes" />
					</p>
				</form>
				<?php } else {
					
					$table_name_log = $wpdb->prefix."blocker_log";
					
					if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name_log'") == $table_name_log ) {
						$logs = $wpdb->get_results("SELECT * FROM $table_name_log");
					} else {
						wp_die( _('Table does not exist in database!'));
					}
					
					if ( isset($_POST['delete']) ) {
						$toDelete = isset($_POST['selected']) ? $_POST['selected'] : null;
					
						if ( is_array($toDelete) && !empty($toDelete) ) {
							$strDelete = '';
								
							foreach ( $toDelete as $key => $item ) {
								$strDelete .= $item.', ';
							}
								
 							if ( !$wpdb->query("DELETE FROM $table_name_log WHERE id IN (".rtrim($strDelete, ', ').")" )) {
 								$wpdb->show_errors();
 								wp_die($wpdb->print_error());
 							}
							
 							echo "<script>location.reload(true)</script>";
 							exit;
						}
					}
				?>
					
					<div class="logs">
					<?php if ( $logs ) { ?>
						<form action="" method="post">
							<div class="buttons">
								<input type="submit" name="delete" id="deleteBtn" value="Delete" />&nbsp;&nbsp;&nbsp;
								<span id="checkAll" onclick="checkAll(this)">Check All</span><br/>
							</div>
							<table>
								<tr>
									<th>Select</th>
									<th>IP</th>
									<th>URL</th>
									<th>Mode</th>
									<th>Time</th>
								</tr>
								<?php foreach ( $logs as $log ) { ?>					
								<tr>	
									<td><input type="checkbox" name="selected[]" value="<?php echo $log->id ?>" /></td>
									<td><?php echo $log->ip ?></td>
									<td><?php echo $log->landing_page ?></td>
									<td><?php echo ($log->stealth_mode == 1) ? '<strong>true</strong>' : 'false' ?></td>
									<td><?php echo date('d.m.Y H:s:i', strtotime($log->systime)) ?></td>
								</tr>
								<?php } ?>
							</table>
						</form>
					<?php } else { ?>
						<h2>No entries to show!</h2>
					<?php } ?>
					</div>
					
				<?php } ?>			
			</div>
		<?php
		echo ob_get_clean(); 
	}
		
	// Add the options page
	function tor_menu() {
		add_options_page('Tor Blocker Plugin Options', 'Tor Blocker', 'manage_options', 'tor_blocker_menu', 'tor_menu_options');
	}
	
	add_action('admin_menu', 'tor_menu');

	
	// Create table in the database
	function tor_plugin_activate(){
		global $wpdb;
			
		$table_name = $wpdb->prefix."blocker";
		$table_name_log = $wpdb->prefix."blocker_log";
		
		if (	$wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name && 
				$wpdb->get_var("SHOW TABLES LIKE '$table_name_log'") != $table_name_log		){

			$defaults = array("default"=>"http://pike.hqpeak.com/api/free.php", "deny"=>"", "check"=>array("visit"), "time"=>time(), "custom_msg" => array("text"=>""), "stealth_mode" => array("Off"));
			$settings = wp_parse_args(get_option('torblockersettings', $defaults), $defaults);
			update_option('torblockersettings', $settings);
			//update_option('torblockersettings', array("default"=>"http://hqpeak.com/torexitlist/free/?format=json","deny"=>"","check"=>array("visit"),"time"=>time()));
			
			$tor_blocker_options = get_option('torblockersettings'); 
			$default_version = $tor_blocker_options['default']; 
			$sql = "CREATE TABLE $table_name(ip INT(10) UNSIGNED NOT NULL, PRIMARY KEY (ip))";
			$sql_log = "CREATE TABLE $table_name_log(
														id INT(10) NOT NULL AUTO_INCREMENT, 
														ip VARCHAR(25) NOT NULL, 
														landing_page VARCHAR(255) NOT NULL, 
														stealth_mode TINYINT(1) NOT NULL DEFAULT 0,
														systime TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL ,
														PRIMARY KEY (id)
													)";
			
			require_once ABSPATH.'wp-admin/includes/upgrade.php';
			
			dbDelta($sql);
			dbDelta($sql_log);

			$ip_arr = tor_get_ip($default_version); // changed
			$ip_long = tor_to_long($ip_arr);
			tor_fill_table($ip_long);
		}
	}
	
	register_activation_hook(__FILE__, 'tor_plugin_activate');
	
	
	// Get Tor users IP
	function tor_get_ip($url){
		/*
		$ch = curl_init();
		$timeout = 5;
	
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);
		$ce = curl_errno($ch);
		curl_close($ch);
	
		if ($ce != 0) return array(); //wp_die(_("Error opening service"));
		//*/
		$response = wp_remote_get($url);
		if( ! is_wp_error( $response ) && is_array( $response ) && isset( $response['body']) ) {
		$data = $response['body'];
		}else{
			return array();
		}
		//decode output as array
		$service_data = json_decode($data, true);
	
		//never trust the input - sanitate every ip
		if (is_array($service_data) && $size = sizeof($service_data) > 0){
			for ($i=0; $i<$size; $i++){
				if (!preg_match("/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}/", $service_data[$i]))
					$service_data[$i] = "0.0.0.0";
			}
		}else{
			return array();//wp_die(_("Bad output"));
		}
	
		return $service_data;
	}
	
	// Convert IPs into long integers
	function tor_to_long($ip_arr){
		if (is_array($ip_arr)){
			$ip_arr = array_unique($ip_arr);
			$ip2long = array();
			
			foreach ($ip_arr as $ip){
				//if ( !in_array(ip2long($ip), $ip2long) )
					$ip2long[] = ip2long($ip);
			}
		}else{
			wp_die(_("Bad output"));
		}
		return $ip2long;
	}
	
	// Put the array with IPs into table
	function tor_fill_table($ip_long){
		
		global $wpdb;
		
		$table_name = $wpdb->prefix."blocker";
		$tmp = $ip_long;
		$q = sizeof($ip_long)/300;
		for ( $i=0;$i<=$q;$i++ ){
			$ip_long = array();							
			for( $k=$i*300; $k<($i+1)*300;$k++ ){
				if (isset($tmp[$k])) $ip_long[] = $tmp[$k];
			}
			if (is_array($ip_long)){
				
				$sql = "INSERT INTO $table_name (ip) VALUES ";
				
				foreach ($ip_long as $long){
					$sql .= "('".$long."'), ";
				}
				$sql = rtrim($sql, ', ');
				$wpdb->query($sql);
			}
		}
	}
	
	
	// Check if the time has passed to update the IPs table
	function tor_table_update_check(){
		
		global $wpdb;
		
		$tor_blocker_options = get_option('torblockersettings');
		$default_version = $tor_blocker_options['default'];
		$time = $tor_blocker_options['time'];
	
		$t = time();
		$diff = $t - $time;
		
		if (($default_version == 'http://pike.hqpeak.com/api/free.php' && $diff > 18000 ) ||
			(preg_match('/^http(s)?:\/\/(w{3}\.)?pike.hqpeak.com(\/.+)+\?id=[0-9a-zA-Z]{40}&format=json/', $default_version) && $diff > 400))
		{
		 	$table_name = $wpdb->prefix."blocker";
			$ip_arr = tor_get_ip($default_version);
			
			if ( is_array($ip_arr) && sizeof($ip_arr) > 0 ){
				$ip_long = tor_to_long($ip_arr);
				$sql = "DELETE FROM $table_name";
				$wpdb->query($sql);
				tor_fill_table($ip_long);
			}
			$tor_blocker_options['time'] = time();
			update_option("torblockersettings", $tor_blocker_options);
		}
	}
	
	add_action('init', 'tor_table_update_check', 1);
	
	
	// Search for match between user ip and ip in the tor exit list
	function match_address(){
			
		global $wpdb;
		
		$table_name = $wpdb->prefix."blocker";
		
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name){
						
			if ( isset( $_SERVER['REMOTE_ADDR'] ) ){
				
				$user_address = $_SERVER['REMOTE_ADDR'];
				$user2long = ip2long($user_address);
			
				$tor_address = $wpdb->get_row("SELECT * FROM $table_name WHERE `ip`=$user2long");
			
				if ($tor_address !== NULL){
					return $tor_address->ip;
				}
			}

			if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ){
				$user_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
				
				$user2long = ip2long($user_address);
			
				$tor_address = $wpdb->get_row("SELECT * FROM $table_name WHERE `ip`=$user2long");
			
				if ($tor_address !== NULL){
					return $tor_address->ip;
				}	
			}
			
			return false;
			
		}else{
			wp_die( __('Table with ip addresses from tor exit list does not exist.'));
		}	
	}
	
	
	// Stores tor user ip, visited url and time in database.
	function savelog($long_ip, $stealth=NULL) {
		global $wpdb;

		$table_name_log = $wpdb->prefix."blocker_log";
		
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name_log'") == $table_name_log){
			$mode = (isset($stealth) && $stealth=="on") ? 1 : 0;
			$default_ip = long2ip($long_ip);
			$page_url = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';
			$page_url .= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

			if ( !$wpdb->insert($table_name_log, array('ip' => $default_ip, 'landing_page' => $page_url, 'stealth_mode' => $mode)) ) {
				$wpdb->show_errors();
				wp_die($wpdb->print_error());
			}
		} else {
			wp_die( __('Table with tor users logs does not exist.'));
		}
	}
	
	
	// Check if stealth_mode is active
	function check_stealth() {
		global $wpdb, $stealth_mode;
		
		if ( ($long_ip = match_address()) && isset($stealth_mode[0]) && $stealth_mode[0] == "on" ) {
			savelog($long_ip, $stealth_mode[0]);
		}
	}
	
	add_action('init', 'check_stealth');
	
	
	// Deny reading public content
	function tor_read_content(){
		
		$tor_blocker_options = get_option('torblockersettings');
		$checkbox_options = isset($tor_blocker_options['check'])? $tor_blocker_options['check']:array("check"=>array());
		$msg = isset($tor_blocker_options['custom_msg'])?$tor_blocker_options['custom_msg']:array("custom_msg"=>array("text"=>""));
		$stealth_mode = isset($tor_blocker_options['stealth_mode'])?$tor_blocker_options['stealth_mode']:array("stealth_mode"=>array());
		
		if (($long_ip = match_address()) && !in_array('visit', $checkbox_options) && !is_admin()) {
			if ( isset($stealth_mode[0]) && $stealth_mode[0] != "on" ) {
				savelog($long_ip);
			}
			
			if ( isset($msg['enabled']) && $msg['enabled'] === "enable" ) {
				$custom_msg = $msg['text'];
				require_once(WP_PLUGIN_DIR.'/tor-exit-nodes-blocker/tor-logo-view.php');
				die();
			} else {
				wp_die( __('You do not have sufficient permissions to read any public content from this site.'));
			}
		}
		
	}
	
	add_action('init', 'tor_read_content');
	
	
	// Deny comments
	function tor_post_comments($comment_id){
		
		$tor_blocker_options = get_option('torblockersettings');
		$checkbox_options = isset($tor_blocker_options['check'])? $tor_blocker_options['check']:array("check"=>array());
		$msg = isset($tor_blocker_options['custom_msg'])?$tor_blocker_options['custom_msg']:array("custom_msg"=>array("text"=>""));
		$stealth_mode = isset($tor_blocker_options['stealth_mode'])?$tor_blocker_options['stealth_mode']:array("stealth_mode"=>array());
			
		if (($long_ip = match_address()) && !in_array('comment', $checkbox_options) && !empty($_POST['comment'])) {
			if ( $stealth_mode[0] != "on" ) {
				savelog($long_ip);
			}
			
			if ( $msg['enabled'] === "enable" ) {
				$custom_msg = $msg['text'];
				require_once(WP_PLUGIN_DIR.'/tor-exit-nodes-blocker/tor-logo-view.php');
				die();
			} else {
				wp_die( __('You do not have sufficient permissions to post comments.'));
			}
		}
		
	}
	
	add_action('init', 'tor_post_comments');
	
	
	// Deny registration
	function tor_user_registration(){
		
		$tor_blocker_options = get_option('torblockersettings');
		$checkbox_options = isset($tor_blocker_options['check'])? $tor_blocker_options['check']:array("check"=>array());
		$msg = isset($tor_blocker_options['custom_msg'])?$tor_blocker_options['custom_msg']:array("custom_msg"=>array("text"=>""));
		$stealth_mode = isset($tor_blocker_options['stealth_mode'])?$tor_blocker_options['stealth_mode']:array("stealth_mode"=>array());
			
		if (($long_ip = match_address()) && !in_array('register', $checkbox_options)) {
			if ( $stealth_mode[0] != "on" ) {
				savelog($long_ip);
			}
			
			if ( $msg['enabled'] === "enable" ) {
				$custom_msg = $msg['text'];
				require_once(WP_PLUGIN_DIR.'/tor-exit-nodes-blocker/tor-logo-view.php');
				die();
			} else {
				wp_die( __('You do not have sufficient permissions to register for this site.'));
			}
		}
		
	}
	
	add_action('register_post', 'tor_user_registration');
	
	
	// Deny subscription
	function tor_subscription(){
		
		$tor_blocker_options = get_option('torblockersettings');
		$checkbox_options = isset($tor_blocker_options['check'])? $tor_blocker_options['check']:array("check"=>array());
		$msg = isset($tor_blocker_options['custom_msg'])?$tor_blocker_options['custom_msg']:array("custom_msg"=>array("text"=>""));
		$stealth_mode = isset($tor_blocker_options['stealth_mode'])?$tor_blocker_options['stealth_mode']:array("stealth_mode"=>array());
		
		$url_parts = explode('/', $_SERVER['REQUEST_URI']);
			
		if (($long_ip = match_address()) && !in_array('subscription', $checkbox_options) && (in_array('feed', array_keys($_REQUEST)) || in_array('feed', $url_parts))) {
			if ( $stealth_mode[0] != "on" ) {
				savelog($long_ip);
			}
			
			if ( $msg['enabled'] === "enable" ) {
				$custom_msg = $msg['text'];
				require_once(WP_PLUGIN_DIR.'/tor-exit-nodes-blocker/tor-logo-view.php');
				die();
			} else {
				wp_die( __('You do not have sufficient permissions to enter the feed section.'));
			}
		}
		
	}
	
	add_action('init', 'tor_subscription');
	
	
	// Deny administration panel access
	function tor_admin_access_deny(){
		
		$tor_blocker_options = get_option('torblockersettings');
		$checkbox_options = isset($tor_blocker_options['check'])? $tor_blocker_options['check']:array("check"=>array());
		$msg = isset($tor_blocker_options['custom_msg'])?$tor_blocker_options['custom_msg']:array("custom_msg"=>array("text"=>""));
		$stealth_mode = isset($tor_blocker_options['stealth_mode'])?$tor_blocker_options['stealth_mode']:array("stealth_mode"=>array());
		
		if (($long_ip = match_address()) && !in_array('administration', $checkbox_options)) {
			if ( $stealth_mode[0] != "on" ) {
				savelog($long_ip);
			}
			
			if ( $msg['enabled'] === "enable" ) {
				$custom_msg = $msg['text'];
				require_once(WP_PLUGIN_DIR.'/tor-exit-nodes-blocker/tor-logo-view.php');
				die();
			} else {
				wp_die( __('You do not have sufficient permissions to enter the Dashboard.'));
			}
		}
		
	}
	
	add_action('admin_init','tor_admin_access_deny');
	
	
	// Deny POST requests
	function tor_post_request_deny(){
		
		$tor_blocker_options = get_option('torblockersettings');
		$checkbox_options = isset($tor_blocker_options['check'])? $tor_blocker_options['check']:array("check"=>array());
		$msg = isset($tor_blocker_options['custom_msg'])?$tor_blocker_options['custom_msg']:array("custom_msg"=>array("text"=>""));
		$stealth_mode = isset($tor_blocker_options['stealth_mode'])?$tor_blocker_options['stealth_mode']:array("stealth_mode"=>array());
		
		if (($long_ip = match_address()) && !in_array('request', $checkbox_options) && $_SERVER['REQUEST_METHOD'] == 'POST') {
			if ( $stealth_mode[0] != "on" ) {
				savelog($long_ip);
			}
			
			if ( $msg['enabled'] === "enable" ) {
				$custom_msg = $msg['text'];
				require_once(WP_PLUGIN_DIR.'/tor-exit-nodes-blocker/tor-logo-view.php');
				die();
			} else {
				wp_die( __('You do not have sufficient permissions to take any actions on this site.'));
			}
		}
				
	}
	
	add_action('init', 'tor_post_request_deny');
	
	
	// Deny specific requests
	function tor_block_requests(){
		
		$tor_blocker_options = get_option('torblockersettings');
		$deny_list = $tor_blocker_options['deny'];
		$msg = isset($tor_blocker_options['custom_msg'])?$tor_blocker_options['custom_msg']:array("custom_msg"=>array("text"=>""));
		$stealth_mode = isset($tor_blocker_options['stealth_mode'])?$tor_blocker_options['stealth_mode']:array("stealth_mode"=>array());
			
		$all_requests = explode(',', $deny_list);
		$check = false;
	
		// changed
		foreach ($all_requests as $request){
			if (($long_ip = match_address()) && (in_array(trim($request), array_keys($_POST)) || in_array(trim($request), array_keys($_GET)))){
				if ( $stealth_mode[0] != "on" ) {
					savelog($long_ip);
				}
				
				$check = true;
				break;
			}
		}
			
		if ($check) {
			if ( $msg['enabled'] === "enable" ) {
				$custom_msg = $msg['text'];
				require_once(WP_PLUGIN_DIR.'/tor-exit-nodes-blocker/tor-logo-view.php');
				die();
			} else {
				wp_die(_('You do not have sufficient permissions to visit this URL.'));
			}
		}
	
	}
	
	add_action('init', 'tor_block_requests');
	
	
	// Delete table in the database
	function tor_plugin_deactivate(){
			
		global $wpdb;
		
		$table_name = $wpdb->prefix."blocker";
		$table_name_log = $wpdb->prefix."blocker_log";
		$sql = "DROP TABLE IF EXISTS $table_name";
		$wpdb->query($sql);
		$sql = "DROP TABLE IF EXISTS $table_name_log";
		$wpdb->query($sql);
		//delete_option('torblockersettings');
	
	}
	
	register_deactivation_hook(__FILE__, 'tor_plugin_deactivate');
	
	
	// Creates widget for the Tor Blocker
	function widget_display($args) {
		echo $args['before_widget'];
					
		if ( $long_ip = match_address() ) {
			echo "<img src='".WP_PLUGIN_URL."/tor-exit-nodes-blocker/img/onion.jpg' width='60px' /><br/>";
			echo "<strong>".long2ip($long_ip)."</strong>";
		} else {
			if ( isset( $_SERVER['REMOTE_ADDR'] ) ){
				$default_ip = $_SERVER['REMOTE_ADDR'];
			}
					
			if ( isset( $_SERVER['REMOTE_ADDR'] ) ){
				$default_ip = $_SERVER['REMOTE_ADDR'];
			}
					
			//echo "<strong>".$default_ip."</strong>";
		}
			
		echo $args['after_widget'];
	}
	
	add_action('widgets_init', function() {
		wp_register_sidebar_widget(
				'Tor_Blocker_Widget',       // unique widget id
				'Tor Blocker Widget',       // widget name
				'widget_display',  	        // callback function
				array(                     // options
						'description' => __('Tor Blocker Widget!', 'text_domain')
				)
		);
	});

?>
