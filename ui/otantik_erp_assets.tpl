{include file="otantik_shell_head.tpl"}
{include file="otantik_erp_inc.tpl"}

<div class="wo-erp-grid">
  <div class="wo-erp-card wo-erp-span" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
    <div>
      <b style="font-size:16px">Actifs & équipements</b>
      <p class="wo-muted" style="margin:4px 0 0;font-size:13px">Inventaire du matériel (antennes, routeurs, batteries, infrastructure…) et sa valeur d’achat.</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      {include file="otantik_export.tpl" export_csv=$export_csv export_pdf=$export_pdf}
      <button type="button" class="wo-btn wo-btn-primary" onclick="woErpModal('woAssetModal')"><i class="fa fa-plus"></i> Nouvel actif</button>
    </div>
  </div>

  <div class="wo-grid wo-4 wo-erp-span" style="margin:0">
    <div class="wo-card wo-kpi"><div><span>Valeur totale investie</span><h3>{$asset_total_value|string_format:"%.0f"} F</h3></div><div class="wo-ico blue"><i class="fa fa-money"></i></div></div>
    <div class="wo-card wo-kpi"><div><span>Nombre total d’actifs</span><h3>{$asset_total_count}</h3></div><div class="wo-ico teal"><i class="fa fa-cubes"></i></div></div>
    <div class="wo-card wo-kpi"><div><span>Actifs en service</span><h3>{$asset_in_service}</h3></div><div class="wo-ico green"><i class="fa fa-check-circle"></i></div></div>
  </div>

  <div class="wo-modal" id="woAssetModal" onclick="if(event.target===this) woErpModalClose('woAssetModal')">
    <div class="wo-modal-card" style="width:min(820px,100%)">
      <div class="wo-modal-h">
        <h2 style="margin:0">Nouvel actif</h2>
        <a href="#" onclick="woErpModalClose('woAssetModal');return false;" class="wo-iconbtn">&times;</a>
      </div>
      <form method="post" action="{Text::url('plugin/wifi_otantik_erp/asset')}" style="margin-top:16px">
        <div class="wo-erp-form-grid">
          <p><label class="wo-label">Référence</label>
          <input class="wo-input" name="asset_ref" placeholder="Laisser vide pour générer"></p>
          <p class="span2"><label class="wo-label">Désignation <span class="wo-req">*</span></label>
          <input class="wo-input" name="asset_name" required placeholder="Ex. Starlink V4, Mikrotik RB4011…"></p>
          <p><label class="wo-label">Catégorie</label>
          <select class="wo-select" name="asset_category">
            {foreach $asset_categories as $c}<option value="{$c|escape}">{$c|escape}</option>{/foreach}
          </select></p>
          <p><label class="wo-label">Marque / Modèle</label>
          <input class="wo-input" name="asset_brand_model" placeholder="Ex. MikroTik Rb2011"></p>
          <p><label class="wo-label">Statut</label>
          <select class="wo-select" name="asset_status">
            {foreach $asset_statuses as $s}<option value="{$s|escape}">{$s|escape}</option>{/foreach}
          </select></p>
          <p><label class="wo-label">État</label>
          <select class="wo-select" name="asset_condition">
            {foreach $asset_conditions as $c}<option value="{$c|escape}"{if $c == 'Bon'} selected{/if}>{$c|escape}</option>{/foreach}
          </select></p>
          <p><label class="wo-label">Coût (FCFA) <span class="wo-req">*</span></label>
          <input class="wo-input" type="number" name="asset_cost" min="0" required></p>
          <p><label class="wo-label">Date d’achat</label>
          <input class="wo-input" type="date" name="asset_purchase_date" value="{$smarty.now|date_format:'%Y-%m-%d'}" max="{$smarty.now|date_format:'%Y-%m-%d'}"></p>
          <p class="span2"><label class="wo-label">Note</label>
          <textarea class="wo-input" name="asset_note" rows="2" placeholder="Contexte, numéro de série, emplacement…"></textarea></p>
        </div>
        <div style="text-align:right;margin-top:14px">
          <button class="wo-btn wo-btn-primary" type="submit">Ajouter l’actif</button>
        </div>
      </form>
    </div>
  </div>

  <div class="wo-erp-card wo-erp-s6">
    <b style="font-size:16px;margin-bottom:12px;display:block">Répartition par catégorie</b>
    {if $asset_by_category|@count == 0}
      <div class="wo-empty"><i class="fa fa-cubes"></i>Aucun actif</div>
    {else}
    <table class="wo-erp-table">
      <thead><tr><th>Catégorie</th><th>Nb.</th><th>Valeur totale</th><th>Coût moyen</th></tr></thead>
      <tbody>
        {foreach $asset_by_category as $c}
        <tr>
          <td>{$c.category|escape}</td>
          <td>{$c.count}</td>
          <td><b>{$c.value|string_format:"%.0f"} F</b></td>
          <td>{$c.avg|string_format:"%.0f"} F</td>
        </tr>
        {/foreach}
      </tbody>
    </table>
    {/if}
  </div>

  <div class="wo-erp-card wo-erp-s6">
    <b style="font-size:16px;margin-bottom:12px;display:block">Répartition par statut</b>
    {if $asset_by_status|@count == 0}
      <div class="wo-empty"><i class="fa fa-cubes"></i>Aucun actif</div>
    {else}
    <table class="wo-erp-table">
      <thead><tr><th>Statut</th><th>Nb.</th><th>% du parc</th></tr></thead>
      <tbody>
        {foreach $asset_by_status as $s}
        <tr>
          <td>
            {if $s.status == 'Actif'}<span class="wo-chip ok">{$s.status|escape}</span>
            {elseif $s.status == 'En Maintenance'}<span class="wo-chip wait">{$s.status|escape}</span>
            {elseif $s.status == 'Mis au rebut'}<span class="wo-chip no">{$s.status|escape}</span>
            {else}<span class="wo-chip">{$s.status|escape}</span>{/if}
          </td>
          <td>{$s.count}</td>
          <td>{$s.pct}%</td>
        </tr>
        {/foreach}
      </tbody>
    </table>
    {/if}
  </div>

  <div class="wo-erp-card wo-erp-span">
    <b style="font-size:16px;margin-bottom:12px;display:block">Inventaire complet</b>
    {if $assets|@count == 0}
      <div class="wo-empty"><i class="fa fa-cubes"></i>Aucun actif enregistré</div>
    {else}
    <div class="wo-table-wrap">
    <table class="wo-erp-table">
      <thead><tr><th>Réf.</th><th>Désignation</th><th>Catégorie</th><th>Marque / Modèle</th><th>Statut</th><th>État</th><th>Coût</th><th>Achat</th><th></th></tr></thead>
      <tbody>
        {foreach $assets as $a}
        <tr>
          <td><small class="wo-muted">{$a.ref|escape}</small></td>
          <td><b>{$a.name|escape}</b>{if $a.note}<br><small class="wo-muted">{$a.note|escape|truncate:60}</small>{/if}</td>
          <td>{$a.category|escape}</td>
          <td>{$a.brand_model|escape}</td>
          <td>
            {if $a.status == 'Actif'}<span class="wo-chip ok">Actif</span>
            {elseif $a.status == 'En Maintenance'}<span class="wo-chip wait">En Maintenance</span>
            {elseif $a.status == 'Mis au rebut'}<span class="wo-chip no">Mis au rebut</span>
            {else}<span class="wo-chip">{$a.status|escape}</span>{/if}
          </td>
          <td>{$a.asset_condition|escape}</td>
          <td><b>{$a.cost|string_format:"%.0f"} F</b></td>
          <td>{$a.purchase_date|escape}</td>
          <td style="white-space:nowrap">
            <a class="wo-btn wo-btn-ghost" style="padding:6px 10px" href="{Text::url('plugin/wifi_otantik_erp/edit_asset/')}{$a.id}">Modifier</a>
            <form method="post" action="{Text::url('plugin/wifi_otantik_erp/delete_asset/')}{$a.id}" style="display:inline" onsubmit="return confirm('Supprimer définitivement {$a.name|escape:'javascript'} ({$a.ref|escape:'javascript'}) ?');">
              <input type="hidden" name="confirm_delete" value="1">
              <button type="submit" style="background:none;border:none;padding:0 0 0 8px;color:#b91c1c;text-decoration:underline;cursor:pointer;font:inherit;font-size:13px">Suppr.</button>
            </form>
          </td>
        </tr>
        {/foreach}
      </tbody>
    </table>
    </div>
    {/if}
  </div>
</div>
</div>
<script src="{$app_url}/ui/ui_custom/otantik-erp.js?v=20260820erp4"></script>
{include file="otantik_shell_foot.tpl"}
