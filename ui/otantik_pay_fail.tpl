<!DOCTYPE HTML>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">
<title>Paiement non abouti — {$company|escape}</title>
<link rel="icon" href="{$app_url}/ui/ui_custom/images/favicon.png" type="image/png">
<link rel="stylesheet" href="{$kit_url}/styles/bootstrap.css">
<link rel="stylesheet" href="{$kit_url}/fonts/bootstrap-icons.css">
<link rel="stylesheet" href="{$kit_url}/styles/style.css?v=wo-hs8">
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<meta id="theme-check" name="theme-color" content="#FFFFFF">
<style id="wo-hs-theme">{if isset($hs_theme_css)}{$hs_theme_css nofilter}{/if}</style>
<style>
.wo-hs-logo{height:32px;width:auto;max-width:88px;object-fit:contain;margin:0 8px 0 0;border-radius:6px}
.header-bar .header-title{display:flex;align-items:center}
.wo-fail-card{text-align:center}
.wo-fail-mark{width:60px;height:60px;border-radius:50%;background:#fee2e2;color:#b91c1c;display:grid;place-items:center;margin:0 auto 14px;font-size:26px;font-weight:800}
.theme-dark .wo-fail-mark{background:#3f1d1d;color:#fca5a5}
</style>
</head>
<body class="theme-light">
<div id="preloader"><div class="spinner-border color-highlight" role="status"></div></div>
<div id="page">
  <div class="header-bar header-fixed header-app header-bar-detached">
    {if $kit_logo}<img class="wo-hs-logo" src="{$kit_logo|escape}" alt="">{/if}
    <a href="{$hs_home}" class="header-title color-theme">{$kit_title|default:$company}</a>
    <a href="#" class="show-on-theme-light default-link" data-toggle-theme><i class="bi bi-moon-fill font-13"></i></a>
    <a href="#" class="show-on-theme-dark default-link" data-toggle-theme><i class="bi bi-lightbulb-fill color-yellow-dark font-13"></i></a>
  </div>
  <div class="page-content header-clear-medium">
    <div class="card card-style">
      <div class="content wo-fail-card">
        <div class="wo-fail-mark">!</div>
        <h3>{$fail_title|escape}</h3>
        <p class="mb-3">{$fail_text|escape}</p>
        <a class="btn btn-full bg-blue-dark rounded-xs text-uppercase font-700 w-100 btn-s" href="{$pay_retry}">Réessayer le paiement</a>
        <a class="default-link d-block mt-3" href="{$pay_home}">Retour à l’accueil</a>
      </div>
    </div>
  </div>
</div>
<script src="{$kit_url}/scripts/bootstrap.min.js"></script>
<script src="{$kit_url}/scripts/custom.js?v=wo-hs9"></script>
</body>
</html>
