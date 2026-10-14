{include file="otantik_shell_head.tpl"}
<div class="wo-bento">
  <div class="wo-hero-wrap wo-s8">
    <div class="wo-card wo-hero">
      <span class="wo-live wo-live-on-dark"><i></i> En direct</span>
      <h1>{$greet}, {$first_name}</h1>
      <div class="wo-hero-amt"><span id="woWallet">{$tenant.wallet|string_format:"%.0f"}</span> <small>FCFA</small></div>
      {if $otantik_commission > 0}<div class="wo-hero-sub">Commission {$otantik_commission}% · vous gardez {$otantik_keep}%</div>{/if}
      <div class="wo-hero-actions">
        <a class="wo-btn" href="{Text::url('plugin/wifi_otantik_wallet')}&new=1">Retirer</a>
        <a class="wo-btn" href="{Text::url('plugin/wifi_otantik_routers/new')}"><i class="fa fa-plus"></i> Nouvelle zone</a>
      </div>
    </div>
    <div class="wo-hero-lottie" id="woHeroLottie" aria-hidden="true"></div>
    <div class="wo-hero-circle a" aria-hidden="true"></div>
    <div class="wo-hero-circle b" aria-hidden="true"></div>
    <div class="wo-hero-circle c" aria-hidden="true"></div>
    <div class="wo-hero-circle d" aria-hidden="true"></div>
  </div>
  <div class="wo-card wo-clock wo-s4">
    <div class="wo-live wo-live-on-dark"><i></i> {$clock_weekday}</div>
    <div class="d" id="woDay">{$clock_day}</div>
    <div class="m">{$clock_month}</div>
    <div class="t" id="woClock"></div>
  </div>

  <div class="wo-kpi-rail" aria-label="Indicateurs du jour">
  <div class="wo-card wo-kpi wo-s3">
    <div>
      <span>Tickets aujourd’hui</span>
      <h3 id="woTodayCount">{$today_count}</h3>
      <span id="woDelta" class="wo-trend {if $delta_today >= 0}up{else}down{/if}">{if $delta_today >= 0}+{/if}{$delta_today}% vs hier</span>
    </div>
    <div class="wo-ico orange"><i class="fa fa-ticket"></i></div>
  </div>
  <div class="wo-card wo-kpi wo-s3">
    <div>
      <span>{if $ca_pro|default:false}CA réel du jour{else}CA du jour{/if}</span>
      <h3><span id="woTodayRev">{$today_rev|string_format:"%.0f"}</span> <small class="wo-unit">FCFA</small></h3>
      {if $ca_pro|default:false}
      <small class="wo-ca-split"><i class="wifi"></i> {$ca_wifi_today|string_format:"%.0f"} · <i class="other"></i> {$ca_other_today|string_format:"%.0f"}</small>
      {else}
      <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_tickets')}">Ventes</a>
      {/if}
    </div>
    <div class="wo-ico teal"><i class="fa fa-money"></i></div>
  </div>
  <div class="wo-card wo-kpi wo-s3">
    <div>
      <span>{if $ca_pro|default:false}CA réel du mois{else}CA du mois{/if}</span>
      <h3><span id="woMonthRev">{$month_rev|string_format:"%.0f"}</span> <small class="wo-unit">FCFA</small></h3>
      {if $ca_pro|default:false}
      <small class="wo-ca-split"><i class="wifi"></i> {$ca_wifi_month|string_format:"%.0f"} · <i class="other"></i> {$ca_other_month|string_format:"%.0f"}</small>
      {else}
      <small id="woZoneMeta">{$router_count} zone(s) · {$online_count} online</small>
      {/if}
    </div>
    <div class="wo-ico sky"><i class="fa fa-line-chart"></i></div>
  </div>
  <div class="wo-card wo-kpi wo-s3">
    <div>
      <span>Croissance abonnés</span>
      <h3 id="woClients">{$clients_total|default:0}</h3>
      <span id="woClientsTrend" class="wo-trend {if $clients_delta >= 0}up{else}down{/if}">{if $clients_week >= 0}+{/if}{$clients_week|default:0} cette semaine</span>
      <small id="woClientsMeta">{if $clients_month >= 0}+{/if}{$clients_month|default:0} ce mois · {$clients_today|default:0} aujourd’hui</small>
    </div>
    <div class="wo-ico green"><i class="fa fa-users"></i></div>
  </div>
  </div>

  <div class="wo-card wo-s12" id="woCa">
    <div class="wo-card-h">
      <div>
        <b>{if $ca_pro|default:false}CA réel{else}Revenu{/if}</b>
        <div class="wo-big"><span id="woRangeRev">{$range_rev|string_format:"%.0f"}</span> <small>FCFA</small>
          <span id="woDelta2" class="wo-trend {if $delta_today >= 0}up{else}down{/if}">{if $delta_today >= 0}+{/if}{$delta_today}%</span>
        </div>
      </div>
      <div class="wo-seg" id="woCaSeg">
        <a class="{if $range == 'today'}on{/if}" href="{Text::url('plugin/wifi_otantik_home')}&range=today">Jour</a>
        <a class="{if $range == 'week'}on{/if}" href="{Text::url('plugin/wifi_otantik_home')}&range=week">Semaine</a>
        <a class="{if $range == 'month'}on{/if}" href="{Text::url('plugin/wifi_otantik_home')}&range=month">Mois</a>
        <a class="{if $range == 'year'}on{/if}" href="{Text::url('plugin/wifi_otantik_home')}&range=year">Année</a>
      </div>
    </div>
    {if $ca_pro|default:false}
    <div class="wo-ca-tiles">
      <div class="wo-ca-tile wifi">
        <span>Connexion Wi‑Fi</span>
        <b>{$ca_wifi_range|string_format:"%.0f"} <small>F</small></b>
      </div>
      <div class="wo-ca-tile other">
        <span>Autres services</span>
        <b>{$ca_other_range|string_format:"%.0f"} <small>F</small></b>
      </div>
    </div>
    <div class="wo-rev-legend">
      <span><i class="wifi"></i> Connexion</span>
      <span><i class="other"></i> Autres services</span>
    </div>
    {/if}
    <div class="wo-chart-box wo-chart-box-ca">
      {if $rev_bars_html}
        {$rev_bars_html nofilter}
      {elseif $range_rev <= 0}
        <div class="wo-chart-hint">Les ventes de la période s’afficheront ici</div>
      {else}
        {$chart_svg nofilter}
      {/if}
    </div>
  </div>

  <div class="wo-card wo-s8">
    <div class="wo-card-h">
      <div>
        <b>Croissance des utilisateurs</b>
        <div class="wo-muted" id="woGrowHint">Cliquez une barre pour voir les inscrits de cette période</div>
      </div>
      <div class="wo-seg wo-seg-sm" id="woGrowSeg">
        <button type="button" class="on" data-grow="week">Semaine</button>
        <button type="button" data-grow="month">Mois</button>
      </div>
    </div>
    <div data-grow-panel="week">{$clients_week_html nofilter}</div>
    <div data-grow-panel="month" hidden>{$clients_month_html nofilter}</div>
  </div>
  <div class="wo-card wo-s4 wo-donut-card">
    <b>Mode de paiement</b>
    <div class="wo-donut-wrap">{$donut_svg nofilter}</div>
    <div class="wo-ops">
      <div><i class="mtn"></i> MTN · <b id="woMtn">{$mtn_rev|string_format:"%.0f"}</b> FCFA</div>
      <div><i class="om"></i> Orange · <b id="woOrange">{$orange_rev|string_format:"%.0f"}</b> FCFA</div>
      <div><i class="cash"></i> Espèces · <b id="woCash">{$other_rev|string_format:"%.0f"}</b> FCFA</div>
    </div>
  </div>

  <div class="wo-card wo-dark wo-s8">
    <div class="wo-card-h">
      <b>Activité live</b>
      <span class="wo-live wo-live-on-dark"><i></i> <span id="woLiveStamp">mise à jour auto</span></span>
    </div>
    <div id="woFeed">
    {if $recent_ui|@count == 0}
      <div class="wo-skel-feed">
        <div class="wo-skel"></div>
        <div class="wo-skel"></div>
        <div class="wo-skel short"></div>
        <div class="wo-empty wo-empty-dark"><i class="fa fa-bolt"></i>En attente de la première vente…</div>
      </div>
    {else}
      {foreach $recent_ui as $r}
      <div class="wo-feed">
        <div class="wo-ava wo-ava-op {if $r.payment_method == 'MTN'}mtn{elseif $r.payment_method == 'ORANGE'}om{else}cash{/if}">{if $r.payment_method == 'MTN'}M{elseif $r.payment_method == 'ORANGE'}O{else}E{/if}</div>
        <div class="wo-feed-body">
          <b>{$r.price} FCFA</b> · {$r.plan_name}<br>
          <small>{$r.routers} · {$r.ticket}{if $r.ticket != $r.username} · {$r.username}{/if}{if $r.issuer} · par {$r.issuer}{/if}</small>
        </div>
        <div class="wo-feed-meta">
          {if $r.status == 2}<span class="wo-chip ok">Payé</span>{elseif $r.status == 1}<span class="wo-chip wait">Attente</span>{else}<span class="wo-chip no">Échec</span>{/if}
          <div><small>{$r.ago}</small></div>
        </div>
      </div>
      {/foreach}
    {/if}
    </div>
    <div style="margin-top:12px"><a href="{Text::url('plugin/wifi_otantik_tickets')}">Tout voir</a></div>
  </div>
  <div class="wo-card wo-s4">
    {if $router_count == 0}
      <b>Démarrer</b>
      <ol class="wo-steps">
        <li>Créez une Wifi Zone (ROS 6 ou 7)</li>
        <li>Collez le script WireGuard ou L2TP</li>
        <li>Vendez le premier ticket</li>
      </ol>
      <a class="wo-btn wo-btn-primary" href="{Text::url('plugin/wifi_otantik_routers/new')}"><i class="fa fa-plus"></i> Créer la zone</a>
    {else}
      <b>Tunnels MikroTik</b>
      <div class="wo-gauge-wrap">
        <svg class="wo-gauge" viewBox="0 0 160 90">
          <path d="M20 80 A60 60 0 0 1 140 80" fill="none" stroke="#e2e8f0" stroke-width="12" stroke-linecap="round"/>
          <path id="woArc" d="M20 80 A60 60 0 0 1 140 80" fill="none" stroke="#002858" stroke-width="12" stroke-linecap="round" stroke-dasharray="188" stroke-dashoffset="188"/>
        </svg>
        <div class="wo-gauge-val" id="woTunPct">{$tun_pct}%</div>
        <small id="woTunMeta">{$peer_ok} connecté(s) · {$peer_wait} en attente</small>
        <small>WG {$tun_wg|default:0} · L2TP {$tun_l2|default:0}</small>
      </div>
      {foreach $by_zone as $z}
        <div class="wo-zonebar">
          <div class="wo-zonebar-h"><span>{$z.zone}</span><b>{$z.total|string_format:"%.0f"}</b></div>
          <div class="wo-bar"><i style="width:{if $range_rev > 0}{($z.total/$range_rev*100)|string_format:"%.0f"}{else}0{/if}%"></i></div>
        </div>
      {/foreach}
    {/if}
  </div>

  <div class="wo-card wo-s8">
    <div class="wo-card-h">
      <div>
        <b>Bientôt expirés</b>
        <div class="wo-muted">Tickets encore actifs, classés par fin de forfait (24 h, sinon les plus proches)</div>
      </div>
      <a href="{Text::url('plugin/wifi_otantik_customers')}">Abonnés</a>
    </div>
    <div class="wo-exp-block" data-wo-scope>
    {if $expire_soon|@count == 0}
      <div class="wo-empty" style="padding:28px 10px"><i class="fa fa-hourglass-half"></i>Aucun ticket proche de l’expiration</div>
    {else}
    <div class="wo-filters" data-wo-filter data-wo-per="10">
      {include file="otantik_find.tpl" placeholder="Rechercher un identifiant, une zone, un forfait…"}
      <span class="wo-filters-meta" data-wo-count></span>
    </div>
    <div class="wo-table-wrap">
      <table class="wo-table wo-exp-table" data-wo-skip-pager="1">
        <thead><tr><th>Identifiant</th><th>Zone</th><th>Forfait</th><th>Reste</th><th>Expire le</th></tr></thead>
        <tbody>
        {foreach $expire_soon as $u}
          <tr data-wo-row data-wo-hay="{$u.username|escape} {$u.zone|escape} {$u.plan|escape}">
            <td><b>{$u.username|escape}</b></td>
            <td>{$u.zone|escape}</td>
            <td>{$u.plan|escape}</td>
            <td><span class="wo-chip {if $u.tone == 'ok'}ok{else}wait{/if}">{$u.label|escape}</span></td>
            <td><small>{$u.end_label|escape}</small></td>
          </tr>
        {/foreach}
        </tbody>
      </table>
    </div>
    <div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucun ticket ne correspond</div>
    {/if}
    </div>
    {if $expire_recent|@count > 0}
    <div class="wo-exp-block wo-exp-recent" data-wo-scope>
    <div class="wo-exp-sub">
      <b>Expirés récemment</b>
      <span class="wo-muted">48 dernières heures</span>
    </div>
    <div class="wo-filters" data-wo-filter data-wo-per="10">
      {include file="otantik_find.tpl" placeholder="Rechercher un identifiant, une zone…"}
      <span class="wo-filters-meta" data-wo-count></span>
    </div>
    <div class="wo-table-wrap">
      <table class="wo-table wo-exp-table" data-wo-skip-pager="1">
        <thead><tr><th>Identifiant</th><th>Zone</th><th>Forfait</th><th></th><th>Expiré le</th></tr></thead>
        <tbody>
        {foreach $expire_recent as $u}
          <tr data-wo-row data-wo-hay="{$u.username|escape} {$u.zone|escape} {$u.plan|escape}">
            <td><b>{$u.username|escape}</b></td>
            <td>{$u.zone|escape}</td>
            <td>{$u.plan|escape}</td>
            <td><span class="wo-chip no">Expiré</span></td>
            <td><small>{$u.end_label|escape}</small></td>
          </tr>
        {/foreach}
        </tbody>
      </table>
    </div>
    </div>
    {/if}
  </div>
  <div class="wo-card wo-s4">
    <div class="wo-card-h">
      <b>Écritures MikroTik</b>
      <a href="{Text::url('plugin/wifi_otantik_mk_logs')}">Tout voir</a>
    </div>
    {if $mk_logs_ui|@count == 0}
      <div class="wo-empty" style="padding:28px 10px"><i class="fa fa-exchange"></i>Aucune écriture RouterOS pour le moment</div>
    {else}
      <div class="wo-filters" data-wo-filter>
        {include file="otantik_find.tpl" placeholder="Filtrer les écritures…"}
        <span class="wo-filters-meta" data-wo-count></span>
      </div>
      {foreach $mk_logs_ui as $l}
      <div class="wo-mklog" data-wo-row data-wo-hay="{$l.action_label|escape} {$l.target|escape} {$l.router_name|escape} {$l.message|escape}">
        <div class="wo-mklog-h">
          <span class="wo-chip {if $l.ok}ok{else}no{/if}">{$l.action_label|escape}</span>
          <small>{$l.ago|escape}</small>
        </div>
        <b>{if $l.target}{$l.target|escape}{else}—{/if}</b>
        <small>{$l.router_name|escape}{if $l.message} · {$l.message|escape}{/if}</small>
      </div>
      {/foreach}
      <div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucune écriture ne correspond</div>
    {/if}
  </div>
