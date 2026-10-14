{include file="otantik_shell_head.tpl"}
{include file="otantik_erp_inc.tpl"}

<div class="wo-erp-grid">
  <div class="wo-erp-card wo-erp-span wo-add-card">
    <div class="wo-add-card-h">
      <div class="wo-expense-title-icon" style="width:44px;height:44px;flex:0 0 44px;font-size:18px;background:linear-gradient(135deg,#002858,#f05018)"><i class="fa fa-file-text-o"></i></div>
      <div>
        <b>Devis & factures</b>
        <small>Créez un devis ou une facture pour un client.</small>
      </div>
    </div>
    <div class="wo-add-card-actions">
      <button type="button" class="wo-btn wo-btn-primary" onclick="woErpModal('woDocModal')"><i class="fa fa-plus"></i> Nouveau devis / facture</button>
    </div>
  </div>

  <div class="wo-modal" id="woDocModal" onclick="if(event.target===this) woErpModalClose('woDocModal')">
    <div class="wo-modal-card" style="width:min(820px,100%)">
      <div class="wo-modal-h">
        <h2 style="margin:0">Nouveau devis / facture</h2>
        <a href="#" onclick="woErpModalClose('woDocModal');return false;" class="wo-iconbtn">&times;</a>
      </div>
      <form method="post" action="{Text::url('plugin/wifi_otantik_erp/document')}" id="woErpDocForm" style="margin-top:16px">
        <div class="wo-erp-form-grid">
          <p><label class="wo-label">Type</label>
          <select class="wo-select" name="doc_kind">
            <option value="quote">Devis</option>
            <option value="invoice">Facture</option>
          </select></p>
          <p><label class="wo-label">Date d’émission</label>
          <input class="wo-input" type="date" name="issue_date" value="{$smarty.now|date_format:'%Y-%m-%d'}" required></p>
          <p><label class="wo-label">Validité (devis)</label>
          <input class="wo-input" type="date" name="valid_until"></p>
          <p><label class="wo-label">Client <span class="wo-req">*</span></label>
          <input class="wo-input" name="client_name" required></p>
          <p><label class="wo-label">Téléphone</label>
          <input class="wo-input" name="client_phone"></p>
          <p class="span2"><label class="wo-label">Email</label>
          <input class="wo-input" type="email" name="client_email"></p>
          <p class="span2"><label class="wo-label">Objet</label>
          <input class="wo-input" name="doc_subject" placeholder="Ex. Développement application"></p>
          <p><label class="wo-label">Taxe (FCFA)</label>
          <input class="wo-input" type="number" name="doc_tax" value="0" min="0"></p>
        </div>
        <b style="display:block;margin:16px 0 8px">Lignes de prestation</b>
        <div id="woErpLines">
          <div class="wo-erp-form-grid wo-erp-line" style="margin-bottom:8px">
            <p class="span2"><input class="wo-input" name="line_label[]" placeholder="Description" required></p>
            <p><input class="wo-input" name="line_period[]" placeholder="Période (ex. 1 mois)"></p>
            <p><input class="wo-input" type="number" name="line_qty[]" value="1" min="1" placeholder="Qté"></p>
            <p class="span2"><input class="wo-input" type="number" name="line_price[]" min="0" placeholder="Prix unitaire (FCFA)" required></p>
            <p class="span2" style="text-align:right;margin:0"><button type="button" class="wo-btn wo-btn-ghost wo-erp-line-remove" style="padding:4px 10px;font-size:12px">Retirer cette ligne</button></p>
          </div>
        </div>
        <button type="button" class="wo-btn wo-btn-ghost" id="woErpAddLine"><i class="fa fa-plus"></i> Ajouter une ligne</button>
        <div style="text-align:right;margin-top:14px">
          <button class="wo-btn wo-btn-primary" type="submit">Créer le document</button>
        </div>
      </form>
    </div>
  </div>

  <div class="wo-erp-card wo-erp-span">
    <b style="font-size:16px;margin-bottom:12px;display:block">Documents</b>
    {if $docs|@count == 0}
      <div class="wo-empty"><i class="fa fa-file-text-o"></i>Aucun document</div>
    {else}
    <table class="wo-erp-table">
      <thead><tr><th>N°</th><th>Client</th><th>Total</th><th>Statut</th><th></th></tr></thead>
      <tbody>
        {foreach $docs as $d}
        <tr>
          <td>
            <span class="wo-erp-doc-chip {if $d.kind == 'invoice'}invoice{else}quote{/if}">{if $d.kind == 'invoice'}FAC{else}DEV{/if}</span>
            <b>{$d.number|escape}</b>
          </td>
          <td>{$d.client_name|escape}<br><small class="wo-muted">{$d.subject|escape|truncate:40}</small></td>
          <td><b>{$d.total|string_format:"%.0f"} F</b></td>
          <td>
            <span class="wo-erp-doc-chip {if $d.status == 'paid' || $d.status == 'accepted'}invoice{elseif $d.status == 'cancelled'}draft{else}quote{/if}">
              {if $d.status == 'draft'}Brouillon
              {elseif $d.status == 'sent'}Envoyé
              {elseif $d.status == 'accepted'}Accepté
              {elseif $d.status == 'paid'}Payé
              {elseif $d.status == 'cancelled'}Annulé
              {else}{$d.status|escape}{/if}
            </span>
          </td>
          <td style="white-space:nowrap">
            <a class="wo-btn wo-btn-ghost" style="padding:6px 10px" href="{Text::url('plugin/wifi_otantik_erp/doc/')}{$d.id}">Ouvrir</a>
            <form method="post" action="{Text::url('plugin/wifi_otantik_erp/doc_delete/')}{$d.id}" style="display:inline" onsubmit="return confirm('Supprimer définitivement {$d.number|escape:'javascript'} ?');">
              <input type="hidden" name="confirm_delete" value="1">
              <button type="submit" style="background:none;border:none;padding:0 0 0 8px;color:#b91c1c;text-decoration:underline;cursor:pointer;font:inherit;font-size:13px">Suppr.</button>
            </form>
          </td>
        </tr>
        {/foreach}
      </tbody>
    </table>
    {/if}
  </div>
</div>
</div>
{literal}
<script>
(function(){
  var box=document.getElementById('woErpLines');
  var btn=document.getElementById('woErpAddLine');
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
<script src="{$app_url}/ui/ui_custom/otantik-erp.js?v=20260820erp4"></script>
{include file="otantik_shell_foot.tpl"}
