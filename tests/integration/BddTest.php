<?php
/**
 * Tests d'intégration BDD — vérifie que les insertions/lectures dans
 * wp_options fonctionnent contre une vraie instance MySQL.
 *
 * Ces tests ne tournent qu'en CI ou via `vendor/bin/phpunit -c phpunit-integration.xml`
 * après `bin/install-wp-tests.sh`.
 *
 * @package WS_FA_To_SVG
 */

class WS_FA2SVG_BDD_Test extends WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		delete_option( 'ws_fa2svg_unmapped' );
		$this->purge_transients();
	}

	public function tearDown(): void {
		delete_option( 'ws_fa2svg_unmapped' );
		$this->purge_transients();
		parent::tearDown();
	}

	private function purge_transients() {
		global $wpdb;
		$wpdb->query(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ws_fa2svg_%' OR option_name LIKE '_transient_timeout_ws_fa2svg_%'"
		);
	}

	/**
	 * Transform crée bien les transients en BDD.
	 */
	public function test_transform_persists_cache_in_database() {
		$transformer = new WS_FA_To_SVG_Transformer( 'ws-fa-to-svg', '2.0.0' );
		$result      = $transformer->transform( '<i class="fa fa-house"></i>' );

		$this->assertStringContainsString( '<svg', $result );

		// Vérifier qu'au moins un transient ws_fa2svg_ existe en DB.
		global $wpdb;
		$count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_ws_fa2svg_%'"
		);
		$this->assertGreaterThan( 0, $count, 'Au moins un transient doit être créé.' );
	}

	/**
	 * Les icônes manquantes sont bien stockées dans wp_options.
	 */
	public function test_unknown_icons_persist_in_wp_options() {
		$transformer = new WS_FA_To_SVG_Transformer( 'ws-fa-to-svg', '2.0.0' );
		$transformer->transform( '<i class="fa fa-totally-fake-icon"></i><i class="fa fa-fake-2"></i>' );

		$stored = get_option( 'ws_fa2svg_unmapped' );
		$this->assertIsArray( $stored );
		$this->assertArrayHasKey( 'totally-fake-icon', $stored );
		$this->assertArrayHasKey( 'fake-2', $stored );
	}

	/**
	 * purge_cache() supprime effectivement les transients en BDD.
	 */
	public function test_purge_cache_removes_transients_from_database() {
		// Setup : créer 3 transients manuellement.
		set_transient( 'ws_fa2svg_test_a', 'val_a', 3600 );
		set_transient( 'ws_fa2svg_test_b', 'val_b', 3600 );
		set_transient( 'ws_fa2svg_test_c', 'val_c', 3600 );

		// Pré-vérif : ils existent.
		$this->assertEquals( 'val_a', get_transient( 'ws_fa2svg_test_a' ) );

		// Act.
		( new WS_FA_To_SVG_Transformer( 'ws-fa-to-svg', '2.0.0' ) )->purge_cache();

		// Post-vérif : tous supprimés.
		$this->assertFalse( get_transient( 'ws_fa2svg_test_a' ) );
		$this->assertFalse( get_transient( 'ws_fa2svg_test_b' ) );
		$this->assertFalse( get_transient( 'ws_fa2svg_test_c' ) );
	}

	/**
	 * on_deactivate() purge les transients (intégration).
	 */
	public function test_on_deactivate_purges_transients_from_database() {
		set_transient( 'ws_fa2svg_pending', 'will_die', 3600 );
		$this->assertEquals( 'will_die', get_transient( 'ws_fa2svg_pending' ) );

		WS_FA_To_SVG::on_deactivate();

		$this->assertFalse( get_transient( 'ws_fa2svg_pending' ) );
	}

	/**
	 * Counters d'unmapped icons accumulent correctement à travers plusieurs transforms.
	 */
	public function test_unmapped_counter_accumulates_across_multiple_transforms() {
		$transformer = new WS_FA_To_SVG_Transformer( 'ws-fa-to-svg', '2.0.0' );

		// 3 passages → cumul 3 pour "unknown-a".
		// On force le bypass cache à chaque appel pour bien réécrire l'option.
		delete_transient( 'ws_fa2svg_' . md5( '<i class="fa fa-unknown-a"></i>' ) );
		$transformer->transform( '<i class="fa fa-unknown-a"></i>' );

		delete_transient( 'ws_fa2svg_' . md5( '<div><i class="fa fa-unknown-a"></i></div>' ) );
		$transformer->transform( '<div><i class="fa fa-unknown-a"></i></div>' );

		delete_transient( 'ws_fa2svg_' . md5( '<span><i class="fa fa-unknown-a"></i></span>' ) );
		$transformer->transform( '<span><i class="fa fa-unknown-a"></i></span>' );

		$stored = get_option( 'ws_fa2svg_unmapped' );
		$this->assertArrayHasKey( 'unknown-a', $stored );
		$this->assertGreaterThanOrEqual( 3, $stored['unknown-a'] );
	}
}
