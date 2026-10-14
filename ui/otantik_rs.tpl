<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$_title} — Revendeur Wifi-Otantik</title>
<link rel="icon" href="{$app_url}/ui/ui_custom/images/favicon.png?v=20260818a" type="image/png">
<link rel="stylesheet" href="{$app_url}/ui/ui/fonts/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="{$app_url}/ui/ui/styles/sweetalert2.min.css">
<link rel="stylesheet" href="{$app_url}/ui/ui_custom/otantik-app.css?v=20260825ios">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="{$app_url}/ui/ui/scripts/sweetalert2.all.min.js"></script>
<script>document.documentElement.setAttribute('data-theme', localStorage.getItem('wo-theme') || 'light');</script>
</head>
<body class="wo-body wo-rs">
<div class="wo-app">
<aside class="wo-side">
  <a class="wo-brand" href="{$rs_home}"><img src="{$app_url}/ui/ui_custom/images/logo.png" alt=""> Revendeur</a>
  <a class="wo-user{if $rs_page == 'profile'} active{/if}" href="{$rs_profile}">
    <div class="wo-ava">{if $rs_avatar}<img src="{$rs_avatar}" alt="">{else}<i class="fa fa-user" style="line-height:42px;text-align:center;display:block"></i>{/if}</div>
    <div><b>{$rs.name|escape}</b><small>{$rs.phone|escape}</small></div>
  </a>
  <nav class="wo-nav">
    <a class="{if $rs_page == 'home'}active{/if}" href="{$rs_home}"><i class="fa fa-th-large"></i> Tableau de bord</a>
    <a class="{if $rs_page == 'sell'}active{/if}" href="{$rs_sell}"><i class="fa fa-ticket"></i> Vendre</a>
    <a class="{if $rs_page == 'recharge'}active{/if}" href="{$rs_recharge}"><i class="fa fa-plus-circle"></i> Recharger</a>
    <a class="{if $rs_page == 'history'}active{/if}" href="{$rs_history}"><i class="fa fa-list"></i> Historique</a>
    <a class="{if $rs_page == 'profile'}active{/if}" href="{$rs_profile}"><i class="fa fa-user"></i> Mon profil</a>
  </nav>
  <div class="wo-cta">
    <h4>Caisse</h4>
    <p style="font-size:22px;font-weight:800;margin:4px 0">{$rs.wallet|string_format:"%.0f"} F</p>
    <a class="wo-btn wo-btn-primary" href="{$rs_logout}"><i class="fa fa-sign-out"></i> Déconnexion</a>
  </div>
</aside>
<div class="wo-main">
  <div class="wo-top">
    <div class="wo-crumb">Espace revendeur / {$_title}</div>
    <button type="button" class="wo-spot-open" id="woSpotOpen" title="Rechercher (Ctrl+K)">
      <i class="fa fa-search"></i>
      <span>Rechercher forfaits, ventes, pages…</span>
      <kbd>Ctrl K</kbd>
    </button>
    <div class="wo-top-actions">
      <a class="wo-iconbtn" href="{$rs_profile}" title="Mon profil"><i class="fa fa-user"></i></a>
      <a class="wo-iconbtn" href="#" onclick="woTheme();return false;" title="Thème"><i class="fa fa-moon-o"></i></a>
    </div>
  </div>
  <div class="wo-spot" id="woSpot" role="dialog" aria-label="Recherche">
    <div class="wo-spot-card">
      <label class="wo-find wo-find-lg">
        <i class="fa fa-search"></i>
        <input class="wo-find-input" id="woSpotQ" type="search" placeholder="Pages, forfaits, ventes…" autocomplete="off" spellcheck="false">
      </label>
      <div class="wo-spot-hits" id="woSpotHits"></div>
      <div class="wo-spot-foot"><span>↑↓ naviguer</span><span>Entrée ouvrir</span><span>Échap fermer</span></div>
    </div>
  </div>
  <div class="wo-page">
    {if isset($notify)}
    <script>
    document.addEventListener('DOMContentLoaded',function(){
      if(window.Swal) Swal.fire({ldelim}icon:'{if $notify_t == "s"}success{else}error{/if}',title:'{if $notify_t == "s"}C’est fait{else}Oups{/if}',text:'{$notify|escape:"javascript"}'{rdelim});
    });
    </script>
    {/if}

