{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Revendeurs</h1>
    <p>Comptes, zones, commissions et caisse. Portail : <a href="{$portal_url}" target="_blank">{$portal_url}</a></p>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    {include file="otantik_export.tpl" export_csv=$export_list_csv export_pdf=$export_list_pdf}
    <a class="wo-btn wo-btn-primary" href="{Text::url('plugin/wifi_otantik_resellers/new')}"><i class="fa fa-plus"></i> Nouveau revendeur</a>
  </div>
</div>
<div class="wo-grid wo-4">
  <div class="wo-card wo-kpi"><div><span>Caisses revendeurs</span><h3>{$sum_wallets|string_format:"%.0f"} FCFA</h3></div><div class="wo-ico blue"><i class="fa fa-briefcase"></i></div></div>
  <div class="wo-card wo-kpi"><div><span>Recharges</span><h3>{$sum_topup|string_format:"%.0f"} FCFA</h3></div><div class="wo-ico orange"><i class="fa fa-plus-circle"></i></div></div>
  <div class="wo-card wo-kpi"><div><span>Votre part (ventes)</span><h3>{$sum_wholesale|string_format:"%.0f"} FCFA</h3></div><div class="wo-ico green"><i class="fa fa-line-chart"></i></div></div>
  <div class="wo-card wo-kpi"><div><span>Commissions versées</span><h3>{$sum_commission|string_format:"%.0f"} FCFA</h3></div><div class="wo-ico yellow"><i class="fa fa-percent"></i></div></div>
</div>
<div class="wo-card" style="margin-top:16px">
{if $rows|@count == 0 && $filter_q == ''}
    <div class="wo-empty"><i class="fa fa-briefcase"></i>Aucun revendeur. Créez un compte : 500 FCFA sont crédités automatiquement.</div>
  {else}
  <form method="get" class="wo-filters" data-wo-filter>
    {include file="otantik_get_keep.tpl"}
    {if $filter_bq}<input type="hidden" name="bq" value="{$filter_bq|escape}">{/if}
    {include file="otantik_find.tpl" placeholder="Rechercher un revendeur, un tél, une zone…" find_name="q" find_value=$filter_q}
    <span class="wo-filters-meta" data-wo-count></span>
  </form>
  {if $rows|@count == 0}
    <div class="wo-empty"><i class="fa fa-search"></i>Aucun revendeur ne correspond</div>
  {else}
  <table class="wo-table">
    <thead><tr>{if $show_owner}<th>Exploitant</th>{/if}<th>Revendeur</th><th>Zones</th><th>Commission</th><th>Caisse</th><th>Ventes</th><th>Gain</th><th>Statut</th><th></th></tr></thead>
    <tbody>
    {foreach $rows as $r}
      <tr data-wo-row data-wo-hay="{$r.name|escape} {$r.phone|escape} {$r.owner_name|escape} {if $r.zones}{$r.zones|implode:' '}{/if}">
        {if $show_owner}<td>{if $r.owner_name}{$r.owner_name|escape}{else}#{$r.owner_id}{/if}</td>{/if}
        <td><b>{$r.name|escape}</b><br><small>{$r.phone|escape}</small></td>
        <td>{if $r.zones}{$r.zones|implode:', '}{else}—{/if}</td>
        <td>{$r.commission|string_format:"%.0f"} %</td>
        <td>{$r.wallet|string_format:"%.0f"} F</td>
        <td>{$r.sales_total|string_format:"%.0f"} F · {$r.tickets_count} ticket{if $r.tickets_count > 1}s{/if}</td>
        <td>{$r.profit|string_format:"%.0f"} F</td>
        <td><span class="wo-chip {if $r.status == 'active'}ok{else}no{/if}">{if $r.status == 'active'}Actif{else}Inactif{/if}</span></td>
        <td style="white-space:nowrap">
          <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_resellers/view/')}{$r.id}">Voir</a>
          <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_resellers/edit/')}{$r.id}">Modifier</a>
        </td>
      </tr>
    {/foreach}
    </tbody>
  </table>
  <div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucun revendeur ne correspond</div>
  {include file="otantik_pager.tpl"}
  {/if}
  {/if}
</div>
<div class="wo-card" style="margin-top:16px">
  <b>Journal comptable revendeurs</b>
  {if $books|@count == 0 && $filter_bq == ''}
    <div class="wo-empty"><i class="fa fa-book"></i>Aucun mouvement pour le moment</div>
  {else}
  <form method="get" class="wo-filters" data-wo-filter>
    {include file="otantik_get_keep.tpl" pager=$pager_books}
    {if $filter_q}<input type="hidden" name="q" value="{$filter_q|escape}">{/if}
    {include file="otantik_find.tpl" placeholder="Filtrer le journal (type, note…)" find_name="bq" find_value=$filter_bq}
    <span class="wo-filters-meta" data-wo-count></span>
    {include file="otantik_export.tpl" export_csv=$export_books_csv export_pdf=$export_books_pdf}
  </form>
  {if $books|@count == 0}
    <div class="wo-empty"><i class="fa fa-search"></i>Aucun mouvement ne correspond</div>
  {else}
  <table class="wo-table">
    <thead><tr><th>Date</th><th>Type</th><th>Montant caisse</th><th>Votre part</th><th>Commission</th><th>Note</th></tr></thead>
    <tbody>
    {foreach $books as $b}
      <tr data-wo-row data-wo-hay="{$b.kind|escape} {$b.note|escape} {$b.amount}">
        <td>{$b.created_at}</td>
        <td>{$b.kind}</td>
        <td>{$b.amount} F</td>
        <td>{$b.client_amount} F</td>
        <td>{$b.commission} F</td>
        <td>{$b.note|escape}</td>
      </tr>
    {/foreach}
    </tbody>
  </table>
  <div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucun mouvement ne correspond</div>
  {include file="otantik_pager.tpl" pager=$pager_books}
  {/if}
  {/if}
</div>
{include file="otantik_shell_foot.tpl"}
