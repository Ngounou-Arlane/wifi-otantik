  </div>
</div>
</div>
<div class="wo-msg-scrim" id="woMsgBox" onclick="if(event.target===this)woMsgClose()">
  <article class="wo-msg-card">
    <button type="button" class="wo-msg-x" onclick="woMsgClose()" aria-label="Fermer">×</button>
    <a class="wo-msg-logo" href="{Text::url('plugin/wifi_otantik_home')}">Wifi-Otantik</a>
    <div class="wo-msg-art" id="woMsgArt"></div>
    <h1 id="woMsgTitle">Oups !</h1>
    <p id="woMsgText"></p>
    <a class="wo-msg-cta" id="woMsgCta" href="#" onclick="woMsgClose();return false;">Fermer →</a>
  </article>
</div>
<div class="wo-modal" id="woZoneModal">
  <div class="wo-modal-card">
    <div class="wo-modal-h">
      <div>
        <h2 style="margin:0">Créer une Wifi Zone</h2>
        <p style="margin:6px 0 0;color:var(--wo-muted)">Configurez un nouveau point d’accès</p>
      </div>
      <a href="#" onclick="woCloseZone();return false;" class="wo-iconbtn">&times;</a>
    </div>
    <form method="post" action="{Text::url('plugin/wifi_otantik_routers/add')}" style="margin-top:16px">
      <p><label class="wo-label">Version RouterOS <span class="wo-req">*</span></label>
      <select class="wo-select" name="ros_version">
        <option value="7">RouterOS 7 — tunnel WireGuard (recommandé)</option>
        <option value="6">RouterOS 6 — tunnel L2TP/IPsec</option>
        <option value="auto">Je ne sais pas — la commande détectera</option>
      </select>
      <small style="color:var(--wo-muted)">WireGuard n’existe pas en RouterOS 6. Les deux méthodes aboutissent au même portail d’achat.</small></p>
      <p><label class="wo-label">Nom de la zone <span class="wo-req">*</span></label>
      <input class="wo-input" name="name" placeholder="Ex. Gare Centrale" required></p>
      <p><label class="wo-label">Description</label>
      <textarea class="wo-input" name="zone_note" rows="2" placeholder="Description de la zone"></textarea></p>
      <div class="wo-row">
        <p><label class="wo-label">IP routeur ou DNS local <span class="wo-req">*</span></label>
        <input class="wo-input" name="lan_host" placeholder="192.168.1.1 ou freewifi.net" required></p>
        <p><label class="wo-label">Nom du réseau (SSID) <span class="wo-req">*</span></label>
        <input class="wo-input" name="ssid" placeholder="Free Wifi Coffee Shop" required></p>
      </div>
      <p><label class="wo-label">Contact assistance <span class="wo-req">*</span></label>
      <input class="wo-input" name="assistance_contact" placeholder="+237 6XX XXX XXX" required></p>
      <p><label class="wo-label">Interface LAN (hotspot + PPPoE)</label>
      <input class="wo-input" name="pppoe_interface" placeholder="bridge, ether2, vlan10…">
      <small style="color:var(--wo-muted)">Laissé vide : le script prend le bridge. Hotspot et PPPoE sont toujours créés.</small></p>
      <div class="wo-map" id="woMap"></div>
      <p><input class="wo-input" id="woSearch" placeholder="Chercher un lieu"></p>
      <div class="wo-row">
        <p><label class="wo-label">Latitude</label><input class="wo-input" id="woLat" name="latitude" placeholder="3.8480"></p>
        <p><label class="wo-label">Longitude</label><input class="wo-input" id="woLng" name="longitude" placeholder="11.5021"></p>
      </div>
      <p class="wo-switch"><input type="hidden" name="zone_active" value="1"><input type="checkbox" checked disabled> Active</p>
      <div style="text-align:right;margin-top:12px">
        <button type="button" class="wo-btn wo-btn-danger" onclick="woCloseZone()">Fermer</button>
        <button class="wo-btn wo-btn-primary" type="submit">Créer</button>
      </div>
    </form>
  </div>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
