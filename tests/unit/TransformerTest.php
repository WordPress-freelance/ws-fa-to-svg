<?php
/**
 * Tests du Transformer — détection et remplacement des balises FA.
 *
 * Pattern WP_Mock 0.5 :
 *   - apply_filters non mocké → passthrough automatique (retourne le 2e arg).
 *   - WP_Mock::onFilter()->reply() pour overrider le retour d'un filter.
 *   - userFunction('apply_filters', …) NE FONCTIONNE PAS (mock défaut interne gagne).
 *
 * @package WS_FA_To_SVG
 */

namespace WS_FA_To_SVG\Tests\Unit;

use WS_FA_To_SVG_Transformer;
use WP_Mock;

class TransformerTest extends WebStrategyTestCase {

	private function make_transformer() {
		return new WS_FA_To_SVG_Transformer( 'ws-fa-to-svg', '2.0.0' );
	}

	// ---------------------------------------------------------------- get_icons

	/** @test */
	public function get_icons_returns_bundled_icons_with_filter_applied() {
		// WP_Mock 0.5 onFilter::with() exige une valeur exacte (les matchers
		// Mockery::type/any ne fonctionnent pas). On prédit donc le bundled
		// et on l'utilise pour matcher.
		require_once WS_FA2SVG_PATH . 'includes/icons.php';
		$bundled = ws_fa2svg_default_icons();

		WP_Mock::onFilter( 'ws_fa2svg_icons' )
			->with( $bundled )
			->reply( array( 'overridden' => '0 0 24 24|<path/>' ) );

		$icons = $this->make_transformer()->get_icons();
		$this->assertEquals( array( 'overridden' => '0 0 24 24|<path/>' ), $icons );
	}

	/** @test */
	public function get_icons_loads_bundled_when_filter_is_pass_through() {
		$icons = $this->make_transformer()->get_icons();
		$this->assertIsArray( $icons );
		$this->assertNotEmpty( $icons );
		$this->assertArrayHasKey( 'house', $icons );
	}

	/** @test */
	public function get_icons_is_lazy_loaded_only_once() {
		$t = $this->make_transformer();
		$a = $t->get_icons();
		$b = $t->get_icons();
		$this->assertSame( $a, $b );
	}

	// ---------------------------------------------------------------- transform

	/** @test */
	public function transform_returns_input_unchanged_when_empty() {
		$this->assertSame( '', $this->make_transformer()->transform( '' ) );
	}

	/** @test */
	public function transform_returns_input_unchanged_when_not_string() {
		$this->assertNull( $this->make_transformer()->transform( null ) );
	}

	/** @test */
	public function transform_returns_input_unchanged_when_no_fa_marker() {
		$html = '<p>Hello world without any icons</p>';
		$this->assertSame( $html, $this->make_transformer()->transform( $html ) );
	}

	/** @test */
	public function transform_returns_cached_value_when_transient_exists() {
		WP_Mock::userFunction( 'get_transient', array( 'return' => '<svg>cached</svg>' ) );

		$result = $this->make_transformer()->transform( '<i class="fa fa-cog"></i>' );
		$this->assertSame( '<svg>cached</svg>', $result );
	}

	/** @test */
	public function transform_replaces_mapped_icon_with_inline_svg() {
		WP_Mock::userFunction( 'get_transient', array( 'return' => false ) );
		WP_Mock::userFunction( 'set_transient', array( 'return' => true ) );
		WP_Mock::userFunction( 'get_option', array( 'return' => array() ) );
		WP_Mock::userFunction( 'update_option', array( 'return' => true ) );

		$result = $this->make_transformer()->transform( '<i class="fa fa-house"></i>' );

		$this->assertStringContainsString( '<svg', $result );
		$this->assertStringContainsString( 'viewBox="', $result );
		$this->assertStringContainsString( 'ws-svg-icon--house', $result );
		$this->assertStringContainsString( 'fill="currentColor"', $result );
		$this->assertStringNotContainsString( '<i class="fa fa-house">', $result );
	}

	/** @test */
	public function transform_skips_size_modifier_classes_to_find_real_icon_name() {
		WP_Mock::userFunction( 'get_transient', array( 'return' => false ) );
		WP_Mock::userFunction( 'set_transient', array( 'return' => true ) );
		WP_Mock::userFunction( 'get_option', array( 'return' => array() ) );
		WP_Mock::userFunction( 'update_option', array( 'return' => true ) );

		$result = $this->make_transformer()->transform( '<i class="fa fa-2x fa-house"></i>' );

		$this->assertStringContainsString( 'ws-svg-icon--house', $result );
		$this->assertStringNotContainsString( '<span class="fa fa-2x', $result );
	}

