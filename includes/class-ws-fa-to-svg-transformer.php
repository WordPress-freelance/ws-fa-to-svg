<?php
/**
 * Transformer — détection et remplacement des balises FA par des SVG inline.
 *
 * @package WS_FA_To_SVG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WS_FA_To_SVG_Transformer
 */
class WS_FA_To_SVG_Transformer {

	/**
	 * Mapping FA → SVG (chargé lazy).
	 *
	 * @var array|null
	 */
	private $icons = null;

	/**
	 * Plugin name.
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
	 * Charge le mapping en lazy.
	 *
	 * @return array
	 */
	public function get_icons() {
		if ( null === $this->icons ) {
			require_once WS_FA2SVG_PATH . 'includes/icons.php';
			$bundled     = function_exists( 'ws_fa2svg_default_icons' ) ? ws_fa2svg_default_icons() : array();
			$this->icons = apply_filters( 'ws_fa2svg_icons', $bundled );
		}
		return $this->icons;
	}

	/**
	 * Filtre principal — transformation d'un bloc Gutenberg.
	 *
	 * @param string $block_content HTML du bloc.
	 * @param array  $block         Définition.
	 * @return string
	 */
	public function transform_block( $block_content, $block ) {
		unset( $block );
		return $this->transform( $block_content );
	}

	/**
	 * Transforme un HTML : remplace <i class="fa-XXX"></i> par <svg>.
	 *
	 * @param string $content HTML brut.
	 * @return string
	 */
	public function transform( $content ) {
		if ( ! is_string( $content ) || '' === $content ) {
			return $content;
		}

		// Court-circuit si pas de "fa-" dans le contenu.
		if ( false === stripos( $content, 'fa-' ) ) {
			return $content;
		}

		// Cache transient par hash.
		$cache_key = 'ws_fa2svg_' . md5( $content );
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$icons      = $this->get_icons();
		$unmapped   = array();
		$pattern    = '#<(i|span)\b([^>]*?)>\s*</\1>#i';
		$transformer = $this;

		$result = preg_replace_callback(
			$pattern,
			function ( $match ) use ( $icons, &$unmapped, $transformer ) {
				$attrs = $match[2];

				if ( ! preg_match( '/\bclass\s*=\s*(["\'])([^"\']+)\1/i', $attrs, $cm ) ) {
					return $match[0];
				}
				$classes = $cm[2];

				if ( ! preg_match( '/\bfa[bsrldk]?\b/i', $classes ) ) {
					return $match[0];
				}

				if ( ! preg_match_all( '/\bfa-([a-z0-9-]+)/i', $classes, $names ) ) {
					return $match[0];
				}

				// Skip les modificateurs FA connus pour trouver le vrai nom.
				$modifiers = array(
					'lg', 'xs', 'sm', 'fw', 'ul', 'li', 'border',
					'pull-left', 'pull-right', 'spin', 'pulse', 'inverse',
					'stack', 'stack-1x', 'stack-2x',
					'2x', '3x', '4x', '5x', '6x', '7x', '8x', '9x', '10x',
					'rotate-90', 'rotate-180', 'rotate-270',
					'flip-horizontal', 'flip-vertical',
				);

				$icon_name      = null;
				$icon_candidate = null;
				foreach ( $names[1] as $candidate ) {
					$candidate_lc = strtolower( $candidate );
					if ( in_array( $candidate_lc, $modifiers, true ) ) {
						continue;
					}
					if ( null === $icon_candidate ) {
						$icon_candidate = $candidate_lc;
					}
					if ( isset( $icons[ $candidate_lc ] ) ) {
						$icon_name = $candidate_lc;
						break;
					}
				}

				if ( null === $icon_name ) {
					// Enregistrer l'icône non mappée pour la notice upgrade.
					if ( null !== $icon_candidate ) {
						$unmapped[ $icon_candidate ] = ( isset( $unmapped[ $icon_candidate ] ) ? $unmapped[ $icon_candidate ] : 0 ) + 1;
					}
					return $match[0];
				}

				return $transformer->build_svg( $icons[ $icon_name ], $icon_name, $attrs, $classes );
			},
			$content
		);

		if ( null === $result ) {
			return $content;
		}

		// Enregistre les icônes non mappées (pour la notice upgrade).
		if ( ! empty( $unmapped ) ) {
			$this->record_unmapped( $unmapped );
		}

		set_transient( $cache_key, $result, apply_filters( 'ws_fa2svg_cache_ttl', DAY_IN_SECONDS ) );

		return $result;
	}

