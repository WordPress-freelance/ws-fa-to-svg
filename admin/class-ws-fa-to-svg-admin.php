<?php
/**
 * Admin — gestion des notices d'upgrade vers la version PRO.
 *
 * @package WS_FA_To_SVG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WS_FA_To_SVG_Admin
 */
class WS_FA_To_SVG_Admin {

	/**
	 * Slug du plugin.
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
	 * Enqueue styles admin.
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		// Pas de CSS admin nécessaire pour la FREE (notice inline).
	}

	/**
	 * Enqueue scripts admin.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		// Pas de JS admin nécessaire pour la FREE.
	}

	/**
	 * Affiche la notice "upgrade PRO" si des icônes non mappées sont détectées.
	 *
	 * @return void
	 */
	public function display_upgrade_notice() {
		// Skip si l'utilisateur l'a dismissée.
		$dismissed = get_user_meta( get_current_user_id(), 'ws_fa2svg_notice_dismissed', true );
		if ( $dismissed && (int) $dismissed > ( time() - WEEK_IN_SECONDS * 4 ) ) {
			return;
		}

		// Skip si pas de droits.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$unmapped = get_option( 'ws_fa2svg_unmapped', array() );
		if ( empty( $unmapped ) || ! is_array( $unmapped ) ) {
			return;
		}

		$count = count( $unmapped );
		if ( $count < 1 ) {
			return;
		}

		$preview = array_slice( array_keys( $unmapped ), 0, 5 );
		$preview_str = implode( ', ', array_map( 'esc_html', $preview ) );
		if ( $count > 5 ) {
			$preview_str .= sprintf( ' (+%d autres)', $count - 5 );
		}

		include WS_FA2SVG_PATH . 'admin/partials/ws-fa-to-svg-admin-notice-upgrade.php';
	}

	/**
	 * AJAX : dismiss de la notice (4 semaines).
	 *
	 * @return void
	 */
	public function dismiss_notice() {
		check_ajax_referer( 'ws_fa2svg_dismiss_notice', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
			return;
		}
		update_user_meta( get_current_user_id(), 'ws_fa2svg_notice_dismissed', time() );
		wp_send_json_success();
		return;
	}

	/**
	 * Ajoute un lien "PRO" dans la liste des plugins.
	 *
	 * @param array $links Action links.
	 * @return array
	 */
	public function add_action_links( $links ) {
		$pro_link = '<a href="' . esc_url( WS_FA2SVG_PRO_URL ) . '" target="_blank" rel="noopener" style="color:#7C5CBF;font-weight:600;">' . esc_html__( 'Passer en PRO', 'ws-fa-to-svg' ) . '</a>';
		array_unshift( $links, $pro_link );
		return $links;
	}
}