	/** @test */
	public function transform_leaves_unknown_icons_unchanged_and_records_them() {
		WP_Mock::userFunction( 'get_transient', array( 'return' => false ) );
		WP_Mock::userFunction( 'set_transient', array( 'return' => true ) );

		$stored_unmapped = null;
		WP_Mock::userFunction( 'get_option', array( 'return' => array() ) );
		WP_Mock::userFunction( 'update_option', array(
			'return' => function( $key, $value, $autoload ) use ( &$stored_unmapped ) {
				if ( 'ws_fa2svg_unmapped' === $key ) {
					$stored_unmapped = $value;
				}
				return true;
			},
		) );

		$result = $this->make_transformer()->transform( '<i class="fa fa-totally-unknown-icon"></i>' );

		$this->assertStringNotContainsString( '<svg', $result );
		$this->assertIsArray( $stored_unmapped );
		$this->assertArrayHasKey( 'totally-unknown-icon', $stored_unmapped );
		$this->assertEquals( 1, $stored_unmapped['totally-unknown-icon'] );
	}

	/** @test */
	public function transform_ignores_balises_without_fa_prefix_class() {
		WP_Mock::userFunction( 'get_transient', array( 'return' => false ) );
		WP_Mock::userFunction( 'set_transient', array( 'return' => true ) );

		$html = '<span class="my-fa-cool-thing">test</span>';
		$this->assertSame( $html, $this->make_transformer()->transform( $html ) );
	}

	/** @test */
	public function transform_block_delegates_to_transform() {
		WP_Mock::userFunction( 'get_transient', array( 'return' => false ) );
		WP_Mock::userFunction( 'set_transient', array( 'return' => true ) );
		WP_Mock::userFunction( 'get_option', array( 'return' => array() ) );
		WP_Mock::userFunction( 'update_option', array( 'return' => true ) );

		$result = $this->make_transformer()->transform_block( '<i class="fa fa-house"></i>', array() );
		$this->assertStringContainsString( '<svg', $result );
	}

	// ---------------------------------------------------------------- build_svg

	/** @test */
	public function build_svg_wraps_in_span_when_avada_classes_detected() {
		$result = $this->make_transformer()->build_svg(
			'0 0 24 24|<path d="M1 1h20v20H1z"/>',
			'house',
			' class="fontawesome-icon circle-yes"',
			'fontawesome-icon circle-yes'
		);

		$this->assertStringStartsWith( '<span class="fontawesome-icon circle-yes"', $result );
		$this->assertStringContainsString( 'width="1em"', $result );
		$this->assertStringContainsString( 'height="1em"', $result );
		$this->assertStringEndsWith( '</svg></span>', $result );
	}

	/** @test */
	public function build_svg_returns_bare_svg_when_no_wrap_needed() {
		$result = $this->make_transformer()->build_svg( '0 0 24 24|<path/>', 'star', ' class="fa fa-star"', 'fa fa-star' );

		$this->assertStringStartsWith( '<svg', $result );
		$this->assertStringContainsString( 'width="24"', $result );
		$this->assertStringContainsString( 'height="24"', $result );
		$this->assertStringNotContainsString( '<span', $result );
	}

	/** @test */
	public function build_svg_uses_title_attr_as_aria_label() {
		$result = $this->make_transformer()->build_svg( '0 0 24 24|<path/>', 'envelope', ' title="Contact us"', 'fa fa-envelope' );

		$this->assertStringContainsString( 'role="img"', $result );
		$this->assertStringContainsString( 'aria-label="Contact us"', $result );
		$this->assertStringNotContainsString( 'aria-hidden', $result );
	}

	/** @test */
	public function build_svg_marks_as_aria_hidden_when_no_label() {
		$result = $this->make_transformer()->build_svg( '0 0 24 24|<path/>', 'star', ' class="fa fa-star"', 'fa fa-star' );

		$this->assertStringContainsString( 'aria-hidden="true"', $result );
		$this->assertStringContainsString( 'focusable="false"', $result );
	}

