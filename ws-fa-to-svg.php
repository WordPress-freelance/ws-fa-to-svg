<?php
/**
 * Plugin Name:       WS Font Awesome to SVG
 * Plugin URI:        https://wordpress-freelance.com/plugins/ws-fa-to-svg/
 * Description:       Remplace les icônes Font Awesome par des SVG inline pour booster PageSpeed. 30 icônes essentielles. <strong>Version PRO : 300+ icônes + scanner intégré.</strong>
 * Version:           2.0.0
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * Author:            WebStrategy
 * Author URI:        https://wordpress-freelance.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ws-fa-to-svg
 * Domain Path:       /languages
 *
 * @package WS_FA_To_SVG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WS_FA2SVG_VERSION', '2.0.0' );
define( 'WS_FA2SVG_FILE', __FILE__ );
define( 'WS_FA2SVG_PATH', plugin_dir_path( __FILE__ ) );
define( 'WS_FA2SVG_URL', plugin_dir_url( __FILE__ ) );
define( 'WS_FA2SVG_SLUG', 'ws-fa-to-svg' );
define( 'WS_FA2SVG_PRO_URL', 'https://wordpress-freelance.com/plugins/ws-fa-to-svg/' );

require_once WS_FA2SVG_PATH . 'includes/class-ws-fa-to-svg.php';

/**
 * Bootstrap.
 *
 * @return void
 */
function ws_fa2svg_run() {
	$plugin = new WS_FA_To_SVG();
	$plugin->run();
}
add_action( 'plugins_loaded', 'ws_fa2svg_run' );

register_activation_hook( __FILE__, array( 'WS_FA_To_SVG', 'on_activate' ) );
register_deactivation_hook( __FILE__, array( 'WS_FA_To_SVG', 'on_deactivate' ) );
