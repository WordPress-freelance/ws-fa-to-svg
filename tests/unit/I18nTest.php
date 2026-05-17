<?php
/**
 * Tests i18n — chargement du textdomain.
 *
 * @package WS_FA_To_SVG
 */

namespace WS_FA_To_SVG\Tests\Unit;

use WS_FA_To_SVG_i18n;
use WP_Mock;

class I18nTest extends WebStrategyTestCase {

	/** @test */
	public function load_plugin_textdomain_is_called_with_correct_args() {
		$captured_args = array();
		WP_Mock::userFunction( 'load_plugin_textdomain', array(
			'return' => function( $domain, $abs_path, $rel_path ) use ( &$captured_args ) {
				$captured_args = func_get_args();
				return true;
			},
		) );

		( new WS_FA_To_SVG_i18n() )->load_plugin_textdomain();

		$this->assertEquals( 'ws-fa-to-svg', $captured_args[0] );
		$this->assertFalse( $captured_args[1] );
		$this->assertStringContainsString( 'ws-fa-to-svg/languages/', $captured_args[2] );
	}
}
