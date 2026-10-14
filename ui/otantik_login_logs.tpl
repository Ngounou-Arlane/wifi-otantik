{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Connexions</h1>
    <p>Historique des connexions à la plateforme (réussies et échouées). Compte SuperAdmin uniquement.</p>
  </div>
</div>
<div class="wo-card">
{if $rows|@count == 0 && $filter_q == ''}
  <div class="wo-empty"><i class="fa fa-sign-in"></i>Aucune connexion enregistrée pour le moment</div>
{else}
<form method="get" class="wo-filters" data-wo-filter>
  {include file="otantik_get_keep.tpl"}
  {include file="otantik_find.tpl" placeholder="Filtrer (utilisateur, IP, appareil…)" find_name="q" find_value=$filter_q}
  <span class="wo-filters-meta" data-wo-count></span>
</form>
{if $rows|@count == 0}
  <div class="wo-empty"><i class="fa fa-search"></i>Aucun résultat</div>
{else}
<div class="wo-table-wrap">
<table class="wo-table">
  <thead><tr><th>Quand</th><th>Utilisateur</th><th>Appareil</th><th>IP</th><th>Statut</th><th></th></tr></thead>
  <tbody>
  {foreach $rows as $r}
    <tr data-wo-row data-wo-hay="{$r.username|escape} {$r.ip|escape} {$r.device_label|escape} {$r.status|escape} {$r.reason|escape}">
      <td><small>{$r.created_at|escape}</small><br><small class="wo-muted">{$r.ago|escape}</small></td>
      <td><b>{$r.username|escape}</b></td>
      <td>{$r.device_label|escape}</td>
      <td><small class="wo-muted">{$r.ip|escape}</small></td>
      <td>
        {if $r.status == 'success'}<span class="wo-chip ok">Réussie</span>
        {else}<span class="wo-chip no">Échouée</span>{if $r.reason}<br><small class="wo-muted">{$r.reason|escape}</small>{/if}{/if}
      </td>
      <td>
        {if $r.user_id > 0}
        <form method="post" action="{Text::url('plugin/wifi_otantik_login_logs/disconnect/')}{$r.user_id}" style="display:inline" onsubmit="return confirm('Déconnecter {$r.username|escape:'javascript'} ? Une nouvelle connexion sera nécessaire sur tous ses appareils actuellement connectés.');">
          <input type="hidden" name="confirm_disconnect" value="1">
          <button type="submit" class="wo-btn wo-btn-ghost" style="padding:6px 10px;color:#b91c1c">Déconnecter</button>
        </form>
        {/if}
      </td>
    </tr>
  {/foreach}
  </tbody>
</table>
</div>
<div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucun résultat</div>
{include file="otantik_pager.tpl"}
{/if}
{/if}
</div>
{include file="otantik_shell_foot.tpl"}
