{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Mes Wifi Zone</h1>
    <p>Chaque carte est un point d’accès. Partagez le lien Wi‑Fi ou Connectik à vos clients.</p>
  </div>
  <div class="wo-head-actions">
    <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_map')}"><i class="fa fa-map-marker"></i> <span>Carte</span></a>
    <a class="wo-btn wo-btn-primary" href="{Text::url('plugin/wifi_otantik_routers/new')}"><i class="fa fa-plus"></i> <span>Nouvelle zone</span></a>
  </div>
</div>
{if $connect}
<div class="wo-card" style="margin-bottom:16px;border-color:#86efac">
  <b>Connecter « {$script_router.name} »</b>
      <p>Winbox → New Terminal. Collez <b>une seule</b> commande (ROS 7 = WireGuard, ROS 6 = L2TP). Attendez le message « import programme », puis ~30 s.</p>
      <p style="color:var(--wo-muted);font-size:13px">Le script configure le hotspot complet (<code>hs-pool</code>, DHCP, serveur hotspot) et le serveur PPPoE. WinBox peut se déconnecter au moment du hotspot : c’est normal. Attendez ~30 secondes, puis reconnectez-vous (IP passerelle <code>10.5.x.1</code> ou via le tunnel).</p>
  <div class="wo-grid wo-2" style="margin-top:12px">
    <div class="wo-card" style="margin:0;{if $connect.ros == '7'}border-color:#002858{/if}">
      <span class="wo-chip ok">RouterOS 7</span>
      <h3 style="margin:8px 0 4px">WireGuard</h3>
      <p style="color:var(--wo-muted)">UDP 51820 · tunnel <code>{$connect.wg_ip}</code></p>
      <textarea class="wo-input" rows="3" readonly onclick="this.select()" style="font-family:monospace;font-size:12px">{$connect.bootstrap7}</textarea>
    </div>
    <div class="wo-card" style="margin:0;{if $connect.ros == '6'}border-color:#f05018{/if}">
      <span class="wo-chip wait">RouterOS 6</span>
      <h3 style="margin:8px 0 4px">L2TP/IPsec</h3>
      <p style="color:var(--wo-muted)">UDP 500 / 4500 / 1701 · tunnel <code>{$connect.l2tp_ip|default:'10.89.x.x'}</code></p>
      <textarea class="wo-input" rows="3" readonly onclick="this.select()" style="font-family:monospace;font-size:12px">{$connect.bootstrap6}</textarea>
    </div>
  </div>
  <p style="margin-top:14px"><b>Version inconnue ?</b> Cette commande détecte ROS 6 ou 7.</p>
  <textarea class="wo-input" rows="2" readonly onclick="this.select()" style="font-family:monospace;font-size:12px">{$connect.bootstrap_auto}</textarea>
</div>
{/if}
{if $routers|@count == 0}
  <div class="wo-card wo-empty">
    <img src="{$app_url}/ui/ui_custom/images/hero-router.jpg" alt="" style="width:180px;border-radius:16px;margin-bottom:12px">
    <div>Aucune zone pour l’instant.</div>
    <p><a class="wo-btn wo-btn-primary" href="{Text::url('plugin/wifi_otantik_routers/new')}">Créer une Wifi Zone</a></p>
  </div>
{else}
<div>
<div class="wo-filters" data-wo-filter>
  {include file="otantik_find.tpl" placeholder="Rechercher une zone, un SSID…"}
  <select class="wo-select" data-wo-st>
    <option value="">Tous les statuts</option>
    <option value="completed">En ligne</option>
    <option value="pending">À connecter</option>
    <option value="offline">Hors ligne</option>
  </select>
  <select class="wo-select" data-wo-ty>
    <option value="">Toutes les versions</option>
    <option value="wg">RouterOS 7 · WireGuard</option>
    <option value="l2tp">RouterOS 6 · L2TP</option>
  </select>
  <span class="wo-filters-meta" data-wo-count></span>
</div>
<div class="wo-zone-grid" data-wo-list>
{foreach $routers as $r}
  {assign var=st value='pending'}
  {if isset($peers[$r.id])}
    {if $peers[$r.id].status == 'completed'}{assign var=st value='completed'}
    {elseif $peers[$r.id].status == 'pending'}{assign var=st value='pending'}
    {else}{assign var=st value='offline'}{/if}
  {/if}
  {assign var=kind value='wg'}
  {if isset($tunnels[$r.id]) && $tunnels[$r.id].method == 'l2tp'}{assign var=kind value='l2tp'}{/if}
  <article class="wo-zone-card" data-wo-row data-wo-status="{$st}" data-wo-type="{$kind}" data-wo-hay="{$r.name|escape} {$r.ssid|escape} {$r.slug|escape}">
    <div class="wo-zone-photo">
      <img src="{$app_url}/ui/ui_custom/images/{if $st == 'completed'}hero-router-gold.jpg{else}hero-router.jpg{/if}" alt="{$r.name|escape}">
      <span class="wo-chip {if $st == 'completed'}ok{elseif $st == 'pending'}wait{else}no{/if}">
        {if $st == 'completed'}En ligne{elseif $st == 'pending'}À connecter{else}Hors ligne{/if}
      </span>
    </div>
    <div class="wo-zone-body">
      <h3>{$r.name}</h3>
      <div class="wo-zone-labels">
        <span><i class="fa fa-wifi"></i> {$r.ssid|default:'SSID à définir'}</span>
        {if isset($tunnels[$r.id]) && $tunnels[$r.id].method == 'l2tp'}
          <span><i class="fa fa-lock"></i> ROS 6 · L2TP</span>
        {elseif isset($tunnels[$r.id])}
          <span><i class="fa fa-shield"></i> ROS 7 · WireGuard</span>
        {/if}
      </div>
      <div class="wo-zone-actions">
        <button type="button" class="wo-zone-act" data-url="{$pay_urls[$r.id]|escape}" onclick="woCopy(this.getAttribute('data-url'))">
          <i class="fa fa-copy"></i><label>Wi‑Fi</label>
        </button>
        <button type="button" class="wo-zone-act" data-url="{$pppoe_urls[$r.id]|escape}" onclick="woCopy(this.getAttribute('data-url'))">
          <i class="fa fa-copy"></i><label>Connectik</label>
        </button>
        <a class="wo-zone-act" href="{Text::url('plugin/wifi_otantik_captive/')}{$r.id}">
          <i class="fa fa-mobile"></i><label>Portail</label>
        </a>
        <a class="wo-zone-act" href="{Text::url('plugin/wifi_otantik_monitor/')}{$r.id}">
          <i class="fa fa-heartbeat"></i><label>Live</label>
        </a>
        <a class="wo-zone-act" href="{Text::url('plugin/wifi_otantik_routers/script/')}{$r.id}">
          <i class="fa fa-plug"></i><label>Lier</label>
        </a>
        <a class="wo-zone-act" href="{Text::url('plugin/wifi_otantik_routers/edit/')}{$r.id}">
          <i class="fa fa-cog"></i><label>Réglages</label>
        </a>
      </div>
    </div>
  </article>
{/foreach}
</div>
<div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucune zone ne correspond</div>
</div>
{/if}
{include file="otantik_shell_foot.tpl"}
