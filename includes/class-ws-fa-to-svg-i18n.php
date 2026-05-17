<?php
/**
 * Chargement de la traduction.
 *
 * @package WS_FA_To_SVG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WS_FA_To_SVG_i18n
 */
class WS_FA_To_SVG_i18n {

	/**
	 * Charge le domain.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'ws-fa-to-svg',
			false,
			dirname( plugin_basename( WS_FA2SVG_FILE ) ) . '/languages/'
		);
	}
}