	/** @test */
	public function build_svg_preserves_viewbox_from_icon_data() {
		$result = $this->make_transformer()->build_svg( '0 0 640 512|<path d="M0 0z"/>', 'wide', ' class="fa fa-wide"', 'fa fa-wide' );
		$this->assertStringContainsString( 'viewBox="0 0 640 512"', $result );
	}

	// ---------------------------------------------------------------- record_unmapped

	/** @test */
	public function unmapped_records_accumulate_count_per_icon() {
		WP_Mock::userFunction( 'get_transient', array( 'return' => false ) );
		WP_Mock::userFunction( 'set_transient', array( 'return' => true ) );

		$stored = array();
		WP_Mock::userFunction( 'get_option', array(
			'return' => function() use ( &$stored ) { return $stored; },
		) );
		WP_Mock::userFunction( 'update_option', array(
			'return' => function( $k, $v ) use ( &$stored ) {
				if ( 'ws_fa2svg_unmapped' === $k ) { $stored = $v; }
				return true;
			},
		) );

		$t = $this->make_transformer();
		$t->transform( '<i class="fa fa-unknown-a"></i><i class="fa fa-unknown-a"></i><i class="fa fa-unknown-b"></i>' );

		$this->assertArrayHasKey( 'unknown-a', $stored );
		$this->assertArrayHasKey( 'unknown-b', $stored );
		$this->assertEquals( 2, $stored['unknown-a'] );
		$this->assertEquals( 1, $stored['unknown-b'] );
	}

	// ---------------------------------------------------------------- dequeue_font_awesome

	/** @test */
	public function dequeue_skips_in_admin() {
		WP_Mock::userFunction( 'is_admin', array( 'return' => true ) );

		$this->make_transformer()->dequeue_font_awesome();
		$this->assertTrue( true );
	}

	/** @test */
	public function dequeue_removes_known_handles_when_enqueued() {
		WP_Mock::userFunction( 'is_admin', array( 'return' => false ) );

		$dequeued_styles = array();
		WP_Mock::userFunction( 'wp_style_is', array(
			'return' => function( $h, $list ) {
				return in_array( $h, array( 'font-awesome', 'awb-fa-icons-css' ), true );
			},
		) );
		WP_Mock::userFunction( 'wp_script_is', array( 'return' => false ) );
		WP_Mock::userFunction( 'wp_dequeue_style', array(
			'return' => function( $h ) use ( &$dequeued_styles ) { $dequeued_styles[] = $h; },
		) );
		WP_Mock::userFunction( 'wp_deregister_style', array( 'return' => true ) );
		WP_Mock::userFunction( 'wp_dequeue_script', array( 'return' => true ) );
		WP_Mock::userFunction( 'wp_deregister_script', array( 'return' => true ) );

		global $wp_styles;
		$wp_styles = new \WP_Styles();
		$wp_styles->queue = array();

		$this->make_transformer()->dequeue_font_awesome();

		$this->assertContains( 'font-awesome', $dequeued_styles );
		$this->assertContains( 'awb-fa-icons-css', $dequeued_styles );
	}

	// ---------------------------------------------------------------- inline_css

	/** @test */
	public function inline_css_outputs_style_block_when_filter_allows() {
		ob_start();
		$this->make_transformer()->inline_css();
		$out = ob_get_clean();

		$this->assertStringContainsString( '<style id="ws-fa2svg-inline">', $out );
		$this->assertStringContainsString( '.ws-svg-icon', $out );
	}

	/** @test */
	public function inline_css_outputs_nothing_when_filter_disables_it() {
		WP_Mock::onFilter( 'ws_fa2svg_inline_css' )->with( true )->reply( false );

		ob_start();
		$this->make_transformer()->inline_css();
		$out = ob_get_clean();

		$this->assertSame( '', $out );
	}

	// ---------------------------------------------------------------- purge_cache

	/** @test */
	public function purge_cache_runs_a_delete_query_on_transients() {
		global $wpdb;
		$wpdb = new \wpdb();

		$this->make_transformer()->purge_cache();

		$this->assertStringContainsString( 'DELETE FROM', $wpdb->last_query );
		$this->assertStringContainsString( 'transient', $wpdb->last_query );
		$this->assertStringContainsString( 'fa2svg', $wpdb->last_query );
	}

	// ---------------------------------------------------------------- sanitize_svg_inner

