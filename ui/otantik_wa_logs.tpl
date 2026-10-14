{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Journal WhatsApp</h1>
    <p>Tous les envois (modèles et réponses) et les messages reçus via le webhook Meta. Compte SuperAdmin uniquement.</p>
  </div>
  <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_pg')}">Réglages WhatsApp</a>
</div>
{if !$wa_ready}
<div class="wo-card" style="margin-bottom:16px">
  <p style="margin:0;color:#b45309">WhatsApp n’est pas encore configuré. Collez l’identifiant du numéro et le jeton d’accès dans <a href="{Text::url('plugin/wifi_otantik_pg')}">Paiements</a>.</p>
</div>
{/if}
{if $wa_last_error}
<div class="wo-card" style="margin-bottom:16px">
  <p style="margin:0;color:#b45309">Dernier échec d'envoi{if $wa_last_at} ({$wa_last_at|escape}){/if} : {$wa_last_error|escape}</p>
</div>
{/if}
<div class="wo-card">
{if $rows|@count == 0 && $filter_q == ''}
  <div class="wo-empty"><i class="fa fa-whatsapp"></i>Aucun message pour le moment. Un test depuis Paiements, un ticket payé, ou un message client apparaîtra ici.</div>
{else}
<form method="get" class="wo-filters" data-wo-filter>
  {include file="otantik_get_keep.tpl"}
  {include file="otantik_find.tpl" placeholder="Filtrer (numéro, modèle, texte…)" find_name="q" find_value=$filter_q}
  <span class="wo-filters-meta" data-wo-count></span>
</form>
{if $rows|@count == 0}
  <div class="wo-empty"><i class="fa fa-search"></i>Aucun résultat</div>
{else}
<table class="wo-table">
  <thead><tr><th>Quand</th><th>Sens</th><th>Type</th><th>Numéro</th><th>Détail</th><th></th></tr></thead>
  <tbody>
  {foreach $rows as $r}
    <tr data-wo-row data-wo-hay="{$r.label|escape} {$r.tpl|escape} {$r.phone|escape} {$r.detail|escape} {$r.kind|escape}">
      <td><small>{$r.created_at|escape}</small><br><small class="wo-muted">{$r.ago|escape}</small></td>
      <td>{if $r.direction == 'in'}Reçu{else}Envoyé{/if}</td>
      <td><b>{$r.label|escape}</b>{if $r.tpl}<br><small class="wo-muted"><code>{$r.tpl|escape}</code></small>{/if}</td>
      <td><b>{$r.phone|escape}</b></td>
      <td>{$r.detail|escape}</td>
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
