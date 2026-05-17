<?php
/**
 * Uninstall — nettoyage complet.
 *
 * @package WS_FA_To_SVG
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Transients.
$wpdb->query(
	"DELETE FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_ws\\_fa2svg\\_%' OR option_name LIKE '\\_transient\\_timeout\\_ws\\_fa2svg\\_%'"
);

// Options.
delete_option( 'ws_fa2svg_unmapped' );

// User meta.
$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key = 'ws_fa2svg_notice_dismissed'" );
