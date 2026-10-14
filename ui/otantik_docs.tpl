{include file="otantik_shell_head.tpl"}
<div class="wo-head"><div><h1>Documentation</h1><p>Mettre une zone en vente, de A à Z.</p></div></div>
<div class="wo-filters" data-wo-filter>
  {include file="otantik_find.tpl" placeholder="Rechercher dans la documentation…"}
  <span class="wo-filters-meta" data-wo-count></span>
</div>
<div class="wo-grid wo-2">
  <div class="wo-card" data-wo-row data-wo-hay="créer zone routeros wireguard l2tp">
    <h3>1. Créer la zone</h3>
    <p>Indiquez si le MikroTik est en <b>RouterOS 7</b> (WireGuard) ou <b>RouterOS 6</b> (L2TP/IPsec). Les deux vendent ensuite le même hotspot / PPPoE client.</p>
  </div>
  <div class="wo-card" data-wo-row data-wo-hay="brancher mikrotik script tunnel udp">
    <h3>2. Brancher le MikroTik</h3>
    <p>WinBox → New Terminal. Collez la commande. Le script crée le tunnel, le pool <b>hs-pool</b>, le DHCP, le serveur hotspot, le pool PPPoE et le serveur <b>pppoe-otantik</b>.</p>
    <p>WinBox peut se couper quand le hotspot s’active : le reste continue tout seul (planificateur). Attendez 30 s puis reconnectez.</p>
  </div>
  <div class="wo-card" data-wo-row data-wo-hay="tarifs hotspot pppoe connectik forfaits">
    <h3>3. Créer les tarifs</h3>
    <p>Hotspot (portail captif Wi‑Fi) ou PPPoE (ligne / fibre). Les forfaits PPPoE ne s’affichent jamais sur le portail hotspot : ils ont leur page <b>Connectik</b> (<code>portal.otantik.cm/connectik/</code>).</p>
  </div>
  <div class="wo-card" data-wo-row data-wo-hay="encaisser lien wifi connectik vendre">
    <h3>4. Encaisser</h3>
    <p>Partagez le lien <b>Wi‑Fi</b> pour le hotspot, et <b>Connectik</b> pour le PPPoE.</p>
  </div>
  <div class="wo-card" data-wo-row data-wo-hay="portail captif telegram bot login">
    <h3>5. Portail captif</h3>
    <p>Menu <b>Portail captif</b> : écran type otantikhub, login simple (code + mot de passe) et tarifs hotspot uniquement. Collez le token @BotFather et le chat ID pour l’aide Telegram.</p>
    <p>Cliquez <b>Envoyer au MikroTik</b> (tunnel en ligne) ou recollez le script de connexion : les pages login/status sont téléchargées dans le dossier hotspot du routeur.</p>
  </div>
</div>
<div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucun article ne correspond</div>
{include file="otantik_shell_foot.tpl"}
