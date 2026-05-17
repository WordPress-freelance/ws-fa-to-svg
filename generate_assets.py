"""Génère les assets PNG du plugin FREE ws-fa-to-svg.

Concept logo : symbole "FA" stylisé qui se transforme en éclair violet (vitesse / PageSpeed boost).
Palette WebStrategy : #14121C / #7C5CBF / #F0EDE8.
"""
import shutil
from pathlib import Path

import cairosvg

ASSETS = Path("/home/claude/ws-fa-to-svg/assets")
ASSETS.mkdir(exist_ok=True)

# Palette WebStrategy
BG       = "#14121C"
BG_ALT   = "#1A1724"
BG_DEEP  = "#221D32"
ACCENT   = "#7C5CBF"
ACC_MID  = "#9B8EC4"
ACC_L    = "#A899D4"
TEXT     = "#F0EDE8"
TEXT_S   = "#C4BFDA"
TEXT_M   = "#9590A8"
BORDER   = "#2E2B38"

# Chrome WP admin
WP_SIDE  = "#1d2327"
WP_BG    = "#f0f0f1"
WP_NAV   = "#23282d"


def write_png(svg: str, name: str, w: int, h: int) -> None:
    out = ASSETS / name
    cairosvg.svg2png(
        bytestring=svg.encode("utf-8"),
        output_width=w,
        output_height=h,
        write_to=str(out),
    )
    print(f"  ✓ {name} ({w}×{h}, {out.stat().st_size} octets)")


# =============================================================================
# 1. ICÔNES — symbole FA stylisé en cercle violet, éclair blanc au centre
# =============================================================================

ICON_SVG = f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
  <defs>
    <linearGradient id="bg-grad" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{BG}"/>
      <stop offset="100%" stop-color="{BG_DEEP}"/>
    </linearGradient>
    <linearGradient id="circle-grad" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{ACCENT}"/>
      <stop offset="100%" stop-color="#5d44a0"/>
    </linearGradient>
    <filter id="glow" x="-50%" y="-50%" width="200%" height="200%">
      <feGaussianBlur stdDeviation="3" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <!-- Fond carré arrondi -->
  <rect width="256" height="256" rx="36" fill="url(#bg-grad)"/>

  <!-- Cercle violet central -->
  <circle cx="128" cy="128" r="86" fill="url(#circle-grad)" opacity="0.95"/>
  <circle cx="128" cy="128" r="86" fill="none" stroke="{ACC_L}" stroke-width="2" opacity="0.4"/>

  <!-- Éclair blanc (symbole vitesse / PageSpeed) -->
  <path d="M 142 60 L 92 142 L 124 142 L 114 196 L 168 110 L 134 110 Z"
        fill="{TEXT}" filter="url(#glow)"/>

  <!-- Petits accents : 3 points en orbite -->
  <circle cx="60"  cy="128" r="4" fill="{ACC_MID}" opacity="0.6"/>
  <circle cx="196" cy="128" r="4" fill="{ACC_MID}" opacity="0.6"/>
  <circle cx="128" cy="60"  r="4" fill="{ACC_MID}" opacity="0.6"/>