	/**
	 * Construit le tag SVG final + wrap éventuel.
	 *
	 * @param string $icon_data    Format "viewBox|inner_paths".
	 * @param string $icon_name    Nom canonique.
	 * @param string $orig_attrs   Attributs HTML de l'élément d'origine.
	 * @param string $orig_classes Valeur de l'attribut class d'origine.
	 * @return string
	 */
	public function build_svg( $icon_data, $icon_name, $orig_attrs, $orig_classes ) {
		// Format compact "viewBox|inner".
		$parts    = explode( '|', $icon_data, 2 );
		$viewbox  = isset( $parts[0] ) ? $parts[0] : '0 0 24 24';
		$inner    = isset( $parts[1] ) ? $parts[1] : '';

		// Aria.
		$aria_label = '';
		if ( preg_match( '/\baria-label\s*=\s*(["\'])([^"\']*)\1/i', $orig_attrs, $am ) ) {
			$aria_label = $am[2];
		} elseif ( preg_match( '/\btitle\s*=\s*(["\'])([^"\']*)\1/i', $orig_attrs, $tm ) ) {
			$aria_label = $tm[2];
		}

		// Style inline.
		$inline_style = '';
		if ( preg_match( '/\bstyle\s*=\s*(["\'])([^"\']*)\1/i', $orig_attrs, $sm ) ) {
			$inline_style = $sm[2];
		}

		// Détection wrap Avada / Fusion Builder.
		$needs_wrap = false;
		if ( preg_match( '/\b(circle-yes|circle-no|fontawesome-icon|fb-icon-element|fusion-li-icon|awb-menu__|fusion-icon|fusion-button)/i', $orig_classes ) ) {
			$needs_wrap = true;
		} elseif ( '' !== $inline_style
			&& preg_match( '/\b(background|border-radius|width|height|line-height|font-size)\s*:/i', $inline_style ) ) {
			$needs_wrap = true;
		}

		$svg_attrs = array(
			'class'           => 'ws-svg-icon ws-svg-icon--' . $icon_name,
			'xmlns'           => 'http://www.w3.org/2000/svg',
			'width'           => $needs_wrap ? '1em' : '24',
			'height'          => $needs_wrap ? '1em' : '24',
			'viewBox'         => $viewbox,
			'fill'            => 'currentColor',
		);

		if ( '' !== $aria_label ) {
			$svg_attrs['role']       = 'img';
			$svg_attrs['aria-label'] = $aria_label;
		} else {
			$svg_attrs['aria-hidden'] = 'true';
			$svg_attrs['focusable']   = 'false';
		}

		$svg_attrs = apply_filters( 'ws_fa2svg_svg_attrs', $svg_attrs, $icon_name, $needs_wrap );

		$attrs_str = '';
		foreach ( $svg_attrs as $key => $val ) {
			$attrs_str .= ' ' . $key . '="' . esc_attr( $val ) . '"';
		}

		$svg = '<svg' . $attrs_str . '>' . $inner . '</svg>';

		if ( ! $needs_wrap ) {
			return $svg;
		}

		$wrap_attrs = ' class="' . esc_attr( $orig_classes ) . '"';
		if ( '' !== $inline_style ) {
			$wrap_attrs .= ' style="' . esc_attr( $inline_style ) . '"';
		}
		if ( preg_match( '/\baria-hidden\s*=\s*(["\'])([^"\']*)\1/i', $orig_attrs, $ahm ) ) {
			$wrap_attrs .= ' aria-hidden="' . esc_attr( $ahm[2] ) . '"';
		}

		return '<span' . $wrap_attrs . '>' . $svg . '</span>';
	}

