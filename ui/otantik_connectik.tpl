<!DOCTYPE html>
<html lang="fr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Connectik — {$company}</title>
<link rel="icon" href="{$app_url}/ui/ui_custom/images/favicon.png" type="image/png">
<link rel="stylesheet" href="{$app_url}/ui/ui_custom/hotspot-ui/portal.css?v=20260817p">
</head>
<body class="wo-hs">
<div class="header-bar">
  <img src="{$app_url}/ui/ui_custom/images/logo.png" alt="">
  <span class="header-title">Connectik</span>
</div>
<div class="page-content">
  <div class="card-style"><div class="hero">
    <h3>Ligne PPPoE</h3>
    <p>Choisissez votre zone pour recharger une connexion fibre / ONU. Le Wi‑Fi hotspot n’est pas ici.</p>
  </div></div>
  <div class="card-style">
    {if $zones|@count == 0}
      <div class="content"><p class="muted">Aucune zone PPPoE n’est encore en vente.</p></div>
    {else}
      {foreach $zones as $z}
        <a class="zone-pick" href="{$z.url}"><b>{$z.name}</b><small>{$z.plans} forfait(s)</small></a>
      {/foreach}
    {/if}
  </div>
</div>
</body></html>
