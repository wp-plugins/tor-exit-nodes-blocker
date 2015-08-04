<?php 
	include_once('../../../wp-load.php');
	
	global $current_user;
	
	if( !in_array('administrator', $current_user->roles) ) {
		wp_die('No access is allowed to this page.');
	}
	
	global $wpdb;
	
	$table_name_log = $wpdb->prefix."blocker_log";
	
	if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name_log'") == $table_name_log ) {
		$logs = $wpdb->get_results("SELECT * FROM $table_name_log");	
	} else {
		wp_die( _('Table does not exist in database!'));
	}
	
	if ( isset($_POST['delete']) ) {
		$toDelete = $_POST['selected'];	

		if ( is_array($toDelete) && !empty($toDelete) ) {
			$strDelete = '';
			
			foreach ( $toDelete as $key => $item ) {
				$strDelete .= $item.', ';
			}
			
			if ( !$wpdb->query("DELETE FROM $table_name_log WHERE id IN (".rtrim($strDelete, ', ').")" )) {
				$wpdb->show_errors();
				wp_die($wpdb->print_error());
			}
			
			wp_redirect($_SERVER['PHP_SELF']);
		}
	}				
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Tor Blocker</title>
		
		<link href='http://fonts.googleapis.com/css?family=Varela' rel='stylesheet' type='text/css'>
		<style>
			body {
				font-family: 'Valera', sans-serif;
			}
			
			.logs {
				padding: 40px;
				width: 940px;
				margin-left: auto;
				margin-right: auto;
			}

			
			h1, h2 {
				text-align: center;
			}
			
			table {
				padding-top: 20px;
				border-collapse: collapse;
				width: 100%;
			}
			
			th {
				background-color: #d9d9d9;
				padding: 7px;
			}
			
			td {
				padding: 4px;
			}	
			
			th, td {
				border: 1px solid #d9d9d9;
				width: auto;
				max-width: 560px;
			} 
			
			td:first-child, td:nth-child(4) {
				text-align: center;
			}
			
			#deleteBtn {
				padding: 0;
				background-color: #fff;
				color: #c00000;
				border: none;
				outline: none;
			}
			
			#checkAll {
				color: #3e73b6;
			}
			
			#deleteBtn, #checkAll {
				cursor: pointer;
				font-size: 14px;
				padding-bottom: 10px;
			}
		</style>
		
		<script>
			function checkAll(obj) {
				checkboxes = document.getElementsByName('selected[]');

				if ( obj.innerText == 'Check All' ) {	
					obj.innerText = 'Uncheck All';
					for( var i=0; i<checkboxes.length; i++ ) {
						checkboxes[i].checked = 'checked';
					}
				} else {
					obj.innerText = 'Check All';
					for( var i=0; i<checkboxes.length; i++ ) {
						checkboxes[i].checked = '';
					}
				}
			}
		</script>
	</head>
	<body>
		<div class="logs">
			<h1>Tor-Users Visits Table</h1>
			<br/>
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
	</body>
</html>