	/**
	 * Enregistre les icônes non mappées dans une option WP (pour la notice upgrade).
	 * Limite à 50 icônes max stockées.
	 *
	 * @param array $batch Tableau icon_name => count.
	 * @return void
	 */
	private function record_unmapped( $batch ) {
		$stored = get_option( 'ws_fa2svg_unmapped', array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		foreach ( $batch as $name => $count ) {
			if ( count( $stored ) >= 50 && ! isset( $stored[ $name ] ) ) {
				break;
			}
			$stored[ $name ] = ( isset( $stored[ $name ] ) ? $stored[ $name ] : 0 ) + (int) $count;
		}

		// Trier par occurrences décroissantes et garder les 50 premières.
		arsort( $stored );
		$stored = array_slice( $stored, 0, 50, true );

		update_option( 'ws_fa2svg_unmapped', $stored, false );
	}

	/**
	 * Dequeue Font Awesome (frontend).
	 *
	 * @return void
	 */
	public function dequeue_font_awesome() {
		if ( ! apply_filters( 'ws_fa2svg_dequeue', true ) ) {
			return;
		}
		if ( is_admin() ) {
			return;
		}

		$handles = array(
			'font-awesome', 'fontawesome', 'font-awesome-official',
			'font-awesome-5', 'fontawesome-5', 'fontawesome-all',
			'fa-icons', 'fa',
			'awb-fa-icons-css', 'fusion-font-awesome', 'fusion-fontawesome',
			'fontawesome-shim',
		);
		$handles = apply_filters( 'ws_fa2svg_dequeue_handles', $handles );

		foreach ( $handles as $h ) {
			if ( wp_style_is( $h, 'enqueued' ) || wp_style_is( $h, 'registered' ) ) {
				wp_dequeue_style( $h );
				wp_deregister_style( $h );
			}
			if ( wp_script_is( $h, 'enqueued' ) || wp_script_is( $h, 'registered' ) ) {
				wp_dequeue_script( $h );
				wp_deregister_script( $h );
			}
		}

		// Dynamique : tout handle contenant "fontawesome".
		global $wp_styles;
		if ( $wp_styles instanceof WP_Styles ) {
			foreach ( (array) $wp_styles->queue as $h ) {
				if ( stripos( $h, 'fontawesome' ) !== false || stripos( $h, 'font-awesome' ) !== false ) {
					wp_dequeue_style( $h );
				}
			}
		}
	}

	/**
	 * CSS inline minimal pour le rendu des SVG.
	 *
	 * @return void
	 */
	public function inline_css() {
		if ( ! apply_filters( 'ws_fa2svg_inline_css', true ) ) {
			return;
		}
		echo '<style id="ws-fa2svg-inline">.ws-svg-icon{vertical-align:-0.125em;line-height:0}.fontawesome-icon:has(>.ws-svg-icon),.fusion-li-icon:has(>.ws-svg-icon),[class*="awb-menu__"]:has(>.ws-svg-icon){display:inline-flex;align-items:center;justify-content:center;line-height:1}</style>' . "\n";
	}

	/**
	 * Purge le cache.
	 *
	 * @return void
	 */
	public function purge_cache() {
		global $wpdb;
		$wpdb->query(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_ws\\_fa2svg\\_%' OR option_name LIKE '\\_transient\\_timeout\\_ws\\_fa2svg\\_%'"
		);
		// Invalide le cache d'objet WordPress : sans ça, get_transient() retournerait
		// la valeur en RAM même après suppression DB (cache `options`/`alloptions`).
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}
	}
}
