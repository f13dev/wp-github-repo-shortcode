<?php
final class WPErrorLogPage {
	public static function indexPage() { ?>
		<div class="wrap">
	  <h2>View WP Error Log</h2> 
	  <div class="postbox " style="display: block; ">
	  <div class="inside">
	  	<form action="" method="post">
	  		<table>
	  			<tr>
	  				<td> 
	  					<?php
	  					$options = array("current" => "Current");
	  					foreach(array_reverse(scandir(ABSPATH."/logs")) as $log) {
		  						if (preg_match("/^log_/i", $log)) {
		  							$options[$log] = $log;
		  						}
		  				}
	  					VWPErrTomM8::add_option_form_field("select", "The Log File", "view_wp_error_log_log", "view_wp_error_log_log", array(), "p", array(), $options);	
	  					?>
	  				</td>
	  			</tr>

	  			<tr>
	  				<td>  	
	  					<?php
	  					VWPErrTomM8::add_option_form_field("select", "Number of lines", "view_wp_error_log_no_lines", "view_wp_error_log_no_lines", array(), "p", array(), array("10"=>"10", "20"=>"20", "30"=>"30"));	
	  					?>
	  				</td>
	  			</tr>
	  		</table>
	  		<p><input type="submit" name="action" value="Update"/> <input type="submit" name="action" value="Delete"/></p>
	  	</form>
	    <form action="" method="post">
	      <textarea cols="200" rows="40">
	      	<?php 

	      	$log_name = get_option("view_wp_error_log_log");
	      	if ($log_name == "current" || $log_name == "") {
	      		$log_name = ABSPATH."error_log";
	      	} else {
	      		$log_name = ABSPATH."logs/".$log_name;
	      	}

	      	echo view_wp_error_log_last_lines($log_name, get_option("view_wp_error_log_no_lines")); ?>
	      </textarea>
	    </form>
	  </div>
	  </div>
	  <?php
	}

	public static function setupPage() { ?>
		<div class="wrap">
		  <h2>View WP Error Log</h2>  
		  <div class="postbox " style="display: block; ">
		  <div class="inside">
		  	<p>In order to use this plugin, you must setup your htaccess file and create a few files. Make sure that you have write permission for your .htaccess file and write permissions for your project. When you click "Install/Setup" it will update your .htaccess file, create a error_log file in your root directory and create a logs directory.</p>
		  	<p><strong>If you get a 500 Internal Server error its because your server won't allow you to change the location of the error log file. In this case you will have to edit the .htaccess file in your root directory and remove the WP ERROR LOG block of code.</strong></p>
		  	<form action="" method="post">
		  		<?php if(!VWPErrTomM8::is_file_writable(ABSPATH.".htaccess")) {
		  			echo ".htaccess file is unwritable, please make it writable.";
		  		} else { ?>
			  		<p><input type="submit" name="action" value="Install/Setup" /></p>
			  	<?php
			  	}?>
		  	</form>
		  </div>
			</div>
		</div>
		<?php
	}
}
?>