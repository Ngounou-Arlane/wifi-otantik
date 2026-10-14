{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Modifier les dates · {$cust.username|escape}</h1>
    <p>Corrige la date de recharge et la date d’expiration de la ligne active.</p>
  </div>
  <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_customers')}">Retour aux abonnés</a>
</div>
<div class="wo-zone-new">
  <div class="wo-card" style="max-width:520px">
    <p class="wo-muted" style="margin-top:0">Forfait <b>{$rech.namebp|escape}</b> · Zone <b>{$rech.routers|escape}</b></p>
    {if $rech.trx_id > 0}
    <p class="wo-muted" style="font-size:13px">La date de recharge est liée à une vente : le tableau de bord, les rapports et le solde seront mis à jour avec la nouvelle date.</p>
    {else}
    <p class="wo-muted" style="font-size:13px">Aucune vente liée à cette recharge — seule la période d’accès est modifiée.</p>
    {/if}
    <form method="post" action="{Text::url('plugin/wifi_otantik_customers/edit_recharge/')}{$cust.id}">
      <input type="hidden" name="save_dates" value="1">
      <div class="wo-row">
        <p><label class="wo-label">Date de recharge</label>
          <input class="wo-input" type="date" name="recharged_on" value="{$rech.recharged_on|escape}" required></p>
        <p><label class="wo-label">Heure</label>
          <input class="wo-input" type="time" name="recharged_time" value="{$rech.recharged_time|escape}" required></p>
      </div>
      <div class="wo-row">
        <p><label class="wo-label">Date d’expiration</label>
          <input class="wo-input" type="date" name="expiration" value="{$rech.expiration|escape}" required></p>
        <p><label class="wo-label">Heure</label>
          <input class="wo-input" type="time" name="expiration_time" value="{$rech.time|escape}" required></p>
      </div>
      <p style="margin-top:14px">
        <label style="display:flex;gap:8px;align-items:flex-start;font-size:13px;color:var(--wo-muted)">
          <input type="checkbox" name="sync_registration" value="1" checked style="margin-top:3px">
          <span>Aussi mettre à jour la date d’inscription de l’abonné (<b>{$cust.created_at|escape}</b> aujourd’hui) pour qu’elle corresponde — sinon il apparaîtra comme « ajouté aujourd’hui » dans Croissance abonnés / Activité live même après ce changement.</span>
        </label>
      </p>
      <div style="text-align:right;margin-top:12px">
        <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_customers')}">Annuler</a>
        <button class="wo-btn wo-btn-primary" type="submit">Enregistrer les dates</button>
      </div>
    </form>
  </div>
</div>
{include file="otantik_shell_foot.tpl"}
