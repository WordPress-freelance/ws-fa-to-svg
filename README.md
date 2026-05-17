# WS Font Awesome to SVG

[![Tests](https://github.com/WordPress-freelance/ws-fa-to-svg/actions/workflows/tests.yml/badge.svg)](https://github.com/WordPress-freelance/ws-fa-to-svg/actions)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)

> Convertit les balises `<i class="fa fa-*"></i>` Font Awesome en SVG inline pour booster vos scores PageSpeed.

Plugin WordPress qui remplace les **icônes Font Awesome rendues via webfont** par des **SVG inline équivalents**, en supprimant l'enqueue de `font-awesome.css` (~70 ko) et le téléchargement du fichier de police (~150 ko).

## Pourquoi ?

Sur un site moyennement chargé en icônes, Font Awesome représente :
- Une requête bloquante (`<link rel="stylesheet">`)
- ~220 ko de webfonts + CSS
- Un FOUT/FOIT (Flash of Unstyled Text / Flash of Invisible Text)
- 30 à 60 points perdus sur Lighthouse en "Eliminate render-blocking resources"

Ce plugin :
- Détecte automatiquement les balises FA dans le HTML rendu
- Remplace chaque `<i class="fa fa-house"></i>` par un `<svg>` inline équivalent
- Dequeue les handles Font Awesome connus (`font-awesome`, `awb-fa-icons-css`, `fontawesome`)

## Couverture FREE vs PRO

| | FREE | [PRO](https://wordpress-freelance.com/plugins/ws-fa-to-svg/) |
|--|--|--|
| Icônes bundled | 30 (les + courantes) | 313 (Solid complet + Brands sociaux) |
| Alias FA4/FA5 | 19 | 88 |
| Scanner URL/sitemap | — | ✅ |
| Ajout dynamique depuis CDN | — | ✅ |
| Custom icons via UI | — | ✅ |
| Mises à jour auto | wordpress.org | wordpress-freelance.com (Lifetime 49€) |

## Installation

Via WordPress :
```
Plugins → Ajouter → Rechercher "WS Font Awesome to SVG" → Installer → Activer
```

Ou manuellement : télécharger le `.zip` depuis [Releases](https://github.com/WordPress-freelance/ws-fa-to-svg/releases), uploader via *Plugins → Ajouter → Téléverser une extension*.

## Hooks développeur

```php
// Override complet du mapping bundled.
add_filter( 'ws_fa2svg_icons', function( $icons ) {
    $icons['my-icon'] = '0 0 24 24|<path d="M0 0h24v24H0z"/>';
    return $icons;
} );

// Désactiver le CSS inline (si vous gérez vous-même `.ws-svg-icon`).
add_filter( 'ws_fa2svg_inline_css', '__return_false' );

// TTL du cache transient (défaut : 7 jours).
add_filter( 'ws_fa2svg_cache_ttl', function() { return DAY_IN_SECONDS; } );

// Ajuster les handles à dequeue.
add_filter( 'ws_fa2svg_dequeue_handles', function( $handles ) {
    $handles[] = 'my-custom-fa-handle';
    return $handles;
} );
```

## Tests

97 tests unitaires + 5 tests d'intégration BDD.

```bash
composer install
vendor/bin/phpunit -c phpunit.xml                  # unit (WP_Mock)
bin/install-wp-tests.sh wp_test root '' localhost  # setup BDD
vendor/bin/phpunit -c phpunit-integration.xml      # integration (WP + MySQL)
```

CI : matrix PHP 7.4 → 8.3 sur push/PR, voir [`.github/workflows/tests.yml`](.github/workflows/tests.yml).

## Architecture

Plugin Boilerplate ([WPPB](https://wppb.me)) strict :

```
includes/        Orchestrateur, loader, i18n, transformer, icons.php
admin/           Notice upgrade, dismiss AJAX
public/          (placeholder, le transformer hooke sur the_content/render_block)
tests/unit/      PHPUnit + WP_Mock 0.5
tests/integration/ PHPUnit avec vraie WP + MySQL
```

## License

GPL v2+ — voir [LICENSE](LICENSE).

Icônes : Font Awesome Free 6.x © [Fonticons, Inc.](https://fontawesome.com), CC BY 4.0.

## Author

[WebStrategy](https://wordpress-freelance.com/) — Sébastien Chaffer
