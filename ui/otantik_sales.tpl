{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div><h1>Ventes</h1><p>Total encaissé {$total|string_format:"%.0f"} FCFA</p></div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    {if $sales_tabs}
    <div class="wo-seg">
      <a href="{Text::url('plugin/wifi_otantik_tickets')}">Tickets</a>
      <a class="on" href="{Text::url('plugin/wifi_otantik_sales')}">Ventes</a>
      <a href="{Text::url('plugin/wifi_otantik_receipts')}">Reçus</a>
    </div>
    {/if}
    {include file="otantik_export.tpl" export_csv=$export_csv export_pdf=$export_pdf}
  </div>
</div>
<div class="wo-card">
{if $rows|@count == 0 && $filter_q == '' && $filter_st == '' && $filter_ty == ''}
  <div class="wo-empty"><i class="fa fa-line-chart"></i>Aucune vente pour le moment</div>
{else}
<form method="get" class="wo-filters" data-wo-filter>
  {include file="otantik_get_keep.tpl"}
  {include file="otantik_find.tpl" placeholder="Rechercher un tél, un forfait, une zone…" find_name="q" find_value=$filter_q}
  <select class="wo-select" name="st" data-wo-st onchange="this.form.submit()">
    <option value="">Tous les statuts</option>
    <option value="2"{if $filter_st == '2'} selected{/if}>Payé</option>
    <option value="1"{if $filter_st == '1'} selected{/if}>Attente</option>
    <option value="0"{if $filter_st == '0'} selected{/if}>Échec</option>
  </select>
  <select class="wo-select" name="ty" data-wo-ty onchange="this.form.submit()">
    <option value="">Tous les moyens</option>
    <option value="MTN"{if $filter_ty == 'MTN'} selected{/if}>MTN</option>
    <option value="ORANGE"{if $filter_ty == 'ORANGE'} selected{/if}>Orange</option>
    <option value="CASH"{if $filter_ty == 'CASH'} selected{/if}>Espèces</option>
  </select>
  <span class="wo-filters-meta" data-wo-count></span>
</form>
{if $rows|@count == 0}
  <div class="wo-empty"><i class="fa fa-search"></i>Aucun résultat</div>
{else}
<table class="wo-table">
<thead><tr><th>#</th><th>Date</th><th>Tél</th><th>Forfait</th><th>Zone</th><th>Montant</th><th>Moyen</th><th>Statut</th><th></th></tr></thead>
<tbody>
{foreach $rows as $r}
<tr data-wo-row data-wo-status="{$r.status}" data-wo-type="{$r.payment_method|default:''}" data-wo-hay="{$r.username|escape} {$r.plan_name|escape} {$r.routers|escape} {$r.payment_method|escape}">
  <td>{$r.id}</td>
  <td>{$r.paid_date|default:$r.created_date}</td>
  <td>{$r.username}</td>
  <td>{$r.plan_name}</td>
  <td>{$r.routers}</td>
  <td>{$r.price}</td>
  <td>{if $r.payment_method == 'CASH'}Espèces{elseif $r.payment_method == 'ORANGE'}Orange{elseif $r.payment_method == 'MTN'}MTN{elseif $r.payment_method == 'CREDIT'}Points{elseif $r.payment_method == 'RESELLER'}Revendeur{elseif $r.payment_method == 'mesomb' || $r.payment_method == 'MeSomb' || $r.payment_method == 'fapshi' || $r.payment_method == 'Fapshi'}Mobile Money{else}{$r.payment_method|default:'—'}{/if}</td>
  <td>{if $r.status == 2}<span class="wo-chip ok">Payé</span>{elseif $r.status == 1}<span class="wo-chip wait">Attente</span>{else}<span class="wo-chip no">Échec</span>{/if}</td>
  <td style="white-space:nowrap">
    {if $r.status == 2}
      <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_receipts/view/')}{$r.id}"><i class="fa fa-file-text-o"></i> Reçu</a>
    {elseif $r.status == 1 && $r.gateway == 'manual'}
      <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_sales/paid/')}{$r.id}">Marquer payé</a>
    {/if}
  </td>
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