window.woLeafletPin = function(kind){
  var src = window.WO_LOGO || (document.querySelector('.wo-brand img')||{}).src || '';
  if (!window.L) return undefined;
  return L.divIcon({
    className: 'wo-pin wo-logo-marker wo-logo-marker-'+(kind||'zone'),
    html: '<span class="wo-logo-pin-body"><img src="'+src+'" alt=""></span>',
    iconSize: [42, 50],
    iconAnchor: [21, 48],
    popupAnchor: [0, -42]
  });
};
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="{$app_url}/ui/ui_custom/otantik-find.js?v=20260826search1"></script>
{literal}
<script>
function woTheme(){var t=document.documentElement.getAttribute('data-theme')==='dark'?'light':'dark';document.documentElement.setAttribute('data-theme',t);localStorage.setItem('wo-theme',t);}
function woToggleNotify(){
  var p=document.getElementById('woNotifyPanel');
  if(p) p.classList.toggle('on');
}
document.addEventListener('click', function(e){
  var box=document.getElementById('woNotifyBox');
  var p=document.getElementById('woNotifyPanel');
  if(!box||!p) return;
  if(!box.contains(e.target)) p.classList.remove('on');
});
(function(){
  function reveal(){ document.documentElement.style.visibility=''; }
  try{
    if (window.__woRestoreY !== undefined) {
      window.scrollTo(0, window.__woRestoreY);
      sessionStorage.removeItem('woScrollY');
    }
  }catch(e){}
  reveal();
  setTimeout(reveal, 500);
  document.addEventListener('click', function(e){
    var a = e.target.closest && e.target.closest('.wo-seg a, .wo-erp-seg a');
    if (!a) return;
    try { sessionStorage.setItem('woScrollY', String(window.scrollY)); } catch(err) {}
  });
})();
function woMsgClose(){
  var b=document.getElementById('woMsgBox');
  if(b) b.classList.remove('on');
  if(window.__woMsgTimer){ clearTimeout(window.__woMsgTimer); window.__woMsgTimer=null; }
}
function woMsg(opts){
  opts=opts||{};
  var b=document.getElementById('woMsgBox');
  if(!b){ window.alert(opts.text||opts.title||''); return; }
  if(window.__woMsgTimer){ clearTimeout(window.__woMsgTimer); window.__woMsgTimer=null; }
  document.getElementById('woMsgTitle').textContent=opts.title|| (opts.kind==='ok'?'C’est fait':'Oups !');
  document.getElementById('woMsgText').textContent=opts.text||'';
  var art=document.getElementById('woMsgArt');
  if(art){
    art.innerHTML = opts.kind==='ok'
      ? '<svg viewBox="0 0 160 160" fill="none"><circle cx="80" cy="80" r="70" fill="#ffe6dc"/><circle cx="80" cy="80" r="48" fill="#f05018"/><path d="M58 82l16 16 30-34" stroke="#fff" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/></svg>'
      : '<svg viewBox="0 0 160 160" fill="none"><circle cx="80" cy="80" r="70" fill="#fee2e2"/><circle cx="80" cy="80" r="48" fill="#002858"/><path d="M64 64l32 32M96 64L64 96" stroke="#fff" stroke-width="8" stroke-linecap="round"/></svg>';
  }
  var cta=document.getElementById('woMsgCta');
  if(opts.href){ cta.href=opts.href; cta.textContent=(opts.cta||'Continuer')+' →'; cta.onclick=null; }
  else { cta.href='#'; cta.textContent='Fermer →'; cta.onclick=function(){ woMsgClose(); return false; }; }
  b.classList.add('on');
  // Success: auto-dismiss, then optional redirect (e.g. tableau de bord / stats).
  if(opts.kind==='ok' && opts.autoclose!==false){
    var ms=opts.timer||3500;
    window.__woMsgTimer=setTimeout(function(){
      woMsgClose();
      if(opts.redirect){
        try{
          if(String(location.href||'').indexOf('wifi_otantik_home')>=0) return;
        }catch(e){}
        location.href=opts.redirect;
      }
    }, ms);
  }
}
document.addEventListener('keydown', function(e){
  if(e.key==='Escape') woMsgClose();
});
function woCopy(url){
  if(navigator.clipboard){ navigator.clipboard.writeText(url).then(function(){ woMsg({kind:'ok', title:'C’est fait', text:'Copié.'}); }); }
  else { window.prompt('Copier', url); }
}
function woOpenZone(){ var m=document.getElementById('woZoneModal'); if(m){ m.classList.add('on'); setTimeout(woMapInit,80);} else { location.href='{/literal}{Text::url("plugin/wifi_otantik_routers/new")}{literal}'; } }
function woCloseZone(){document.getElementById('woZoneModal').classList.remove('on');}
var woMapObj=null, woMarker=null;
function woMapInit(){
  if(!window.L) return;
  var box=document.querySelector('.wo-zone-new')||document;
  var el=box.querySelector('#woMap')||document.getElementById('woMap');
  var search=box.querySelector('#woSearch')||document.getElementById('woSearch');
  var geoBtn=box.querySelector('#woGeoBtn')||document.getElementById('woGeoBtn');
  var hint=box.querySelector('#woGeoHint')||document.getElementById('woGeoHint');
  if(!el) return;
  if(woMapObj){ woMapObj.invalidateSize(); return; }
  woMapObj=L.map(el).setView([3.848,11.502], 12);
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'© OSM'}).addTo(woMapObj);
  woMapObj.on('click', function(e){ woSet(e.latlng.lat,e.latlng.lng, true); });
  var la=box.querySelector('#woLat')||document.getElementById('woLat');
  var ln=box.querySelector('#woLng')||document.getElementById('woLng');
  var hasPos=la && ln && la.value && ln.value;
  if(hasPos){
    var a=parseFloat(la.value), b=parseFloat(ln.value);
    if(!isNaN(a) && !isNaN(b)){ woSet(a,b, false); woMapObj.setView([a,b], 15); }
  } else {
    woLocate(true, hint);
  }
  if(search){
    var t=null;
    search.addEventListener('keydown', function(ev){
      if(ev.key!=='Enter') return; ev.preventDefault();
      woSearchPlace(this.value);
    });
    search.addEventListener('input', function(){
      var q=this.value;
      clearTimeout(t);
      if(String(q||'').trim().length<5) return;
      t=setTimeout(function(){ woSearchPlace(q); }, 700);
    });
  }
  if(geoBtn) geoBtn.addEventListener('click', function(ev){ ev.preventDefault(); woLocate(true, hint); });
}
function woNomHeaders(){
  return { 'Accept-Language': 'fr' };
}
function woSearchPlace(q){
  q=String(q||'').trim();
  if(q.length<3 || !woMapObj) return;
  fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&addressdetails=1&accept-language=fr&q='+encodeURIComponent(q), {headers: woNomHeaders()})
    .then(function(r){ return r.json(); })
    .then(function(d){
      if(!d || !d[0]) return;
      woSet(parseFloat(d[0].lat), parseFloat(d[0].lon), false);
      woMapObj.setView([d[0].lat, d[0].lon], 16);
      woFillAddr(d[0].display_name||'');
    }).catch(function(){});
}
function woRevGeo(lat,lng){
  fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&zoom=18&addressdetails=1&accept-language=fr&lat='+encodeURIComponent(lat)+'&lon='+encodeURIComponent(lng), {headers: woNomHeaders()})
    .then(function(r){ return r.json(); })
    .then(function(d){ if(d && d.display_name) woFillAddr(d.display_name); }).catch(function(){});
}
function woFillAddr(addr){
  var box=document.querySelector('.wo-zone-new')||document;
  var search=box.querySelector('#woSearch')||document.getElementById('woSearch');
  var hint=box.querySelector('#woGeoHint')||document.getElementById('woGeoHint');
  if(search && addr) search.value=addr;
  if(hint && addr) hint.textContent=addr;
}
function woLocate(reverse, hint){
  if(!navigator.geolocation){
    if(hint) hint.textContent='Localisation indisponible. Cliquez sur la carte ou cherchez un lieu.';
    return;
  }
  if(hint) hint.textContent='Détection de votre position…';
  navigator.geolocation.getCurrentPosition(function(pos){
    var lat=pos.coords.latitude, lng=pos.coords.longitude;
    woSet(lat, lng, !!reverse);
    if(woMapObj) woMapObj.setView([lat,lng], 16);
  }, function(){
    if(hint) hint.textContent='Position refusée. Cliquez sur la carte ou cherchez un lieu.';
  }, {enableHighAccuracy:true, timeout:10000, maximumAge:60000});
}
function woSet(lat,lng, reverse){
  var box=document.querySelector('.wo-zone-new')||document;
  var la=box.querySelector('#woLat')||document.getElementById('woLat');
  var ln=box.querySelector('#woLng')||document.getElementById('woLng');
  if(la) la.value=lat.toFixed(6);
  if(ln) ln.value=lng.toFixed(6);
  if(woMarker) woMarker.setLatLng([lat,lng]); else if(woMapObj) woMarker=L.marker([lat,lng], window.woLeafletPin ? {icon: woLeafletPin('zone')} : {}).addTo(woMapObj);
  if(reverse) woRevGeo(lat, lng);
}
</script>
{/literal}
<nav class="wo-tabbar" id="woTabbar" aria-label="Navigation principale">
  <a class="wo-tab {if $otantik_nav == 'wifi_otantik_home'}on{/if}" href="{Text::url('plugin/wifi_otantik_home')}">
    <i class="fa fa-th-large"></i><span>Accueil</span>
  </a>
  <a class="wo-tab {if $otantik_nav == 'wifi_otantik_routers' || $otantik_nav == 'wifi_otantik_zone_new' || $otantik_nav == 'wifi_otantik_zone_edit'}on{/if}" href="{Text::url('plugin/wifi_otantik_routers')}">
    <i class="fa fa-wifi"></i><span>Zones</span>
  </a>
  <a class="wo-tab wo-tab-main {if $otantik_nav == 'wifi_otantik_tickets' || $otantik_nav == 'wifi_otantik_sales' || $otantik_nav == 'wifi_otantik_receipts'}on{/if}" href="{Text::url('plugin/wifi_otantik_tickets')}">
    <span class="wo-tab-fab"><i class="fa fa-ticket"></i></span><span>Tickets</span>
  </a>
  <a class="wo-tab {if $otantik_nav == 'wifi_otantik_wallet'}on{/if}" href="{Text::url('plugin/wifi_otantik_wallet')}">
    <i class="fa fa-money"></i><span>Caisse</span>
  </a>
  <button type="button" class="wo-tab" id="woTabMore" onclick="woNavOpen()" aria-label="Plus de menus">
    <i class="fa fa-ellipsis-h"></i><span>Plus</span>
  </button>
