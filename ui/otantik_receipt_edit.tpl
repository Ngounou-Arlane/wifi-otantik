{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Modifier le reçu</h1>
    <p>Réservé à l’administrateur de la plateforme — le solde et les rapports sont recalculés automatiquement.</p>
  </div>
  <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_receipts/view/')}{$trx.id}">Retour au reçu</a>
</div>
<div class="wo-zone-new">
  <div class="wo-card" style="max-width:520px">
    <form method="post" action="{Text::url('plugin/wifi_otantik_receipts/edit/')}{$trx.id}">
      <input type="hidden" name="save_receipt" value="1">
      <p><label class="wo-label">Client (téléphone / identifiant)</label>
        <input class="wo-input" name="client" value="{$trx.username|escape}" required></p>
      <p><label class="wo-label">Forfait / description</label>
        <input class="wo-input" name="plan_name" value="{$trx.plan_name|escape}" required></p>
      <p><label class="wo-label">Montant (FCFA)</label>
        <input class="wo-input" type="number" name="price" value="{$trx.price|string_format:'%.0f'}" min="0" required></p>
      <p class="wo-muted" style="font-size:13px">Changer le montant ajuste immédiatement le solde et l’écriture ERP liée à cette vente.</p>
      <div style="text-align:right;margin-top:12px">
        <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_receipts/view/')}{$trx.id}">Annuler</a>
        <button class="wo-btn wo-btn-primary" type="submit">Enregistrer</button>
      </div>
    </form>
  </div>
</div>
{include file="otantik_shell_foot.tpl"}
