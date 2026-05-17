<?php
/**
 * Bootstrap PHPUnit — ordre critique des 6 étapes.
 *
 * ORDRE OBLIGATOIRE :
 *   1. Constantes WP + plugin
 *   2. require vendor/autoload.php (charge Patchwork côté lib)
 *   3. WP_Mock::bootstrap() ← active Patchwork ici
 *   4. Stubs natifs PHP des fonctions WP JAMAIS mockées
 *   5. Classes stubs (WP_Error, wpdb, is_wp_error)
 *   6. require_once des classes du plugin à tester
 *
 * @package WS_FA_To_SVG
 */

// =========================================================================
// 1. Constantes WP + plugin
// =========================================================================

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wordpress/' );
}
if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes' );
}
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', true );
}
if ( ! defined( 'OBJECT' ) ) {
	define( 'OBJECT', 'OBJECT' );
}
if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}
if ( ! defined( 'ARRAY_N' ) ) {
	define( 'ARRAY_N', 'ARRAY_N' );
}
if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}
if ( ! defined( 'WEEK_IN_SECONDS' ) ) {
	define( 'WEEK_IN_SECONDS', 604800 );
}

$plugin_root = dirname( __DIR__ );

if ( ! defined( 'WS_FA2SVG_VERSION' ) ) {
	define( 'WS_FA2SVG_VERSION', '2.0.0' );
}
if ( ! defined( 'WS_FA2SVG_FILE' ) ) {
	define( 'WS_FA2SVG_FILE', $plugin_root . '/ws-fa-to-svg.php' );
}
if ( ! defined( 'WS_FA2SVG_PATH' ) ) {
	define( 'WS_FA2SVG_PATH', $plugin_root . '/' );
}
if ( ! defined( 'WS_FA2SVG_URL' ) ) {
	define( 'WS_FA2SVG_URL', 'http://example.test/wp-content/plugins/ws-fa-to-svg/' );
}
if ( ! defined( 'WS_FA2SVG_SLUG' ) ) {
	define( 'WS_FA2SVG_SLUG', 'ws-fa-to-svg' );
}
if ( ! defined( 'WS_FA2SVG_PRO_URL' ) ) {
	define( 'WS_FA2SVG_PRO_URL', 'https://wordpress-freelance.com/plugins/ws-fa-to-svg/' );
}

// =========================================================================
// 2. require vendor/autoload.php
// =========================================================================

require_once $plugin_root . '/vendor/autoload.php';

// =========================================================================
// 3. WP_Mock::bootstrap()
// =========================================================================

WP_Mock::bootstrap();

// =========================================================================
// 4. Stubs natifs PHP — fonctions WP JAMAIS mockées par les tests
// =========================================================================
// Ces fonctions sont déclarées en PHP natif après le bootstrap. Elles
// ne seront PAS mockables (mais on n'en a pas besoin — elles sont
// utilitaires simples).

if ( ! function_exists( 'absint' ) ) {
	function absint( $val ) { return abs( (int) $val ); }
}

if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $val ) { return is_string( $val ) ? stripslashes( $val ) : $val; }
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $val ) {
		return htmlspecialchars( (string) $val, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $val ) {
		return htmlspecialchars( (string) $val, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $val ) {
		$val = filter_var( (string) $val, FILTER_SANITIZE_URL );
		return htmlspecialchars( (string) $val, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	function esc_url_raw( $val ) {
		return filter_var( (string) $val, FILTER_SANITIZE_URL );
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $val ) {
		$val = (string) $val;
		$val = preg_replace( '/[\r\n\t]+/', ' ', $val );
		return trim( strip_tags( $val ) );
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data, $flags = 0, $depth = 512 ) {
		return json_encode( $data, $flags, $depth );
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $val ) { return (string) $val; }
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( $hook, $cb, $priority = 10, $args = 1 ) { return true; }
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $hook, $cb, $priority = 10, $args = 1 ) { return true; }
}

if ( ! function_exists( 'add_shortcode' ) ) {
	function add_shortcode( $tag, $cb ) { return true; }
}

if ( ! function_exists( 'register_activation_hook' ) ) {
	function register_activation_hook( $file, $cb ) { return true; }
}

if ( ! function_exists( 'register_deactivation_hook' ) ) {
	function register_deactivation_hook( $file, $cb ) { return true; }
}

if ( ! function_exists( 'register_uninstall_hook' ) ) {
	function register_uninstall_hook( $file, $cb ) { return true; }
}

if ( ! function_exists( 'plugin_basename' ) ) {
	function plugin_basename( $file ) {
		return basename( dirname( $file ) ) . '/' . basename( $file );
	}
}

if ( ! function_exists( 'plugin_dir_path' ) ) {
	function plugin_dir_path( $file ) { return dirname( $file ) . '/'; }
}

if ( ! function_exists( 'plugin_dir_url' ) ) {
	function plugin_dir_url( $file ) {
		return 'http://example.test/wp-content/plugins/' . basename( dirname( $file ) ) . '/';
	}
}

// =========================================================================
// 5. Classes stubs (utilisées par le plugin sans qu'on les charge depuis WP)
// =========================================================================

if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		public $errors  = array();
		public $error_data = array();

		public function __construct( $code = '', $message = '', $data = '' ) {
			if ( '' !== $code ) {
				$this->errors[ $code ][] = $message;
				if ( '' !== $data ) {
					$this->error_data[ $code ] = $data;
				}
			}
		}
		public function get_error_message() {
			$codes = array_keys( $this->errors );
			return isset( $this->errors[ $codes[0] ][0] ) ? $this->errors[ $codes[0] ][0] : '';
		}
		public function get_error_code() {
			$codes = array_keys( $this->errors );
			return isset( $codes[0] ) ? $codes[0] : '';
		}
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ) { return ( $thing instanceof WP_Error ); }
}

if ( ! class_exists( 'wpdb' ) ) {
	class wpdb { // phpcs:ignore
		public $prefix      = 'wp_';
		public $options     = 'wp_options';
		public $usermeta    = 'wp_usermeta';
		public $last_query  = '';
		public $insert_id   = 0;
		public $rows_affected = 0;

		public function query( $sql ) { $this->last_query = $sql; return 0; }
		public function prepare( $sql, ...$args ) { return vsprintf( str_replace( array( '%s', '%d' ), array( "'%s'", '%d' ), $sql ), $args ); }
		public function get_results( $sql, $output = OBJECT ) { return array(); }
		public function get_var( $sql ) { return null; }
		public function get_row( $sql ) { return null; }
		public function insert( $table, $data ) { $this->insert_id = 1; return 1; }
		public function update( $table, $data, $where ) { return 1; }
		public function delete( $table, $where ) { return 1; }
		public function esc_like( $val ) { return addcslashes( (string) $val, '_%\\' ); }
	}
}

if ( ! class_exists( 'WP_Styles' ) ) {
	class WP_Styles {
		public $queue = array();
	}
}

if ( ! class_exists( 'WP_Screen' ) ) {
	class WP_Screen {
		public $id = '';
	}
}

// =========================================================================
// 6. require_once des classes du plugin à tester
// =========================================================================

require_once $plugin_root . '/includes/class-ws-fa-to-svg-loader.php';
require_once $plugin_root . '/includes/class-ws-fa-to-svg-i18n.php';
require_once $plugin_root . '/includes/class-ws-fa-to-svg-transformer.php';
require_once $plugin_root . '/admin/class-ws-fa-to-svg-admin.php';
require_once $plugin_root . '/public/class-ws-fa-to-svg-public.php';
require_once $plugin_root . '/includes/class-ws-fa-to-svg.php';
