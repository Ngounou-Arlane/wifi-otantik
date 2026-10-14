{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>{$row.name|escape}</h1>
    <p>{$row.phone|escape} · commission {$row.commission|string_format:"%.0f"} % · exemple 1 000 F → vous {$split.wholesale} F, revendeur {$split.profit} F</p>
  </div>
  <div>
    <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_resellers/edit/')}{$row.id}">Modifier</a>
    <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_resellers/toggle/')}{$row.id}">{if $row.status == 'active'}Désactiver{else}Activer{/if}</a>
  </div>
</div>
<div class="wo-grid wo-4">
  <div class="wo-card wo-kpi"><div><span>Caisse</span><h3>{$row.wallet|string_format:"%.0f"} FCFA</h3></div><div class="wo-ico blue"><i class="fa fa-money"></i></div></div>
  <div class="wo-card wo-kpi"><div><span>Ventes</span><h3>{$row.sales_total|string_format:"%.0f"} FCFA</h3><small>{$row.tickets_count} ticket{if $row.tickets_count > 1}s{/if}</small></div><div class="wo-ico orange"><i class="fa fa-ticket"></i></div></div>
  <div class="wo-card wo-kpi"><div><span>Commissions</span><h3>{$row.profit|string_format:"%.0f"} FCFA</h3></div><div class="wo-ico green"><i class="fa fa-percent"></i></div></div>
  <div class="wo-card wo-kpi"><div><span>Statut</span><h3>{if $row.status == 'active'}Actif{else}Inactif{/if}</h3></div><div class="wo-ico yellow"><i class="fa fa-user"></i></div></div>
</div>
<div class="wo-grid wo-2" style="margin-top:16px">
  <div class="wo-card">
    <h3 style="margin-top:0">Recharger la caisse</h3>
    <form method="post" action="{Text::url('plugin/wifi_otantik_resellers/topup/')}{$row.id}">
      <p><label class="wo-label">Montant (FCFA)</label>
        <input class="wo-input" type="number" name="amount" min="100" required></p>
      <label class="wo-switch"><input type="checkbox" name="from_wallet" value="1"> Déduire de mon portefeuille (retraits)</label>
      <p class="wo-muted">Recharge manuelle : crédit de caisse seulement, sans frais Wifi-Otantik et sans alimenter les retraits. Cochez la case pour prendre l’argent sur votre solde déjà encaissé. Les ventes de tickets ne créditent jamais les retraits.</p>
      <button class="wo-btn wo-btn-primary" type="submit">Créditer</button>
    </form>
  </div>
  <div class="wo-card">
    <h3 style="margin-top:0">Autorisations</h3>
    <p>Zones : {foreach $zones as $z}{$z.name|escape}{if !$z@last}, {/if}{foreachelse}—{/foreach}</p>
    <p>Hotspot : {if $perm.sell_hotspot}oui{else}non{/if} · PPPoE : {if $perm.sell_pppoe}oui{else}non{/if} · Recharge : {if $perm.recharge}oui{else}non{/if}</p>
    <form method="post" action="{Text::url('plugin/wifi_otantik_resellers/delete/')}{$row.id}" onsubmit="return confirm('Supprimer ce compte revendeur ?');">
      <input type="hidden" name="confirm_delete" value="1">
      <button class="wo-btn wo-btn-ghost" type="submit" style="color:#b91c1c">Supprimer le compte</button>
    </form>
  </div>
</div>
<div class="wo-card" style="margin-top:16px">
  <b>Ventes</b>
  {if $sales|@count == 0 && $filter_q == ''}
    <div class="wo-empty">Aucune vente</div>
  {else}
  <form method="get" class="wo-filters" data-wo-filter>
    {include file="otantik_get_keep.tpl"}
    {if $filter_bq}<input type="hidden" name="bq" value="{$filter_bq|escape}">{/if}
    {include file="otantik_find.tpl" placeholder="Rechercher une vente (zone, forfait, code…)" find_name="q" find_value=$filter_q}
    <span class="wo-filters-meta" data-wo-count></span>
    {include file="otantik_export.tpl" export_csv=$export_sales_csv export_pdf=$export_sales_pdf}
  </form>
  {if $sales|@count == 0}
    <div class="wo-empty"><i class="fa fa-search"></i>Aucune vente ne correspond</div>
  {else}
  <table class="wo-table">
    <thead><tr><th>Date</th><th>Zone</th><th>Forfait</th><th>Code</th><th>Prix</th></tr></thead>
    <tbody>
    {foreach $sales as $s}
      <tr data-wo-row data-wo-hay="{$s.routers|escape} {$s.plan_name|escape} {$s.payment_channel|escape}">
        <td>{$s.paid_date|default:$s.created_date}</td>
        <td>{$s.routers|escape}</td>
        <td>{$s.plan_name|escape}</td>
        <td>{$s.payment_channel|escape}</td>
        <td>{$s.price} F</td>
      </tr>
    {/foreach}
    </tbody>
  </table>
  <div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucune vente ne correspond</div>
  {include file="otantik_pager.tpl"}
  {/if}
  {/if}
</div>
<div class="wo-card" style="margin-top:16px">
  <b>Mouvements comptables</b>
  {if $books|@count == 0 && $filter_bq == ''}
    <div class="wo-empty">Aucun mouvement</div>
  {else}
  <form method="get" class="wo-filters" data-wo-filter>
    {include file="otantik_get_keep.tpl" pager=$pager_books}
    {if $filter_q}<input type="hidden" name="q" value="{$filter_q|escape}">{/if}
    {include file="otantik_find.tpl" placeholder="Filtrer les mouvements…" find_name="bq" find_value=$filter_bq}
    <span class="wo-filters-meta" data-wo-count></span>
    {include file="otantik_export.tpl" export_csv=$export_books_csv export_pdf=$export_books_pdf}
  </form>
  {if $books|@count == 0}
    <div class="wo-empty"><i class="fa fa-search"></i>Aucun mouvement ne correspond</div>
  {else}
  <table class="wo-table">
    <thead><tr><th>Date</th><th>Type</th><th>Caisse</th><th>Votre part</th><th>Commission</th><th>Solde après</th><th>Note</th></tr></thead>
    <tbody>
    {foreach $books as $b}
      <tr data-wo-row data-wo-hay="{$b.kind|escape} {$b.note|escape}">
        <td>{$b.created_at}</td>
        <td>{$b.kind}</td>
        <td>{$b.amount} F</td>
        <td>{$b.client_amount} F</td>
        <td>{$b.commission} F</td>
        <td>{$b.wallet_after} F</td>
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