{if $rs_page == 'home'}
<div class="wo-head"><div><h1>Bonjour {$rs.name|escape}</h1><p>Commission {$rs.commission|string_format:"%.0f"} % · sur 1 000 F vous gardez {$rs_split.profit} F, l’exploitant {$rs_split.wholesale} F.</p></div></div>
<div class="wo-grid wo-4">
  <div class="wo-card wo-kpi"><div><span>Caisse</span><h3>{$rs.wallet|string_format:"%.0f"} F</h3></div><div class="wo-ico blue"><i class="fa fa-money"></i></div></div>
  <div class="wo-card wo-kpi"><div><span>Ventes</span><h3>{$rs.sales_total|string_format:"%.0f"} F</h3></div><div class="wo-ico orange"><i class="fa fa-line-chart"></i></div></div>
  <div class="wo-card wo-kpi"><div><span>Commissions</span><h3>{$rs.profit|string_format:"%.0f"} F</h3></div><div class="wo-ico green"><i class="fa fa-percent"></i></div></div>
  <div class="wo-card wo-kpi"><div><span>Tickets</span><h3>{$rs.tickets_count}</h3></div><div class="wo-ico yellow"><i class="fa fa-ticket"></i></div></div>
</div>
<div class="wo-rs-stage">
  <section class="wo-card wo-rs-scope">
    <div class="wo-rs-scope-top">
      <span class="wo-rs-kicker">Périmètre</span>
      <h2>Zones et services</h2>
      <p>Ce que l’exploitant vous autorise à vendre.</p>
    </div>

    <div class="wo-rs-block">
      <div class="wo-rs-block-h">
        <b>Zones autorisées</b>
        <span class="wo-rs-count">{$rs_zones|@count}</span>
      </div>
      {if $rs_zones|@count == 0}
        <p class="wo-muted">Aucune zone attribuée. Contactez l’exploitant.</p>
      {else}
      <div class="wo-rs-zones">
        {foreach $rs_zones as $z}
        <div class="wo-rs-zone">
          <div class="wo-rs-zone-ico"><i class="fa fa-map-marker"></i></div>
          <div>
            <strong>{$z.name|escape}</strong>
            <small>Wifi Zone</small>
          </div>
        </div>
        {/foreach}
      </div>
      {/if}
    </div>

    <div class="wo-rs-block">
      <div class="wo-rs-block-h"><b>Services</b></div>
      <div class="wo-rs-svcs">
        <div class="wo-rs-svc {if $rs_perm.sell_hotspot}on{else}off{/if}">
          <span class="wo-rs-svc-ico"><i class="fa fa-wifi"></i></span>
          <strong>Tickets hotspot</strong>
          <small>{if $rs_perm.sell_hotspot}Autorisé{else}Non autorisé{/if}</small>
        </div>
        <div class="wo-rs-svc {if $rs_perm.sell_pppoe}on{else}off{/if}">
          <span class="wo-rs-svc-ico"><i class="fa fa-plug"></i></span>
          <strong>Lignes PPPoE</strong>
          <small>{if $rs_perm.sell_pppoe}Autorisé{else}Non autorisé{/if}</small>
        </div>
      </div>
    </div>

    <a class="wo-btn wo-btn-primary wo-rs-cta" href="{$rs_sell}"><i class="fa fa-plus"></i> Vendre un ticket</a>
  </section>

  <section class="wo-card wo-rs-catalog">
    <div class="wo-rs-cat-h">
      <div>
        <span class="wo-rs-kicker">Catalogue</span>
        <h2>Forfaits disponibles</h2>
        <p>{if $rs_plans|@count == 0}Aucun forfait sur vos zones.{else}{$rs_plans|@count} forfait{if $rs_plans|@count > 1}s{/if} prêts à vendre.{/if}</p>
      </div>
    </div>
    {if $rs_plans|@count == 0}
      <div class="wo-rs-empty">
        <i class="fa fa-ticket"></i>
        <p>Les forfaits apparaîtront ici dès qu’ils seront créés sur vos zones.</p>
      </div>
    {else}
      <div class="wo-filters" id="woRsFindBar">
        <label class="wo-find">
          <i class="fa fa-search"></i>
          <input class="wo-find-input" id="woRsPlanQ" type="search" placeholder="Rechercher un forfait, un prix, une zone…" autocomplete="off">
          <button type="button" class="wo-find-x" hidden aria-label="Effacer">&times;</button>
        </label>
      </div>
      {if $rs_zones|@count > 1}
      <div class="wo-rs-filters" id="woRsFilters">
        <button type="button" class="on" data-zone="">Tous</button>
        {foreach $rs_zones as $z}
        <button type="button" data-zone="{$z.name|escape}">{$z.name|escape}</button>
        {/foreach}
      </div>
      {/if}
      <div class="wo-rs-plans" id="woRsPlans">
        {foreach $rs_plans as $p}
        {assign var=kind value=$p.type|upper}
        {math equation="round(x * c / 100)" x=$p.price c=$rs.commission assign="gain"}
        <article class="wo-rs-plan {if $kind == 'PPPOE'}is-pppoe{else}is-hotspot{/if}" data-zone="{$p.routers|escape}" data-wo-row data-wo-hay="{$p.name_plan|escape} {$p.routers|escape} {$p.price} {if $kind == 'PPPOE'}pppoe{else}hotspot{/if}">
          <div class="wo-rs-plan-meta">
            <span class="wo-chip {if $kind == 'PPPOE'}wait{else}ok{/if}">{if $kind == 'PPPOE'}PPPoE{else}Hotspot{/if}</span>
            <span class="wo-rs-plan-zone"><i class="fa fa-map-marker"></i> {$p.routers|escape}</span>
          </div>
          <h3>{$p.name_plan|escape}</h3>
          <div class="wo-rs-plan-price">{$p.price}<em>F</em></div>
          <ul>
            {if $p.validity}
            <li><i class="fa fa-clock-o"></i> {$p.validity} {if $p.validity_unit == 'Hrs'}heure{if $p.validity > 1}s{/if}{elseif $p.validity_unit == 'Mins'}min{else}jour{if $p.validity > 1}s{/if}{/if}</li>
            {/if}
            <li><i class="fa fa-percent"></i> Votre gain {$gain} F</li>
          </ul>
          <a class="wo-rs-plan-cta" href="{$rs_sell}">Vendre</a>
        </article>
        {/foreach}
      </div>
      <script>
      {literal}
      (function(){
        var bar = document.getElementById('woRsFilters');
        var wrap = document.getElementById('woRsPlans');
        var qEl = document.getElementById('woRsPlanQ');
        var xBtn = qEl && qEl.parentNode.querySelector('.wo-find-x');
        if (!wrap) return;
        var cards = wrap.querySelectorAll('.wo-rs-plan');
        var zone = '';
        function apply(){
          var q = String(qEl && qEl.value || '').toLowerCase().replace(/^\s+|\s+$/g, '');
          if (xBtn) xBtn.hidden = !q;
          for (var i = 0; i < cards.length; i++) {
            var z = (cards[i].getAttribute('data-zone') || '').replace(/^\s+|\s+$/g, '');
            var hay = (cards[i].getAttribute('data-wo-hay') || cards[i].textContent || '').toLowerCase();
            var off = (zone !== '' && z !== zone) || (q !== '' && hay.indexOf(q) < 0);
            cards[i].classList.toggle('is-off', off);
            if (off) cards[i].setAttribute('hidden', 'hidden');
            else cards[i].removeAttribute('hidden');
          }
        }
        if (bar) {
          var btns = bar.querySelectorAll('button');
          for (var i = 0; i < btns.length; i++) {
            btns[i].addEventListener('click', function(){
              for (var j = 0; j < btns.length; j++) btns[j].classList.toggle('on', btns[j] === this);
              zone = (this.getAttribute('data-zone') || '').replace(/^\s+|\s+$/g, '');
              apply();
            });
          }
        }
        if (qEl) qEl.addEventListener('input', apply);
        if (xBtn && qEl) xBtn.addEventListener('click', function(){ qEl.value=''; apply(); qEl.focus(); });
      })();
      {/literal}
      </script>
    {/if}
  </section>
