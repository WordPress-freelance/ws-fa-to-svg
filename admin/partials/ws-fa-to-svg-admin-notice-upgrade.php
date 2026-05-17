<?php
/**
 * Admin notice : upgrade vers la version PRO.
 *
 * Variables disponibles :
 *
 * @var int    $count        Nombre d'icônes non mappées.
 * @var string $preview_str  Liste tronquée des icônes manquantes.
 *
 * @package WS_FA_To_SVG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$nonce   = wp_create_nonce( 'ws_fa2svg_dismiss_notice' );
$ajaxurl = esc_url( admin_url( 'admin-ajax.php' ) );
?>
<div class="notice notice-warning is-dismissible ws-fa2svg-notice" data-nonce="<?php echo esc_attr( $nonce ); ?>" data-ajaxurl="<?php echo esc_attr( $ajaxurl ); ?>" style="border-left-color:#7C5CBF;">
	<p style="font-size:14px;line-height:1.6;">
		<strong style="color:#14121C;">⚡ <?php esc_html_e( 'WS Font Awesome to SVG', 'ws-fa-to-svg' ); ?></strong> —
		<?php
		printf(
			/* translators: 1: nombre d'icônes, 2: liste des icônes */
			esc_html__( '%1$d icône(s) Font Awesome détectée(s) sur votre site mais non remplacée(s) par le set FREE : %2$s.', 'ws-fa-to-svg' ),
			(int) $count,
			'<code style="background:#F0EDE8;padding:2px 6px;border-radius:3px;">' . wp_kses_post( $preview_str ) . '</code>'
		);
		?>
		<br>
		<?php esc_html_e( 'La version PRO inclut 300+ icônes officielles Font Awesome, un scanner automatique de pages, et une metabox dans l\'éditeur.', 'ws-fa-to-svg' ); ?>
	</p>
	<p>
		<a href="<?php echo esc_url( WS_FA2SVG_PRO_URL ); ?>" target="_blank" rel="noopener" class="button button-primary" style="background:#7C5CBF;border-color:#7C5CBF;text-shadow:none;box-shadow:none;">
			<?php esc_html_e( 'Voir l\'offre PRO →', 'ws-fa-to-svg' ); ?>
		</a>
		<a href="<?php echo esc_url( WS_FA2SVG_PRO_URL ); ?>" target="_blank" rel="noopener" style="margin-left:10px;color:#7C5CBF;font-size:13px;text-decoration:none;">
			<?php esc_html_e( 'En savoir plus', 'ws-fa-to-svg' ); ?>
		</a>
	</p>
</div>
<script>
(function() {
	var notice = document.querySelector('.ws-fa2svg-notice');
	if (!notice) return;
	var dismissBtn = notice.querySelector('.notice-dismiss');
	if (!dismissBtn) return;
	dismissBtn.addEventListener('click', function() {
		var data = new FormData();
		data.append('action', 'ws_fa2svg_dismiss_notice');
		data.append('nonce', notice.dataset.nonce);
		fetch(notice.dataset.ajaxurl, { method: 'POST', body: data, credentials: 'same-origin' });
	});
})();
</script>
