{include file="otantik_shell_head.tpl"}
<div class="wo-head"><div><h1>Stats & alertes</h1><p>État des tunnels WireGuard (ROS 7) et L2TP/IPsec (ROS 6).</p></div></div>
<div class="wo-card">
{if $peers|@count == 0}
  <div class="wo-empty"><i class="fa fa-bell-o"></i>Aucune alerte. Créez une zone pour suivre le tunnel.</div>
{else}
<div class="wo-filters" data-wo-filter>
  {include file="otantik_find.tpl" placeholder="Rechercher une zone, une IP, un statut…"}
  <span class="wo-filters-meta" data-wo-count></span>
</div>
<table class="wo-table">
  <thead><tr><th>Zone</th><th>Méthode</th><th>IP tunnel</th><th>Statut</th><th>Dernier contact</th></tr></thead>
  <tbody>
    {foreach $peers as $p}
    <tr data-wo-row data-wo-hay="{if isset($names[$p.router_id])}{$names[$p.router_id]|escape}{/if} {$p.status|escape} {$p.wg_ip|escape}">
      <td>{if isset($names[$p.router_id])}{$names[$p.router_id]}{else}#{$p.router_id}{/if}</td>
      <td>{if isset($tunnels[$p.router_id])}{$tunnels[$p.router_id].label}{else}—{/if}</td>
      <td><code>{if isset($tunnels[$p.router_id])}{$tunnels[$p.router_id].ip}{else}{$p.wg_ip}{/if}</code></td>
      <td><span class="wo-chip {if $p.status == 'completed'}ok{elseif $p.status == 'pending'}wait{else}no{/if}">{$p.status}</span></td>
      <td>{$p.last_seen|default:'—'}</td>
    </tr>
  {/foreach}
  </tbody>
</table>
<div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucune alerte ne correspond</div>
{/if}
</div>
{include file="otantik_shell_foot.tpl"}