</div>
<div class="wo-card" style="margin-top:16px">
  <b>Derniers mouvements</b>
  {if $books|@count == 0}<div class="wo-empty">Aucun mouvement</div>
  {else}
  <div class="wo-filters" data-wo-filter>
    <label class="wo-find"><i class="fa fa-search"></i><input class="wo-find-input" data-wo-q type="search" placeholder="Filtrer les mouvements…" autocomplete="off"><button type="button" class="wo-find-x" hidden aria-label="Effacer">&times;</button></label>
    <span class="wo-filters-meta" data-wo-count></span>
  </div>
  <table class="wo-table"><thead><tr><th>Date</th><th>Type</th><th>Montant</th><th>Note</th></tr></thead><tbody>
  {foreach $books as $b}<tr data-wo-row data-wo-hay="{$b.kind|escape} {$b.note|escape} {$b.amount}"><td>{$b.created_at}</td><td>{$b.kind}</td><td>{$b.amount} F</td><td>{$b.note|escape}</td></tr>{/foreach}
  </tbody></table>
  <div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucun mouvement ne correspond</div>
  {/if}
</div>
{/if}

{if $rs_page == 'sell'}
<div class="wo-head"><div><h1>Vendre un ticket</h1><p>Le débit sur votre caisse = prix − votre commission ({$rs.commission|string_format:"%.0f"} %).</p></div></div>
{if $issued}
<div class="wo-card wo-issued">
  <b>{if $issued.kind == 'PPPOE'}Ligne PPPoE{else}Ticket hotspot{/if} prêt</b>
  <p>{$issued.zone} · {$issued.plan} · {$issued.price} F · débit {$issued.wholesale} F · gain {$issued.profit} F</p>
  <label class="wo-label">{if $issued.kind == 'PPPOE'}Identifiant{else}Code{/if}</label>
  <div class="wo-issued-code">{$issued.user|escape}</div>
  {if $issued.kind == 'PPPOE'}<label class="wo-label">Mot de passe</label><div class="wo-issued-code">{$issued.pass|escape}</div>{/if}
  {if $issued.push_error}<p class="wo-muted" style="color:#b45309">MikroTik : {$issued.push_error|escape}</p>{/if}