	/** @test */
	public function sanitize_svg_inner_returns_empty_for_non_string_or_empty() {
		$this->assertSame( '', \WS_FA_To_SVG_Transformer::sanitize_svg_inner( '' ) );
		$this->assertSame( '', \WS_FA_To_SVG_Transformer::sanitize_svg_inner( null ) );
		$this->assertSame( '', \WS_FA_To_SVG_Transformer::sanitize_svg_inner( 42 ) );
	}

	/** @test */
	public function sanitize_svg_inner_preserves_legitimate_paths() {
		$safe = '<path d="M0 0h24v24H0z" fill="currentColor"/>';
		$out  = \WS_FA_To_SVG_Transformer::sanitize_svg_inner( $safe );
		$this->assertStringContainsString( '<path', $out );
		$this->assertStringContainsString( 'M0 0h24v24H0z', $out );
	}

	/**
	 * @test
	 * @dataProvider dangerous_svg_payloads
	 */
	public function sanitize_svg_inner_strips_active_content( $payload, $forbidden, $label ) {
		$out = \WS_FA_To_SVG_Transformer::sanitize_svg_inner( $payload );
		foreach ( (array) $forbidden as $needle ) {
			$this->assertStringNotContainsString( $needle, $out, "$label : « $needle » ne devrait pas subsister" );
		}
	}

	public function dangerous_svg_payloads() {
		return array(
			// script tags.
			array(
				'<path d="M0 0"/><script>alert(1)</script>',
				array( '<script', 'alert(1)' ),
				'script inline',
			),
			array(
				'<script src="//evil.com/x.js"></script><path d="M0 0"/>',
				array( '<script', 'evil.com' ),
				'script externe',
			),
			array(
				'<script/><path d="M0 0"/>',
				array( '<script' ),
				'script orphelin auto-fermant',
			),
			// event handlers.
			array(
				'<path d="M0 0" onload="alert(1)"/>',
				array( 'onload', 'alert(1)' ),
				'onload attribute',
			),
			array(
				"<path d='M0 0' onclick='alert(1)'/>",
				array( 'onclick' ),
				'onclick avec simples quotes',
			),
			array(
				'<path onmouseover=alert(1)/>',
				array( 'onmouseover', 'alert(1)' ),
				'onmouseover sans quotes',
			),
			// href javascript:.
			array(
				'<a href="javascript:alert(1)"><path d="M0 0"/></a>',
				array( 'javascript:alert' ),
				'href javascript:',
			),
			array(
				'<use xlink:href="javascript:alert(1)"/>',
				array( 'javascript:alert' ),
				'xlink:href javascript:',
			),
			// foreignObject.
			array(
				'<foreignObject><body><script>x</script></body></foreignObject><path d="M0 0"/>',
				array( 'foreignObject', '<script' ),
				'foreignObject wrapper',
			),
			// use externe.
			array(
				'<use href="https://evil.com/x.svg#foo"/><path d="M0 0"/>',
				array( 'evil.com', '<use' ),
				'use avec URL externe',
			),
			// animate SMIL.
			array(
				'<animate attributeName="href" values="javascript:alert(1)"/><path d="M0 0"/>',
				array( '<animate', 'javascript:' ),
				'animate SMIL',
			),
			// data:text/html embed.
			array(
				'<a href="data:text/html,<script>x</script>"><path/></a>',
				array( 'data:text/html' ),
				'data:text/html URI',
			),
		);
	}

	/** @test */
	public function sanitize_svg_inner_preserves_local_fragment_use() {
		$svg = '<defs><path id="p" d="M0 0"/></defs><use href="#p"/>';
		$out = \WS_FA_To_SVG_Transformer::sanitize_svg_inner( $svg );
		$this->assertStringContainsString( '<use', $out, 'use avec fragment local #id doit être conservé' );
		$this->assertStringContainsString( '#p', $out );
	}

	/** @test */
	public function build_svg_output_never_contains_active_content_when_icon_data_is_malicious() {
		// Simule une icône polluée injectée via le filter ws_fa2svg_icons.
		$evil     = '0 0 24 24|<path d="M0 0"/><script>alert(1)</script><path onload="x"/>';
		$out      = $this->make_transformer()->build_svg( $evil, 'evil', '', '' );
		$this->assertStringNotContainsString( '<script', $out );
		$this->assertStringNotContainsString( 'alert(1)', $out );
		$this->assertStringNotContainsString( 'onload', $out );
	}
}
