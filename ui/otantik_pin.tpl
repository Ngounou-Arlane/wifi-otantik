{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Position · {$cust.username|escape}</h1>
    <p>La position se détecte automatiquement. Cliquez, cherchez, ou utilisez Ma position.</p>
  </div>
  <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_map')}">Retour à la carte</a>
</div>
<div class="wo-zone-new">
  <div class="wo-card" style="max-width:720px">
    <form method="post" action="{Text::url('plugin/wifi_otantik_customers/pin/')}{$cust.id}">
      <input type="hidden" name="save_pin" value="1">
      <p><button type="button" class="wo-btn wo-btn-ghost" id="woGeoBtn"><i class="fa fa-location-arrow"></i> Ma position</button>
        <span class="wo-muted" id="woGeoHint"></span></p>
      <p><label class="wo-label">Adresse</label>
        <input class="wo-input" id="woSearch" name="location_address" value="{$cust.location_address|escape}" placeholder="Remplie automatiquement"></p>
      <div class="wo-map" id="woMap" style="height:320px"></div>
      <div class="wo-row">
        <p><label class="wo-label">Latitude</label><input class="wo-input" id="woLat" name="latitude" value="{$cust.latitude|escape}" placeholder="3.8480" required></p>
        <p><label class="wo-label">Longitude</label><input class="wo-input" id="woLng" name="longitude" value="{$cust.longitude|escape}" placeholder="11.5021" required></p>
      </div>
      <div style="text-align:right;margin-top:12px">
        <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_customers')}">Annuler</a>
        <button class="wo-btn wo-btn-primary" type="submit">Enregistrer la position</button>
      </div>
    </form>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  if (window.woMapInit) woMapInit();
});
</script>
{include file="otantik_shell_foot.tpl"}