</div>
{/if}
<div class="wo-card">
  {if $rs_zones|@count == 0}
    <p>Aucune zone ne vous est attribuée. Contactez l’exploitant.</p>
  {else}
  <form method="post" id="woRsSell">
    <input type="hidden" name="sell_ticket" value="1">
    {if $auto_zone && $rs_solo}
      <input type="hidden" name="router_id" id="woZone" value="{$rs_solo.id}" data-name="{$rs_solo.name|escape}">
      <p>Zone : <b>{$rs_solo.name|escape}</b></p>
    {else}
      <p><label class="wo-label">Wifi Zone</label>
        <select class="wo-select" name="router_id" id="woZone" required>
          {foreach $rs_zones as $z}<option value="{$z.id}" data-name="{$z.name|escape}">{$z.name|escape}</option>{/foreach}
        </select></p>
    {/if}
    <p><label class="wo-label">Forfait</label>
      <select class="wo-select" name="plan_id" id="woPlan" required></select>
      <small class="wo-muted" id="woPlanHint"></small></p>
    <p><label class="wo-label">Téléphone client (optionnel)</label>
      <input class="wo-input" name="phone" placeholder="6XX XXX XXX"></p>
    <p id="woPassWrap" style="display:none"><label class="wo-label">Mot de passe PPPoE (optionnel)</label>
      <input class="wo-input" name="password"></p>
    <button class="wo-btn wo-btn-primary" type="submit"><i class="fa fa-ticket"></i> Générer</button>
  </form>
  {/if}
