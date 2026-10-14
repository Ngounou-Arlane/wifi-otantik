{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Modifier {$a.name|escape}</h1>
    <p>Référence <b>{$a.ref|escape}</b></p>
  </div>
  <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_erp/assets')}">Retour aux actifs</a>
</div>
<div class="wo-zone-new">
  <div class="wo-card" style="max-width:680px">
    <form method="post">
      <input type="hidden" name="save_asset" value="1">
      <div class="wo-erp-form-grid">
        <p><label class="wo-label">Référence <span class="wo-req">*</span></label>
        <input class="wo-input" name="asset_ref" value="{$a.ref|escape}" required></p>
        <p class="span2"><label class="wo-label">Désignation <span class="wo-req">*</span></label>
        <input class="wo-input" name="asset_name" value="{$a.name|escape}" required></p>
        <p><label class="wo-label">Catégorie</label>
        <select class="wo-select" name="asset_category">
          {foreach $asset_categories as $c}<option value="{$c|escape}"{if $c == $a.category} selected{/if}>{$c|escape}</option>{/foreach}
        </select></p>
        <p><label class="wo-label">Marque / Modèle</label>
        <input class="wo-input" name="asset_brand_model" value="{$a.brand_model|escape}"></p>
        <p><label class="wo-label">Statut</label>
        <select class="wo-select" name="asset_status">
          {foreach $asset_statuses as $s}<option value="{$s|escape}"{if $s == $a.status} selected{/if}>{$s|escape}</option>{/foreach}
        </select></p>
        <p><label class="wo-label">État</label>
        <select class="wo-select" name="asset_condition">
          {foreach $asset_conditions as $c}<option value="{$c|escape}"{if $c == $a.asset_condition} selected{/if}>{$c|escape}</option>{/foreach}
        </select></p>
        <p><label class="wo-label">Coût (FCFA) <span class="wo-req">*</span></label>
        <input class="wo-input" type="number" name="asset_cost" min="0" value="{$a.cost|string_format:'%.0f'}" required></p>
        <p><label class="wo-label">Date d’achat</label>
        <input class="wo-input" type="date" name="asset_purchase_date" value="{$a.purchase_date|escape}" max="{$smarty.now|date_format:'%Y-%m-%d'}"></p>
        <p class="span2"><label class="wo-label">Note</label>
        <textarea class="wo-input" name="asset_note" rows="2">{$a.note|escape}</textarea></p>
      </div>
      <div style="text-align:right;margin-top:14px">
        <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_erp/assets')}">Annuler</a>
        <button class="wo-btn wo-btn-primary" type="submit">Enregistrer</button>
      </div>
    </form>
  </div>
</div>
{include file="otantik_shell_foot.tpl"}
