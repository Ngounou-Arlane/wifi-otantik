{include file="otantik_shell_head.tpl"}
{include file="otantik_erp_inc.tpl"}

<div class="wo-erp-grid">
  <div class="wo-erp-card wo-erp-hero wo-erp-reveal">
    <div>
      <span class="wo-erp-live"><i></i> Trésorerie en direct</span>
      <h1>ERP Otantik</h1>
      <p>Entrées, sorties et marge nette — synchronisées avec vos ventes Wi‑Fi et retraits.</p>
      <div class="wo-erp-seg" style="margin-top:14px">
        <a class="{if $range == 'today'}on{/if}" href="{Text::url('plugin/wifi_otantik_erp')}&range=today">Jour</a>
        <a class="{if $range == 'week'}on{/if}" href="{Text::url('plugin/wifi_otantik_erp')}&range=week">Semaine</a>
        <a class="{if $range == 'month'}on{/if}" href="{Text::url('plugin/wifi_otantik_erp')}&range=month">Mois</a>
        <a class="{if $range == 'year'}on{/if}" href="{Text::url('plugin/wifi_otantik_erp')}&range=year">Année</a>
      </div>
    </div>
    <div class="wo-erp-hero-net {if $net >= 0}positive{else}negative{/if}">
      <span data-erp-count="{$net|string_format:'%.0f'}">0</span> <small>FCFA net</small>
      <div style="font-size:13px;font-weight:600;margin-top:8px;opacity:.85">Marge {$margin_pct}%</div>
    </div>
  </div>

  <div class="wo-erp-card wo-erp-span wo-erp-reveal wo-add-card" id="woIncomeForm">
    <div class="wo-add-card-h">
      <div class="wo-expense-title-icon" style="width:44px;height:44px;flex:0 0 44px;font-size:18px;background:linear-gradient(135deg,#002858,#f05018)"><i class="fa fa-plus"></i></div>
      <div>
        <b>Autre revenu</b>
        <small>Impression, cyber, boutique, dépannage… — visible sur le tableau de bord Pro.</small>
      </div>
    </div>
    <div class="wo-add-card-actions">
      <button type="button" class="wo-btn wo-btn-primary" onclick="woErpModal('woIncomeModal')"><i class="fa fa-plus"></i> Ajouter un revenu</button>
    </div>
  </div>

  <div class="wo-modal" id="woIncomeModal" onclick="if(event.target===this) woErpModalClose('woIncomeModal')">
    <div class="wo-modal-card" style="width:min(720px,100%)">
      <div class="wo-modal-h">
        <div class="wo-expense-title" style="margin-bottom:0">
          <div class="wo-expense-title-icon" style="background:linear-gradient(135deg,#002858,#f05018)"><i class="fa fa-plus"></i></div>
          <div>
            <span>CA RÉEL</span>
            <h1 style="margin:0;font-size:20px">Enregistrer un autre revenu</h1>
          </div>
        </div>
        <a href="#" onclick="woErpModalClose('woIncomeModal');return false;" class="wo-iconbtn">&times;</a>
      </div>
      <form method="post" action="{Text::url('plugin/wifi_otantik_erp/income')}" style="margin-top:16px">
        <div class="wo-erp-form-grid">
          <p class="span2"><label class="wo-label">Libellé <span class="wo-req">*</span></label>
          <input class="wo-input" name="income_label" required placeholder="Ex. Impression A4, recharge cyber, vente boisson…"></p>
          <p><label class="wo-label">Date <span class="wo-req">*</span></label>
          <input class="wo-input" type="date" name="income_date" value="{$smarty.now|date_format:'%Y-%m-%d'}" max="{$smarty.now|date_format:'%Y-%m-%d'}" required></p>
          <p><label class="wo-label">Montant (FCFA) <span class="wo-req">*</span></label>
          <input class="wo-input" type="number" name="income_amount" min="1" required></p>
          <p><label class="wo-label">Catégorie</label>
          <select class="wo-select" name="income_category">
            <option value="impression">Impression / photocopie</option>
            <option value="cyber">Cyber / jeux</option>
            <option value="boutique">Boutique / boissons</option>
            <option value="installation">Installation / dépannage</option>
            <option value="services">Autre service</option>
          </select></p>
          <p><label class="wo-label">Mode d’encaissement</label>
          <select class="wo-select" name="income_method">
            <option value="Espèces">Espèces</option>
            <option value="MTN MoMo">MTN MoMo</option>
            <option value="Orange Money">Orange Money</option>
            <option value="Virement">Virement</option>
          </select></p>
          <p class="span2"><label class="wo-label">Note</label>
          <input class="wo-input" name="income_note" placeholder="Optionnel"></p>
        </div>
        <div style="text-align:right;margin-top:12px">
          <button class="wo-btn wo-btn-primary" type="submit"><i class="fa fa-check"></i> Ajouter au CA</button>
        </div>
      </form>
    </div>
  </div>

  <div class="wo-erp-card wo-erp-kpi wo-erp-s3 wo-erp-reveal">
    <div>
      <span>Entrées</span>
      <h3><span data-erp-count="{$in_total|string_format:'%.0f'}">0</span> <small style="font-size:14px;font-weight:600">F</small></h3>
      <small style="color:var(--wo-muted)">Wi‑Fi {$wifi_in|default:0|string_format:"%.0f"} · Autres {$other_in|default:0|string_format:"%.0f"}</small>
    </div>
    <div class="wo-erp-ico in"><i class="fa fa-arrow-down"></i></div>
  </div>
  <div class="wo-erp-card wo-erp-kpi wo-erp-s3 wo-erp-reveal">
    <div>
      <span>Sorties</span>
      <h3><span data-erp-count="{$out_total|string_format:'%.0f'}">0</span> <small style="font-size:14px;font-weight:600">F</small></h3>
      <small style="color:var(--wo-muted)">Retraits &amp; dépenses</small>
    </div>
    <div class="wo-erp-ico out"><i class="fa fa-arrow-up"></i></div>
  </div>
  <div class="wo-erp-card wo-erp-kpi wo-erp-s3 wo-erp-reveal">
    <div>
      <span>Dépenses</span>
      <h3><span data-erp-count="{$expenses|string_format:'%.0f'}">0</span> <small style="font-size:14px;font-weight:600">F</small></h3>
      <a class="wo-btn wo-btn-ghost" style="margin-top:6px;padding:6px 10px;font-size:11px" href="{Text::url('plugin/wifi_otantik_erp/expenses')}">Gérer</a>
    </div>
    <div class="wo-erp-ico out"><i class="fa fa-credit-card"></i></div>
  </div>
  <div class="wo-erp-card wo-erp-kpi wo-erp-s3 wo-erp-reveal">
    <div>
      <span>Devis en attente</span>
      <h3>{$pending_quotes|default:0}</h3>
      <a class="wo-btn wo-btn-ghost" style="margin-top:6px;padding:6px 10px;font-size:11px" href="{Text::url('plugin/wifi_otantik_erp/documents')}">Documents</a>
    </div>
    <div class="wo-erp-ico doc"><i class="fa fa-file-text"></i></div>
  </div>

  <div class="wo-erp-card wo-erp-s8 wo-erp-reveal">
    <div class="wo-card-h" style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
      <div>
        <b style="font-size:16px">Flux · Connexion vs Autres</b>
        <div class="wo-muted" style="font-size:12px;margin-top:4px">Barres bleues = Wi‑Fi · orange = autres services</div>
      </div>
      <span class="wo-erp-live"><i></i> Live</span>
    </div>
    <div class="wo-erp-chart-wrap">
      <canvas id="woErpChart" data-series="{$series_json nofilter}" height="200"></canvas>
    </div>
    {if $chart_svg}<div style="margin-top:-200px;opacity:0;height:0;overflow:hidden">{$chart_svg nofilter}</div>{/if}
  </div>

  <div class="wo-erp-card wo-erp-s4 wo-erp-reveal">
    <b style="font-size:16px">Répartition</b>
    <div class="wo-erp-donut">
      <div class="wo-erp-donut-ring" data-in="{$in_total|string_format:'%.0f'}" data-out="{$out_total|string_format:'%.0f'}" data-label="—"></div>
      <div class="wo-erp-legend">
        <div><i class="in"></i> Entrées · <b data-erp-count="{$in_total|string_format:'%.0f'}">0</b> F</div>
        <div><i class="out"></i> Sorties · <b data-erp-count="{$out_total|string_format:'%.0f'}">0</b> F</div>
        <div style="border:0;padding-top:12px;font-weight:700;color:var(--erp-net)">Net · <span data-erp-count="{$net|string_format:'%.0f'}">0</span> F</div>
      </div>
    </div>
  </div>

  <div class="wo-erp-card wo-erp-s8 wo-erp-reveal">
    <div class="wo-card-h" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
      <b style="font-size:16px">Mouvements récents</b>
      <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_erp/flow')}">Tout voir</a>
    </div>
    <div class="wo-erp-feed">
      {if $recent|@count == 0}
        <div class="wo-empty"><i class="fa fa-line-chart"></i>Aucun flux — lancez une synchronisation</div>
      {else}
        {foreach $recent as $r name=feed}
        <div class="wo-erp-flow-row" style="--i:{$smarty.foreach.feed.iteration}">
          <div class="wo-erp-flow-dot {if $r.direction == 'in'}in{else}out{/if}">
            {if $r.direction == 'in'}+{else}−{/if}
          </div>
          <div class="wo-erp-flow-body">
            <b>{$r.label|escape}</b>
            <small>{$r.category|escape} · {$r.when|escape}</small>
          </div>
          <div style="text-align:right">
            <div class="wo-erp-flow-amt {if $r.direction == 'in'}in{else}out{/if}">
              {if $r.direction == 'in'}+{else}−{/if}{$r.amount|string_format:"%.0f"} F
            </div>
            {if $r.ref_type == 'payment' && $r.ref_id > 0}
              <a style="font-size:11px" href="{Text::url('plugin/wifi_otantik_receipts/view/')}{$r.ref_id}">Reçu</a>
            {elseif $r.ref_type == 'invoice' && $r.ref_id > 0}
              <a style="font-size:11px" href="{Text::url('plugin/wifi_otantik_erp/doc/')}{$r.ref_id}">Facture</a>
            {elseif $r.direction == 'out' && $r.expense_id > 0}
              <a style="font-size:11px" href="{Text::url('plugin/wifi_otantik_erp/report/')}{$r.expense_id}">Rapport</a>
            {/if}
          </div>
        </div>
        {/foreach}
      {/if}
    </div>
  </div>

  <div class="wo-erp-card wo-erp-s4 wo-erp-reveal">
    <b style="font-size:16px">Charges récurrentes</b>
    {if $recurring|@count == 0}
      <p class="wo-muted" style="font-size:13px;margin:16px 0">Aucune charge fixe — ajoutez loyer, internet, salaires…</p>
      <a class="wo-btn wo-btn-primary" href="{Text::url('plugin/wifi_otantik_erp/expenses')}"><i class="fa fa-plus"></i> Ajouter</a>
    {else}
      <ul style="list-style:none;margin:16px 0 0;padding:0">
        {foreach $recurring as $rec}
        <li style="padding:10px 0;border-bottom:1px dashed var(--wo-line);font-size:13px">
          <b>{$rec.title|escape}</b><br>
          <span class="wo-muted">{$rec.amount|string_format:"%.0f"} F · jour {$rec.day_of_month}</span>
        </li>
        {/foreach}
      </ul>
    {/if}
  </div>
</div>
</div>
<script src="{$app_url}/ui/ui_custom/otantik-erp.js?v=20260820erp4"></script>
{literal}
<script>
if (location.hash === '#woIncomeForm') {
  history.replaceState(null, '', location.pathname + location.search);
  document.addEventListener('DOMContentLoaded', function(){ if(window.woErpModal) woErpModal('woIncomeModal'); });
}
</script>
{/literal}
{include file="otantik_shell_foot.tpl"}
