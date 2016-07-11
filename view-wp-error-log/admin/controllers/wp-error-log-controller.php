<?php
final class WPErrorLogController {
	public static function indexAction() {
		WPErrorLogPage::indexPage();
	}

	public static function updateAction() {
		update_option("view_wp_error_log_no_lines", $_POST["view_wp_error_log_no_lines"]);
		update_option("view_wp_error_log_log", $_POST["view_wp_error_log_log"]);
	}

	public static function deleteAction() {
		if (preg_match("/^log_/i", $_POST["view_wp_error_log_log"])) {
			@unlink(ABSPATH."logs/".$_POST["view_wp_error_log_log"]);
			update_option("view_wp_error_log_log", "current");
		} else {
			echo("<div class='updated below-h2'><p>Sorry, you can't delete the current log file, you can only delete past ones.</p></div>");
		}
	}

	public static function setupAction() {
		WPErrorLogPage::setupPage();
	}

	public static function setupCreateAction() {
		
		if (!file_exists(ABSPATH.'error_log')) {
			// If log file doesn't exist.
			VWPErrTomM8::write_to_htaccess_file("WP ERROR LOG", "<Files error_log>\norder allow,deny\ndeny from all\n</Files>\nphp_flag  log_errors on\nphp_value error_log error_log");
			VWPErrTomM8::write_to_file("", ABSPATH.'error_log');
		} else {
			// Log file does exist.
			VWPErrTomM8::write_to_htaccess_file("WP ERROR LOG", "<Files error_log>\norder allow,deny\ndeny from all\n</Files>\n");
		}
		@mkdir(ABSPATH."logs");
		VWPErrTomM8::write_to_file("Deny from all", ABSPATH.'/logs/.htaccess');
		WPErrorLogPage::indexPage();
	}
}
?>