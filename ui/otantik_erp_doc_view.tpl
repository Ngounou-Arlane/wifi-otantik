{include file="otantik_shell_head.tpl"}
{if !$print_mode}
{include file="otantik_erp_inc.tpl"}
<div class="wo-head" style="margin-bottom:16px">
  <div>
    <h1 style="margin:0">{$doc.kind_label|escape} {$doc.number|escape}</h1>
    <p style="margin:6px 0 0;color:var(--wo-muted)">{$doc.status_label|escape} · {$doc.created_at|escape}</p>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_erp/documents')}"><i class="fa fa-arrow-left"></i> Liste</a>
    <button type="button" class="wo-btn wo-btn-ghost" onclick="woErpModal('woDocEditModal')"><i class="fa fa-pencil"></i> Modifier</button>
    <a class="wo-btn wo-btn-primary" href="{Text::url('plugin/wifi_otantik_erp/doc/')}{$doc.id}/print" target="_blank"><i class="fa fa-print"></i> Imprimer</a>
  </div>
</div>

<div class="wo-modal" id="woDocEditModal" onclick="if(event.target===this) woErpModalClose('woDocEditModal')">
  <div class="wo-modal-card" style="width:min(820px,100%)">
    <div class="wo-modal-h">
      <h2 style="margin:0">Modifier {$doc.kind_label|escape} {$doc.number|escape}</h2>
      <a href="#" onclick="woErpModalClose('woDocEditModal');return false;" class="wo-iconbtn">&times;</a>
    </div>
    <form method="post" action="{Text::url('plugin/wifi_otantik_erp/doc_update/')}{$doc.id}" id="woErpEditDocForm" style="margin-top:16px">
      <div class="wo-erp-form-grid">
        <p><label class="wo-label">Date d’émission</label>
        <input class="wo-input" type="date" name="issue_date" value="{$doc.issue_date|escape}" required></p>
        <p><label class="wo-label">Validité (devis)</label>
        <input class="wo-input" type="date" name="valid_until" value="{$doc.valid_until|escape}"></p>
        <p><label class="wo-label">Client <span class="wo-req">*</span></label>
        <input class="wo-input" name="client_name" value="{$doc.client_name|escape}" required></p>
        <p><label class="wo-label">Téléphone</label>
        <input class="wo-input" name="client_phone" value="{$doc.client_phone|escape}"></p>
        <p class="span2"><label class="wo-label">Email</label>
        <input class="wo-input" type="email" name="client_email" value="{$doc.client_email|escape}"></p>
        <p class="span2"><label class="wo-label">Objet</label>
        <input class="wo-input" name="doc_subject" value="{$doc.subject|escape}" placeholder="Ex. Développement application"></p>
        <p><label class="wo-label">Taxe (FCFA)</label>
        <input class="wo-input" type="number" name="doc_tax" value="{$doc.tax|string_format:'%.0f'}" min="0"></p>
      </div>
      <b style="display:block;margin:16px 0 8px">Lignes de prestation</b>
      <div id="woErpEditLines">
        {foreach $doc.lines as $ln}
        <div class="wo-erp-form-grid wo-erp-line" style="margin-bottom:8px">
          <p class="span2"><input class="wo-input" name="line_label[]" value="{$ln.label|escape}" placeholder="Description" required></p>
          <p><input class="wo-input" name="line_period[]" value="{$ln.period|escape}" placeholder="Période (ex. 1 mois)"></p>
          <p><input class="wo-input" type="number" name="line_qty[]" value="{$ln.qty}" min="1" placeholder="Qté"></p>
          <p class="span2"><input class="wo-input" type="number" name="line_price[]" value="{$ln.price|string_format:'%.0f'}" min="0" placeholder="Prix unitaire (FCFA)" required></p>
          <p class="span2" style="text-align:right;margin:0"><button type="button" class="wo-btn wo-btn-ghost wo-erp-line-remove" style="padding:4px 10px;font-size:12px">Retirer cette ligne</button></p>
        </div>
        {/foreach}
      </div>
      <button type="button" class="wo-btn wo-btn-ghost" id="woErpEditAddLine"><i class="fa fa-plus"></i> Ajouter une ligne</button>
      <div style="text-align:right;margin-top:14px">
        <button class="wo-btn wo-btn-primary" type="submit">Enregistrer les modifications</button>
      </div>
    </form>
  </div>
</div>
{literal}
<script>
(function(){
  var box=document.getElementById('woErpEditLines');
  var btn=document.getElementById('woErpEditAddLine');
  if(!box||!btn) return;
  function wireRemove(row){
    var rm=row.querySelector('.wo-erp-line-remove');
    if(!rm) return;
    rm.addEventListener('click', function(){
      if(box.querySelectorAll('.wo-erp-line').length<=1) return;
      row.remove();
    });
  }
  Array.prototype.forEach.call(box.querySelectorAll('.wo-erp-line'), wireRemove);
  btn.addEventListener('click', function(){
    var row=document.createElement('div');
    row.className='wo-erp-form-grid wo-erp-line';
    row.style.marginBottom='8px';
    row.innerHTML='<p class="span2"><input class="wo-input" name="line_label[]" placeholder="Description" required></p>'+
      '<p><input class="wo-input" name="line_period[]" placeholder="Période"></p>'+
      '<p><input class="wo-input" type="number" name="line_qty[]" value="1" min="1"></p>'+
      '<p class="span2"><input class="wo-input" type="number" name="line_price[]" min="0" placeholder="Prix unitaire (FCFA)" required></p>'+
      '<p class="span2" style="text-align:right;margin:0"><button type="button" class="wo-btn wo-btn-ghost wo-erp-line-remove" style="padding:4px 10px;font-size:12px">Retirer cette ligne</button></p>';
    box.appendChild(row);
    wireRemove(row);
  });
})();
</script>
{/literal}
{/if}

