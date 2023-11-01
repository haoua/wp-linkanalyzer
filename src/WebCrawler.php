<?php

namespace ROCKET_WP_CRAWLER;

class WebCrawler {

	/**
	 * URL of the website to be crawled.
	 *
	 * @var string $url
	 */
	private $url;

	/**
	 * The unique ID of current crawl run.
	 *
	 * @var int $crawl_id
	 */
	private $crawl_id;

	/**
	 * The WP db object for database operations.
	 *
	 * @var wpdb $wpdb
	 */
	private $wpdb;

	/**
	 * Initialize the LinkAnalyzer crawler.
	 */
	public function __construct() {
		$this->url = get_home_url();

		global $wpdb;
		$this->wpdb = $wpdb;

		$this->crawl_id = $this->register_run();
	}


	/**
	 * Initiates a crawl, in ordre to extract and store internal links.
	 *
	 * 1. Clears previous crawl results.
	 * 2. Fetches the content of the homepage.
	 * 3. Extracts internal links from the homepage.
	 * 4. Saves the homepage's HTML content to a file.
	 * 5. Stores the extracted internal links in the database.
	 *
	 * @return  array   An array of internal links, each represented as an associative array.
	 *                  Each array item includes information such as the crawl id, node content and href attributes
	 */
	public function crawl() {
		$this->clear_prev_results();
		$page_content = $this->fetch_content( $this->url );

		$internal_links = $this->extract_internal_links( $page_content );
		// Save homepage.html.
		global $wp_filesystem;

		// Check if the WP_Filesystem class is available.
		if ( ! isset( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$data_dir = WP_PLUGIN_DIR . '/wp-linkanalyzer/data/';
		$wp_filesystem->put_contents( $data_dir . 'homepage.html', $page_content );

		$this->store_results( $internal_links );

		$this->update_finished_run();

		return $internal_links;
	}

	/**
	 * Fetchs the content of a web page given its URL.
	 * Retrieves the response body, containing the content of the web page.
	 *
	 * @param   string $url    URL to fetch content from.
	 * @return  string|false            Content of the page or false on failure.
	 */
	private function fetch_content( $url ) {
		$response     = wp_remote_get( $url );
		$page_content = wp_remote_retrieve_body( $response );

		return $page_content;
	}


	/**
	 * Clears previous analysis results stored in plugin folder.
	 *
	 * @return void
	 */
	private function clear_prev_results() {
		$data_dir = WP_PLUGIN_DIR . '/wp-linkanalyzer/data/';
		// Delete sitemap and html file.
		$files_to_del = array( $data_dir . 'sitemap.html', $data_dir . 'homepage.html' );

		foreach ( $files_to_del as $file ) {
			if ( file_exists( $file ) ) {
				wp_delete_file( $file );
			}
		}
	}

	/**
	 * Stores an array of links in the database.
	 * The links are crawled and already analyzed.
	 *
	 * @param   array $links_array    Array of links.
	 * @return  void
	 */
	private function store_results( $links_array ) {
		foreach ( $links_array as $link ) {
			$this->wpdb->insert( $this->wpdb->prefix . 'linkanalyzer_links', $link );
		}
	}


	/**
	 * Extracts internal links from HTML content by parsing it.
	 * Only extracts internal links. Normalizes and prepares links to be stored in db.
	 *
	 * @param   string $html   HTML content to parse for link search.
	 * @return  array           An array of internal links containing crawl_id, node content and href attributes
	 */
	private function extract_internal_links( $html ) {
		$internal_links = array();
		$doc            = new \DOMDocument();
		@$doc->loadHTML( $html );

		$hrefs = $doc->getElementsByTagName( 'a' );
		foreach ( $hrefs as $href ) {
			$link = $this->normalize_url( $href->getAttribute( 'href' ) );
			if ( strpos( $link, $this->url ) === 0 ) {
				$internal_links[] = array(
					'crawl_id'  => $this->crawl_id,
					'link_text' => $href->textContent,
					'href'      => $link,
				);
			}
		}

		return $internal_links;
	}


	/**
	 * Nprmalizes URLs, resolves relative URLs to absolute URLs as needed.
	 *
	 * @param   string $url    URL to be normalized.
	 * @return  string          Normalized and valid URL.
	 */
	private function normalize_url( $url ) {
		// Trim extra whitespace.
		$url = trim( $url );

		// If URL is not in a valid format, resolve it to an absolute URL.
		if ( filter_var( $url, FILTER_VALIDATE_URL ) === false ) {
			$url = rtrim( $this->url, '/' ) . '/' . ltrim( $url, '/' );
		}

		return $url;
	}

	/**
	 * Inserts a new crawl run entry in db (including the start date of the crawl).
	 * The crawl run is either initiated when an admin triggers the crawl of the plugin pages
	 * or hourly when cron uses it.
	 *
	 * @return int|false The insert ID of the new crawl run, or false on failure.
	 */
	private function register_run() {
		// Data to be inserted.
		$data_insert = array( 'start_date' => time() );

		$this->wpdb->insert( $this->wpdb->prefix . 'linkanalyzer_crawl', $data_insert );

		return $this->wpdb->insert_id;
	}

	/**
	 * Updates the end date of the current crawl in the database.
	 * Helps mark the completion of crawl.
	 *
	 * @return void
	 */
	private function update_finished_run() {
		// Data to be updated.
		$data_update  = array( 'end_date' => time() );
		$update_where = array( 'crawl_id' => $this->crawl_id );

		$this->wpdb->update( $this->wpdb->prefix . 'linkanalyzer_crawl', $data_update, $update_where );
	}
}
