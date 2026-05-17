<?php
/**
 * Tests du WPPB Loader.
 *
 * Stratégie : on n'a pas besoin de mocker `add_action` ici puisque celles-ci
 * sont stubbées en bootstrap (retournent true). On vérifie surtout que les
 * tableaux internes contiennent bien ce qu'on attend après add_*.
 *
 * @package WS_FA_To_SVG
 */

namespace WS_FA_To_SVG\Tests\Unit;

use WS_FA_To_SVG_Loader;
use WP_Mock;

class LoaderTest extends WebStrategyTestCase {

	/** @test */
	public function it_initializes_with_empty_collections() {
		$loader = new WS_FA_To_SVG_Loader();
		$this->assertEquals( array(), $this->get_property( $loader, 'actions' ) );
		$this->assertEquals( array(), $this->get_property( $loader, 'filters' ) );
		$this->assertEquals( array(), $this->get_property( $loader, 'shortcodes' ) );
	}

	/** @test */
	public function it_stores_added_actions_with_full_metadata() {
		$loader    = new WS_FA_To_SVG_Loader();
		$component = new \stdClass();

		$loader->add_action( 'init', $component, 'my_cb', 20, 2 );

		$actions = $this->get_property( $loader, 'actions' );
		$this->assertCount( 1, $actions );
		$this->assertEquals( 'init', $actions[0]['hook'] );
		$this->assertSame( $component, $actions[0]['component'] );
		$this->assertEquals( 'my_cb', $actions[0]['callback'] );
		$this->assertEquals( 20, $actions[0]['priority'] );
		$this->assertEquals( 2, $actions[0]['accepted_args'] );
	}

	/** @test */
	public function it_applies_default_priority_and_args_when_omitted() {
		$loader = new WS_FA_To_SVG_Loader();
		$loader->add_filter( 'the_content', new \stdClass(), 'my_filter' );

		$filters = $this->get_property( $loader, 'filters' );
		$this->assertEquals( 10, $filters[0]['priority'] );
		$this->assertEquals( 1, $filters[0]['accepted_args'] );
	}

	/** @test */
	public function it_accumulates_multiple_actions() {
		$loader = new WS_FA_To_SVG_Loader();
		$comp   = new \stdClass();

		$loader->add_action( 'init', $comp, 'a' );
		$loader->add_action( 'admin_init', $comp, 'b', 5 );
		$loader->add_action( 'init', $comp, 'c', 99 );

		$actions = $this->get_property( $loader, 'actions' );
		$this->assertCount( 3, $actions );
		$this->assertEquals( 'a', $actions[0]['callback'] );
		$this->assertEquals( 'b', $actions[1]['callback'] );
		$this->assertEquals( 'c', $actions[2]['callback'] );
	}

	/** @test */
	public function it_stores_shortcodes() {
		$loader = new WS_FA_To_SVG_Loader();
		$loader->add_shortcode( 'my_sc', new \stdClass(), 'render' );

		$shortcodes = $this->get_property( $loader, 'shortcodes' );
		$this->assertCount( 1, $shortcodes );
		$this->assertEquals( 'my_sc', $shortcodes[0]['tag'] );
		$this->assertEquals( 'render', $shortcodes[0]['callback'] );
	}

	/** @test */
	public function run_does_not_throw_with_registered_hooks() {
		// add_action / add_filter / add_shortcode étant stubbés en bootstrap,
		// run() doit juste itérer sans throw.
		$loader = new WS_FA_To_SVG_Loader();
		$loader->add_action( 'init', new \stdClass(), 'cb_a' );
		$loader->add_filter( 'the_content', new \stdClass(), 'cb_b', 99, 1 );
		$loader->add_shortcode( 'my_sc', new \stdClass(), 'cb_c' );

		$this->expectNotToPerformAssertions();
		$loader->run();
	}
}
