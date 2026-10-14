{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Messages du portail</h1>
    <p>Les clients Wi‑Fi vous écrivent depuis la page Contact. Telegram reste un bonus.</p>
  </div>
</div>
<div class="wo-card">
{if $rows|@count == 0 && $filter_q == ''}
  <div class="wo-empty"><i class="fa fa-envelope-o"></i>Aucun message pour le moment</div>
{else}
<form method="post" onsubmit="return confirm('Vider toute la boîte ?');" style="margin-bottom:12px">
  <button class="wo-btn wo-btn-ghost" type="submit" name="clear_inbox" value="1">Tout supprimer</button>
</form>
<form method="get" class="wo-filters" data-wo-filter>
  {include file="otantik_get_keep.tpl"}
  {include file="otantik_find.tpl" placeholder="Rechercher un nom, un tél, une zone, un message…" find_name="q" find_value=$filter_q}
  <span class="wo-filters-meta" data-wo-count></span>
</form>
{if $rows|@count == 0}
  <div class="wo-empty"><i class="fa fa-search"></i>Aucun message ne correspond</div>
{else}
<table class="wo-table">
  <thead><tr><th>Quand</th><th>Zone</th><th>Nom / tél</th><th>Message</th><th></th></tr></thead>
  <tbody>
  {foreach $rows as $r}
    <tr data-wo-row data-wo-hay="{$r.router_name|escape} {$r.name|escape} {$r.phone|escape} {$r.message|escape}" {if $r.seen == 0}style="background:#fff8f5"{/if}>
      <td><small>{$r.created_at}</small></td>
      <td>{$r.router_name|escape}</td>
      <td><b>{$r.name|escape}</b><br><small>{$r.phone|escape}</small></td>
      <td>{$r.message|escape}</td>
      <td>{if $r.seen == 0}<a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_inbox/')}{$r.id}">Lu</a>{else}<span class="wo-chip ok">Lu</span>{/if}</td>
    </tr>
  {/foreach}
  </tbody>
</table>
<div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucun message ne correspond</div>
{include file="otantik_pager.tpl"}
{/if}
{/if}
</div>
{include file="otantik_shell_foot.tpl"}
