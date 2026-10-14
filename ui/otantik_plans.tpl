{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Mes tarifs</h1>
    <p>Hotspot, PPPoE et essai gratuit. La zone s’applique aux trois cartes.</p>
  </div>
</div>

{if $routers|@count == 0}
<div class="wo-card"><p>Ajoutez d’abord une Wifi Zone, puis revenez créer vos forfaits.</p></div>
{else}

<div class="wo-plan-bar">
  <label>Zone
    <select class="wo-select" id="woPlanZone"{if $sel_zone_from_url} data-from-url="1"{/if}>{foreach $routers as $r}<option value="{$r.id}" data-name="{$r.name|escape}" data-trial-on="{$r.trial_on|default:0}" data-trial-mins="{$r.trial_mins|default:15}" data-trial-down="{$r.trial_down|default:1}" data-trial-up="{$r.trial_up|default:1}"{if $sel_zone == $r.id} selected{/if}>{$r.name}</option>{/foreach}</select>
  </label>
</div>

<div class="wo-plan-board" id="woPlanBoard">
  <form class="wo-plan-card is-trial wo-plan-create" method="post" action="{Text::url('plugin/wifi_otantik_plans/trial')}" onsubmit="return woPlanReady(this)" id="woTrialForm">
    <input type="hidden" name="router_id" class="js-zone">
    <input type="hidden" name="trial_on" value="0">
    <div class="wo-plan-card-h">
      <div>
        <i class="fa fa-gift wo-plan-gem"></i>
        <strong>Essai gratuit</strong>
        <small>Test Wi‑Fi, une fois par appareil</small>
      </div>
      <div class="wo-plan-price free">0<em>F</em></div>
    </div>
    <label class="wo-plan-toggle">
      <input type="checkbox" name="trial_on" value="1" id="woTrialOn">
      Activer l’essai sur cette zone
    </label>
    <ul class="wo-plan-feats">
      <li class="ok"><i class="fa fa-clock-o"></i> Durée
        <span class="wo-plan-inline">
          <input type="number" name="validity" min="1" value="15" required>
          <select name="validity_unit"><option value="Mins" selected>Min</option><option value="Hrs">Heures</option><option value="Days">Jours</option></select>
        </span>
      </li>
      <li class="ok"><i class="fa fa-arrow-down"></i> Descendant
        <span class="wo-plan-inline">
          <input type="number" name="rate_down" min="1" value="1" required> <em>Mbps</em>
        </span>
      </li>
      <li class="ok"><i class="fa fa-arrow-up"></i> Montant
        <span class="wo-plan-inline">
          <input type="number" name="rate_up" min="1" value="1" required> <em>Mbps</em>
        </span>
      </li>
    </ul>
    <p class="wo-plan-hint">Bouton « Essai » sur le portail captif. Renvoyez le portail si le bouton n’apparaît pas encore.</p>
    <button class="wo-plan-cta" type="submit">Enregistrer l’essai</button>
    <button class="wo-plan-cta wo-plan-cta-danger" type="button" id="woTrialOffBtn" style="display:none" onclick="var f=document.getElementById('woTrialForm');document.getElementById('woTrialOn').checked=false;if(window.woPlanReady(f))f.submit();">Désactiver l’essai</button>
  </form>

  <form class="wo-plan-card is-featured wo-plan-create" method="post" action="{Text::url('plugin/wifi_otantik_plans/add')}" onsubmit="return woPlanReady(this)">
    <input type="hidden" name="router_id" class="js-zone">
    <input type="hidden" name="plan_type" value="Hotspot">
    <div class="wo-plan-card-h">
      <div>
        <i class="fa fa-wifi wo-plan-gem"></i>
        <strong>Hotspot</strong>
        <small>Wi‑Fi partagé</small>
      </div>
      <div class="wo-plan-price">
        <input type="number" name="price" min="0" placeholder="0" required>
        <em>F</em>
      </div>
    </div>
    <input class="wo-plan-name" name="name_plan" placeholder="Nom du forfait" required>
    <ul class="wo-plan-feats">
      <li class="ok"><i class="fa fa-clock-o"></i> Durée
        <span class="wo-plan-inline">
          <input type="number" name="validity" min="1" value="1" required>
          <select name="validity_unit"><option value="Mins">Min</option><option value="Hrs" selected>Heures</option><option value="Days">Jours</option></select>
        </span>
      </li>
      <li class="ok"><i class="fa fa-arrow-down"></i> Descendant
        <span class="wo-plan-inline">
          <input type="number" name="rate_down" min="1" value="5" required> <em>Mbps</em>
        </span>
      </li>
      <li class="ok"><i class="fa fa-arrow-up"></i> Montant
        <span class="wo-plan-inline">
          <input type="number" name="rate_up" min="1" value="5" required> <em>Mbps</em>
        </span>
      </li>
      <li class="ok"><i class="fa fa-database"></i> Volume
        <span class="wo-plan-inline">
          <input type="number" name="data_limit" min="0" value="0" required>
          <select name="data_unit"><option value="MB">Mo</option><option value="GB">Go</option></select>
        </span>
      </li>
      <li class="ok"><i class="fa fa-users"></i> Sessions
        <span class="wo-plan-inline">
          <input type="number" name="shared_users" min="1" value="1" required>
        </span>
      </li>
      <li class="ok"><i class="fa fa-bolt"></i> Burst
        <span class="wo-plan-inline">
          <select name="burst_preset" class="wo-plan-burst">
            <option value="none">Aucun</option>
            <option value="leger">Léger 1,5× / 8s</option>
            <option value="standard">Standard 2× / 8s</option>
            <option value="fort">Fort 3× / 10s</option>
          </select>
        </span>
      </li>
    </ul>
    <p class="wo-plan-hint">Volume 0 = illimité · Prix 0 = test gratuit</p>
    <button class="wo-plan-cta" type="submit">Créer le forfait Hotspot</button>
  </form>

  <form class="wo-plan-card is-pppoe wo-plan-create" method="post" action="{Text::url('plugin/wifi_otantik_plans/add')}" onsubmit="return woPlanReady(this)">
    <input type="hidden" name="router_id" class="js-zone">
    <input type="hidden" name="plan_type" value="PPPOE">
    <input type="hidden" name="shared_users" value="1">
    <div class="wo-plan-card-h">
      <div>
        <i class="fa fa-plug wo-plan-gem"></i>
        <strong>PPPoE</strong>
        <small>Ligne / fibre</small>
      </div>
      <div class="wo-plan-price">
        <input type="number" name="price" min="100" placeholder="0" required>
        <em>F</em>
      </div>
    </div>
    <input class="wo-plan-name" name="name_plan" placeholder="Nom du forfait" required>
    <ul class="wo-plan-feats">
      <li class="ok"><i class="fa fa-clock-o"></i> Durée
        <span class="wo-plan-inline">
          <input type="number" name="validity" min="1" value="30" required>
          <select name="validity_unit"><option value="Mins">Min</option><option value="Hrs">Heures</option><option value="Days" selected>Jours</option></select>
        </span>
      </li>
      <li class="ok"><i class="fa fa-arrow-down"></i> Descendant
        <span class="wo-plan-inline">
          <input type="number" name="rate_down" min="1" value="10" required> <em>Mbps</em>
        </span>
      </li>
      <li class="ok"><i class="fa fa-arrow-up"></i> Montant
        <span class="wo-plan-inline">
          <input type="number" name="rate_up" min="1" value="10" required> <em>Mbps</em>
        </span>
      </li>
      <li class="ok"><i class="fa fa-database"></i> Volume
        <span class="wo-plan-inline">
          <input type="number" name="data_limit" min="0" value="0" required>
          <select name="data_unit"><option value="MB">Mo</option><option value="GB">Go</option></select>
        </span>
      </li>
      <li class="ok"><i class="fa fa-bolt"></i> Burst
        <span class="wo-plan-inline">
          <select name="burst_preset" class="wo-plan-burst">
            <option value="none">Aucun</option>
            <option value="leger">Léger 1,5× / 8s</option>
            <option value="standard">Standard 2× / 8s</option>
            <option value="fort">Fort 3× / 10s</option>
          </select>
        </span>
      </li>
    </ul>
    <p class="wo-plan-hint">Volume 0 = illimité</p>
    <button class="wo-plan-cta" type="submit">Créer le forfait PPPoE</button>
  </form>
</div>
{/if}

{if $trial_cards|@count > 0}
<h3 class="wo-plan-sec wo-plan-sec-trial"><span class="wo-chip wait">Essai</span> Test Wi‑Fi actif</h3>
<div class="wo-plan-live" id="woTrialLive">
  {foreach $trial_cards as $t}
  <div class="wo-plan-card wo-plan-mini is-trial" data-wo-zone="{$t.name|escape}">
    <div class="wo-plan-card-h">
      <div><strong>Essai gratuit</strong><small>{$t.name}</small></div>
      <div class="wo-plan-price static">0<em>F</em></div>
    </div>
    <ul class="wo-plan-feats">
      <li class="ok"><i class="fa fa-check"></i> {$t.label}</li>
      <li class="ok"><i class="fa fa-check"></i> {$t.down} ↓ / {$t.up} ↑ Mbps</li>
    </ul>
  </div>
  {/foreach}
</div>
{/if}

{if $hotspot_plans|@count > 0 || $pppoe_plans|@count > 0}
<div id="woPlanLiveWrap">
<div class="wo-filters" data-wo-filter data-wo-zone-select="#woPlanZone">
  {include file="otantik_find.tpl" placeholder="Rechercher un forfait, une zone, un prix…"}
  <span class="wo-filters-meta" data-wo-count></span>
</div>
{if $hotspot_plans|@count > 0}
<h3 class="wo-plan-sec"><span class="wo-chip ok">Hotspot</span> Forfaits Wi‑Fi actifs</h3>
<div class="wo-plan-live">
  {foreach $hotspot_plans as $p}
  {if $p.enabled}
  <div class="wo-plan-card wo-plan-mini" data-wo-row data-wo-zone="{$p.routers|escape}" data-wo-hay="{$p.name_plan|escape} {$p.routers|escape} {$p.price} hotspot">
    <div class="wo-plan-card-h">
      <div><strong>{$p.name_plan}</strong><small>{$p.routers}</small></div>
      <div class="wo-plan-price static">{$p.price}<em>F</em></div>
    </div>
    <ul class="wo-plan-feats">
      <li class="ok"><i class="fa fa-check"></i> {$p.validity} {if $p.validity_unit == 'Hrs'}heures{elseif $p.validity_unit == 'Mins'}min{else}jours{/if}</li>
      <li class="ok"><i class="fa fa-check"></i> {if $p.data_limit > 0}{$p.data_limit} {$p.data_unit}{else}Volume illimité{/if}</li>
      {if $p.bw_down > 0 || $p.bw_up > 0}
      <li class="ok"><i class="fa fa-check"></i> {$p.bw_down} ↓ / {$p.bw_up} ↑ Mbps</li>
      {/if}
      {if $p.burst_label}
      <li class="ok"><i class="fa fa-check"></i> Burst {$p.burst_label}</li>
      {/if}
      {if $p.shared_users > 0}
      <li class="ok"><i class="fa fa-check"></i> {$p.shared_users} session{if $p.shared_users > 1}s{/if}</li>
      {/if}
    </ul>
    <div class="wo-plan-actions">
      <a class="wo-plan-edit" href="{Text::url('plugin/wifi_otantik_plans/edit/')}{$p.id}">Modifier</a>
      <a class="wo-plan-off" href="{Text::url('plugin/wifi_otantik_plans/del/')}{$p.id}"{if !$p.has_history} onclick="return confirm('Supprimer définitivement ce tarif ?')"{/if}>{if $p.has_history}Désactiver{else}Supprimer{/if}</a>
    </div>
  </div>
  {/if}
  {/foreach}
</div>
{/if}

{if $pppoe_plans|@count > 0}
<h3 class="wo-plan-sec"><span class="wo-chip wait">PPPoE</span> Forfaits ligne / fibre</h3>
<div class="wo-plan-live">
  {foreach $pppoe_plans as $p}
  {if $p.enabled}
  <div class="wo-plan-card wo-plan-mini" data-wo-row data-wo-zone="{$p.routers|escape}" data-wo-hay="{$p.name_plan|escape} {$p.routers|escape} {$p.price} pppoe">
    <div class="wo-plan-card-h">
      <div><strong>{$p.name_plan}</strong><small>{$p.routers}</small></div>
      <div class="wo-plan-price static">{$p.price}<em>F</em></div>
    </div>
    <ul class="wo-plan-feats">
      <li class="ok"><i class="fa fa-check"></i> {$p.validity} {if $p.validity_unit == 'Hrs'}heures{elseif $p.validity_unit == 'Mins'}min{else}jours{/if}</li>
      <li class="ok"><i class="fa fa-check"></i> {if $p.data_limit > 0}{$p.data_limit} {$p.data_unit}{else}Volume illimité{/if}</li>
      {if $p.bw_down > 0 || $p.bw_up > 0}
      <li class="ok"><i class="fa fa-check"></i> {$p.bw_down} ↓ / {$p.bw_up} ↑ Mbps</li>
      {/if}
      {if $p.burst_label}
      <li class="ok"><i class="fa fa-check"></i> Burst {$p.burst_label}</li>
      {/if}
    </ul>
    <div class="wo-plan-actions">
      <a class="wo-plan-edit" href="{Text::url('plugin/wifi_otantik_plans/edit/')}{$p.id}">Modifier</a>
      <a class="wo-plan-off" href="{Text::url('plugin/wifi_otantik_plans/del/')}{$p.id}"{if !$p.has_history} onclick="return confirm('Supprimer définitivement ce tarif ?')"{/if}>{if $p.has_history}Désactiver{else}Supprimer{/if}</a>
    </div>
  </div>
  {/if}
  {/foreach}
</div>
{/if}

<div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucun forfait ne correspond</div>
</div>
{/if}

{literal}
<script>
(function(){
  var zone = document.getElementById('woPlanZone');
  function zoneName(){
    if(!zone || zone.selectedIndex < 0) return '';
    var o = zone.options[zone.selectedIndex];
    return o ? (o.getAttribute('data-name') || o.text || '').replace(/^\s+|\s+$/g,'') : '';
  }
  function syncZone(){
    var v = zone ? zone.value : '';
    document.querySelectorAll('#woPlanBoard .js-zone').forEach(function(i){ i.value = v; });
  }
  function fillTrial(){
    if(!zone) return;
    var o = zone.options[zone.selectedIndex];
    if(!o) return;
    var on = document.getElementById('woTrialOn');
    var isOn = o.getAttribute('data-trial-on') === '1';
    if(on) on.checked = isOn;
    var offBtn = document.getElementById('woTrialOffBtn');
    if(offBtn) offBtn.style.display = isOn ? '' : 'none';
    var form = document.getElementById('woTrialForm');
    if(!form) return;
    var mins = parseInt(o.getAttribute('data-trial-mins') || '15', 10);
    var n = mins, unit = 'Mins';
    if(mins % 1440 === 0){ unit = 'Days'; n = mins / 1440; }
    else if(mins >= 60 && mins % 60 === 0){ unit = 'Hrs'; n = mins / 60; }
    form.validity.value = n;
    form.validity_unit.value = unit;
    form.rate_down.value = o.getAttribute('data-trial-down') || '1';
    form.rate_up.value = o.getAttribute('data-trial-up') || '1';
  }
  function hideEmptySecs(){
    document.querySelectorAll('#woPlanLiveWrap .wo-plan-sec').forEach(function(h){
      var live = h.nextElementSibling;
      if(!live || !live.classList.contains('wo-plan-live')) return;
      var vis = live.querySelector('[data-wo-row]:not(.is-off)');
      h.style.display = vis ? '' : 'none';
      live.style.display = vis ? '' : 'none';
    });
  }
  function filterTrials(){
    var name = zoneName();
    var vis = 0;
    document.querySelectorAll('#woTrialLive [data-wo-zone]').forEach(function(c){
      var on = !name || (c.getAttribute('data-wo-zone') || '') === name;
      c.style.display = on ? '' : 'none';
      if(on) vis++;
    });
    var sec = document.querySelector('.wo-plan-sec-trial');
    var live = document.getElementById('woTrialLive');
    if(sec) sec.style.display = vis ? '' : 'none';
    if(live) live.style.display = vis ? '' : 'none';
  }
  function rememberZone(){
    if(!zone) return;
    try { localStorage.setItem('woPlanZone', zone.value); } catch(e){}
    try {
      var u = new URL(window.location.href);
      u.searchParams.set('zone', zone.value);
      history.replaceState(null, '', u.toString());
    } catch(e){}
  }
  if(zone && zone.getAttribute('data-from-url') !== '1'){
    try {
      var saved = localStorage.getItem('woPlanZone');
      if(saved){
        for(var i=0;i<zone.options.length;i++){
          if(zone.options[i].value === saved){ zone.selectedIndex = i; break; }
        }
      }
    } catch(e){}
  }
  if(zone) zone.addEventListener('change', function(){ syncZone(); fillTrial(); filterTrials(); rememberZone(); });
  syncZone();
  fillTrial();
  filterTrials();
  var wrap = document.getElementById('woPlanLiveWrap');
  if(wrap) wrap.addEventListener('wo-filtered', hideEmptySecs);
  window.woPlanReady = function(f){
    var b=f.querySelector('[type=submit]');
    if(b.disabled) return false;
    b.disabled=true;
    return true;
  };
})();
</script>
{/literal}
{include file="otantik_shell_foot.tpl"}
