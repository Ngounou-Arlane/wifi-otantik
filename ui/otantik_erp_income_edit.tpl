{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Modifier autre revenu</h1>
    <p>Corrige le libellé, la date, le montant ou la catégorie de cette entrée.</p>
  </div>
  <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_erp/flow')}">Retour à la trésorerie</a>
</div>
<div class="wo-zone-new">
  <div class="wo-card" style="max-width:600px">
    <form method="post" action="{Text::url('plugin/wifi_otantik_erp/edit_income/')}{$flow.id}">
      <input type="hidden" name="save_income" value="1">
      <div class="wo-erp-form-grid">
        <p class="span2"><label class="wo-label">Libellé <span class="wo-req">*</span></label>
        <input class="wo-input" name="income_label" value="{$flow.label|escape}" required></p>
        <p><label class="wo-label">Date <span class="wo-req">*</span></label>
        <input class="wo-input" type="date" name="income_date" value="{$flow.occurred_at|date_format:'%Y-%m-%d'}" required></p>
        <p><label class="wo-label">Montant (FCFA) <span class="wo-req">*</span></label>
        <input class="wo-input" type="number" name="income_amount" value="{$flow.amount|string_format:'%.0f'}" min="1" required></p>
        <p><label class="wo-label">Catégorie</label>
        <select class="wo-select" name="income_category">
          <option value="impression" {if $flow.category == 'impression'}selected{/if}>Impression / photocopie</option>
          <option value="cyber" {if $flow.category == 'cyber'}selected{/if}>Cyber / jeux</option>
          <option value="boutique" {if $flow.category == 'boutique'}selected{/if}>Boutique / boissons</option>
          <option value="installation" {if $flow.category == 'installation'}selected{/if}>Installation / dépannage</option>
          <option value="services" {if $flow.category == 'services'}selected{/if}>Autre service</option>
        </select></p>
        <p><label class="wo-label">Mode d’encaissement</label>
        <select class="wo-select" name="income_method">
          <option value="Espèces" {if $flow.method == 'Espèces'}selected{/if}>Espèces</option>
          <option value="MTN MoMo" {if $flow.method == 'MTN MoMo'}selected{/if}>MTN MoMo</option>
          <option value="Orange Money" {if $flow.method == 'Orange Money'}selected{/if}>Orange Money</option>
          <option value="Virement" {if $flow.method == 'Virement'}selected{/if}>Virement</option>
        </select></p>
      </div>
      <div style="text-align:right;margin-top:14px">
        <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_erp/flow')}">Annuler</a>
        <button class="wo-btn wo-btn-primary" type="submit">Enregistrer</button>
      </div>
    </form>
  </div>
</div>
{include file="otantik_shell_foot.tpl"}