</div>
<script>
var PLAN={$plans_json nofilter};
var COMM={$rs.commission};
{literal}
function zoneName(){
  var z=document.getElementById('woZone');
  if(!z) return '';
  if(z.tagName==='SELECT'){
    var sel=z.options[z.selectedIndex];
    return sel?((sel.getAttribute('data-name')||sel.text)||'').replace(/^\s+|\s+$/g,''):'';
  }
  return (z.getAttribute('data-name')||'').replace(/^\s+|\s+$/g,'');
}
function zoneId(){
  var z=document.getElementById('woZone');
  return z?String(z.value||''):'';
}
function fill(){
  var p=document.getElementById('woPlan');
  var h=document.getElementById('woPlanHint');
  var w=document.getElementById('woPassWrap');
  if(!p) return;
  var id=zoneId();
  var name=zoneName();
  var opts=PLAN.filter(function(x){
    if(x.router_id && id) return String(x.router_id)===id;
    return !name || String(x.routers||'').replace(/^\s+|\s+$/g,'')===name;
  });
  p.innerHTML='';
  opts.forEach(function(x){
    var o=document.createElement('option');
    o.value=x.id; o.textContent=x.name+' — '+x.price+' F'; o.setAttribute('data-type',x.type); o.setAttribute('data-price',x.price);
    p.appendChild(o);
  });
  function hint(){
    var o=p.options[p.selectedIndex];
    if(!o){ if(h) h.textContent=''; if(w) w.style.display='none'; return; }
    var price=parseInt(o.getAttribute('data-price')||'0',10);
    var profit=Math.round(price*COMM/100);
    var cost=price-profit;
    if(h) h.textContent='Débit caisse '+cost+' F · votre gain '+profit+' F';
    if(w) w.style.display=(o.getAttribute('data-type')==='PPPOE')?'block':'none';
  }
  p.onchange=hint; hint();
}
var zel=document.getElementById('woZone');
if(zel) zel.addEventListener('change', fill);
fill();
{/literal}
</script>
{/if}

{if $rs_page == 'recharge'}
<div class="wo-head"><div><h1>Recharger la caisse</h1><p>Paiement MTN MoMo ou Orange Money. Minimum 500 FCFA.</p></div></div>
<div class="wo-card" style="max-width:480px">
  {if !$rs_perm.recharge}
    <p>La recharge n’est pas autorisée sur votre compte.</p>
  {else}
  <form method="post" id="woRsPay">
    <p><label class="wo-label">Montant</label><input class="wo-input" type="number" name="amount" min="500" required></p>
    <p><label class="wo-label">Numéro à débiter</label><input class="wo-input" name="phone" id="woRsPhone" value="{$rs.phone|escape}" required></p>
    <button class="wo-btn wo-btn-primary" type="submit">Payer et créditer</button>
  </form>
  {/if}
</div>
{/if}

{if $rs_page == 'history'}
<div class="wo-head"><div><h1>Historique</h1><p>Ventes, recharges et commissions.</p></div></div>
<div class="wo-card">
  <b>Ventes</b>
  {if $sales|@count == 0 && $filter_q == ''}<div class="wo-empty">Aucune vente</div>
  {else}
  <form method="get" action="{$rs_history}" class="wo-filters" data-wo-filter>
    {include file="otantik_get_keep.tpl"}
    {if $filter_bq}<input type="hidden" name="bq" value="{$filter_bq|escape}">{/if}
    <label class="wo-find"><i class="fa fa-search"></i><input class="wo-find-input" data-wo-q name="q" value="{$filter_q|escape}" type="search" placeholder="Rechercher une vente (zone, forfait, code…)" autocomplete="off"><button type="button" class="wo-find-x" hidden aria-label="Effacer">&times;</button></label>
    <span class="wo-filters-meta" data-wo-count></span>
    {include file="otantik_export.tpl" export_csv=$export_sales_csv export_pdf=$export_sales_pdf}
  </form>
  {if $sales|@count == 0}<div class="wo-empty"><i class="fa fa-search"></i>Aucune vente ne correspond</div>
  {else}
  <table class="wo-table"><thead><tr><th>Date</th><th>Zone</th><th>Forfait</th><th>Code</th><th>Prix</th></tr></thead><tbody>
  {foreach $sales as $s}<tr data-wo-row data-wo-hay="{$s.routers|escape} {$s.plan_name|escape} {$s.payment_channel|escape}"><td>{$s.paid_date|default:$s.created_date}</td><td>{$s.routers|escape}</td><td>{$s.plan_name|escape}</td><td>{$s.payment_channel|escape}</td><td>{$s.price} F</td></tr>{/foreach}
  </tbody></table>
  <div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucune vente ne correspond</div>
  {include file="otantik_pager.tpl"}
  {/if}
  {/if}
