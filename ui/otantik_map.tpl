{include file="otantik_shell_head.tpl"}
<div class="wo-head wo-map-head">
  <div>
    <h1>{if $is_platform_map}Carte réseau{else}Carte{/if}</h1>
    <p>Wifi Zones et clients PPPoE. Touchez un point pour le détail.</p>
  </div>
  <a class="wo-btn wo-btn-ghost wo-map-new" href="{Text::url('plugin/wifi_otantik_routers/new')}"><i class="fa fa-plus"></i> <span>Nouvelle zone</span></a>
</div>
<div class="wo-map-layout" id="woMapLayout">
  <div class="wo-card wo-map-stage">
    <div id="woViewMap" class="wo-map-canvas" role="application" aria-label="Carte des zones"></div>
    <div class="wo-map-float">
      <div class="wo-seg wo-seg-sm wo-map-chips" id="woMapFilter">
        <a class="on" href="#" data-kind="all">Tout</a>
        <a href="#" data-kind="zone">Zones <b>{$zone_count}</b></a>
        <a href="#" data-kind="pppoe">PPPoE <b>{$pppoe_count}</b></a>
      </div>
      <p class="wo-map-float-meta" id="woMapMeta">{$placed_count} point(s)</p>
    </div>
  </div>
  <aside class="wo-card wo-map-side" id="woMapSide">
    <button type="button" class="wo-map-sheet-handle" id="woMapSheetToggle" aria-expanded="false" aria-controls="woMapSheetBody">
      <span class="wo-map-sheet-bar" aria-hidden="true"></span>
      <span class="wo-map-sheet-label">Liste des points</span>
      <i class="fa fa-chevron-up" id="woMapSheetIco"></i>
    </button>
    <div class="wo-map-sheet-body" id="woMapSheetBody">
      <div class="wo-map-side-tools">
        {include file="otantik_find.tpl" placeholder="Rechercher une zone ou un client…" find_id="woMapQ"}
        <div class="wo-map-legend">
          <span><i class="wo-pin wo-pin-logo wo-pin-zone"><img src="{$app_url}/ui/ui_custom/images/logo.png" alt=""></i> Wifi Zone</span>
          <span><i class="wo-pin wo-pin-logo wo-pin-pppoe"><img src="{$app_url}/ui/ui_custom/images/logo.png" alt=""></i> Client PPPoE</span>
        </div>
      </div>
      <div class="wo-map-list" id="woMapList"></div>
    </div>
  </aside>
