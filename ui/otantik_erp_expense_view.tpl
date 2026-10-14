{include file="otantik_shell_head.tpl"}
{if !$print_mode}
{include file="otantik_erp_inc.tpl"}
<div class="wo-head no-print">
  <div>
    <h1>Rapport de dépense</h1>
    <p>{$expense.number|escape} · {$expense.date|escape}</p>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_erp/expenses')}"><i class="fa fa-arrow-left"></i> Dépenses</a>
    <a class="wo-btn wo-btn-primary" href="{Text::url('plugin/wifi_otantik_erp/report/')}{$expense.id}/print" target="_blank"><i class="fa fa-print"></i> Imprimer / PDF</a>
  </div>
</div>
{/if}

<article class="wo-expense-report{if $print_mode} is-print{/if}">
  <header class="wo-expense-report-head">
    <div class="wo-expense-report-brand">
      <img src="{$app_url}/ui/ui_custom/images/logo.png" alt="">
      <div>
        <b>{$expense.company|escape}</b>
        {if $expense.company_niu}<small>NIU · {$expense.company_niu|escape}</small>{/if}
        {if $expense.company_phone}<small>{$expense.company_phone|escape}</small>{/if}
      </div>
    </div>
    <div class="wo-expense-report-meta">
      <span>RAPPORT DE DÉPENSE</span>
      <strong>{$expense.number|escape}</strong>
      <small>Date · {$expense.date|escape}</small>
    </div>
  </header>

  <section class="wo-expense-report-intro">
    <h1>{$expense.title|escape}</h1>
    <p>Rapport émis pour justifier et tracer cette sortie de trésorerie.</p>
  </section>

  <table class="wo-expense-report-table">
    <thead>
      <tr><th>Désignation</th><th>Catégorie</th><th>Mode de paiement</th><th>Montant (FCFA)</th></tr>
    </thead>
    <tbody>
      <tr>
        <td>
          <strong>{$expense.title|escape}</strong>
          {if $expense.vendor}<small>Fournisseur / bénéficiaire · {$expense.vendor|escape}</small>{/if}
          {if $expense.note}<small>{$expense.note|escape|nl2br nofilter}</small>{/if}
        </td>
        <td>{$expense.category|escape}</td>
        <td>{if $expense.payment_method}{$expense.payment_method|escape}{else}—{/if}</td>
        <td><b>{$expense.amount|string_format:"%.0f"}</b></td>
      </tr>
    </tbody>
  </table>

  <div class="wo-expense-report-total">
    <span>TOTAL SORTIE</span>
    <b>{$expense.amount|string_format:"%.0f"} <small>FCFA</small></b>
  </div>

  <section class="wo-expense-evidence">
    <div class="wo-expense-evidence-h">
      <div>
        <span>PIÈCES JUSTIFICATIVES</span>
        <b>{$expense.files|@count} fichier{if $expense.files|@count != 1}s{/if}</b>
      </div>
      {if !$print_mode}
      <form method="post" action="{Text::url('plugin/wifi_otantik_erp/expense_file/')}{$expense.id}" enctype="multipart/form-data">
        <label class="wo-btn wo-btn-ghost">
          <i class="fa fa-paperclip"></i> Ajouter des pièces
          <input type="file" name="expense_files[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp,.gif" hidden onchange="this.form.submit()">
        </label>
      </form>
      {/if}
    </div>
    {if $expense.files|@count == 0}
      <p class="wo-muted">Aucune pièce jointe à ce rapport.</p>
    {else}
      <div class="wo-evidence-grid">
      {foreach $expense.files as $f}
        <a class="wo-evidence-file" href="{Text::url('plugin/wifi_otantik_erp/file/')}{$f.id}" target="_blank">
          <span><i class="fa fa-file-o"></i></span>
          <div>
            <b>{if $f.original_name}{$f.original_name|escape}{else}{$f.filename|escape}{/if}</b>
            <small>Pièce justificative</small>
          </div>
        </a>
      {/foreach}
      </div>
    {/if}
  </section>

  <footer class="wo-expense-report-foot">
    <span>Document comptable généré par Wifi-Otantik ERP</span>
    <span>{$expense.company|escape}{if $expense.company_phone} · {$expense.company_phone|escape}{/if}</span>
  </footer>
</article>

{if !$print_mode}</div>{/if}
{if $print_mode}
{literal}<script>window.addEventListener('load',function(){window.print();});</script>{/literal}
{/if}
{include file="otantik_shell_foot.tpl"}
