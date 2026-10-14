{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Reçus</h1>
    <p>Preuves de paiement · {$total|string_format:"%.0f"} FCFA encaissés</p>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    {if $sales_tabs}
    <div class="wo-seg">
      <a href="{Text::url('plugin/wifi_otantik_tickets')}">Tickets</a>
      <a href="{Text::url('plugin/wifi_otantik_sales')}">Ventes</a>
      <a class="on" href="{Text::url('plugin/wifi_otantik_receipts')}">Reçus</a>
    </div>
    {/if}
  </div>
</div>

<div class="wo-rx-hero">
  <div class="wo-rx-hero-glow"></div>
  <div class="wo-rx-orb wo-rx-orb-a"></div>
  <div class="wo-rx-orb wo-rx-orb-b"></div>
  <div class="wo-rx-hero-copy">
    <span class="wo-rx-pill"><i class="fa fa-file-text-o"></i> Collection premium</span>
    <h2>Vos encaissements, en un regard</h2>
    <p>Chaque paiement validé génère un reçu imprimable — numéro, forfait, zone, moyen de paiement.</p>
    <div class="wo-rx-legend">
      <span class="wo-rx-leg mtn">MTN</span>
      <span class="wo-rx-leg orange">Orange</span>
      <span class="wo-rx-leg cash">Espèces</span>
    </div>
  </div>
  <div class="wo-rx-hero-stat">
    <b>{$count_paid|default:0}</b>
    <span>reçus payés</span>
  </div>
</div>

<div class="wo-card">
<form method="get" class="wo-filters" data-wo-filter>
  {include file="otantik_get_keep.tpl"}
  {include file="otantik_find.tpl" placeholder="N° reçu, tél, forfait, zone…" find_name="q" find_value=$filter_q}
  <select class="wo-select" name="ty" data-wo-ty onchange="this.form.submit()">
    <option value="">Tous les moyens</option>
    <option value="MTN"{if $filter_ty == 'MTN'} selected{/if}>MTN</option>
    <option value="ORANGE"{if $filter_ty == 'ORANGE'} selected{/if}>Orange</option>
    <option value="CASH"{if $filter_ty == 'CASH'} selected{/if}>Espèces</option>
  </select>
  <span class="wo-filters-meta" data-wo-count></span>
</form>

{if $rows|@count == 0}
  <div class="wo-rx-empty">
    <div class="wo-rx-empty-art" aria-hidden="true">
      <span></span><span></span><span></span>
    </div>
    <h3>Aucun reçu pour le moment</h3>
    <p>Dès qu’une vente est payée (Mobile Money ou espèces), le reçu apparaît ici — prêt à imprimer.</p>
    <a class="wo-btn wo-btn-primary" href="{Text::url('plugin/wifi_otantik_tickets')}"><i class="fa fa-ticket"></i> Émettre un ticket</a>
  </div>
{else}
<div class="wo-rx-grid">
{foreach from=$rows item=r name=rx}
  <article class="wo-rx-card wo-rx-anim is-{$r.tone|escape}" data-wo-row data-wo-type="{$r.method_raw|escape}" data-wo-hay="{$r.number|escape} {$r.phone|escape} {$r.plan|escape} {$r.zone|escape} {$r.method|escape}" style="--wo-rx-i:{$smarty.foreach.rx.index}">
    <div class="wo-rx-card-ribbon">{$r.kind|escape}</div>
    <div class="wo-rx-card-top">
      <div>
        <span class="wo-rx-no">{$r.number|escape}</span>
        <small>{$r.date|escape}</small>
      </div>
      <span class="wo-chip ok">Payé</span>
    </div>
    <div class="wo-rx-card-mid">
      <div class="wo-rx-amount">{$r.amount|string_format:"%.0f"} <span>FCFA</span></div>
      <div class="wo-rx-meta">
        <div><i class="fa fa-tag"></i> {$r.plan|escape}</div>
        <div><i class="fa fa-map-marker"></i> {$r.zone|escape}</div>
        <div><i class="fa fa-phone"></i> {$r.phone|escape}</div>
      </div>
    </div>
    <div class="wo-rx-card-bot">
      <span class="wo-rx-method">{$r.method|escape}</span>
      <div class="wo-rx-actions">
        <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_receipts/view/')}{$r.id}"><i class="fa fa-eye"></i> Voir</a>
        <a class="wo-btn wo-btn-primary" href="{Text::url('plugin/wifi_otantik_receipts/print/')}{$r.id}" target="_blank"><i class="fa fa-print"></i> Imprimer</a>
      </div>
    </div>
  </article>
{/foreach}
</div>
<div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucun résultat</div>
{include file="otantik_pager.tpl"}
{/if}
</div>
{include file="otantik_shell_foot.tpl"}
