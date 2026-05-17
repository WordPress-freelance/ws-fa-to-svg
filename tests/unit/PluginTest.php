<?php
/**
 * Tests de l'orchestrateur principal.
 *
 * Couvre uniquement les méthodes statiques (les hooks injectés sont
 * intégrés et déjà couverts par les tests de LoaderTest).
 *
 * @package WS_FA_To_SVG
 */

namespace WS_FA_To_SVG\Tests\Unit;

use WS_FA_To_SVG;
use WP_Mock;

class PluginTest extends WebStrategyTestCase {

	/** @test */
	public function on_activate_resets_unmapped_option() {
		$deleted_key = null;
		WP_Mock::userFunction( 'delete_option', array(
			'return' => function( $key ) use ( &$deleted_key ) {
				$deleted_key = $key;
				return true;
			},
		) );

		WS_FA_To_SVG::on_activate();
		$this->assertEquals( 'ws_fa2svg_unmapped', $deleted_key );
	}

	/** @test */
	public function on_deactivate_purges_transients() {
		global $wpdb;
		$wpdb = new \wpdb();

		WS_FA_To_SVG::on_deactivate();

		$this->assertStringContainsString( 'DELETE FROM', $wpdb->last_query );
		$this->assertStringContainsString( 'transient', $wpdb->last_query );
		$this->assertStringContainsString( 'fa2svg', $wpdb->last_query );
	}

	/** @test */
	public function instance_can_be_built_and_run_without_error() {
		// plugin_basename est stub natif (bootstrap étape 4), pas besoin de mock.
		$plugin = new WS_FA_To_SVG();
		$this->assertInstanceOf( WS_FA_To_SVG::class, $plugin );

		$plugin->run();
		$this->assertTrue( true );
	}
}
