<?php
	/*
	 * Plugin Name: Tor Blocker
	 * Plugin URI: http://hqpeak.com/torexitlist/
	 * Description: Block Tor exit nodes 
	 * Version: 1.0
	 * Author: HQPeak
	 * Author URI: http://hqpeak.com
	 * License: GPL2
	 */

	/*  Copyright 2014  HQPeak  (email: contact@hqpeak.com)
	
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
	$checkbox_options = $tor_blocker_options['check'];
	$time = $tor_blocker_options['time'];

	
	// Add the plugin settings
	function tor_menu_setting(){
		register_setting('torblockergroup', 'torblockersettings');
	}	
	add_action('admin_init', 'tor_menu_setting');
	
	
	// Create plugin options page
	function tor_menu_options() {
			
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __('You do not have sufficient permissions to access this page.'));
		}
	
		global $tor_blocker_options, $default_version, $checkbox_options;
		ob_start();?>
			<div class="wrap">
				<form method="post" action="options.php">
					<?php settings_fields('torblockergroup'); ?>					
					<h1>Tor Blocker Settings</h1>
					<a href="http://hqpeak.com/torexitlist/" target="_blank"><strong>Plugin page</strong></a>
					<p>		
						<label><big><strong>Update Tor block list:</strong></big></label><br />
						<label><small>Default is free version of the tor exit list service. <a href="http://hqpeak.com/torexitlist/" target="_blank">Learn more</a> or get <a href="http://hqpeak.com/torexitlist/account/" target="_blank">premium service</a> access.</small></label><br />
						<input type="text" name="torblockersettings[default]" value="<?php echo $tor_blocker_options['default']; ?>" size="40" />
					</p>
					<br />
					<p>	
						<label><big><strong>Requests to deny:</strong></big></label><br />
						<label><small>(Here goes all the POST and GET parameters you want to deny [enter them one by one, separated by comma])</small></label><br />
						<textarea name="torblockersettings[deny]" rows="10" cols="60"><?php echo $tor_blocker_options['deny']; ?></textarea>
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
					</p>
					<p class="submit">
						<input type="submit" name="submit" class="button-primary" value="Save Changes" />
					</p>
				</form>			
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
		
		if ($wpdb->get_var($wpdb->get_var("SHOW TABLES LIKE '$table_name'")) != $table_name){
				
			update_option('torblockersettings', array("default"=>"http://hqpeak.com/torexitlist/free/?format=json","deny"=>"","check"=>array("visit"),"time"=>time()));
			
			$tor_blocker_options = get_option('torblockersettings'); 
			$default_version = $tor_blocker_options['default']; 
			$sql = "CREATE TABLE $table_name(ip INT(10) NOT NULL, PRIMARY KEY (ip))";
			
			require_once ABSPATH.'wp-admin/includes/upgrade.php';
			
			dbDelta($sql);

			$ip_arr = tor_get_ip($default_version); // changed
			$ip_long = tor_to_long($ip_arr);
			tor_fill_table($ip_long);
		}
	}
	
	register_activation_hook(__FILE__, 'tor_plugin_activate');
	
	
	// Get Tor users IP
	function tor_get_ip($url){
		
		$ch = curl_init();
		$timeout = 5;
	
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);
		$ce = curl_errno($ch);
		curl_close($ch);
	
		if ($ce != 0) return array();//wp_die(_("Error opening service"));
	
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
			$ip2long = array();
			foreach ($ip_arr as $ip){
				if ( !in_array(ip2long($ip), $ip2long) )
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
					$sql .= "('".mysql_real_escape_string($long)."'), ";
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
		
		if (($default_version == 'http://hqpeak.com/torexitlist/free/?format=json' && $diff > 18000 ) ||
			(preg_match('/^http(s)?:\/\/(w{3}\.)?hqpeak.com(\/.+)+\?id=[0-9a-zA-Z]{40}&format=json/', $default_version) && $diff > 400))
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
		$check = false;
		
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name){
			
			
			if ( isset( $_SERVER['REMOTE_ADDR'] ) ){
				
				$user_address = $_SERVER['REMOTE_ADDR'];
				$user2long = ip2long($user_address);
			
				$tor_address = $wpdb->get_row("SELECT * FROM $table_name WHERE `ip`=$user2long");
			
				if ($tor_address !== NULL){
					return true;
				}
			}

			if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ){
				$user_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
				
				$user2long = ip2long($user_address);
			
				$tor_address = $wpdb->get_row("SELECT * FROM $table_name WHERE `ip`=$user2long");
			
				if ($tor_address !== NULL){
					return true;
				}	
			}
		}else{
			wp_die( __('Table with ip addresses from tor exit list does not exist.'));
		}	

		return $check;
	}
	
	
	// Deny reading public content
	function tor_read_content(){
		
		$tor_blocker_options = get_option('torblockersettings');
		$checkbox_options = $tor_blocker_options['check'];
		
		if (match_address() && !in_array('visit', $checkbox_options))
			wp_die( __('You do not have sufficient permissions to visit this site.'));
	}
	
	add_action('init', 'tor_read_content');
	
	
	// Deny comments
	function tor_post_comments($comment_id){
		
		$tor_blocker_options = get_option('torblockersettings');
		$checkbox_options = $tor_blocker_options['check'];
			
		if (match_address() && !in_array('comment', $checkbox_options) && !empty($_POST['comment'])){
			wp_die( __('You do not have sufficient permissions to post comments.'));
		}
	}
	
	add_action('init', 'tor_post_comments');
	
	
	// Deny registration
	function tor_user_registration(){
		
		$tor_blocker_options = get_option('torblockersettings');
		$checkbox_options = $tor_blocker_options['check'];
			
		if (match_address() && !in_array('register', $checkbox_options))
			wp_die( __('You do not have sufficient permissions to register for this site.'));
	}
	
	add_action('register_post', 'tor_user_registration');
	
	
	// Deny subscription
	function tor_subscription(){
		
		$tor_blocker_options = get_option('torblockersettings');
		$checkbox_options = $tor_blocker_options['check'];
		
		$url_parts = explode('/', $_SERVER['REQUEST_URI']);
			
		if (match_address() && !in_array('subscription', $checkbox_options) && in_array('feed', array_keys($_REQUEST)) || in_array('feed', $url_parts))
			wp_die( __('You do not have sufficient permissions to enter the feed section.'));
	}
	
	add_action('init', 'tor_subscription');
	
	
	// Deny administration panel access
	function tor_admin_access_deny(){
		
		$tor_blocker_options = get_option('torblockersettings');
		$checkbox_options = $tor_blocker_options['check'];
		if (match_address() && !in_array('administration', $checkbox_options))
			wp_die( __('You do not have sufficient permissions to enter the Dashboard.'));
		
	}
	
	add_action('admin_init','tor_admin_access_deny');
	
	
	// Deny POST requests
	function tor_post_request_deny(){
		
		$tor_blocker_options = get_option('torblockersettings');
		$checkbox_options = $tor_blocker_options['check'];
		
		if (match_address() && !in_array('request', $checkbox_options) && $_SERVER['REQUEST_METHOD'] == 'POST')
			wp_die( __('You do not have sufficient permissions to take any actions on this site.'));
		
	}
	
	add_action('init', 'tor_post_request_deny');
	
	
	// Deny specific requests
	function tor_block_requests(){
		
		$tor_blocker_options = get_option('torblockersettings');
		$deny_list = $tor_blocker_options['deny'];
			
		$all_requests = explode(',', $deny_list);
		$check = false;
	
		// changed
		foreach ($all_requests as $request){
			if (match_address() && (in_array(trim($request), array_keys($_POST)) || in_array(trim($request), array_keys($_GET)))){
				$check = true;
				break;
			}
		}
			
		if ($check)
			wp_die(_('You do not have sufficient permissions to visit this URL.'));
	}
	
	add_action('init', 'tor_block_requests');
	
	
	// Delete table in the database
	function tor_plugin_deactivate(){
			
		global $wpdb;
		
		$table_name = $wpdb->prefix."blocker";
		$sql = "DROP TABLE IF EXISTS $table_name";
		$wpdb->query($sql);
		delete_option('torblockersettings');
	
	}
	
	register_deactivation_hook(__FILE__, 'tor_plugin_deactivate');

?>
