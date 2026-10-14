{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Modifier {$expense.number|escape}</h1>
    <p>Corrige l’intitulé, la date, le montant ou la catégorie de cette dépense.</p>
  </div>
  <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_erp/expenses')}">Retour aux dépenses</a>
</div>
<div class="wo-zone-new">
  <div class="wo-card" style="max-width:640px">
    <form method="post" action="{Text::url('plugin/wifi_otantik_erp/edit_expense/')}{$expense.id}">
      <input type="hidden" name="save_expense" value="1">
      <div class="wo-erp-form-grid">
        <p class="span2"><label class="wo-label">Intitulé <span class="wo-req">*</span></label>
        <input class="wo-input" name="expense_title" value="{$expense.title|escape}" required></p>
        <p><label class="wo-label">Date de la dépense <span class="wo-req">*</span></label>
        <input class="wo-input" type="date" name="expense_date" value="{if $expense.expense_date}{$expense.expense_date|escape}{else}{$expense.paid_at|date_format:'%Y-%m-%d'}{/if}" max="{$smarty.now|date_format:'%Y-%m-%d'}" required></p>
        <p><label class="wo-label">Montant (FCFA) <span class="wo-req">*</span></label>
        <input class="wo-input" type="number" name="expense_amount" value="{$expense.amount|string_format:'%.0f'}" min="1" required></p>
        <p><label class="wo-label">Catégorie</label>
        <select class="wo-select" name="expense_category">
          <option value="équipement" {if $expense.category == 'équipement'}selected{/if}>Équipement</option>
          <option value="internet" {if $expense.category == 'internet'}selected{/if}>Internet & télécom</option>
          <option value="électricité" {if $expense.category == 'électricité'}selected{/if}>Électricité</option>
          <option value="transport" {if $expense.category == 'transport'}selected{/if}>Transport</option>
          <option value="salaires" {if $expense.category == 'salaires'}selected{/if}>Salaires</option>
          <option value="loyer" {if $expense.category == 'loyer'}selected{/if}>Loyer</option>
          <option value="maintenance" {if $expense.category == 'maintenance'}selected{/if}>Maintenance</option>
          <option value="fournisseur" {if $expense.category == 'fournisseur'}selected{/if}>Fournisseur</option>
          <option value="autre" {if $expense.category == 'autre'}selected{/if}>Autre</option>
        </select></p>
        <p><label class="wo-label">Mode de paiement</label>
        <select class="wo-select" name="expense_payment_method">
          <option value="Espèces" {if $expense.payment_method == 'Espèces'}selected{/if}>Espèces</option>
          <option value="MTN MoMo" {if $expense.payment_method == 'MTN MoMo'}selected{/if}>MTN MoMo</option>
          <option value="Orange Money" {if $expense.payment_method == 'Orange Money'}selected{/if}>Orange Money</option>
          <option value="Virement" {if $expense.payment_method == 'Virement'}selected{/if}>Virement bancaire</option>
          <option value="Carte" {if $expense.payment_method == 'Carte'}selected{/if}>Carte</option>
          <option value="Wallet" {if $expense.payment_method == 'Wallet'}selected{/if}>Wallet / retrait</option>
        </select></p>
        <p><label class="wo-label">Fournisseur</label>
        <input class="wo-input" name="expense_vendor" value="{$expense.vendor|escape}"></p>
        <p class="span2"><label class="wo-label">Note</label>
        <textarea class="wo-input" name="expense_note" rows="3">{$expense.note|escape}</textarea></p>
      </div>
      {if $files|@count > 0}
      <div style="margin-top:12px">
        <label class="wo-label">Pièces justificatives</label>
        {foreach $files as $f}
          <div><a href="{Text::url('plugin/wifi_otantik_erp/file/')}{$f.id}" target="_blank">{$f.filename|escape}</a></div>
        {/foreach}
        <p class="wo-muted" style="font-size:12px;margin-top:4px">Pour ajouter d’autres pièces, utilisez le champ fichier sur la liste des dépenses.</p>
      </div>
      {/if}
      <div style="text-align:right;margin-top:14px">
        <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_erp/expenses')}">Annuler</a>
        <button class="wo-btn wo-btn-primary" type="submit">Enregistrer</button>
      </div>
    </form>
  </div>
</div>
{include file="otantik_shell_foot.tpl"}