</svg>"""

write_png(ICON_SVG, "icon-128x128.png", 128, 128)
write_png(ICON_SVG, "icon-256x256.png", 256, 256)


# =============================================================================
# 2. BANNER 1544×500 — fond gradient + logo + titre + tagline + démo visuelle
# =============================================================================

BANNER_SVG = f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1544 500">
  <defs>
    <linearGradient id="banner-bg" x1="0" y1="0" x2="1" y2="0">
      <stop offset="0%" stop-color="{BG}"/>
      <stop offset="60%" stop-color="{BG_ALT}"/>
      <stop offset="100%" stop-color="{BG_DEEP}"/>
    </linearGradient>
    <linearGradient id="circle-grad-b" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{ACCENT}"/>
      <stop offset="100%" stop-color="#5d44a0"/>
    </linearGradient>
    <radialGradient id="halo" cx="50%" cy="50%" r="50%">
      <stop offset="0%" stop-color="{ACCENT}" stop-opacity="0.25"/>
      <stop offset="100%" stop-color="{ACCENT}" stop-opacity="0"/>
    </radialGradient>
  </defs>

  <!-- Fond -->
  <rect width="1544" height="500" fill="url(#banner-bg)"/>

  <!-- Halo violet décoratif côté droit -->
  <ellipse cx="1280" cy="250" rx="380" ry="280" fill="url(#halo)"/>

  <!-- Séparateur subtil -->
  <line x1="1020" y1="80" x2="1020" y2="420" stroke="{BORDER}" stroke-width="1" opacity="0.4"/>

  <!-- Logo à gauche -->
  <g transform="translate(80, 175)">
    <rect width="150" height="150" rx="24" fill="url(#circle-grad-b)"/>
    <path d="M 90 30 L 55 80 L 75 80 L 68 117 L 100 65 L 80 65 Z" fill="{TEXT}"/>
  </g>

  <!-- Titre -->
  <text x="265" y="230" font-family="Georgia, serif" font-weight="700"
        font-size="62" fill="{TEXT}">WS Font Awesome</text>
  <text x="265" y="295" font-family="Georgia, serif" font-weight="700"
        font-size="62" fill="{ACCENT}">to SVG</text>

  <!-- Tagline -->
  <text x="265" y="345" font-family="Helvetica, Arial, sans-serif" font-weight="400"
        font-size="22" fill="{TEXT_S}">Inline SVG icons · Zero webfont · Faster PageSpeed</text>

  <!-- Démo visuelle à droite : transformation FA → SVG -->
  <g transform="translate(1080, 195)">
    <!-- "Avant" : balise FA -->
    <rect x="0" y="0" width="170" height="110" rx="8" fill="{BG_DEEP}" stroke="{BORDER}"/>
    <text x="85" y="40" text-anchor="middle" font-family="Courier, monospace"
          font-size="13" fill="{TEXT_M}">&lt;i class="fa</text>
    <text x="85" y="62" text-anchor="middle" font-family="Courier, monospace"
          font-size="13" fill="{TEXT_M}">fa-house"&gt;</text>
    <text x="85" y="84" text-anchor="middle" font-family="Courier, monospace"
          font-size="13" fill="{TEXT_M}">&lt;/i&gt;</text>

    <!-- Flèche -->
    <path d="M 188 55 L 232 55 M 222 45 L 232 55 L 222 65"
          stroke="{ACCENT}" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- "Après" : SVG inline avec icône maison -->
    <rect x="250" y="0" width="170" height="110" rx="8" fill="{BG_DEEP}" stroke="{ACCENT}" stroke-width="2"/>
    <g transform="translate(295, 30)">
      <path d="M 0 30 L 40 0 L 80 30 L 80 60 L 50 60 L 50 40 L 30 40 L 30 60 L 0 60 Z"
            fill="{ACC_L}" stroke="{TEXT}" stroke-width="1.5"/>
    </g>
  </g>

  <!-- Badge WebStrategy en bas -->
  <text x="80" y="460" font-family="Helvetica, Arial, sans-serif"
        font-size="13" fill="{TEXT_M}" opacity="0.7">by WebStrategy · wordpress-freelance.com</text>
</svg>"""

write_png(BANNER_SVG, "banner-1544x500.png", 1544, 500)

# Banner mobile 772×250
write_png(BANNER_SVG, "banner-772x250.png", 772, 250)


# =============================================================================
# 3. SCREENSHOTS — chrome WP admin + scènes représentatives
# =============================================================================

