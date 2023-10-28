<?php
/**
 * Plugin main class
 *
 * @package     WP LinkAnalyze
 * @since       2023
 * @author      Haoua Soualmia
 * @license     GPL-2.0-or-later
 */

namespace ROCKET_WP_CRAWLER;

/**
 * Main plugin class. It manages initialization, install, and activations.
 */
class Rocket_Wpc_Plugin_Class {
	/**
	 * Manages plugin initialization
	 *
	 * @return void
	 */
	public function __construct() {

		// Register plugin lifecycle hooks.
		register_deactivation_hook( ROCKET_CRWL_PLUGIN_FILENAME, array( $this, 'wpc_deactivate' ) );
	}

	/**
	 * Handles plugin activation:
	 *
	 * @return void
	 */
	public static function wpc_activate() {
		global $wpdb;

		// Security checks.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		$plugin = isset( $_REQUEST['plugin'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) ) : '';
		check_admin_referer( "activate-plugin_{$plugin}" );

		// Database creation.
		$sql = 'CREATE TABLE ' . $wpdb->prefix . 'linkanalyzer_links (
			link_id mediumint(8) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
			link_text varchar(255) NOT NULL,
			href varchar(255) NOT NULL
		);';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Handles plugin deactivation
	 *
	 * @return void
	 */
	public function wpc_deactivate() {
		// Security checks.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		$plugin = isset( $_REQUEST['plugin'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) ) : '';
		check_admin_referer( "deactivate-plugin_{$plugin}" );
	}

	/**
	 * Handles plugin uninstall
	 *
	 * @return void
	 */
	public static function wpc_uninstall() {
		// Security checks.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		// Delete DB if plugin is uninstall.
		global $wpdb;
		$sql = 'DROP TABLE' . $wpdb->prefix . 'linkanalyzer_links';
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
