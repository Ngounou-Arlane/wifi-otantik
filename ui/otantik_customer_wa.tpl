{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Envoyer un modèle WhatsApp</h1>
    <p>Abonné : <b>{$cust.username|escape}</b> — {$cust.phonenumber|escape}</p>
  </div>
  <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_customers')}">Retour aux abonnés</a>
</div>
{if !$wa_ready}
<div class="wo-card" style="margin-bottom:16px">
  <p style="margin:0;color:#b45309">WhatsApp n'est pas encore activé/configuré. Voir <a href="{Text::url('plugin/wifi_otantik_pg')}">Paiements</a>.</p>
</div>
{/if}
{if $wa_last_error}
<div class="wo-card" style="margin-bottom:16px">
  <p style="margin:0;color:#b45309">Dernier échec : {$wa_last_error|escape}</p>
</div>
{/if}
<form class="wo-card" method="post">
  <p><label class="wo-label">Modèle à envoyer</label>
    <select class="wo-input" name="wa_tpl" required>
      <option value="">— Choisir —</option>
      {foreach $wa_catalog as $key => $label}
      <option value="{$key|escape}">{$label|escape} ({$key|escape})</option>
      {/foreach}
    </select>
  </p>
  <p style="color:var(--wo-muted);font-size:13px">Les informations (zone, code, forfait, échéance) sont reprises automatiquement depuis le dernier ticket de cet abonné.</p>
  <button class="wo-btn wo-btn-primary" type="submit"{if !$wa_ready} disabled{/if}>Envoyer</button>
</form>
{include file="otantik_shell_foot.tpl"}