def screenshot(filename: str, title: str, body_inner: str) -> None:
    """Wrapper screenshot 1200×900 avec chrome WordPress."""
    svg = f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 900">
      <!-- Body WP -->
      <rect width="1200" height="900" fill="{WP_BG}"/>

      <!-- Admin bar du haut -->
      <rect x="0" y="0" width="1200" height="32" fill="{WP_NAV}"/>
      <circle cx="16" cy="16" r="6" fill="#8c8f94"/>
      <text x="32" y="20" font-family="Helvetica, sans-serif" font-size="13" fill="#fff">Site Test</text>
      <text x="1130" y="20" font-family="Helvetica, sans-serif" font-size="13" fill="#fff">👤 Sébastien</text>

      <!-- Sidebar gauche -->
      <rect x="0" y="32" width="160" height="868" fill="{WP_SIDE}"/>
      <text x="14" y="68"  font-family="Helvetica, sans-serif" font-size="13" fill="#fff" opacity="0.9">Tableau de bord</text>
      <text x="14" y="98"  font-family="Helvetica, sans-serif" font-size="13" fill="#fff" opacity="0.7">Articles</text>
      <text x="14" y="124" font-family="Helvetica, sans-serif" font-size="13" fill="#fff" opacity="0.7">Médias</text>
      <text x="14" y="150" font-family="Helvetica, sans-serif" font-size="13" fill="#fff" opacity="0.7">Pages</text>
      <text x="14" y="176" font-family="Helvetica, sans-serif" font-size="13" fill="#fff" opacity="0.7">Apparence</text>
      <text x="14" y="202" font-family="Helvetica, sans-serif" font-size="13" fill="#fff" opacity="1">Extensions</text>
      <rect x="0" y="187" width="3" height="22" fill="{ACCENT}"/>
      <text x="14" y="228" font-family="Helvetica, sans-serif" font-size="13" fill="#fff" opacity="0.7">Utilisateurs</text>
      <text x="14" y="254" font-family="Helvetica, sans-serif" font-size="13" fill="#fff" opacity="0.7">Outils</text>
      <text x="14" y="280" font-family="Helvetica, sans-serif" font-size="13" fill="#fff" opacity="0.7">Réglages</text>

      <!-- Contenu principal -->
      <g transform="translate(180, 52)">
        <text x="0" y="30" font-family="Helvetica, sans-serif" font-weight="400"
              font-size="23" fill="#1d2327">{title}</text>
        {body_inner}
      </g>
    </svg>"""
    write_png(svg, filename, 1200, 900)


# --- Screenshot 1 : page Extensions avec notice upgrade vers PRO ---
sc1_body = f"""
  <!-- Notice violet upgrade -->
  <rect x="0" y="60" width="1000" height="180" rx="4" fill="#fff" stroke="{ACCENT}" stroke-width="4"/>
  <rect x="0" y="60" width="6" height="180" fill="{ACCENT}"/>

  <text x="30" y="100" font-family="Helvetica, sans-serif" font-weight="700"
        font-size="18" fill="#1d2327">WS Font Awesome to SVG</text>

  <text x="30" y="132" font-family="Helvetica, sans-serif"
        font-size="15" fill="#3c434a">7 icônes Font Awesome utilisées sur votre site ne sont pas couvertes</text>
  <text x="30" y="152" font-family="Helvetica, sans-serif"
        font-size="15" fill="#3c434a">par la version FREE et restent en webfont (impact PageSpeed).</text>

  <!-- Pills d'icônes manquantes -->
  <g transform="translate(30, 170)">
    <rect x="0"   y="0" width="100" height="26" rx="13" fill="#f0e8ff" stroke="{ACCENT}" stroke-width="1"/>
    <text x="50"  y="17" text-anchor="middle" font-family="monospace" font-size="12" fill="{ACCENT}">fa-ambulance</text>

    <rect x="110" y="0" width="90"  height="26" rx="13" fill="#f0e8ff" stroke="{ACCENT}" stroke-width="1"/>
    <text x="155" y="17" text-anchor="middle" font-family="monospace" font-size="12" fill="{ACCENT}">fa-bullhorn</text>

    <rect x="210" y="0" width="80"  height="26" rx="13" fill="#f0e8ff" stroke="{ACCENT}" stroke-width="1"/>
    <text x="250" y="17" text-anchor="middle" font-family="monospace" font-size="12" fill="{ACCENT}">fa-rocket</text>

    <rect x="300" y="0" width="80"  height="26" rx="13" fill="#f0e8ff" stroke="{ACCENT}" stroke-width="1"/>
    <text x="340" y="17" text-anchor="middle" font-family="monospace" font-size="12" fill="{ACCENT}">fa-camera</text>

    <text x="395" y="17" font-family="Helvetica, sans-serif" font-size="12" fill="#3c434a" opacity="0.7">+3 autres</text>
  </g>

  <!-- Bouton CTA -->
  <rect x="30" y="208" width="200" height="36" rx="3" fill="{ACCENT}"/>
  <text x="130" y="231" text-anchor="middle" font-family="Helvetica, sans-serif" font-weight="600"
        font-size="14" fill="#fff">Voir l'offre PRO →</text>

  <text x="246" y="231" font-family="Helvetica, sans-serif" font-size="13"
        fill="#3c434a" opacity="0.6">ou cliquer pour ignorer</text>

  <!-- Liste d'extensions classique en dessous -->
  <line x1="0" y1="280" x2="1000" y2="280" stroke="#dcdcde"/>
  <text x="0" y="310" font-family="Helvetica, sans-serif" font-weight="400"
        font-size="16" fill="#1d2327">WS Font Awesome to SVG</text>
  <text x="0" y="332" font-family="Helvetica, sans-serif" font-size="13"
        fill="#3c434a" opacity="0.7">Activé · Version 2.0.0 · par WebStrategy</text>

  <line x1="0" y1="370" x2="1000" y2="370" stroke="#dcdcde"/>
