{include file="otantik_shell_head.tpl"}
{include file="otantik_erp_inc.tpl"}

<div class="wo-erp-grid">
  <div class="wo-erp-card wo-erp-span">
    <h1 style="margin:0;font-size:22px">Journal de trésorerie</h1>
    <p style="margin:6px 0 0;color:var(--wo-muted);font-size:13px">Chaque vente Wi‑Fi, retrait, dépense ou facture payée apparaît ici.</p>
  </div>
  <div class="wo-erp-card wo-erp-span">
    {if $flows|@count == 0}
      <div class="wo-empty"><i class="fa fa-exchange"></i>Aucun mouvement — <a href="{Text::url('plugin/wifi_otantik_erp/sync')}">synchroniser les ventes</a></div>
    {else}
    <table class="wo-erp-table">
      <thead>
        <tr><th>Date</th><th>Libellé</th><th>Catégorie</th><th>Source</th><th>Montant</th><th></th></tr>
      </thead>
      <tbody>
        {foreach $flows as $f}
        <tr>
          <td>{$f.occurred_at|escape}</td>
          <td><b>{$f.label|escape}</b>{if $f.method}<br><small class="wo-muted">{$f.method|escape}</small>{/if}</td>
          <td>{$f.category|escape}</td>
          <td><span class="wo-chip">
            {if $f.source == 'wifi_sale'}Vente Wi‑Fi
            {elseif $f.source == 'withdrawal'}Retrait
            {elseif $f.source == 'expense'}Dépense
            {elseif $f.source == 'recurring'}Charge récurrente
            {elseif $f.source == 'invoice'}Facture
            {else}{$f.source|escape}{/if}
          </span></td>
          <td>
            <span class="wo-erp-flow-amt {if $f.direction == 'in'}in{else}out{/if}">
              {if $f.direction == 'in'}+{else}−{/if}{$f.amount|string_format:"%.0f"} F
            </span>
          </td>
          <td>
            {if $f.ref_type == 'payment' && $f.ref_id > 0}
              <a href="{Text::url('plugin/wifi_otantik_receipts/view/')}{$f.ref_id}">Reçu</a>
            {elseif $f.ref_type == 'invoice' && $f.ref_id > 0}
              <a href="{Text::url('plugin/wifi_otantik_erp/doc/')}{$f.ref_id}">Facture</a>
            {elseif $f.ref_type == 'expense' && $f.ref_id > 0}
              <a href="{Text::url('plugin/wifi_otantik_erp/report/')}{$f.expense_id}">Rapport</a>
            {elseif $f.direction == 'out' && $f.expense_id > 0}
              <a href="{Text::url('plugin/wifi_otantik_erp/report/')}{$f.expense_id}">Rapport</a>
            {elseif $f.source == 'other_service'}
              <a href="{Text::url('plugin/wifi_otantik_erp/edit_income/')}{$f.id}">Modifier</a>
              <form method="post" action="{Text::url('plugin/wifi_otantik_erp/delete_income/')}{$f.id}" style="display:inline" onsubmit="return confirm('Supprimer ce revenu ?');">
                <input type="hidden" name="confirm_delete" value="1">
                <button type="submit" style="background:none;border:none;padding:0;color:#b91c1c;text-decoration:underline;cursor:pointer;font:inherit">Supprimer</button>
              </form>
            {else}—{/if}
          </td>
        </tr>
        {/foreach}
      </tbody>
    </table>
    {/if}
  </div>
</div>
</div>
<script src="{$app_url}/ui/ui_custom/otantik-erp.js?v=20260820erp4"></script>
{include file="otantik_shell_foot.tpl"}