</div>
<div class="wo-card" style="margin-top:16px">
  <b>Caisse et recharges</b>
  {if $books|@count == 0 && $filter_bq == ''}<div class="wo-empty">Aucun mouvement</div>
  {else}
  <form method="get" action="{$rs_history}" class="wo-filters" data-wo-filter>
    {include file="otantik_get_keep.tpl" pager=$pager_books}
    {if $filter_q}<input type="hidden" name="q" value="{$filter_q|escape}">{/if}
    <label class="wo-find"><i class="fa fa-search"></i><input class="wo-find-input" data-wo-q name="bq" value="{$filter_bq|escape}" type="search" placeholder="Filtrer caisse et recharges…" autocomplete="off"><button type="button" class="wo-find-x" hidden aria-label="Effacer">&times;</button></label>
    <span class="wo-filters-meta" data-wo-count></span>
    {include file="otantik_export.tpl" export_csv=$export_books_csv export_pdf=$export_books_pdf}
  </form>
  {if $books|@count == 0}<div class="wo-empty"><i class="fa fa-search"></i>Aucun mouvement ne correspond</div>
  {else}
  <table class="wo-table"><thead><tr><th>Date</th><th>Type</th><th>Caisse</th><th>Commission</th><th>Solde</th><th>Note</th></tr></thead><tbody>
  {foreach $books as $b}<tr data-wo-row data-wo-hay="{$b.kind|escape} {$b.note|escape}"><td>{$b.created_at}</td><td>{$b.kind}</td><td>{$b.amount} F</td><td>{$b.commission} F</td><td>{$b.wallet_after} F</td><td>{$b.note|escape}</td></tr>{/foreach}
  </tbody></table>
  <div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucun mouvement ne correspond</div>
  {include file="otantik_pager.tpl" pager=$pager_books}
  {/if}
  {/if}
</div>
{/if}