"""
screenshot("screenshot-1.png", "Extensions", sc1_body)


# --- Screenshot 2 : avant/après PageSpeed score ---
sc2_body = f"""
  <text x="0" y="60" font-family="Helvetica, sans-serif" font-size="14" fill="#3c434a">
    Conversion automatique de toutes les balises Font Awesome en SVG inline.
  </text>

  <!-- Carte AVANT -->
  <rect x="0" y="100" width="450" height="280" rx="6" fill="#fff" stroke="#dcdcde"/>
  <text x="20" y="135" font-family="Helvetica, sans-serif" font-weight="600"
        font-size="16" fill="#1d2327">Avant</text>
  <text x="20" y="158" font-family="Helvetica, sans-serif" font-size="13"
        fill="#3c434a" opacity="0.7">Font Awesome via webfont</text>

  <!-- Score circulaire rouge -->
  <circle cx="225" cy="260" r="60" fill="none" stroke="#fce4e4" stroke-width="12"/>
  <circle cx="225" cy="260" r="60" fill="none" stroke="#d63638" stroke-width="12"
          stroke-dasharray="200 377" transform="rotate(-90 225 260)"/>
  <text x="225" y="270" text-anchor="middle" font-family="Helvetica, sans-serif"
        font-weight="700" font-size="36" fill="#d63638">52</text>

  <text x="225" y="350" text-anchor="middle" font-family="Helvetica, sans-serif"
        font-size="12" fill="#3c434a" opacity="0.7">PageSpeed Performance</text>

  <!-- Carte APRÈS -->
  <rect x="490" y="100" width="450" height="280" rx="6" fill="#fff" stroke="{ACCENT}" stroke-width="2"/>
  <text x="510" y="135" font-family="Helvetica, sans-serif" font-weight="600"
        font-size="16" fill="#1d2327">Après</text>
  <text x="510" y="158" font-family="Helvetica, sans-serif" font-size="13"
        fill="{ACCENT}" opacity="0.9">SVG inline · 0 webfont chargée</text>

  <!-- Score circulaire vert -->
  <circle cx="715" cy="260" r="60" fill="none" stroke="#e0f5e9" stroke-width="12"/>
  <circle cx="715" cy="260" r="60" fill="none" stroke="#00a32a" stroke-width="12"
          stroke-dasharray="357 377" transform="rotate(-90 715 260)"/>
  <text x="715" y="270" text-anchor="middle" font-family="Helvetica, sans-serif"
        font-weight="700" font-size="36" fill="#00a32a">94</text>

  <text x="715" y="350" text-anchor="middle" font-family="Helvetica, sans-serif"
        font-size="12" fill="#3c434a" opacity="0.7">PageSpeed Performance</text>

  <!-- Gains détaillés -->
  <rect x="0" y="420" width="940" height="200" rx="6" fill="#fff" stroke="#dcdcde"/>
  <text x="20" y="460" font-family="Helvetica, sans-serif" font-weight="600"
        font-size="15" fill="#1d2327">Gains mesurés (typique)</text>

  <text x="20"  y="500" font-family="Helvetica, sans-serif" font-size="13" fill="#3c434a">Render-blocking CSS</text>
  <text x="280" y="500" font-family="Helvetica, sans-serif" font-weight="600" font-size="13" fill="#00a32a">−1 requête</text>

  <text x="20"  y="530" font-family="Helvetica, sans-serif" font-size="13" fill="#3c434a">Poids transféré</text>
  <text x="280" y="530" font-family="Helvetica, sans-serif" font-weight="600" font-size="13" fill="#00a32a">−220 ko</text>

  <text x="20"  y="560" font-family="Helvetica, sans-serif" font-size="13" fill="#3c434a">FOIT (Flash of Invisible Text)</text>
  <text x="280" y="560" font-family="Helvetica, sans-serif" font-weight="600" font-size="13" fill="#00a32a">éliminé</text>

  <text x="20"  y="590" font-family="Helvetica, sans-serif" font-size="13" fill="#3c434a">Score Lighthouse</text>
  <text x="280" y="590" font-family="Helvetica, sans-serif" font-weight="600" font-size="13" fill="#00a32a">+30 à +60 pts</text>
