{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Journal MikroTik</h1>
    <p>Chaque écriture RouterOS depuis le VPS (compte, tarif, portail, expiration, kick) apparaît ici pour vos zones.</p>
  </div>
  <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_home')}">Tableau de bord</a>
</div>
<div class="wo-card">
{if $rows|@count == 0 && $filter_q == ''}
  <div class="wo-empty"><i class="fa fa-exchange"></i>Aucune écriture enregistrée pour le moment</div>
{else}
<form method="get" class="wo-filters" data-wo-filter>
  {include file="otantik_get_keep.tpl"}
  {include file="otantik_find.tpl" placeholder="Filtrer (identifiant, zone, action…)" find_name="q" find_value=$filter_q}
  <span class="wo-filters-meta" data-wo-count></span>
</form>
{if $rows|@count == 0}
  <div class="wo-empty"><i class="fa fa-search"></i>Aucun résultat</div>
{else}
<table class="wo-table">
  <thead><tr><th>Quand</th><th>Action</th><th>Cible</th><th>Zone</th><th>Détail</th><th></th></tr></thead>
  <tbody>
  {foreach $rows as $r}
    <tr data-wo-row data-wo-hay="{$r.action_label|escape} {$r.target|escape} {$r.router_name|escape} {$r.message|escape}">
      <td><small>{$r.created_at|escape}</small><br><small class="wo-muted">{$r.ago|escape}</small></td>
      <td>{$r.action_label|escape}</td>
      <td><b>{if $r.target}{$r.target|escape}{else}—{/if}</b></td>
      <td>{$r.router_name|escape}</td>
      <td>{$r.message|escape}</td>
      <td>{if $r.ok}<span class="wo-chip ok">OK</span>{else}<span class="wo-chip no">Échec</span>{/if}</td>
    </tr>
  {/foreach}
  </tbody>
</table>
<div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucun résultat</div>
{include file="otantik_pager.tpl"}
{/if}
{/if}
</div>
{include file="otantik_shell_foot.tpl"}
