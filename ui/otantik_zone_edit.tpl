{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Réglages · {$router.name}</h1>
    <p>Identifiants API, version RouterOS et lien d’achat.</p>
  </div>
  <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_routers')}">Retour aux zones</a>
</div>
<div class="wo-zone-new">
  <div class="wo-3d-stage">
    <span class="wo-3d-ring"></span>
    <span class="wo-3d-spot"></span>
    <img class="wo-router" src="{$app_url}/ui/ui_custom/images/hero-router-gold.jpg" alt="{$router.name|escape}">
  </div>
  <div class="wo-card">
    <h3 style="margin-top:0">Accès MikroTik</h3>
    <form method="post" action="{Text::url('plugin/wifi_otantik_routers/api')}">
      <input type="hidden" name="id" value="{$router.id}">
      <p><label class="wo-label">Version RouterOS</label>
        <select class="wo-select" name="ros_version">
          <option value="auto"{if $router.ros_version == 'auto' || $router.ros_version == ''} selected{/if}>Auto (détection au script)</option>
          <option value="7"{if $router.ros_version == '7'} selected{/if}>RouterOS 7 — WireGuard</option>
          <option value="6"{if $router.ros_version == '6'} selected{/if}>RouterOS 6 — L2TP/IPsec</option>
        </select></p>
      <p><label class="wo-label">Utilisateur API</label>
        <input class="wo-input" name="username" value="{$router.username}" placeholder="admin"></p>
      <p><label class="wo-label">Mot de passe API</label>
        <input class="wo-input" type="password" name="password" placeholder="Laisser vide pour ne pas changer"></p>
      <p><label class="wo-label">Interface LAN (hotspot + PPPoE)</label>
        <input class="wo-input" name="pppoe_interface" value="{$router.pppoe_interface}" placeholder="bridge, ether2…">
        <small style="color:var(--wo-muted)">Vide = bridge. Recollez le script de connexion après modification.</small></p>
      <p><label class="wo-label">Nom du réseau (SSID)</label>
        <input class="wo-input" name="ssid" value="{$router.ssid|escape}" placeholder="Free Wifi Coffee Shop">
        <small style="color:var(--wo-muted)">Recollez le script de connexion après modification pour appliquer le nouveau SSID.</small></p>
      <p><label class="wo-label">DNS local (nom d’hôte)</label>
        <input class="wo-input" name="lan_host" value="{$router.lan_host|escape}" placeholder="freewifi.net">
        <small style="color:var(--wo-muted)">Un nom d’hôte uniquement, pas une adresse IP — une IP ici est ignorée.</small></p>
      <p><label class="wo-label">Contact assistance</label>
        <input class="wo-input" name="assistance_contact" value="{$router.assistance_contact|escape}" placeholder="+237 6XX XXX XXX"></p>
      <p><label class="wo-label">Description</label>
        <textarea class="wo-input" name="zone_note" rows="2" placeholder="Description de la zone">{$router.zone_note|escape}</textarea></p>
      <p class="wo-switch">
        <input type="hidden" name="mac_bypass_strict" value="0">
        <input type="checkbox" name="mac_bypass_strict" value="1"{if $router.mac_bypass_strict == 1} checked{/if}>
        Bloquer le code une fois l’appareil connecté automatiquement
      </p>
      <small style="color:var(--wo-muted);display:block;margin:-8px 0 12px">Une fois qu’un appareil est reconnu automatiquement (sans repasser par le portail), son code ticket ne pourra plus être utilisé pour se connecter depuis un autre appareil. À activer avec prudence : si la reconnaissance automatique venait à sauter, l’appareil devra attendre la réactivation pour repasser par le portail. Désactivable à tout moment.</small>
      <hr style="border:0;border-top:1px solid var(--wo-line);margin:22px 0">
      <h3>Position sur la carte</h3>
      <p class="wo-muted" id="woGeoHint">Cliquez sur la carte, cherchez un lieu, ou utilisez votre position.</p>
      <p><input class="wo-input" id="woSearch" name="location_address" value="{$router.location_address|escape}" placeholder="Adresse (remplie automatiquement)"></p>
      <div class="wo-map" id="woMap" style="height:260px"></div>
      <div class="wo-row">
        <p><label class="wo-label">Latitude</label><input class="wo-input" id="woLat" name="latitude" value="{$router.latitude|escape}" placeholder="3.8480"></p>
        <p><label class="wo-label">Longitude</label><input class="wo-input" id="woLng" name="longitude" value="{$router.longitude|escape}" placeholder="11.5021"></p>
      </div>
      <p><button class="wo-btn wo-btn-ghost" type="button" id="woGeoBtn"><i class="fa fa-location-arrow"></i> Ma position</button></p>
      <button class="wo-btn wo-btn-primary" type="submit">Enregistrer</button>
    </form>
    <hr style="border:0;border-top:1px solid var(--wo-line);margin:22px 0">
    <h3>Liens clients</h3>
    <p><label class="wo-label">Hotspot (Wi‑Fi)</label>
      <input class="wo-input" readonly onclick="this.select()" value="{$pay_url}"></p>
    <p><label class="wo-label">Connectik (PPPoE)</label>
      <input class="wo-input" readonly onclick="this.select()" value="{$pppoe_url}"></p>
    <div class="wo-share">
      <a class="wo-btn wo-btn-ghost" target="_blank" href="{$pay_url}">Ouvrir Wi‑Fi</a>
      <a class="wo-btn wo-btn-ghost" target="_blank" href="{$pppoe_url}">Ouvrir Connectik</a>
      <a class="wo-btn wo-btn-primary" href="{Text::url('plugin/wifi_otantik_routers/script/')}{$router.id}">Lier le routeur</a>
    </div>
    <p style="margin-top:14px">
      <a class="wo-btn wo-btn-ghost" target="_blank" href="{$webfig_url}">Ouvrir l’interface MikroTik (WebFig)</a>
    </p>
    <small style="color:var(--wo-muted);display:block;margin-top:-8px">Accès direct au routeur via le tunnel. Certaines pages de WebFig peuvent s’afficher partiellement selon les liens qu’elles chargent.</small>
    <p style="margin-top:22px">
      <a class="wo-btn wo-btn-danger" href="{Text::url('plugin/wifi_otantik_routers/del/')}{$router.id}" onclick="return confirm('Supprimer cette zone ?')">Supprimer la zone</a>
    </p>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){ if(window.woMapInit) woMapInit(); });
</script>
{include file="otantik_shell_foot.tpl"}