"""
screenshot("screenshot-2.png", "Pourquoi ce plugin ?", sc2_body)


# --- Screenshot 3 : exemple de remplacement DOM ---
sc3_body = f"""
  <text x="0" y="60" font-family="Helvetica, sans-serif" font-size="14" fill="#3c434a">
    Le HTML rendu côté front-end est automatiquement réécrit, sans toucher au code source.
  </text>

  <!-- Bloc HTML "avant" -->
  <text x="0" y="110" font-family="Helvetica, sans-serif" font-weight="600"
        font-size="14" fill="#1d2327">HTML rendu par votre thème (avant)</text>
  <rect x="0" y="125" width="940" height="160" rx="6" fill="#1d2327"/>
  <text x="20" y="160" font-family="Courier, monospace" font-size="13" fill="#a1d4ff">&lt;ul class="contact"&gt;</text>
  <text x="40" y="185" font-family="Courier, monospace" font-size="13" fill="#a1d4ff">&lt;li&gt;&lt;i class="fa fa-envelope"&gt;&lt;/i&gt; contact@site.fr&lt;/li&gt;</text>
  <text x="40" y="210" font-family="Courier, monospace" font-size="13" fill="#a1d4ff">&lt;li&gt;&lt;i class="fa fa-phone"&gt;&lt;/i&gt; 01 23 45 67 89&lt;/li&gt;</text>
  <text x="40" y="235" font-family="Courier, monospace" font-size="13" fill="#a1d4ff">&lt;li&gt;&lt;i class="fa fa-house"&gt;&lt;/i&gt; 75001 Paris&lt;/li&gt;</text>
  <text x="20" y="265" font-family="Courier, monospace" font-size="13" fill="#a1d4ff">&lt;/ul&gt;</text>

  <!-- Flèche transition -->
  <text x="465" y="320" text-anchor="middle" font-family="Helvetica, sans-serif" font-size="20"
        fill="{ACCENT}">↓</text>
  <text x="540" y="324" font-family="Helvetica, sans-serif" font-size="13"
        fill="{ACCENT}">automatique, transparent</text>

  <!-- Bloc HTML "après" -->
  <text x="0" y="370" font-family="Helvetica, sans-serif" font-weight="600"
        font-size="14" fill="#1d2327">HTML envoyé au navigateur (après)</text>
  <rect x="0" y="385" width="940" height="200" rx="6" fill="#1d2327" stroke="{ACCENT}" stroke-width="2"/>
  <text x="20" y="420" font-family="Courier, monospace" font-size="12" fill="#a1d4ff">&lt;ul class="contact"&gt;</text>
  <text x="40" y="445" font-family="Courier, monospace" font-size="11" fill="#a1d4ff">&lt;li&gt;&lt;svg viewBox="0 0 512 512" class="ws-svg-icon ws-svg-icon--envelope"&gt;...&lt;/svg&gt; contact@site.fr&lt;/li&gt;</text>
  <text x="40" y="470" font-family="Courier, monospace" font-size="11" fill="#a1d4ff">&lt;li&gt;&lt;svg viewBox="0 0 512 512" class="ws-svg-icon ws-svg-icon--phone"&gt;...&lt;/svg&gt; 01 23 45 67 89&lt;/li&gt;</text>
  <text x="40" y="495" font-family="Courier, monospace" font-size="11" fill="#a1d4ff">&lt;li&gt;&lt;svg viewBox="0 0 576 512" class="ws-svg-icon ws-svg-icon--house"&gt;...&lt;/svg&gt; 75001 Paris&lt;/li&gt;</text>
  <text x="20" y="525" font-family="Courier, monospace" font-size="12" fill="#a1d4ff">&lt;/ul&gt;</text>

  <text x="20" y="560" font-family="Helvetica, sans-serif" font-size="12" fill="{ACCENT}" opacity="0.9">
    Aucune modification de votre thème ou de votre code. Le hook `the_content` fait tout.
  </text>
"""
screenshot("screenshot-3.png", "Comment ça marche", sc3_body)


print(f"\n✅ {len(list(ASSETS.glob('*.png')))} assets générés dans {ASSETS}")