</div>
{literal}
<script src="https://cdn.jsdelivr.net/npm/lottie-web@5.12.2/build/player/lottie.min.js"></script>
<script>
function woFmt(n){ return Math.round(Number(n)||0).toLocaleString('fr-FR'); }
function woEsc(s){ return String(s||'').replace(/[&<>"']/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]; }); }
function woTick(){
  var el=document.getElementById('woClock');
  if(el) el.textContent=new Date().toLocaleTimeString('fr-FR');
}
function woChip(st){
  if(st==2) return '<span class="wo-chip ok">Payé</span>';
  if(st==1) return '<span class="wo-chip wait">Attente</span>';
  return '<span class="wo-chip no">Échec</span>';
}
function woFeedHtml(rows){
  if(!rows || !rows.length) return '<div class="wo-skel-feed"><div class="wo-skel"></div><div class="wo-skel"></div><div class="wo-skel short"></div><div class="wo-empty wo-empty-dark"><i class="fa fa-bolt"></i>En attente de la première vente…</div></div>';
  return rows.map(function(r){
    var method = String(r.payment_method||'').toUpperCase();
    var op = method.indexOf('MTN')>=0 ? 'mtn' : (method.indexOf('ORANGE')>=0 ? 'om' : 'cash');
    var letter = op==='mtn' ? 'M' : (op==='om' ? 'O' : 'E');
    return '<div class="wo-feed"><div class="wo-ava wo-ava-op '+op+'">'+letter+'</div><div class="wo-feed-body"><b>'+woFmt(r.price)+' FCFA</b> · '+woEsc(r.plan_name)+'<br><small>'+woEsc(r.routers)+' · '+woEsc(r.ticket)+(r.ticket!==r.username?' · '+woEsc(r.username):'')+(r.issuer?' · par '+woEsc(r.issuer):'')+'</small></div><div class="wo-feed-meta">'+woChip(r.status)+'<div><small>'+woEsc(r.ago)+'</small></div></div></div>';
  }).join('');
}
function woSetTrend(id, d){
  var el=document.getElementById(id); if(!el) return;
  el.className='wo-trend '+(d>=0?'up':'down');
  el.textContent=(d>=0?'+':'')+d+'% vs hier';
}
function woGauge(pct){
  var arc=document.getElementById('woArc');
  if(arc) arc.style.strokeDashoffset = String(188 - (188*(Number(pct)||0)/100));
  var t=document.getElementById('woTunPct'); if(t) t.textContent=(Number(pct)||0)+'%';
}
var woLiveBusy=false;
function woLive(){
  if(woLiveBusy) return;
  woLiveBusy=true;
  var ctrl = typeof AbortController!=='undefined' ? new AbortController() : null;
  var to = ctrl ? setTimeout(function(){ ctrl.abort(); }, 8000) : null;
  fetch({/literal}'{$live_url}'{literal}, {credentials:'same-origin', signal: ctrl?ctrl.signal:undefined}).then(function(r){return r.json();}).then(function(d){
    var w=document.getElementById('woWallet'); if(w) w.textContent=woFmt(d.wallet);
    var c=document.getElementById('woTodayCount'); if(c) c.textContent=d.today_count;
    var tr=document.getElementById('woTodayRev'); if(tr) tr.textContent=woFmt(d.today_rev);
    var mr=document.getElementById('woMonthRev'); if(mr) mr.textContent=woFmt(d.month_rev);
    var rr=document.getElementById('woRangeRev'); if(rr) rr.textContent=woFmt(d.range_rev);
    var mtn=document.getElementById('woMtn'); if(mtn) mtn.textContent=woFmt(d.mtn_rev);
    var om=document.getElementById('woOrange'); if(om) om.textContent=woFmt(d.orange_rev);
    var cash=document.getElementById('woCash'); if(cash) cash.textContent=woFmt(d.cash_rev);
    var zm=document.getElementById('woZoneMeta'); if(zm) zm.textContent=d.router_count+' zone(s) · '+d.online_count+' online';
    var cl=document.getElementById('woClients'); if(cl) cl.textContent=woFmt(d.clients_total);
    var ct=document.getElementById('woClientsTrend');
    if(ct){ ct.className='wo-trend '+((d.clients_delta||0)>=0?'up':'down'); ct.textContent=((d.clients_week||0)>=0?'+':'')+(d.clients_week||0)+' cette semaine'; }
    var cm=document.getElementById('woClientsMeta');
    if(cm){ cm.textContent='+'+(d.clients_month||0)+' ce mois · '+(d.clients_today||0)+' aujourd’hui'; }
    var tm=document.getElementById('woTunMeta'); if(tm) tm.textContent=d.peer_ok+' connecté(s) · '+d.peer_wait+' en attente';
    woSetTrend('woDelta', d.delta_today);
    var d2=document.getElementById('woDelta2');
    if(d2){ d2.className='wo-trend '+(d.delta_today>=0?'up':'down'); d2.textContent=(d.delta_today>=0?'+':'')+d.delta_today+'%'; }
    woGauge(d.tun_pct);
    var f=document.getElementById('woFeed'); if(f) f.innerHTML=woFeedHtml(d.recent);
    var s=document.getElementById('woLiveStamp'); if(s) s.textContent='à '+ (d.now||'');
  }).catch(function(){}).then(function(){ woLiveBusy=false; if(to) clearTimeout(to); });
}
setInterval(woTick,1000); woTick();
document.addEventListener('DOMContentLoaded', function(){
  woGauge({/literal}{$tun_pct}{literal});
  setInterval(woLive, 12000);
  document.querySelectorAll('#woGrowSeg [data-grow]').forEach(function(b){
    b.addEventListener('click', function(ev){
      ev.preventDefault();
      document.querySelectorAll('#woGrowSeg [data-grow]').forEach(function(x){ x.classList.remove('on'); });
      b.classList.add('on');
      var m = b.getAttribute('data-grow');
      document.querySelectorAll('[data-grow-panel]').forEach(function(p){
        p.hidden = p.getAttribute('data-grow-panel') !== m;
      });
    });
  });
  var lottieEl = document.getElementById('woHeroLottie');
  if (lottieEl && window.lottie) {
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var anim = lottie.loadAnimation({
      container: lottieEl,
      renderer: 'svg',
      loop: !reduced,
      autoplay: !reduced,
      path: {/literal}'{$app_url}'{literal} + '/ui/ui_custom/lottie/marketing-analytics.json'
    });
    if (reduced) {
      anim.addEventListener('DOMLoaded', function(){ anim.goToAndStop(0, true); });
    }
  }
});
</script>
{/literal}
{if $open_modal}<script>location.href='{Text::url("plugin/wifi_otantik_routers/new")}';</script>{/if}
{include file="otantik_shell_foot.tpl"}
