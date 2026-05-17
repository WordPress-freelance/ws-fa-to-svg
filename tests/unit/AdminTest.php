<?php
/**
 * Tests de la classe Admin (notice upgrade + dismiss).
 *
 * @package WS_FA_To_SVG
 */

namespace WS_FA_To_SVG\Tests\Unit;

use WS_FA_To_SVG_Admin;
use WP_Mock;

class AdminTest extends WebStrategyTestCase {

	private function make_admin() {
		return new WS_FA_To_SVG_Admin( 'ws-fa-to-svg', '2.0.0' );
	}

	// ---------------------------------------------------------------- display_upgrade_notice

	/** @test */
	public function notice_is_hidden_when_user_dismissed_recently() {
		WP_Mock::userFunction( 'get_current_user_id', array( 'return' => 1 ) );
		WP_Mock::userFunction( 'get_user_meta', array(
			'args'   => array( 1, 'ws_fa2svg_notice_dismissed', true ),
			'return' => time() - 60, // dismiss il y a 1 minute
		) );

		ob_start();
		$this->make_admin()->display_upgrade_notice();
		$out = ob_get_clean();

		$this->assertSame( '', $out, 'La notice ne doit pas apparaître si dismiss récent.' );
	}

	/** @test */
	public function notice_is_hidden_without_manage_options_capability() {
		WP_Mock::userFunction( 'get_current_user_id', array( 'return' => 1 ) );
		WP_Mock::userFunction( 'get_user_meta', array( 'return' => '' ) );
		WP_Mock::userFunction( 'current_user_can', array(
			'args'   => array( 'manage_options' ),
			'return' => false,
		) );

		ob_start();
		$this->make_admin()->display_upgrade_notice();
		$out = ob_get_clean();

		$this->assertSame( '', $out );
	}

	/** @test */
	public function notice_is_hidden_when_no_unmapped_icons() {
		WP_Mock::userFunction( 'get_current_user_id', array( 'return' => 1 ) );
		WP_Mock::userFunction( 'get_user_meta', array( 'return' => '' ) );
		WP_Mock::userFunction( 'current_user_can', array( 'return' => true ) );
		WP_Mock::userFunction( 'get_option', array(
			'args'   => array( 'ws_fa2svg_unmapped', array() ),
			'return' => array(),
		) );

		ob_start();
		$this->make_admin()->display_upgrade_notice();
		$out = ob_get_clean();

		$this->assertSame( '', $out );
	}

	/** @test */
	public function notice_is_displayed_when_unmapped_present_and_not_dismissed() {
		WP_Mock::userFunction( 'get_current_user_id', array( 'return' => 1 ) );
		WP_Mock::userFunction( 'get_user_meta', array( 'return' => '' ) );
		WP_Mock::userFunction( 'current_user_can', array( 'return' => true ) );
		WP_Mock::userFunction( 'get_option', array(
			'return' => array( 'ambulance' => 5, 'bullhorn' => 3 ),
		) );
		WP_Mock::userFunction( 'wp_create_nonce', array( 'return' => 'fake-nonce' ) );
		WP_Mock::userFunction( 'admin_url', array(
			'return' => function( $path = '' ) { return 'http://example.test/wp-admin/' . $path; },
		) );

		ob_start();
		$this->make_admin()->display_upgrade_notice();
		$out = ob_get_clean();

		$this->assertStringContainsString( 'notice-warning', $out );
		$this->assertStringContainsString( 'WS Font Awesome to SVG', $out );
		$this->assertStringContainsString( 'ambulance', $out );
		$this->assertStringContainsString( 'Voir l', $out ); // "Voir l'offre PRO →" (apostrophe encodée)
		$this->assertStringContainsString( 'wordpress-freelance.com', $out );
	}

	/** @test */
	public function notice_shows_preview_of_first_five_unmapped_only() {
		WP_Mock::userFunction( 'get_current_user_id', array( 'return' => 1 ) );
		WP_Mock::userFunction( 'get_user_meta', array( 'return' => '' ) );
		WP_Mock::userFunction( 'current_user_can', array( 'return' => true ) );
		WP_Mock::userFunction( 'get_option', array(
			'return' => array(
				'icon-a' => 1, 'icon-b' => 1, 'icon-c' => 1,
				'icon-d' => 1, 'icon-e' => 1, 'icon-f' => 1,
				'icon-g' => 1,
			),
		) );
		WP_Mock::userFunction( 'wp_create_nonce', array( 'return' => 'n' ) );
		WP_Mock::userFunction( 'admin_url', array( 'return' => 'http://x/' ) );

		ob_start();
		$this->make_admin()->display_upgrade_notice();
		$out = ob_get_clean();

		// 5 premières affichées + mention "+2 autres"
		$this->assertStringContainsString( 'icon-a', $out );
		$this->assertStringContainsString( 'icon-e', $out );
		$this->assertStringContainsString( '+2 autres', $out );
		$this->assertStringNotContainsString( 'icon-g', $out );
	}

	// ---------------------------------------------------------------- dismiss_notice

	/** @test */
	public function dismiss_notice_updates_user_meta_when_authorized() {
		WP_Mock::userFunction( 'check_ajax_referer', array(
			'args'   => array( 'ws_fa2svg_dismiss_notice', 'nonce' ),
			'return' => true,
		) );
		WP_Mock::userFunction( 'current_user_can', array( 'return' => true ) );
		WP_Mock::userFunction( 'get_current_user_id', array( 'return' => 1 ) );

		$meta_updated = false;
		WP_Mock::userFunction( 'update_user_meta', array(
			'return' => function( $user_id, $key, $value ) use ( &$meta_updated ) {
				if ( 1 === $user_id && 'ws_fa2svg_notice_dismissed' === $key ) {
					$meta_updated = true;
				}
				return true;
			},
		) );

		// wp_send_json_success est mocké comme une fonction normale (pas wp_die en test).
		$sent = false;
		WP_Mock::userFunction( 'wp_send_json_success', array(
			'return' => function() use ( &$sent ) { $sent = true; },
		) );

		$this->make_admin()->dismiss_notice();

		$this->assertTrue( $meta_updated );
		$this->assertTrue( $sent );
	}

	/** @test */
	public function dismiss_notice_refuses_without_capability() {
		WP_Mock::userFunction( 'check_ajax_referer', array( 'return' => true ) );
		WP_Mock::userFunction( 'current_user_can', array( 'return' => false ) );

		$error_sent = false;
		WP_Mock::userFunction( 'wp_send_json_error', array(
			'return' => function() use ( &$error_sent ) { $error_sent = true; },
		) );
		// wp_send_json_success ne doit pas être appelée → on ne la mocke pas.

		$this->make_admin()->dismiss_notice();
		$this->assertTrue( $error_sent );
	}

	// ---------------------------------------------------------------- add_action_links

	/** @test */
	public function action_links_prepend_pro_upgrade_link() {
		$result = $this->make_admin()->add_action_links( array(
			'<a href="#">Deactivate</a>',
		) );

		$this->assertCount( 2, $result );
		$this->assertStringContainsString( 'wordpress-freelance.com', $result[0] );
		$this->assertStringContainsString( 'Passer en PRO', $result[0] );
		$this->assertStringContainsString( '#7C5CBF', $result[0] );
	}
}
