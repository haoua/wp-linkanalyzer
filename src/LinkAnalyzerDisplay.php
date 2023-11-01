<?php

namespace ROCKET_WP_CRAWLER;

class LinkAnalyzerDisplay {
	/**
	 * Callback function to display admin option page of plugins.
	 *
	 * @return void
	 */
	public static function render_admin_page() {
		$data_links = array();
		$error      = array();
		if ( isset( $_GET['action'] ) ) {
			if ( 'run' === $_GET['action'] ) {
				$crawler = new WebCrawler();
				// Call the crawl method to start crawling.
				$data_links = $crawler->crawl();
				if ( count( $data_links ) === 0 ) {
					$error = array(
						'level'   => 'error',
						'details' => 'Une erreur est survenue au moment du crawl.',
					);
				}
			} elseif ( 'display' === $_GET['action'] ) {
				$data_links = self::get_data();
				if ( count( $data_links ) === 0 ) {
					$error = array(
						'level'   => 'warning',
						'details' => "Aucune information n'a été récupérée en base, avez-vous déjà lancé un crawl ?",
					);
				}
			}
		}

		echo '
		<div class="wrap">
			<div class="flex-container">
				<div class="flex-col">
					<h1>WP LinkAnalyzer</h1>
				</div>
				<div class="button-class">
					<a href="/wp-admin/admin.php?page=link-analyzer&action=run" class="button-primary">Lancer un crawl</a>
					<a href="/wp-admin/admin.php?page=link-analyzer&action=display" class="button-primary">Voir le résultat du dernier crawl</a>
				</div>
			</div>';

		if ( count( $data_links ) > 0 ) {

			echo '<div class="flex-container">';
			echo '<div class="flex-col"><h3>Résultat du crawl</h3></div>';
			echo '<div class="button-class">';
			if ( file_exists( WP_PLUGIN_DIR . '/wp-linkanalyzer/data/homepage.html' ) ) {
				echo '<a class="button-sec	" href="' . esc_url( plugins_url() . '/wp-linkanalyzer/data/homepage.html' ) . '" target="_blank">Voir la page d\'accueil</a>';
			}

			if ( file_exists( WP_PLUGIN_DIR . '/wp-linkanalyzer/data/sitemap.html' ) ) {
				echo '<a class="button-sec" href="' . esc_url( plugins_url() . '/wp-linkanalyzer/data/sitemap.html' ) . '" target="_blank">Voir le sitemap</a>';
			}
			echo '</div>';
			echo '</div>';

			echo '<table class="widefat">';
			echo '<thead>';
			echo '<tr>';
			echo '<th>Nom du lien</th>';
			echo '<th>URL du lien</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';

			foreach ( $data_links as $link ) {
				$link = (array) $link;
				echo '<tr>';
				echo '<td>' . esc_html( $link['link_text'] ) . '</td>';
				echo '<td><a href="' . esc_url( $link['href'] ) . '">' . esc_url( $link['href'] ) . '</a></td>';
				echo '</tr>';
			}

			echo '</tbody>';
			echo '</table>';
			echo '</div>';
		}

		if ( count( $error ) > 0 ) {
			echo '<div class="' . esc_html( 'error-' . $error['level'] ) . '">' . esc_html( $error['details'] ) . '</div>';
		}

		echo '
		</div>';
	}

	/**
	 * Get crawl result data, either from cache or db.
	 * If data is retrieved from db, it's then stored in cache for next use.
	 *
	 * @return array|object|null Result data retrieved from the cache or database.
	 */
	public static function get_data() {
		global $wp_object_cache;
		$cached_data = false;
		$cached_data = wp_cache_get( 'crawl_result' );

		if ( false !== $cached_data ) {
			// Data found in the cache.
			return $cached_data;
		} else {
			// Data not found in the cache, fetch it from db.
			$data = (array) self::get_data_from_database();

			// Store found data in cache.
			wp_cache_set( 'crawl_result', $data, '', 86400 );

			// Return the fetched data.
			return $data;
		}
	}

	/**
	 * It retrieves data from the 'linkanalyzer_links' table and returns it as an array.
	 * It retrieves data from the most recent crawl using 'crawl_id' from the 'linkanalyzer_crawl' table.
	 *
	 * @return array|object|null    An array of result rows or null if no results are found.
	 */
	public static function get_data_from_database() {
		global $wpdb;

		$query =
			'SELECT l.link_text, l.href
			FROM ' . $wpdb->prefix . 'linkanalyzer_links AS l
			JOIN ' . $wpdb->prefix . 'linkanalyzer_crawl AS c ON l.crawl_id = c.crawl_id
			WHERE c.crawl_id = (
				SELECT MAX(crawl_id)
				FROM ' . $wpdb->prefix . 'linkanalyzer_crawl
			)';

		$results = $wpdb->get_results( $query );

		return $results;
	}
}