{if $rs_page == 'profile'}
<div class="wo-head"><div><h1>Mon profil</h1><p>Photo, coordonnées et mot de passe.</p></div></div>
<div class="wo-profile">
  <div class="wo-card" style="text-align:center">
    <form method="post" enctype="multipart/form-data">
      <label class="wo-ava lg wo-ava-edit" title="Changer la photo">
        {if $rs_avatar}<img src="{$rs_avatar}" alt="">{else}<span style="display:grid;place-items:center;width:108px;height:108px;border-radius:50%;background:var(--wo-blue-soft)"><i class="fa fa-user" style="font-size:36px;color:var(--wo-blue)"></i></span>{/if}
        <span class="wo-ava-cam"><i class="fa fa-camera"></i></span>
        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" onchange="this.form.submit()">
      </label>
    </form>
    <p class="wo-muted" style="margin:0 0 10px">Cliquez sur la photo pour la changer</p>
    <h3 style="margin:0">{$rs.name|escape}</h3>
    <p class="wo-phone-line">{$phone_fmt|default:$rs.phone|escape}</p>
    <p class="wo-muted">{if $rs.email}{$rs.email|escape}{else}Email non renseigné{/if}</p>
    <p style="margin-top:14px;font-size:12px;color:var(--wo-muted)">Commission {$rs.commission|string_format:"%.0f"} % · caisse {$rs.wallet|string_format:"%.0f"} F</p>
  </div>
  <div class="wo-card">
    <h3>Informations</h3>
    {if $email_verify}
    <p>Un code a été envoyé à <b>{$email_pending_hint}</b> pour confirmer l’adresse.</p>
    <form method="post">
      <p><label class="wo-label">Code à 6 chiffres</label>
        <input class="wo-input" name="email_code" inputmode="numeric" maxlength="8" required autofocus placeholder="000000"></p>
      <button class="wo-btn wo-btn-primary" type="submit">Confirmer l’email</button>
    </form>
    {else}
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="save_profile" value="1">
      <p><label class="wo-label">Photo de profil</label>
        <input class="wo-input" type="file" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif"></p>
      <p><label class="wo-label">Nom</label><input class="wo-input" name="name" value="{$rs.name|escape}" required></p>
      <p><label class="wo-label">Téléphone (connexion et Mobile Money)</label>
        <input class="wo-input" name="phone" value="{$rs.phone|escape}" required inputmode="tel" placeholder="6XX XXX XXX"></p>
      <p><label class="wo-label">Email</label>
        <input class="wo-input" type="email" name="email" value="{$rs.email|escape}" placeholder="vous@gmail.com" title="Exemple : vous@gmail.com" oninvalid="this.setCustomValidity('Indiquez un email du type vous@gmail.com')" oninput="this.setCustomValidity('')"></p>
      <button class="wo-btn wo-btn-primary" type="submit">Enregistrer</button>
    </form>
    {/if}
    <h3 style="margin-top:28px">Mot de passe</h3>
    <form method="post">
      <input type="hidden" name="save_password" value="1">
      <p><label class="wo-label">Mot de passe actuel</label><input class="wo-input" type="password" name="current_password" required autocomplete="current-password"></p>
      <p><label class="wo-label">Nouveau mot de passe</label><input class="wo-input" type="password" name="new_password" minlength="6" required autocomplete="new-password"></p>
      <p><label class="wo-label">Confirmation</label><input class="wo-input" type="password" name="new_password2" minlength="6" required autocomplete="new-password"></p>
      <button class="wo-btn wo-btn-primary" type="submit">Changer le mot de passe</button>
    </form>
  </div>
</div>
{/if}

  </div>
</div>
</div>
{if $rs_page == 'recharge' && $rs_perm.recharge}
<div class="wo-pay-scrim" id="woPayWait" hidden>
  <div class="wo-pay-wait">
    <div class="spin"></div>
    <h2>Validez sur votre téléphone</h2>
    <p>Une demande Mobile Money a été envoyée. Entrez votre code secret.</p>
    <div class="wo-ussd">
      <div><b>MTN MoMo</b><code>*126#</code></div>
      <div><b>Orange Money</b><code>#150*50#</code></div>
    </div>
    <p>Pas de pop-up ? Composez le code USSD, puis confirmez.</p>
  </div>
</div>
{/if}
<script src="{$app_url}/ui/ui_custom/otantik-find.js?v=20260825a"></script>
<script>
function woTheme(){ var t=document.documentElement.getAttribute('data-theme')==='dark'?'light':'dark'; document.documentElement.setAttribute('data-theme',t); localStorage.setItem('wo-theme',t); }
(function(){
  var f=document.getElementById('woRsPay');
  if(!f) return;
  f.addEventListener('submit', function(ev){
    if(f.getAttribute('data-off')){ ev.preventDefault(); return; }
    ev.preventDefault();
    f.setAttribute('data-off','1');
    try{ if(document.activeElement && document.activeElement.blur) document.activeElement.blur(); }catch(e){}
    var s=document.getElementById('woPayWait');
    if(s){ s.hidden=false; s.removeAttribute('hidden'); s.classList.add('is-on'); try{ void s.offsetWidth; }catch(e){} }
    var go=function(){ HTMLFormElement.prototype.submit.call(f); };
    if(window.requestAnimationFrame) requestAnimationFrame(function(){ requestAnimationFrame(function(){ setTimeout(go,80); }); });
    else setTimeout(go,120);
  });
})();
</script>
</body>
</html>