<article class="wo-expense-report{if $print_mode} is-print{/if}">
  <header class="wo-expense-report-head">
    <div class="wo-expense-report-brand">
      <img src="{$app_url}/ui/ui_custom/images/logo.png" alt="">
      <div>
        <b>{$doc.company|escape}</b>
        {if $doc.company_niu}<small>NIU · {$doc.company_niu|escape}</small>{/if}
        {if $doc.company_phone}<small>{$doc.company_phone|escape}</small>{/if}
      </div>
    </div>
    <div class="wo-expense-report-meta">
      <span>{$doc.kind_label|upper|escape}</span>
      <strong>{$doc.number|escape}</strong>
      <small>Date · {$doc.issue_date|escape}</small>
    </div>
  </header>

  <section class="wo-expense-report-intro">
    <h1>{if $doc.subject}{$doc.subject|escape}{else}{$doc.kind_label|escape}{/if}</h1>
    <p>{$doc.kind_label|escape} émis pour <b>{$doc.client_name|escape}</b>{if $doc.client_phone} · {$doc.client_phone|escape}{/if}{if $doc.client_email} · {$doc.client_email|escape}{/if}.</p>
    {if $doc.valid_until}<p style="margin-top:5px">Valide jusqu’au {$doc.valid_until|escape}</p>{/if}
  </section>

  <table class="wo-expense-report-table">
    <thead><tr><th>Désignation</th><th>Période</th><th>Quantité</th><th>Montant (FCFA)</th></tr></thead>
    <tbody>
    {foreach $doc.lines as $ln}
      <tr>
        <td><strong>{$ln.label|escape}</strong></td>
        <td>{if $ln.period}{$ln.period|escape}{else}—{/if}</td>
        <td>{$ln.qty}</td>
        <td><b>{$ln.total|string_format:"%.0f"} F</b></td>
      </tr>
    {/foreach}
    </tbody>
  </table>
  <div class="wo-expense-report-total">
    <span>{if $doc.tax > 0}SOUS-TOTAL {$doc.subtotal|string_format:"%.0f"} + TAXE {$doc.tax|string_format:"%.0f"}{else}TOTAL{/if}</span>
    <b>{$doc.total|string_format:"%.0f"} <small>FCFA</small></b>
  </div>
  <footer class="wo-expense-report-foot">
    <span>Merci de votre confiance</span>
    <span>{$doc.company|escape}{if $doc.company_phone} · {$doc.company_phone|escape}{/if}</span>
  </footer>
</article>

{if !$print_mode}
<div class="wo-card" style="max-width:780px;margin:16px auto;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
  <b style="margin-right:8px">Actions</b>
  {if $doc.status == 'draft'}
  <form method="post" action="{Text::url('plugin/wifi_otantik_erp/doc_status/')}{$doc.id}" style="display:inline">
    <input type="hidden" name="status" value="sent">
    <button class="wo-btn wo-btn-primary" type="submit">Marquer envoyé</button>
  </form>
  {/if}
  {if $doc.kind == 'quote' && $doc.status != 'cancelled' && $doc.status != 'accepted'}
  <form method="post" action="{Text::url('plugin/wifi_otantik_erp/doc_to_invoice/')}{$doc.id}" style="display:inline">
    <button class="wo-btn wo-btn-ghost" type="submit">Convertir en facture</button>
  </form>
  <form method="post" action="{Text::url('plugin/wifi_otantik_erp/doc_status/')}{$doc.id}" style="display:inline">
    <input type="hidden" name="status" value="paid">
    <button class="wo-btn wo-btn-primary" type="submit">Accepter &amp; encaisser</button>
  </form>
  {/if}
  {if $doc.kind == 'invoice' && $doc.status != 'paid' && $doc.status != 'cancelled'}
  <form method="post" action="{Text::url('plugin/wifi_otantik_erp/doc_status/')}{$doc.id}" style="display:inline">
    <input type="hidden" name="status" value="paid">
    <button class="wo-btn wo-btn-primary" type="submit">Marquer payée (entrée trésorerie)</button>
  </form>
  {/if}
  {if $doc.status != 'cancelled' && $doc.status != 'paid'}
  <form method="post" action="{Text::url('plugin/wifi_otantik_erp/doc_status/')}{$doc.id}" style="display:inline" onsubmit="return confirm('Annuler ce document ?');">
    <input type="hidden" name="status" value="cancelled">
    <button class="wo-btn wo-btn-danger" type="submit">Annuler</button>
  </form>
  {/if}
  <form method="post" action="{Text::url('plugin/wifi_otantik_erp/doc_delete/')}{$doc.id}" style="display:inline;margin-left:auto" onsubmit="return confirm('Supprimer définitivement {$doc.number|escape:'javascript'} ? Cette action est irréversible.');">
    <input type="hidden" name="confirm_delete" value="1">
    <button class="wo-btn wo-btn-danger" type="submit"><i class="fa fa-trash"></i> Supprimer</button>
  </form>
</div>
</div>
<script src="{$app_url}/ui/ui_custom/otantik-erp.js?v=20260820erp4"></script>
{/if}
{if $print_mode}
{literal}<script>window.addEventListener('load',function(){window.print();});</script>{/literal}
{/if}
{include file="otantik_shell_foot.tpl"}
