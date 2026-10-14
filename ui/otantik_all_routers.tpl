{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div><h1>Tous les routeurs</h1></div>
  <a class="wo-btn wo-btn-primary" href="{Text::url('plugin/wifi_otantik_map')}"><i class="fa fa-map-marker"></i> Carte réseau</a>
</div>
<div class="wo-card">
{if $routers|@count == 0}
  <div class="wo-empty"><i class="fa fa-wifi"></i>Aucun routeur</div>
{else}
<form method="get" class="wo-filters" data-wo-filter>
  {include file="otantik_get_keep.tpl"}
  {include file="otantik_find.tpl" placeholder="Rechercher une zone, un client, un SSID…" find_name="q" find_value=$filter_q}
  <select class="wo-select" name="st" data-wo-st onchange="this.form.submit()">
    <option value="">Tous les statuts</option>
    <option value="1"{if $filter_st == '1'} selected{/if}>Actif</option>
    <option value="0"{if $filter_st == '0'} selected{/if}>Inactif</option>
  </select>
  <select class="wo-select" name="ty" data-wo-ty>
    <option value="">Tous les tunnels</option>
    <option value="wg"{if $filter_ty == 'wg'} selected{/if}>WireGuard</option>
    <option value="l2tp"{if $filter_ty == 'l2tp'} selected{/if}>L2TP</option>
  </select>
  <span class="wo-filters-meta" data-wo-count></span>
</form>
<table class="wo-table">
<thead><tr><th>Zone</th><th>Client</th><th>Tunnel</th><th>IP</th><th>SSID</th><th>Slug</th><th>Statut</th></tr></thead>
<tbody>
{foreach $routers as $r}
{assign var=kind value='wg'}
{if isset($tunnels[$r.id]) && $tunnels[$r.id].method == 'l2tp'}{assign var=kind value='l2tp'}{/if}
<tr data-wo-row data-wo-status="{$r.enabled|default:$r.status}" data-wo-type="{$kind}" data-wo-hay="{$r.name|escape} {if isset($owners[$r.owner_id])}{$owners[$r.owner_id]|escape}{/if} {$r.ssid|escape} {$r.slug|escape}">
<td>{$r.name}</td>
<td>{if isset($owners[$r.owner_id])}{$owners[$r.owner_id]}{else}#{$r.owner_id}{/if}</td>
<td>{if isset($tunnels[$r.id])}{$tunnels[$r.id].short}{else}—{/if}</td>
<td>{if isset($tunnels[$r.id])}<code>{$tunnels[$r.id].ip}</code>{elseif isset($peers[$r.id])}{$peers[$r.id].wg_ip}{else}{$r.ip_address}{/if}</td>
<td>{$r.ssid}</td>
<td>{$r.slug}</td>
<td>{$r.status}</td>
</tr>
{/foreach}
</tbody></table>
<div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucun résultat</div>
{include file="otantik_pager.tpl"}
{/if}
</div>
{include file="otantik_shell_foot.tpl"}
