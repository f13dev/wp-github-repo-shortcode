<?php
/*
Plugin Name: View WP Error Log
Plugin URI: http://wordpress.org/extend/plugins/view-wp-error-log/
Description: Simply view the error log

Installation:

1) Install WordPress 3.8 or higher

2) Download the following file:

http://downloads.wordpress.org/plugin/view-wp-error-log.zip

3) Login to WordPress admin, click on Plugins / Add New / Upload, then upload the zip file you just downloaded.

4) Activate the plugin.

Version: 3.1
Author: TheOnlineHero - Tom Skroza
License: GPL2
*/

if (!class_exists("VWPErrTomM8")) {
	include_once("lib/tom-m8te.php");
}
include_once("admin/controllers/wp-error-log-controller.php");
include_once("admin/pages/wp-error-log-page.php");

register_activation_hook( __FILE__, 'view_wp_error_log_activate' );
function view_wp_error_log_activate() {
	add_option("view_wp_error_log_no_lines", "10");
}

//call register settings function
add_action( 'admin_init', 'register_view_wp_error_log_settings' );
function register_view_wp_error_log_settings() {
  register_setting( 'view-wp-error-log-settings-group', 'view_wp_error_log_log' );
}

add_action('admin_menu', 'register_view_wp_error_log_page');
function register_view_wp_error_log_page() {
  add_menu_page('WP Error Log', 'WP Error Log', 'manage_options', 'view-wp-error-log/view-wp-error-log.php', 'view_wp_error_log_router');
}

function view_wp_error_log_router() { 

	if (!file_exists(ABSPATH."error_log") || !file_exists(ABSPATH."logs")) {
		if ($_POST["action"] == "Install/Setup") {
			WPErrorLogController::setupCreateAction();
		} else {
			WPErrorLogController::setupAction();
		}
	} else {
		if ($_POST["action"] == "Update") {
			WPErrorLogController::updateAction();
		}
		if ($_POST["action"] == "Delete") {
			WPErrorLogController::deleteAction();
		}
		WPErrorLogController::indexAction();
	}
	
}

add_action( 'wp', 'view_wp_error_log_setup_schedule' );
/**
 * On an early action hook, check if the hook is scheduled - if not, schedule it.
 */
function view_wp_error_log_setup_schedule() {
	if ( ! wp_next_scheduled( 'view_wp_error_log_daily_event' ) ) {
		wp_schedule_event( time(), 'daily', 'view_wp_error_log_daily_event');
	}
}


add_action( 'view_wp_error_log_daily_event', 'view_wp_error_log_do_this_daily' );
/**
 * On the scheduled action hook, run a function.
 */
function view_wp_error_log_do_this_daily() {
  @copy(ABSPATH.'error_log', ABSPATH.'/logs/log_'.date("Ymd", (time() - 60 * 60 * 24)));
  VWPErrTomM8::write_to_file("", ABSPATH.'error_log');
}


/**
 * Reads lines from end of file. Memory-safe.
 *
 * @link http://stackoverflow.com/questions/6451232/php-reading-large-files-from-end/6451391#6451391
 *
 * @param string  $path
 * @param integer $line_count
 * @param integer $block_size
 * 
 * @return string
 */
function view_wp_error_log_last_lines( $path, $line_count, $block_size = 512 ) {
	$lines = array();

	// we will always have a fragment of a non-complete line
	// keep this in here till we have our next entire line.
	$leftover = '';

	$fh = fopen( $path, 'r' );
	// go to the end of the file
	fseek( $fh, 0, SEEK_END );

	do {
		// need to know whether we can actually go back
		// $block_size bytes
		$can_read = $block_size;

		if ( ftell( $fh ) <= $block_size )
			$can_read = ftell( $fh );

		if ( empty( $can_read ) )
			break;

		// go back as many bytes as we can
		// read them to $data and then move the file pointer
		// back to where we were.
		fseek( $fh, - $can_read, SEEK_CUR );
		$data  = fread( $fh, $can_read );
		$data .= $leftover;
		fseek( $fh, - $can_read, SEEK_CUR );

		// split lines by \n. Then reverse them,
		// now the last line is most likely not a complete
		// line which is why we do not directly add it, but
		// append it to the data read the next time.
		$split_data = array_reverse( explode( "\n", $data ) );
		$new_lines  = array_slice( $split_data, 0, - 1 );
		$lines      = array_merge( $lines, $new_lines );
		$leftover   = $split_data[count( $split_data ) - 1];
	} while ( count( $lines ) < $line_count && ftell( $fh ) != 0 );

	if ( ftell( $fh ) == 0 )
		$lines[] = $leftover;

	fclose( $fh );
	// Usually, we will read too many lines, correct that here.
	$content = "";
	$tempArray = array_slice($lines, 0, $line_count);
	foreach ($tempArray as $line) {
		$content .= substr($line, 0, 900)."\n";
	}
	return $content;
}
?>