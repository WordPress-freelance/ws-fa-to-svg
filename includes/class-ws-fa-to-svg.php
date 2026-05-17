<?php
/**
 * Orchestrateur principal.
 *
 * @package WS_FA_To_SVG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WS_FA_To_SVG
 */
class WS_FA_To_SVG {

	/**
	 * Loader.
	 *
	 * @var WS_FA_To_SVG_Loader
	 */
	protected $loader;

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	protected $plugin_name;

	/**
	 * Version.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Constructeur.
	 */
	public function __construct() {
		$this->version     = defined( 'WS_FA2SVG_VERSION' ) ? WS_FA2SVG_VERSION : '2.0.0';
		$this->plugin_name = defined( 'WS_FA2SVG_SLUG' ) ? WS_FA2SVG_SLUG : 'ws-fa-to-svg';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Charge les classes du plugin.
	 *
	 * @return void
	 */
	private function load_dependencies() {
		require_once WS_FA2SVG_PATH . 'includes/class-ws-fa-to-svg-loader.php';
		require_once WS_FA2SVG_PATH . 'includes/class-ws-fa-to-svg-i18n.php';
		require_once WS_FA2SVG_PATH . 'includes/class-ws-fa-to-svg-transformer.php';
		require_once WS_FA2SVG_PATH . 'admin/class-ws-fa-to-svg-admin.php';
		require_once WS_FA2SVG_PATH . 'public/class-ws-fa-to-svg-public.php';

		$this->loader = new WS_FA_To_SVG_Loader();
	}

	/**
	 * Setup i18n.
	 *
	 * @return void
	 */
	private function set_locale() {
		$i18n = new WS_FA_To_SVG_i18n();
		$this->loader->add_action( 'plugins_loaded', $i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Hooks admin.
	 *
	 * @return void
	 */
	private function define_admin_hooks() {
		$admin = new WS_FA_To_SVG_Admin( $this->plugin_name, $this->version );

		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_notices', $admin, 'display_upgrade_notice' );
		$this->loader->add_action( 'wp_ajax_ws_fa2svg_dismiss_notice', $admin, 'dismiss_notice' );

		$basename = plugin_basename( WS_FA2SVG_FILE );
		$this->loader->add_filter( 'plugin_action_links_' . $basename, $admin, 'add_action_links' );
	}

	/**
	 * Hooks public.
	 *
	 * @return void
	 */
	private function define_public_hooks() {
		$public      = new WS_FA_To_SVG_Public( $this->plugin_name, $this->version );
		$transformer = new WS_FA_To_SVG_Transformer( $this->plugin_name, $this->version );

		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_scripts' );

		// Transformations de contenu — désactivable globalement.
		if ( apply_filters( 'ws_fa2svg_enabled', true ) ) {
			$this->loader->add_filter( 'the_content', $transformer, 'transform', 99, 1 );
			$this->loader->add_filter( 'widget_text_content', $transformer, 'transform', 99, 1 );
			$this->loader->add_filter( 'widget_block_content', $transformer, 'transform', 99, 1 );
			$this->loader->add_filter( 'render_block', $transformer, 'transform_block', 99, 2 );
		}

		// Dequeue + CSS inline.
		$this->loader->add_action( 'wp_enqueue_scripts', $transformer, 'dequeue_font_awesome', 999 );
		$this->loader->add_action( 'wp_head', $transformer, 'inline_css', 100 );

		// Purge cache.
		$this->loader->add_action( 'save_post', $transformer, 'purge_cache' );
		$this->loader->add_action( 'switch_theme', $transformer, 'purge_cache' );
	}

	/**
	 * Run.
	 *
	 * @return void
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Activation.
	 *
	 * @return void
	 */
	public static function on_activate() {
		delete_option( 'ws_fa2svg_unmapped' );
	}

	/**
	 * Désactivation : purge des transients.
	 *
	 * @return void
	 */
	public static function on_deactivate() {
		global $wpdb;
		$wpdb->query(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_ws\\_fa2svg\\_%' OR option_name LIKE '\\_transient\\_timeout\\_ws\\_fa2svg\\_%'"
		);
	}
}
