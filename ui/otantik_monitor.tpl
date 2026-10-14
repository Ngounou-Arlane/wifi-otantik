{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Supervision MikroTik</h1>
    <p>Les routeurs hors ligne s’affichent en rouge et ne sont plus interrogés, pour ne pas bloquer le site.</p>
  </div>
</div>
{if $routers|@count == 0}
  <div class="wo-card wo-empty"><i class="fa fa-wifi"></i>Ajoutez d’abord une Wifi Zone connectée.</div>
{else}
<div class="wo-card" style="margin-bottom:16px">
  <label class="wo-label">Zone à superviser</label>
  <select class="wo-select" id="woMonRouter" onchange="woMonPick(this.value)">
    {foreach $routers as $r}
      <option value="{$r.id}" {if $router && $router.id == $r.id}selected{/if}>{if $router_states[$r.id].offline}● Hors ligne · {else}● En ligne · {/if}{$r.name} · {$r.ssid|default:$r.slug}</option>
    {/foreach}
  </select>
</div>
{if $router}
<div class="wo-zone-new">
  <div class="wo-3d-stage{if $router_link.offline} wo-offline{/if}" id="woMonStage">
    <span class="wo-3d-ring"></span>
    <span class="wo-3d-spot"></span>
    <img class="wo-router" src="{$app_url}/ui/ui_custom/images/hero-router-gold.jpg" alt="Supervision">
    <div class="wo-callouts">
      <div class="wo-callout wo-c-cpu"><b>Processeur</b><strong id="woCpu">—</strong></div>
      <div class="wo-callout wo-c-temp"><b>Température</b><strong id="woTemp">—</strong></div>
      <div class="wo-callout wo-c-volt"><b>RAM</b><strong id="woRam">—</strong></div>
      <div class="wo-callout wo-c-hs"><b>Hotspot en ligne</b><strong id="woHs">—</strong></div>
      <div class="wo-callout wo-c-ppp"><b>PPPoE en ligne</b><strong id="woPpp">—</strong></div>
    </div>
  </div>
  <div class="wo-card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap">
      <div><span class="wo-pulse{if $router_link.offline} off{/if}" id="woLiveDot"></span><b id="woLiveLabel">{if $router_link.offline}Hors ligne{else}En attente…{/if}</b></div>
      <span class="wo-chip {if $router_link.offline}no{else}ok{/if}" id="woMonChip">{if $router_link.code == 'pending'}Non connecté{elseif $router_link.offline}Hors ligne{else}En ligne{/if}</span>
    </div>
    <div class="wo-mon-banner" id="woMonBanner"{if !$router_link.offline} hidden{/if}>{$router_link.label|default:'Routeur hors ligne. Aucune donnée n’est demandée.'}</div>
    <small id="woBoard" style="color:var(--wo-muted)"></small>
    <div class="wo-grid wo-2" style="margin-top:14px" id="woMeta"></div>
    <div class="wo-filters" id="woMonFilterBar">
      {include file="otantik_find.tpl" placeholder="Filtrer les lignes (compte, IP, journal…)" find_id="woMonQ"}
      <span class="wo-filters-meta" id="woMonCount"></span>
    </div>
    <div class="wo-mon-tabs" id="woTabs">
      <button type="button" class="on" data-tab="hotspot">Hotspot</button>
      <button type="button" data-tab="pppoe">PPPoE</button>
      <button type="button" data-tab="traffic">Trafic</button>
      <button type="button" data-tab="logs">Journaux</button>
      <button type="button" data-tab="wireless">Sans fil</button>
      <button type="button" data-tab="dhcp">DHCP</button>
      <button type="button" data-tab="queues">Files</button>
    </div>
    <div id="woTabBody"><div class="wo-empty">Les données arrivent uniquement lorsque cet onglet est visible.</div></div>
  </div>
</div>
{/if}
{/if}
<script>
window.WO_MON_ID = {if $router}{$router.id}{else}0{/if};
window.WO_MON_URL = '{Text::url("plugin/wifi_otantik_monitor/live/")}';
window.WO_MON_OFFLINE = {if $router_link.offline}true{else}false{/if};
window.WO_MON_OFF_MSG = '{if $router_link.label}{$router_link.label|escape:"javascript"}{else}Routeur hors ligne{/if}';
</script>
{literal}
<script>
(function(){
  var tab='hotspot', timer=null, ctrl=null, prevT=null, prevTs=0, page=1, perPage=20, lastData=null, offline=!!window.WO_MON_OFFLINE;
  function visible(){ return document.visibilityState==='visible'; }
  function pick(id){ location.href = '{/literal}{Text::url("plugin/wifi_otantik_monitor/")}{literal}'+id; }
  window.woMonPick = pick;
  window.woMonPage = function(n){ page = n; if(lastData) applyTab(lastData); };
  function setOffline(on, msg){
    offline = !!on;
    var stage=document.getElementById('woMonStage');
    if(stage) stage.classList.toggle('wo-offline', offline);
    var dot=document.getElementById('woLiveDot');
    if(dot) dot.classList.toggle('off', offline);
    var lab=document.getElementById('woLiveLabel');
    if(lab) lab.textContent = offline ? (msg || window.WO_MON_OFF_MSG || 'Hors ligne') : 'En attente…';
    var chip=document.getElementById('woMonChip');
    if(chip){
      chip.className = 'wo-chip '+(offline?'no':'ok');
      chip.textContent = offline ? 'Hors ligne' : 'En ligne';
    }
    var ban=document.getElementById('woMonBanner');
    if(ban){
      if(offline){ ban.hidden=false; ban.textContent = msg || window.WO_MON_OFF_MSG || 'Routeur hors ligne.'; }
      else ban.hidden=true;
    }
    if(offline){
      ['woCpu','woTemp','woRam','woHs','woPpp'].forEach(function(id){
        var el=document.getElementById(id); if(el) el.textContent='—';
      });
      var body=document.getElementById('woTabBody');
      if(body) body.innerHTML='<div class="wo-empty wo-empty-off"><i class="fa fa-plug"></i>Routeur hors ligne — aucune information n’est demandée.</div>';
    }
  }
  function monFilter(list){
    var q = (document.getElementById('woMonQ')||{}).value || '';
    q = String(q).toLowerCase().trim();
    var all = list || [];
    if(!q) return all;
    return all.filter(function(r){
      var hay = '';
      for(var k in r){ if(Object.prototype.hasOwnProperty.call(r,k)) hay += ' '+r[k]; }
      return hay.toLowerCase().indexOf(q) >= 0;
    });
  }
  var monQ = document.getElementById('woMonQ');
  if(monQ) monQ.addEventListener('input', function(){ page=1; if(lastData) applyTab(lastData); });
  document.querySelectorAll('#woTabs button').forEach(function(b){
    b.addEventListener('click', function(){
      document.querySelectorAll('#woTabs button').forEach(function(x){x.classList.remove('on')});
      b.classList.add('on'); tab=b.getAttribute('data-tab'); page=1; lastData=null;
      var body=document.getElementById('woTabBody'); if(body) body.innerHTML='<div class="wo-empty">Chargement…</div>';
      tick();
    });
  });
  function esc(s){ return String(s==null?'':s).replace(/[&<>"]/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]); }); }
  function pager(total){
    var pages = Math.max(1, Math.ceil(total / perPage));
    if(page > pages) page = pages;
    if(page < 1) page = 1;
    var count = document.getElementById('woMonCount');
    if(count) count.textContent = total ? (total+' ligne'+(total>1?'s':'')) : '';
    if(!total) return '';
    var from = (page-1)*perPage+1;
    var to = Math.min(total, page*perPage);
    var start = Math.max(1, page-2), end = Math.min(pages, page+2), nums=[];
    if(start>1){ nums.push(1); if(start>2) nums.push(0); }
    for(var i=start;i<=end;i++) nums.push(i);
    if(end<pages){ if(end<pages-1) nums.push(0); nums.push(pages); }
    var nav = '';
    nav += page>1 ? '<a href="#" onclick="woMonPage('+(page-1)+');return false">Préc.</a>' : '<span class="is-off">Préc.</span>';
    nums.forEach(function(n){
      if(!n) nav += '<span class="wo-pager-gap">…</span>';
      else if(n===page) nav += '<a class="on" href="#" onclick="woMonPage('+n+');return false" aria-current="page">'+n+'</a>';
      else nav += '<a href="#" onclick="woMonPage('+n+');return false">'+n+'</a>';
    });
    nav += page<pages ? '<a href="#" onclick="woMonPage('+(page+1)+');return false">Suiv.</a>' : '<span class="is-off">Suiv.</span>';
    return '<nav class="wo-pager" aria-label="Pagination"><span class="wo-pager-meta">'+from+'–'+to+' sur '+total+'</span><div class="wo-pager-nav">'+nav+'</div></nav>';
  }
  function pageSlice(list){
    var all = list || [];
    return all.slice((page-1)*perPage, page*perPage);
  }
  function rows(list, cols){
    var all = monFilter(list);
    if(!all.length) return '<div class="wo-empty">Aucune donnée</div>'+pager(0);
    var h='<table class="wo-table"><thead><tr>';
    cols.forEach(function(c){ h+='<th>'+c[1]+'</th>'; });
    h+='</tr></thead><tbody>';
    pageSlice(all).forEach(function(r){
      h+='<tr>'; cols.forEach(function(c){ h+='<td>'+esc(r[c[0]]||'—')+'</td>'; }); h+='</tr>';
    });
    return h+'</tbody></table>'+pager(all.length);
  }
  function capNote(shown, total){
    shown = Number(shown||0); total = Number(total||0);
    if(!total || shown >= total) return '';
    return '<div class="wo-mon-cap">Aperçu de '+shown+' sessions sur '+total+' en ligne — le reste n’est pas chargé pour rester fluide.</div>';
  }
  function fmtRate(bps){
    if(!isFinite(bps) || bps < 0) return '—';
    var mb = bps / 1048576;
    if(mb < 0.05) return (bps/1024).toFixed(1)+' Ko/s';
    return mb.toFixed(2)+' Mo/s';
  }
  function withRates(list){
    var now = Date.now();
    var dt = prevTs ? Math.max(1, (now - prevTs)/1000) : 0;
    var out = (list||[]).map(function(i){
      var rxr='—', txr='—';
      if(prevT && dt && prevT[i.name]){
        rxr = fmtRate((i.rx_b - prevT[i.name].rx_b)/dt);
        txr = fmtRate((i.tx_b - prevT[i.name].tx_b)/dt);
      }
      return Object.assign({}, i, {rx_rate: rxr, tx_rate: txr});
    });
    prevT = {};
    (list||[]).forEach(function(i){ prevT[i.name] = {rx_b: i.rx_b, tx_b: i.tx_b}; });
    prevTs = now;
    return out;
  }
  function meta(d){
    var c=d.caps||{};
    var html='';
    [['Plateforme', c.platform],['Carte', c.board],['Version', c.version],['Uptime', c.uptime],['CPU', c.cpu],['RAM utilisée', c.ram],['RAM libre', c.ram_free],['Disque utilisé', c.disk],['Disque libre', c.disk_free],['Température', d.temperature],['Tension', d.voltage]].forEach(function(x){
      html+='<div class="wo-mini" style="background:var(--wo-blue-soft)"><small>'+esc(x[0])+'</small><div class="wo-big" style="font-size:15px">'+(x[1]||'—')+'</div></div>';
    });
    document.getElementById('woMeta').innerHTML=html;
    document.getElementById('woBoard').textContent=(c.board||'')+' · '+(c.version||'');
    document.getElementById('woRam').textContent=c.ram_free||'—';
  }
  function logRows(list){
    var all = monFilter(list);
    if(!all.length) return '<div class="wo-empty">Aucun journal</div>'+pager(0);
    var h='<table class="wo-table"><thead><tr><th>Heure</th><th>Sujet</th><th>Message</th></tr></thead><tbody>';
    pageSlice(all).forEach(function(r){
      var t=(r.topics||'').toLowerCase();
      var cls = (t.indexOf('error')>=0||t.indexOf('critical')>=0||t.indexOf('warning')>=0) ? 'wo-log-err' : (t.indexOf('hotspot')>=0?'wo-log-hot':'');
      h+='<tr class="'+cls+'"><td>'+esc(r.time)+'</td><td>'+esc(r.topics)+'</td><td>'+esc(r.message)+'</td></tr>';
    });
    return h+'</tbody></table>'+pager(all.length);
  }
  function applyTab(d){
    lastData = d;
    var el=document.getElementById('woTabBody');
    var colsUser=[['user','Compte'],['address','IP'],['uptime','Durée'],['remaining','Temps restant'],['rx','Reçu'],['tx','Envoyé'],['mac','MAC']];
    if(tab==='hotspot') el.innerHTML=capNote(d.hotspot_shown, d.hotspot_count)+rows(d.hotspot, colsUser);
    else if(tab==='pppoe') el.innerHTML=capNote(d.pppoe_shown, d.pppoe_count)+rows(d.pppoe, colsUser);
    else if(tab==='traffic') el.innerHTML=rows(withRates(d.traffic), [['name','Interface'],['running','État'],['rx','Reçu'],['tx','Envoyé'],['rx_rate','Débit ↓'],['tx_rate','Débit ↑']]);
    else if(tab==='logs') el.innerHTML=logRows(d.logs);
    else if(tab==='wireless') el.innerHTML=rows(d.wireless, [['name','Wifi'],['ssid','SSID'],['disabled','Off'],['running','Run']]);
    else if(tab==='dhcp') el.innerHTML=rows(d.dhcp, [['address','IP'],['mac-address','MAC'],['status','Statut'],['host-name','Hôte']]);
    else if(tab==='queues') el.innerHTML=rows(d.queues, [['name','File'],['target','Cible'],['rate','Débit']]);
  }
  function tick(){
    if(!window.WO_MON_ID) return;
    if(!visible()) return;
    if(ctrl) ctrl.abort();
    ctrl=new AbortController();
    var to=setTimeout(function(){ if(ctrl) ctrl.abort(); }, 8000);
    fetch(window.WO_MON_URL + window.WO_MON_ID + '&tab=' + encodeURIComponent(tab), {signal: ctrl.signal, credentials:'same-origin'})
      .then(function(r){ return r.json(); })
      .then(function(d){
        if(d.offline || d.error){
          setOffline(true, d.error || window.WO_MON_OFF_MSG);
          return;
        }
        setOffline(false);
        document.getElementById('woLiveDot').classList.remove('off');
        document.getElementById('woLiveLabel').textContent=(d.cached?'Cache · ':'Live · ')+(d.at||'');
        document.getElementById('woCpu').textContent=(d.caps && d.caps.cpu_load ? d.caps.cpu_load+'%' : (d.resource && d.resource['cpu-load']!=null ? d.resource['cpu-load']+'%' : '—'));
        document.getElementById('woTemp').textContent=d.temperature||'—';
        document.getElementById('woHs').textContent=d.hotspot_count!=null?d.hotspot_count:'—';
        document.getElementById('woPpp').textContent=d.pppoe_count!=null?d.pppoe_count:'—';
        meta(d); applyTab(d);
      })
      .catch(function(e){ if(e.name==='AbortError') return; setOffline(true, 'Hors ligne'); })
      .then(function(){ clearTimeout(to); });
  }
  function loop(){ tick(); timer=setTimeout(loop, offline ? 45000 : (tab==='traffic'?6000:12000)); }
  document.addEventListener('visibilitychange', function(){
    if(!visible()){ if(ctrl) ctrl.abort(); if(timer){ clearTimeout(timer); timer=null; } }
    else if(!timer) loop();
  });
  if(window.WO_MON_ID){
    if(offline) setOffline(true, window.WO_MON_OFF_MSG);
    loop();
  }
})();
</script>
{/literal}
{include file="otantik_shell_foot.tpl"}