</nav>
{literal}
<script>
function woNavOpen(){
  document.documentElement.classList.add('wo-nav-open');
  var s=document.getElementById('woNavScrim');
  if(s){ s.hidden=false; }
  var side=document.getElementById('woSide');
  if(side){ side.setAttribute('aria-hidden','false'); }
  try{ document.body.style.overflow='hidden'; }catch(e){}
}
function woNavClose(){
  document.documentElement.classList.remove('wo-nav-open');
  var s=document.getElementById('woNavScrim');
  if(s){ s.hidden=true; }
  var side=document.getElementById('woSide');
  if(side){ side.setAttribute('aria-hidden','true'); }
  try{ document.body.style.overflow=''; }catch(e){}
}
(function(){
  var side=document.getElementById('woSide');
  if(side){
    side.addEventListener('click', function(e){
      var a=e.target.closest && e.target.closest('a');
      if(a && window.matchMedia && window.matchMedia('(max-width:800px)').matches){
        setTimeout(woNavClose, 80);
      }
    });
  }
  document.addEventListener('keydown', function(e){
    if(e.key==='Escape' && document.documentElement.classList.contains('wo-nav-open')) woNavClose();
  });
  if(window.matchMedia){
    var mq=window.matchMedia('(max-width:800px)');
    function syncAria(){
      var side=document.getElementById('woSide');
      if(!side) return;
      if(mq.matches && !document.documentElement.classList.contains('wo-nav-open')){
        side.setAttribute('aria-hidden','true');
      } else {
        side.removeAttribute('aria-hidden');
      }
    }
    syncAria();
    if(mq.addEventListener) mq.addEventListener('change', syncAria);
    else if(mq.addListener) mq.addListener(syncAria);
  }
})();
</script>
{/literal}
</body></html>
