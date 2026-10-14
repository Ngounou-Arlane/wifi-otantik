<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#002858">
{literal}
<script>
(function(){
  try{
    if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
    var v = sessionStorage.getItem('woScrollY');
    if (v !== null) {
      document.documentElement.style.visibility = 'hidden';
      window.__woRestoreY = parseInt(v, 10) || 0;
    }
  }catch(e){}
})();
</script>
{/literal}
<title>{$_title} — Wifi-Otantik</title>
<link rel="icon" href="{$app_url}/ui/ui_custom/images/favicon.png?v=20260818a" type="image/png">
<link rel="apple-touch-icon" href="{$app_url}/ui/ui_custom/images/favicon.png?v=20260818a">
<link rel="stylesheet" href="{$app_url}/ui/ui/fonts/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="{$app_url}/ui/ui/fonts/ionicons/css/ionicons.min.css">
<link rel="stylesheet" href="{$app_url}/ui/ui/styles/sweetalert2.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="{$app_url}/ui/ui_custom/otantik-app.css?v=20260909lottie16">
<link rel="stylesheet" href="{$app_url}/ui/ui_custom/otantik-msg.css?v=20260817z">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
{literal}
<style>
.wo-bento{display:grid!important;grid-template-columns:repeat(12,minmax(0,1fr))!important;gap:18px!important;align-items:stretch}
.wo-s8{grid-column:span 8}
.wo-s4{grid-column:span 4}
.wo-s3{grid-column:span 3}
.wo-span-3{grid-column:1/-1}
.wo-hero{background:linear-gradient(135deg,#001e42 0%,#002858 46%,#f05018 130%)!important;color:#fff!important;border:0!important;min-height:220px;position:relative;overflow:hidden;display:flex;flex-direction:column;justify-content:center}
.wo-hero h1{color:#fff;font-size:30px;margin:8px 0 4px}
.wo-clock{background:#002858!important;color:#fff!important;border:0!important;min-height:220px;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center}
.wo-kpi{min-height:118px}
.wo-dark{background:#002858!important;color:#f8fafc!important;border:0!important}
@media(max-width:1100px){.wo-s8,.wo-s4,.wo-s3,.wo-span-3,.wo-bento>.wo-card{grid-column:1/-1}}
@media(max-width:800px){
  .wo-bento{gap:12px!important}
  .wo-hero{min-height:0;padding:18px 16px!important}
  .wo-hero h1{font-size:22px;margin:6px 0 2px}
  .wo-clock{display:none!important}
  .wo-kpi{min-height:0}
}
</style>
{/literal}
<script src="{$app_url}/ui/ui/scripts/sweetalert2.all.min.js"></script>
<script>document.documentElement.setAttribute('data-theme', localStorage.getItem('wo-theme') || 'light');</script>
</head>
<body class="wo-body{if $print_mode|default:false} wo-rx-print-page{/if}">
<div class="wo-nav-scrim" id="woNavScrim" onclick="woNavClose()" hidden></div>
<div class="wo-app">
<aside class="wo-side" id="woSide" aria-label="Navigation">
  <div class="wo-side-mobi-h">
    <a class="wo-brand" href="{Text::url('plugin/wifi_otantik_home')}"><img src="{$app_url}/ui/ui_custom/images/logo.png" alt="Wifi-Otantik"> Wifi-Otantik</a>
    <button type="button" class="wo-iconbtn wo-side-close" onclick="woNavClose()" aria-label="Fermer le menu"><i class="fa fa-times"></i></button>
  </div>
  <a class="wo-brand wo-brand-desk" href="{Text::url('plugin/wifi_otantik_home')}"><img src="{$app_url}/ui/ui_custom/images/logo.png" alt="Wifi-Otantik"> Wifi-Otantik</a>
  <a class="wo-user{if $otantik_nav == 'wifi_otantik_profile'} active{/if}" href="{Text::url('plugin/wifi_otantik_profile')}">
    <div class="wo-ava"><img src="{$otantik_avatar}" alt=""></div>
    <div>
      <b>{$otantik_user_name}</b>
      <small>{$otantik_phone}</small>
    </div>
  </a>
  <nav class="wo-nav">
    {if $otantik_can_dashboard}
    <div class="wo-nav-label">Accueil</div>
    <a class="{if $otantik_nav == 'wifi_otantik_home'}active{/if}" href="{Text::url('plugin/wifi_otantik_home')}"><i class="fa fa-th-large"></i> Tableau de bord</a>
    {/if}
    {if $otantik_can_network}
    <a class="{if $otantik_nav == 'wifi_otantik_routers'}active{/if}" href="{Text::url('plugin/wifi_otantik_routers')}"><i class="fa fa-wifi"></i> Mes Wifi Zone</a>
    <a class="{if $otantik_nav == 'wifi_otantik_captive'}active{/if}" href="{Text::url('plugin/wifi_otantik_captive')}"><i class="fa fa-mobile"></i> Portail captif</a>
    {/if}
    {if $otantik_can_sales}
    <div class="wo-nav-label">Vente</div>
    <a class="{if $otantik_nav == 'wifi_otantik_plans'}active{/if}" href="{Text::url('plugin/wifi_otantik_plans')}"><i class="fa fa-tags"></i> Mes tarifs</a>
    <a class="{if $otantik_nav == 'wifi_otantik_tickets' || $otantik_nav == 'wifi_otantik_sales' || $otantik_nav == 'wifi_otantik_receipts'}active{/if}" href="{Text::url('plugin/wifi_otantik_tickets')}"><i class="fa fa-ticket"></i> Mes tickets</a>
    <a class="{if $otantik_nav == 'wifi_otantik_resellers'}active{/if}" href="{Text::url('plugin/wifi_otantik_resellers')}"><i class="fa fa-briefcase"></i> Revendeurs</a>
    <a class="{if $otantik_nav == 'wifi_otantik_customers'}active{/if}" href="{Text::url('plugin/wifi_otantik_customers')}"><i class="fa fa-users"></i> Abonnés</a>
    {/if}
    {if $otantik_can_network}
    <div class="wo-nav-label">Réseau</div>
    <a class="{if $otantik_nav == 'wifi_otantik_inbox'}active{/if}" href="{Text::url('plugin/wifi_otantik_inbox')}"><i class="fa fa-envelope"></i> Messages{if $otantik_inbox_pending > 0} <span class="wo-badge">{$otantik_inbox_pending}</span>{/if}</a>
    <a class="{if $otantik_nav == 'wifi_otantik_monitor'}active{/if}" href="{Text::url('plugin/wifi_otantik_monitor')}"><i class="fa fa-heartbeat"></i> Supervision</a>
    <a class="{if $otantik_nav == 'wifi_otantik_reconcile'}active{/if}" href="{Text::url('plugin/wifi_otantik_reconcile')}"><i class="fa fa-check-square"></i> Réconciliation</a>
    {if !$otantik_is_platform}
    <a class="{if $otantik_nav == 'wifi_otantik_wa_inbox'}active{/if}" href="{Text::url('plugin/wifi_otantik_wa_inbox')}"><i class="fa fa-whatsapp"></i> WhatsApp {if $otantik_wa_on}<span class="wo-badge-premium">Pro</span>{else}<span class="wo-badge-premium" style="opacity:.6">Premium</span>{/if}</a>
    {/if}
    <a class="{if $otantik_nav == 'wifi_otantik_mk_logs'}active{/if}" href="{Text::url('plugin/wifi_otantik_mk_logs')}"><i class="fa fa-list-alt"></i> Journal MikroTik</a>
    <a class="{if $otantik_nav == 'wifi_otantik_map'}active{/if}" href="{Text::url('plugin/wifi_otantik_map')}"><i class="fa fa-map-marker"></i> Carte</a>
    {/if}
    {if $otantik_can_finance_view}
    <div class="wo-nav-label">Compte</div>
    <a class="{if $otantik_nav == 'wifi_otantik_erp'}active{/if}" href="{Text::url('plugin/wifi_otantik_erp')}"><i class="fa fa-pie-chart"></i> ERP {if $otantik_erp_on}<span class="wo-badge-premium">Pro</span>{else}<span class="wo-badge-premium" style="opacity:.6">Premium</span>{/if}</a>
    <a class="{if $otantik_nav == 'wifi_otantik_reports'}active{/if}" href="{Text::url('plugin/wifi_otantik_reports')}"><i class="fa fa-bar-chart"></i> Rapports</a>
    {if $otantik_can_finance_manage}
    <a class="{if $otantik_nav == 'wifi_otantik_wallet'}active{/if}" href="{Text::url('plugin/wifi_otantik_wallet')}"><i class="fa fa-money"></i> Retraits</a>
    {/if}
    <a class="{if $otantik_nav == 'wifi_otantik_referrals'}active{/if}" href="{Text::url('plugin/wifi_otantik_referrals')}"><i class="fa fa-share-alt"></i> Parrainages</a>
    <a class="{if $otantik_nav == 'wifi_otantik_docs'}active{/if}" href="{Text::url('plugin/wifi_otantik_docs')}"><i class="fa fa-book"></i> Documentation</a>
    {/if}
    {if $otantik_can_staff_manage}
    <a class="{if $otantik_nav == 'wifi_otantik_staff'}active{/if}" href="{Text::url('plugin/wifi_otantik_staff')}"><i class="fa fa-user-plus"></i> Équipe</a>
    {/if}
    {if $otantik_is_platform}
    <div class="wo-nav-label">Plateforme</div>
    <a class="{if $otantik_nav == 'wifi_otantik_tenants' || $otantik_nav == 'wifi_otantik_platform'}active{/if}" href="{Text::url('plugin/wifi_otantik_tenants')}"><i class="fa fa-building"></i> Clients</a>
    <a class="{if $otantik_nav == 'wifi_otantik_pg'}active{/if}" href="{Text::url('plugin/wifi_otantik_pg')}"><i class="fa fa-credit-card"></i> Paiements</a>
    <a class="{if $otantik_nav == 'wifi_otantik_wa_inbox'}active{/if}" href="{Text::url('plugin/wifi_otantik_wa_inbox')}"><i class="fa fa-whatsapp"></i> Boîte WhatsApp</a>
    <a class="{if $otantik_nav == 'wifi_otantik_wa_logs'}active{/if}" href="{Text::url('plugin/wifi_otantik_wa_logs')}"><i class="fa fa-whatsapp"></i> Journal WhatsApp</a>
    <a class="{if $otantik_nav == 'wifi_otantik_all_routers'}active{/if}" href="{Text::url('plugin/wifi_otantik_all_routers')}"><i class="fa fa-sitemap"></i> Tous les routeurs</a>
    <a class="{if $otantik_nav == 'wifi_otantik_all_sales'}active{/if}" href="{Text::url('plugin/wifi_otantik_all_sales')}"><i class="fa fa-bar-chart"></i> Toutes les ventes</a>
    <a class="{if $otantik_nav == 'wifi_otantik_login_logs'}active{/if}" href="{Text::url('plugin/wifi_otantik_login_logs')}"><i class="fa fa-sign-in"></i> Connexions</a>
    {/if}
  </nav>
  {if $otantik_zone_count < 1}
  <div class="wo-cta">
    <h4>On commence ?</h4>
    <p>Monétisez votre Wifi Zone dès aujourd’hui.</p>
    <a class="wo-btn wo-btn-primary" href="{Text::url('plugin/wifi_otantik_routers/new')}"><i class="fa fa-plus"></i> Nouvelle Wifi Zone</a>
  </div>
  {/if}
  <div class="wo-side-foot">
    <a href="#" onclick="woTheme();return false;"><i class="fa fa-moon-o"></i> Thème</a>
    <a href="{Text::url('logout')}"><i class="fa fa-sign-out"></i> Déconnexion</a>
  </div>
</aside>
<div class="wo-main">
  <div class="wo-top">
    <div class="wo-top-mobi">
      <button type="button" class="wo-iconbtn wo-mobi-menu" id="woNavOpen" onclick="woNavOpen()" aria-label="Ouvrir le menu"><i class="fa fa-bars"></i></button>
      <div class="wo-top-mobi-title">
        <span class="wo-top-brand">Wifi-Otantik</span>
        <strong>{$_title}</strong>
      </div>
      <button type="button" class="wo-iconbtn" id="woSpotOpenMobi" onclick="document.getElementById('woSpotOpen').click()" aria-label="Rechercher"><i class="fa fa-search"></i></button>
    </div>
    <div class="wo-crumb">Espace de travail / {$_title}</div>
    <button type="button" class="wo-spot-open" id="woSpotOpen" title="Rechercher (Ctrl+K)">
      <i class="fa fa-search"></i>
      <span>Rechercher zones, tickets, abonnés…</span>
      <kbd>Ctrl K</kbd>
    </button>
    <div class="wo-top-actions">
      <a class="wo-btn wo-btn-primary wo-btn-recover" href="{$otantik_recover}" target="_blank"><i class="fa fa-ticket"></i> <span>Retrouver un ticket</span></a>
      <a class="wo-iconbtn wo-btn-recover-ico" href="{$otantik_recover}" target="_blank" title="Retrouver un ticket"><i class="fa fa-ticket"></i></a>
      <div class="wo-notify" id="woNotifyBox">
        <a class="wo-iconbtn" href="#" onclick="woToggleNotify();return false;" title="Notifications"><i class="fa fa-bell"></i>{if $otantik_notify > 0}<span class="wo-badge">{$otantik_notify}</span>{/if}</a>
        <div class="wo-notify-panel" id="woNotifyPanel">
          <b>Alertes</b>
          {if $otantik_inbox_pending > 0}
            <a class="wo-notify-row" href="{Text::url('plugin/wifi_otantik_inbox/mark_all')}">
              <span>Messages du portail<small style="display:block;opacity:.7;font-weight:500">Cliquer pour tout marquer lu</small></span>
              <span class="wo-chip wait">{$otantik_inbox_pending}</span>
            </a>
          {/if}
          {if $otantik_is_platform && $otantik_kyc_pending > 0}
            <a class="wo-notify-row" href="{Text::url('plugin/wifi_otantik_tenants')}">
              <span>Dossiers KYC à vérifier</span>
              <span class="wo-chip wait">{$otantik_kyc_pending}</span>
            </a>
          {/if}
          {if $otantik_alerts|@count == 0 && $otantik_inbox_pending == 0 && (!$otantik_is_platform || $otantik_kyc_pending == 0)}
            <p class="wo-notify-empty">Rien à signaler.</p>
          {else}
            {foreach $otantik_alerts as $a}
              <a class="wo-notify-row" href="{$a.href|default:'#'|escape}">
                <span>{$a.name|escape}<small style="display:block;opacity:.7;font-weight:500">{$a.label|default:$a.status|escape}</small></span>
                <span class="wo-chip {if $a.tone == 'ok'}ok{elseif $a.tone == 'wait'}wait{else}no{/if}">{$a.label|default:$a.status|escape}</span>
              </a>
            {/foreach}
          {/if}
        </div>
      </div>
      <a class="wo-iconbtn" href="#" onclick="woTheme();return false;" title="Thème"><i class="fa fa-moon-o"></i></a>
      <a class="wo-iconbtn" href="{$_url}{$otantik_nav}" title="Actualiser"><i class="fa fa-refresh"></i></a>
      <a class="wo-iconbtn" href="{Text::url('logout')}" title="Déconnexion"><i class="fa fa-sign-out"></i></a>
    </div>
  </div>
  <div class="wo-spot" id="woSpot" role="dialog" aria-label="Recherche">
    <div class="wo-spot-card">
      <label class="wo-find wo-find-lg">
        <i class="fa fa-search"></i>
        <input class="wo-find-input" id="woSpotQ" type="search" placeholder="Pages, zones, tickets, clients…" autocomplete="off" spellcheck="false">
      </label>
      <div class="wo-spot-hits" id="woSpotHits"></div>
      <div class="wo-spot-foot"><span>↑↓ naviguer</span><span>Entrée ouvrir</span><span>Échap fermer</span></div>
    </div>
  </div>
  <div class="wo-page">
    {if isset($notify)}
    <script>
    document.addEventListener('DOMContentLoaded',function(){
      if(!window.woMsg) return;
      {if $notify_t == "s"}
      woMsg({ldelim}
        title:'C’est fait',
        text:'{$notify|escape:"javascript"}',
        kind:'ok',
        href:'{Text::url("plugin/wifi_otantik_home")|escape:"javascript"}',
        cta:'Tableau de bord',
        redirect:'{Text::url("plugin/wifi_otantik_home")|escape:"javascript"}',
        timer:4000
      {rdelim});
      {else}
      woMsg({ldelim}title:'Oups !', text:'{$notify|escape:"javascript"}', kind:'err'{rdelim});
      {/if}
    });
    </script>
    {/if}
