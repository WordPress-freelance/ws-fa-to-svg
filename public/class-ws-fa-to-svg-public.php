<?php
/**
 * Public — partie frontend du plugin.
 *
 * @package WS_FA_To_SVG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WS_FA_To_SVG_Public
 */
class WS_FA_To_SVG_Public {

	/**
	 * Slug.
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Constructeur.
	 *
	 * @param string $plugin_name Slug.
	 * @param string $version     Version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Enqueue styles (vide en FREE, CSS inline dans le head).
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		// CSS inline via Transformer::inline_css().
	}

	/**
	 * Enqueue scripts (aucun JS public).
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		// Aucun JS frontend.
	}
}
