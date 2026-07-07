=== WS Font Awesome to SVG ===
Contributors: webstrategy
Tags: font-awesome, svg, performance, pagespeed, core-web-vitals
Requires at least: 5.6
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 2.0.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Remplace 30 icônes Font Awesome essentielles par des SVG inline pour booster PageSpeed. PRO : 300+ icônes + scanner.

== Description ==

Plugin ultra-léger qui remplace automatiquement les balises `<i class="fa fa-XXX"></i>` (et variantes `fas`, `far`, `fab`) par des SVG inline 24×24 issus de Font Awesome 6 Free (CC BY 4.0).

**Pourquoi ?**

Font Awesome charge un CSS de ~80 ko et un web font de ~75 ko sur chaque page. C'est render-blocking, ça plombe LCP et FCP. Avec des SVG inline, vous économisez 150 ko de payload réseau et supprimez 2 requêtes HTTP. Mesurable immédiatement sur PageSpeed Insights.

**Comment ça marche :**

1. Hook sur `the_content`, `widget_text_content`, `widget_block_content` et `render_block` (priorité 99, après les shortcodes).
2. Regex de détection des balises `<i>` / `<span>` avec classe `fa fa-NAME`.
3. Remplacement par un SVG inline avec `fill="currentColor"` (hérite la couleur du parent).
4. Cache transient 24h.
5. Dequeue automatique du CSS Font Awesome en frontend.

**30 icônes incluses (FREE) :**

home, bars, xmark (times/close), check, magnifying-glass (search), arrow-right/left, chevron-right/left/down/up, envelope, phone, user, users, heart, star, calendar, clock, location-dot (map-marker), gear (cog), download, upload, external-link, info-circle, exclamation-circle, check-circle, plus, minus, trash — plus une vingtaine d'alias FA4/FA5 (envelope-o, fa-times, fa-cog, fa-home, etc.).

**== Version PRO ==**

Pour les sites qui utilisent davantage d'icônes (Avada, Elementor, Divi, blogs riches, e-commerce), la version PRO inclut :

* **300+ icônes** officielles Font Awesome 6 Free (Solid + Brands sociaux populaires)
* **Scanner intégré** : analysez n'importe quelle URL ou un sitemap entier pour détecter toutes les icônes utilisées
* **Metabox éditeur** : voyez les icônes détectées dans chaque post/page et ajoutez les manquantes en un clic
* **Ajout dynamique** : fetch SVG depuis le CDN officiel Font Awesome pour les icônes manquantes

[**Découvrir la version PRO →**](https://wordpress-freelance.com/plugins/ws-fa-to-svg/)

**Filtres disponibles :**

* `ws_fa2svg_enabled` (bool) — désactive tout le plugin.
* `ws_fa2svg_icons` (array) — modifie le mapping FA → SVG.
* `ws_fa2svg_dequeue` (bool) — active/désactive le dequeue de FA.
* `ws_fa2svg_dequeue_handles` (array) — liste des handles à dequeue.
* `ws_fa2svg_svg_attrs` (array) — modifie les attributs du SVG généré.
* `ws_fa2svg_cache_ttl` (int) — TTL du transient (défaut 24h).
* `ws_fa2svg_inline_css` (bool) — désactive l'injection CSS.

**Compatibilité builders :**

* ✅ Gutenberg / FSE
* ✅ Avada / Fusion Builder (wrap automatique des classes `fontawesome-icon`, `circle-yes`, `fusion-li-icon`)
* ✅ Beaver Builder
* ⚠️ Elementor (partiel, OK pour les contenus passant par `the_content`)
* ❌ Oxygen, Bricks (renderers internes — nécessite la version PRO)

== Installation ==

1. Téléversez le plugin via Extensions > Ajouter > Téléverser une extension.
2. Activez le plugin.
3. C'est tout. Aucune configuration.

Pour vérifier : ouvrez une page contenant des icônes FA, inspectez le HTML. Les `<i>` doivent être remplacés par des `<svg>`. Vérifiez aussi que `font-awesome.css` et les `*.woff2` FA ne sont plus dans l'onglet Network.

== Frequently Asked Questions ==

= Combien d'icônes sont incluses ? =

30 icônes essentielles (utility de base : navigation, actions, communication, statut). Pour davantage d'icônes, voir la [version PRO](https://wordpress-freelance.com/plugins/ws-fa-to-svg/) qui en inclut 300+.

= Mon site utilise des icônes non listées, que se passe-t-il ? =

Les icônes non couvertes par le set FREE restent en place comme des `<i>` Font Awesome — mais comme le CSS FA est dequeued, elles ne s'afficheront pas. Une notice admin vous indique combien d'icônes sont concernées et propose d'upgrader vers la PRO.

= Comment ajouter mes propres icônes ? =

Utilisez le filtre `ws_fa2svg_icons` :

`add_filter( 'ws_fa2svg_icons', function( $icons ) {
    $icons['mon-icone'] = '0 0 24 24|<path d="..."/>';
    return $icons;
} );`

Format : `viewBox|inner_paths`. Ou achetez la PRO et utilisez le scanner intégré.

= Compatible avec un plugin de cache (LiteSpeed, WP Rocket) ? =

Oui. Le filtre s'applique avant la mise en cache de la page. Pensez à purger le cache après activation.

= Font Awesome continue de se charger malgré le dequeue =

Si votre thème charge FA via un `<link>` en dur dans `header.php`, le plugin ne peut pas l'intercepter. Éditez votre thème ou désactivez Font Awesome depuis les options du thème (Avada → Options Globales → Performance → Désactiver Font Awesome).

== Screenshots ==

1. Avant / après : remplacement des icônes Font Awesome par des SVG inline
2. Notice admin proposant l'upgrade PRO quand des icônes non mappées sont détectées
3. Comparatif PageSpeed avant / après installation du plugin

== Changelog ==

= 2.0.1 =
* Sécurité : durcissement du builder SVG contre les balises actives (script, foreignObject, iframe, animate…), les gestionnaires d'événements on* et les schémas javascript:/data:text/html dans href/xlink:href. Défense en profondeur contre un SVG injecté via le filter ws_fa2svg_icons par un tiers.

= 2.0.0 =
* Refonte complète : structure WPPB strict.
* 30 icônes Font Awesome 6 officielles (CC BY 4.0) au lieu de Feather Icons.
* Notice admin "upgrade PRO" basée sur la détection automatique des icônes non mappées.
* Compatibilité Avada renforcée : wrap automatique des classes `fontawesome-icon`, `circle-yes`, `fusion-li-icon`.
* CSS inline 250 octets pour alignement vertical des SVG.

= 1.0.2 =
* Plugin interne avec ~50 icônes Feather Icons.

== Credits ==

Icônes Font Awesome 6 Free, sous licence CC BY 4.0. Copyright Fonticons, Inc. — https://fontawesome.com/
