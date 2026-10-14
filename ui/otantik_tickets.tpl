{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Mes tickets</h1>
    <p>Émettez un code hotspot ou rechargez une ligne PPPoE en quelques secondes.</p>
  </div>
  <div class="wo-ticket-head-actions">
    <div class="wo-seg">
      <a class="on" href="{Text::url('plugin/wifi_otantik_tickets')}">Tickets</a>
      <a href="{Text::url('plugin/wifi_otantik_sales')}">Ventes</a>
      <a href="{Text::url('plugin/wifi_otantik_receipts')}">Reçus</a>
    </div>
    {include file="otantik_export.tpl" export_csv=$export_csv export_pdf=$export_pdf}
  </div>
</div>

{if $issued}
<div class="wo-issued wo-issued-hero">
  <div class="wo-issued-badge">{if $issued.kind == 'PPPOE'}PPPoE{else}Hotspot{/if}</div>
  <div>
    <h2>{if $issued.kind == 'PPPOE'}Ligne prête{else}Ticket prêt{/if}</h2>
    <p>{$issued.zone|escape} · {$issued.plan|escape} · {$issued.price} FCFA
      {if $issued.paid}<span class="wo-chip ok">Dans le CA</span>{else}<span class="wo-chip wait">Hors CA</span>{/if}
    </p>
  </div>
  <div class="wo-issued-creds">
    <div>
      <span>{if $issued.kind == 'PPPOE'}Identifiant{else}Code{/if}</span>
      <b>{$issued.user|escape}</b>
    </div>
    {if $issued.kind == 'PPPOE'}
    <div>
      <span>Mot de passe</span>
      <b>{$issued.pass|escape}</b>
    </div>
    {/if}
  </div>
  <div class="wo-issued-actions">
  {if $issued.kind == 'PPPOE'}
  <button type="button" class="wo-btn wo-btn-primary" data-copy="{$issued.user|escape}:{$issued.pass|escape}" onclick="woCopy(this.getAttribute('data-copy'))">Copier les identifiants</button>
  {else}
  <button type="button" class="wo-btn wo-btn-primary" data-copy="{$issued.user|escape}" onclick="woCopy(this.getAttribute('data-copy'))">Copier le code</button>
  {/if}
  {if $issued.paid && $issued.receipt_id}
  <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_receipts/view/')}{$issued.receipt_id}"><i class="fa fa-file-text-o"></i> Voir le reçu</a>
  {/if}
  </div>
  {if $issued.push_error}<p class="wo-issued-warn">MikroTik : {$issued.push_error|escape}. Les identifiants restent valables.</p>{/if}
</div>
{/if}

{if $routers|@count == 0}
<div class="wo-card wo-ticket-empty">
  <div class="wo-ticket-empty-ico"><i class="fa fa-wifi"></i></div>
  <h2>Pas encore de zone</h2>
  <p>Créez une Wifi Zone et un tarif, puis vous pourrez émettre des tickets ici.</p>
  <a class="wo-btn wo-btn-primary" href="{Text::url('plugin/wifi_otantik_routers/new')}"><i class="fa fa-plus"></i> Nouvelle zone</a>
</div>
{else}
<form method="post" action="{Text::url('plugin/wifi_otantik_tickets')}" id="woIssueForm" class="wo-ticket-studio">
  <input type="hidden" name="create_ticket" value="1">
  <section class="wo-ticket-compose">
    <header class="wo-ticket-compose-h">
      <h2>Nouveau</h2>
      <p>Choisissez le type, la zone et le forfait.</p>
    </header>
    <div class="wo-kind-picks" id="woKindPicks">
      <label class="wo-kind-pick">
        <input type="radio" name="kind" value="Hotspot" checked>
        <i class="fa fa-wifi"></i>
        <span>Hotspot</span>
        <small>Code Wi‑Fi</small>
      </label>
      <label class="wo-kind-pick">
        <input type="radio" name="kind" value="PPPOE">
        <i class="fa fa-plug"></i>
        <span>PPPoE</span>
        <small>Ligne / fibre</small>
      </label>
    </div>
    <div class="wo-ticket-fields">
      <p>
        <label class="wo-label">Wifi Zone</label>
        <select class="wo-select" name="router_id" id="woZone" required>
          {foreach $routers as $z}
          <option value="{$z.id}" data-name="{$z.name|escape}">{$z.name|escape}</option>
          {/foreach}
        </select>
      </p>
      <p>
        <label class="wo-label">Forfait</label>
        <select class="wo-select" name="plan_id" id="woPlan" required></select>
        <small class="wo-muted" id="woPlanHint"></small>
      </p>
      <p>
        <label class="wo-label">Téléphone</label>
        <input class="wo-input" name="phone" placeholder="6XX XXX XXX" inputmode="tel" autocomplete="tel">
      </p>
      <p>
        <label class="wo-label" id="woUserLbl">Code (optionnel)</label>
        <input class="wo-input" name="username" id="woUser" placeholder="Généré si vide" autocomplete="off">
      </p>
      <p id="woPassWrap" hidden>
        <label class="wo-label">Mot de passe (optionnel)</label>
        <input class="wo-input" name="password" placeholder="Généré si vide" autocomplete="new-password">
      </p>
      <p>
        <label class="wo-label">Date de recharge (optionnel)</label>
        <input class="wo-input" type="date" name="recharged_on">
      </p>
      <p>
        <label class="wo-label">Heure (optionnel)</label>
        <input class="wo-input" type="time" name="recharged_time">
      </p>
    </div>
    <div class="wo-ticket-foot">
      <label class="wo-paid-toggle">
        <input type="hidden" name="paid" value="0">
        <input type="checkbox" name="paid" value="1" checked>
        <span>Déjà payé <small>ajouté au chiffre d’affaires</small></span>
      </label>
      <button class="wo-btn wo-btn-primary wo-ticket-submit" type="submit" id="woIssueBtn"><i class="fa fa-plus"></i> Créer</button>
    </div>
  </section>
  <aside id="woPppoeGeo" class="wo-ticket-geo" hidden>
    <div class="wo-ticket-geo-bar">
      <input class="wo-input" id="woSearch" name="location_address" placeholder="Adresse du client…">
      <button type="button" class="wo-btn wo-btn-ghost" id="woGeoBtn" title="Ma position"><i class="fa fa-location-arrow"></i></button>
    </div>
    <p class="wo-muted wo-ticket-geo-hint" id="woGeoHint">Position détectée automatiquement</p>
    <div class="wo-map wo-ticket-map" id="woMap"></div>
    <input type="hidden" id="woLat" name="latitude">
    <input type="hidden" id="woLng" name="longitude">
  </aside>
</form>
{/if}

<div class="wo-card wo-ticket-list">
  <div class="wo-ticket-list-h">
    <h2>Historique</h2>
  </div>
{if $rows|@count == 0 && $filter_q == '' && $filter_st == '' && $filter_ty == ''}
  <div class="wo-empty"><i class="fa fa-ticket"></i>Aucun ticket pour le moment</div>
{else}
<form method="get" class="wo-filters" data-wo-filter>
  {include file="otantik_get_keep.tpl"}
  {include file="otantik_find.tpl" placeholder="Rechercher un ticket, une zone…" find_name="q" find_value=$filter_q}
  <select class="wo-select" name="st" data-wo-st onchange="this.form.submit()">
    <option value="">Tous les statuts</option>
    <option value="on"{if $filter_st == 'on'} selected{/if}>Actif</option>
    <option value="off"{if $filter_st == 'off'} selected{/if}>Expiré</option>
  </select>
  <select class="wo-select" name="ty" data-wo-ty onchange="this.form.submit()">
    <option value="">Tous les types</option>
    <option value="Hotspot"{if $filter_ty == 'Hotspot'} selected{/if}>Hotspot</option>
    <option value="PPPOE"{if $filter_ty == 'PPPOE'} selected{/if}>PPPoE</option>
  </select>
  <span class="wo-filters-meta" data-wo-count></span>
</form>
{if $rows|@count == 0}
  <div class="wo-empty"><i class="fa fa-search"></i>Aucun résultat</div>
{else}
<div class="wo-table-wrap">
<table class="wo-table wo-table-tickets">
  <thead><tr><th>Identifiant</th><th>Zone</th><th>Expiration</th><th>Statut</th><th></th></tr></thead>
  <tbody>
  {foreach $rows as $r}
    {assign var=rid value=$r.id}
    {assign var=ring value=$rings[$rid]}
    {assign var=kind value='Hotspot'}
    {if $r.type == 'PPPOE'}{assign var=kind value='PPPOE'}{/if}
    {assign var=st value='off'}
    {if !$ring.expired && $r.status == 'on'}{assign var=st value='on'}{/if}
    <tr data-wo-row data-wo-status="{$st}" data-wo-type="{$kind}" data-wo-hay="{$r.username|escape} {$r.routers|escape} {$r.namebp|escape} {$kind}">
      <td data-label="Identifiant">
        <div class="wo-td-user">
          <span class="wo-td-ava {if $kind == 'PPPOE'}is-pppoe{/if}">{$r.username|substr:0:1|upper}</span>
          <span>
            <b>{$r.username|escape}</b>
            <small>{if $kind == 'PPPOE'}PPPoE{else}Hotspot{/if} · {$r.recharged_on}{if isset($issuers[$r.admin_id])} · par {$issuers[$r.admin_id]|escape}{/if}</small>
          </span>
        </div>
      </td>
      <td data-label="Zone">
        <b>{$r.routers|escape}</b>
        <small class="wo-td-sub">{$r.namebp|escape}</small>
      </td>
      <td data-label="Expiration">{include file="otantik_expiry_ring.tpl" ring=$ring}</td>
      <td data-label="Statut">{if $st == 'on'}<span class="wo-chip ok">Actif</span>{else}<span class="wo-chip no">Expiré</span>{/if}</td>
      <td class="wo-td-act" data-label="">{if $kind == 'PPPOE' && isset($recharge_ids[$r.username])}<a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_customers/recharge/')}{$recharge_ids[$r.username]}">Recharger</a>{/if}</td>
    </tr>
  {/foreach}
  </tbody>
</table>
</div>
<div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucun résultat</div>
{include file="otantik_pager.tpl"}
{/if}
{/if}
</div>
{if $routers|@count > 0}
<script>
window.WO_PLANS = {$plans_json nofilter};
{literal}
function woKindVal(){
  var r=document.querySelector('#woIssueForm input[name=kind]:checked');
  return r ? r.value : 'Hotspot';
}
function woFillPlans(){
  var kind = woKindVal();
  var studio=document.getElementById('woIssueForm');
  var zoneEl = document.getElementById('woZone');
  var zoneId = zoneEl ? String(zoneEl.value || '') : '';
  var zoneName = '';
  if (zoneEl && zoneEl.selectedIndex >= 0) {
    var zo = zoneEl.options[zoneEl.selectedIndex];
    zoneName = ((zo && (zo.getAttribute('data-name') || zo.text)) || '').replace(/^\s+|\s+$/g,'');
  }
  var sel = document.getElementById('woPlan');
  var hint = document.getElementById('woPlanHint');
  var geo = document.getElementById('woPppoeGeo');
  var passWrap = document.getElementById('woPassWrap');
  var userLbl = document.getElementById('woUserLbl');
  if (geo) geo.hidden = kind !== 'PPPOE';
  if (studio) studio.classList.toggle('has-geo', kind === 'PPPOE');
  if (passWrap) passWrap.hidden = kind !== 'PPPOE';
  if (userLbl) userLbl.textContent = kind === 'PPPOE' ? 'Identifiant (optionnel)' : 'Code (optionnel)';
  if (kind === 'PPPOE' && window.woMapInit) setTimeout(woMapInit, 80);
  if (!sel) return;
  var plans = (window.WO_PLANS||[]).filter(function(p){
    if (p.type !== kind) return false;
    if (p.router_id && zoneId) return String(p.router_id) === zoneId;
    return String(p.routers || '').replace(/^\s+|\s+$/g,'') === zoneName;
  });
  sel.innerHTML = '';
  if (!plans.length) {
    var o = document.createElement('option');
    o.value = '';
    o.textContent = 'Aucun forfait';
    sel.appendChild(o);
    if (hint) hint.textContent = 'Créez un tarif ' + (kind === 'PPPOE' ? 'PPPoE' : 'hotspot') + ' pour cette zone.';
    return;
  }
  plans.forEach(function(p){
    var o=document.createElement('option');
    o.value=p.id;
    o.textContent=p.name+' · '+p.price+' F';
    sel.appendChild(o);
  });
  if (hint) hint.textContent='';
}
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('#woIssueForm input[name=kind]').forEach(function(el){
    el.addEventListener('change', woFillPlans);
  });
  var z=document.getElementById('woZone');
  if (z) z.addEventListener('change', woFillPlans);
  woFillPlans();
});
{/literal}
</script>
{/if}
{include file="otantik_shell_foot.tpl"}
