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

require_once plugin_dir_path( __FILE__ ) . 'WebCrawler.php';
require_once plugin_dir_path( __FILE__ ) . 'LinkAnalyzerDisplay.php';

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
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_custom_admin_styles' ) );
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

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Database creation.
		$sql_links_table = 'CREATE TABLE ' . $wpdb->prefix . 'linkanalyzer_links (
			link_id mediumint(8) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
			link_text varchar(255) NOT NULL,
			href varchar(255) NOT NULL
		);';

		dbDelta( $sql_links_table );

		// Database creation.
		$sql_crawl_table = 'CREATE TABLE ' . $wpdb->prefix . 'linkanalyzer_crawl (
			crawl_id mediumint(8) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
			start_date varchar(255) NOT NULL,
			end_date varchar(255) NOT NULL
		);';

		dbDelta( $sql_crawl_table );
	}


	/**
	 * Callback function to add an admin menu for plugin.
	 *
	 * @return void
	 */
	public function add_admin_menu() {
		add_menu_page(
			'WP LinkAnalyzer',
			'LinkAnalyzer',
			'manage_options',
			'link-analyzer',
			array( 'ROCKET_WP_CRAWLER\LinkAnalyzerDisplay', 'render_admin_page' )
		);
	}

	/**
	 * Callback function to display admin option page of plugins.
	 *
	 * @return void
	 */
	public function arender_admin_page() {
		$data_links = array();
		if ( isset( $_GET['action'] ) && 'run' === $_GET['action'] ) {
			$crawler = new WebCrawler();

			// Call the crawl method to start crawling.
			$data_links = $crawler->crawl();
		}

		echo '
		<div class="wrap">
			<h2>WP LinkAnalyzer</h2>
			<a href="http://localhost:3001/wp-admin/admin.php?page=link-analyzer&action=run">Lancer un crawl</a>
		</div>';
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

	/**
	 * Enqueues custom styles for plugin
	 */
	public function enqueue_custom_admin_styles() {
		wp_enqueue_style( 'custom-admin-styles', plugin_dir_url( __FILE__ ) . '/css/admin-styles.css' );
	}
}