</div>
<script>
window.WO_MAP = {$points_json nofilter};
window.WO_MAP_FOCUS = {$focus_json nofilter};
window.WO_LOGO = '{$app_url}/ui/ui_custom/images/logo.png';
{literal}
(function(){
  var data = window.WO_MAP || {zones:[], clients:[]};
  var points = [].concat(data.zones||[], data.clients||[]);
  var kind = 'all';
  var map, layers = {};
  var logo = window.WO_LOGO || '';
  function pin(kind){
    return L.divIcon({
      className: 'wo-pin wo-logo-marker wo-logo-marker-' + kind,
      html: '<span class="wo-logo-pin-body"><img src="'+logo+'" alt=""></span>',
      iconSize: [42, 50],
      iconAnchor: [21, 48],
      popupAnchor: [0, -42]
    });
  }
  function popup(p){
    var bits = ['<div class="wo-map-pop">'];
    if (p.kind === 'zone') {
      bits.push('<b>' + esc(p.name) + '</b>');
      if (p.ssid) bits.push('<span>SSID · ' + esc(p.ssid) + '</span>');
      if (p.address) bits.push('<span>' + esc(p.address) + '</span>');
      bits.push(p.status === 'online' ? '<span class="wo-chip ok">En ligne</span>' : '<span class="wo-chip no">Hors ligne</span>');
      if (p.owner) bits.push('<span>' + esc(p.owner) + '</span>');
      bits.push('<a class="wo-btn wo-btn-ghost wo-map-pop-btn" href="' + esc(p.url) + '">Réglages</a>');
    } else {
      bits.push('<b>' + esc(p.name) + '</b><small>PPPoE</small>');
      if (p.zone) bits.push('<span>Zone · ' + esc(p.zone) + '</span>');
      if (p.plan) bits.push('<span>' + esc(p.plan) + '</span>');
      if (p.phone) bits.push('<span>' + esc(p.phone) + '</span>');
      bits.push(p.status === 'online' ? '<span class="wo-chip ok">Actif</span>' : '<span class="wo-chip wait">Inactif</span>');
      if (!p.precise) bits.push('<small>Position estimée près de la zone</small>');
      if (p.owner) bits.push('<span>' + esc(p.owner) + '</span>');
      bits.push('<a class="wo-btn wo-btn-ghost wo-map-pop-btn" href="' + esc(p.url) + '">Définir la position</a>');
    }
    bits.push('</div>');
    return bits.join('');
  }
  function esc(s){
    return String(s||'').replace(/[&<>"']/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]; });
  }
  function visible(p){
    if (kind !== 'all' && p.kind !== kind) return false;
    var qEl = document.getElementById('woMapQ');
    var q = (qEl && qEl.value || '').toLowerCase().trim();
    if (!q) return true;
    return (p.name+' '+(p.ssid||'')+' '+(p.zone||'')+' '+(p.phone||'')+' '+(p.owner||'')+' '+(p.address||'')).toLowerCase().indexOf(q) >= 0;
  }
  function renderList(){
    var box = document.getElementById('woMapList');
    if (!box) return;
    var html = '';
    var n = 0, placed = 0;
    points.forEach(function(p){
      if (!visible(p)) return;
      n++;
      if (p.lat != null && p.lng != null) placed++;
      html += '<button type="button" class="wo-map-item" data-key="'+esc(p.key)+'">'
        + '<span class="wo-pin wo-pin-logo wo-pin-'+p.kind+'"><img src="'+esc(logo)+'" alt=""></span>'
        + '<span class="wo-map-item-txt"><b>'+esc(p.name)+'</b><small>'+(p.kind==='zone'?(esc(p.ssid)||'Wifi Zone'):('PPPoE'+(p.zone?' · '+esc(p.zone):'')))+'</small></span>'
        + (p.lat==null?'<em>sans GPS</em>':'<i class="fa fa-chevron-right wo-map-item-go" aria-hidden="true"></i>')
        + '</button>';
    });
    box.innerHTML = html || '<div class="wo-empty"><i class="fa fa-map-marker"></i>Aucun point</div>';
    var meta = document.getElementById('woMapMeta');
    if (meta) meta.textContent = placed + ' / ' + n + ' sur la carte';
  }
  function apply(){
    Object.keys(layers).forEach(function(k){
      var p = points.filter(function(x){ return x.key === k; })[0];
      var show = p && visible(p) && p.lat != null && p.lng != null;
      if (show) { if (!map.hasLayer(layers[k])) layers[k].addTo(map); }
      else if (map.hasLayer(layers[k])) map.removeLayer(layers[k]);
    });
    renderList();
    resizeMap();
  }
  function focus(key){
    var m = layers[key];
    if (!m) return;
    map.setView(m.getLatLng(), Math.max(map.getZoom(), 15));
    m.openPopup();
    resizeMap();
  }
  function resizeMap(){
    if (!map) return;
    setTimeout(function(){
      try { map.invalidateSize({animate:false}); } catch(e){}
    }, 60);
  }
  function setSheet(open){
    var layout = document.getElementById('woMapLayout');
    var btn = document.getElementById('woMapSheetToggle');
    var ico = document.getElementById('woMapSheetIco');
    if (!layout) return;
    layout.classList.toggle('is-sheet-open', !!open);
    if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (ico) ico.className = open ? 'fa fa-chevron-down' : 'fa fa-chevron-up';
    resizeMap();
  }
  document.addEventListener('DOMContentLoaded', function(){
    if (!window.L) return;
    map = L.map('woViewMap', {
      zoomControl: true,
      attributionControl: true
    }).setView([5.7, 12.4], 6);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '© OSM'
    }).addTo(map);
    if (map.zoomControl) map.zoomControl.setPosition('bottomright');
    var bounds = [];
    points.forEach(function(p){
      if (p.lat == null || p.lng == null) return;
      var mk = L.marker([p.lat, p.lng], {icon: pin(p.kind)}).bindPopup(popup(p), {
        maxWidth: 280,
        className: 'wo-map-leaflet-pop'
      });
      layers[p.key] = mk;
      mk.addTo(map);
      bounds.push([p.lat, p.lng]);
    });
    if (bounds.length) map.fitBounds(bounds, {padding: [36, 36], maxZoom: 13});
    renderList();
    resizeMap();
    window.addEventListener('resize', resizeMap);
    var filter = document.getElementById('woMapFilter');
    if (filter) filter.addEventListener('click', function(e){
      var a = e.target.closest('a');
      if (!a) return;
      e.preventDefault();
      kind = a.getAttribute('data-kind') || 'all';
      this.querySelectorAll('a').forEach(function(x){ x.classList.toggle('on', x === a); });
      apply();
    });
    var q = document.getElementById('woMapQ');
    if (q) q.addEventListener('input', apply);
    var list = document.getElementById('woMapList');
    if (list) list.addEventListener('click', function(e){
      var b = e.target.closest('[data-key]');
      if (!b) return;
      var key = b.getAttribute('data-key');
      var p = points.filter(function(x){ return x.key === key; })[0];
      if (p && p.lat != null) {
        if (window.matchMedia && window.matchMedia('(max-width:800px)').matches) setSheet(false);
        focus(key);
      } else if (p && p.url) location.href = p.url;
    });
    var toggle = document.getElementById('woMapSheetToggle');
    if (toggle) toggle.addEventListener('click', function(){
      var layout = document.getElementById('woMapLayout');
      setSheet(!(layout && layout.classList.contains('is-sheet-open')));
    });
    if (window.WO_MAP_FOCUS) focus(window.WO_MAP_FOCUS);
  });
})();
{/literal}
</script>
{include file="otantik_shell_foot.tpl"}
