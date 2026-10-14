{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Nouvelle Wifi Zone</h1>
    <p>Votre MikroTik rejoint Wifi-Otantik. Le tunnel se crée après cette étape.</p>
  </div>
  <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_routers')}">Retour aux zones</a>
</div>
<div class="wo-zone-new">
  <div class="wo-3d-stage">
    <span class="wo-3d-ring"></span>
    <span class="wo-3d-spot"></span>
    <img class="wo-router" src="{$app_url}/ui/ui_custom/images/hero-router.jpg" alt="Routeur Wifi-Otantik">
  </div>
  <div class="wo-card">
    <h2 style="margin-top:0">Configurer le point d’accès</h2>
    <form method="post" action="{Text::url('plugin/wifi_otantik_routers/add')}">
      <p><label class="wo-label">Version RouterOS <span class="wo-req">*</span></label>
      <select class="wo-select" name="ros_version">
        <option value="7">RouterOS 7 — tunnel WireGuard (recommandé)</option>
        <option value="6">RouterOS 6 — tunnel L2TP/IPsec</option>
        <option value="auto">Je ne sais pas — la commande détectera</option>
      </select>
      <small style="color:var(--wo-muted)">WireGuard n’existe pas en RouterOS 6. Les deux méthodes ouvrent le même portail d’achat.</small></p>
      <p><label class="wo-label">Nom de la zone <span class="wo-req">*</span></label>
      <input class="wo-input" name="name" placeholder="Ex. Gare Centrale" required></p>
      <p><label class="wo-label">Description</label>
      <textarea class="wo-input" name="zone_note" rows="2" placeholder="Description de la zone"></textarea></p>
      <div class="wo-row">
        <p><label class="wo-label">DNS local <span class="wo-req">*</span></label>
        <input class="wo-input" name="lan_host" placeholder="freewifi.net" required>
        <small style="color:var(--wo-muted)">Un nom d’hôte uniquement, pas une adresse IP — une IP ici est ignorée.</small></p>
        <p><label class="wo-label">Nom du réseau (SSID) <span class="wo-req">*</span></label>
        <input class="wo-input" name="ssid" placeholder="Free Wifi Coffee Shop" required></p>
      </div>
      <p><label class="wo-label">Contact assistance <span class="wo-req">*</span></label>
      <input class="wo-input" name="assistance_contact" placeholder="+237 6XX XXX XXX" required></p>
      <p><label class="wo-label">Interface LAN (hotspot + PPPoE)</label>
      <input class="wo-input" name="pppoe_interface" placeholder="bridge, ether2, vlan10…">
      <small style="color:var(--wo-muted)">Laissé vide : le script prend le bridge. Hotspot et PPPoE sont toujours créés.</small></p>
      <h3>Position</h3>
      <p class="wo-muted" id="woGeoHint">Votre position est détectée automatiquement. Vous pouvez aussi cliquer sur la carte ou chercher un lieu.</p>
      <p><input class="wo-input" id="woSearch" name="location_address" placeholder="Adresse (remplie automatiquement)"></p>
      <div class="wo-map" id="woMap"></div>
      <div class="wo-row">
        <p><label class="wo-label">Latitude</label><input class="wo-input" id="woLat" name="latitude" placeholder="3.8480"></p>
        <p><label class="wo-label">Longitude</label><input class="wo-input" id="woLng" name="longitude" placeholder="11.5021"></p>
      </div>
      <p><button class="wo-btn wo-btn-ghost" type="button" id="woGeoBtn"><i class="fa fa-location-arrow"></i> Ma position</button></p>
      <p class="wo-switch"><input type="hidden" name="zone_active" value="1"><input type="checkbox" checked disabled> Zone active</p>
      <div style="text-align:right;margin-top:12px">
        <a class="wo-btn wo-btn-danger" href="{Text::url('plugin/wifi_otantik_routers')}">Annuler</a>
        <button class="wo-btn wo-btn-primary" type="submit">Créer la zone</button>
      </div>
    </form>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){ if(window.woMapInit) woMapInit(); });
</script>
{include file="otantik_shell_foot.tpl"}
