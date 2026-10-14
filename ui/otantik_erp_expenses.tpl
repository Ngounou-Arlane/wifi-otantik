{include file="otantik_shell_head.tpl"}
{include file="otantik_erp_inc.tpl"}

<div class="wo-erp-grid">
  <div class="wo-erp-card wo-erp-span wo-add-card">
    <div class="wo-add-card-h">
      <div class="wo-expense-title-icon" style="width:44px;height:44px;flex:0 0 44px;font-size:18px"><i class="fa fa-file-text-o"></i></div>
      <div>
        <b>Dépenses & charges</b>
        <small>Enregistrez une sortie ponctuelle ou une charge fixe mensuelle.</small>
      </div>
    </div>
    <div class="wo-add-card-actions">
      {include file="otantik_export.tpl" export_csv=$export_csv export_pdf=$export_pdf}
      <button type="button" class="wo-btn wo-btn-ghost" onclick="woErpModal('woRecurringModal')"><i class="fa fa-repeat"></i> Charge récurrente</button>
      <button type="button" class="wo-btn wo-btn-primary" onclick="woErpModal('woExpenseModal')"><i class="fa fa-plus"></i> Nouvelle dépense</button>
    </div>
  </div>

  <div class="wo-modal" id="woExpenseModal" onclick="if(event.target===this) woErpModalClose('woExpenseModal')">
    <div class="wo-modal-card wo-expense-composer" style="width:min(820px,100%)">
      <div class="wo-modal-h">
        <div class="wo-expense-title" style="margin-bottom:0">
          <div class="wo-expense-title-icon"><i class="fa fa-file-text-o"></i></div>
          <div>
            <span>NOUVEAU RAPPORT</span>
            <h1>Enregistrer une dépense</h1>
            <p>La sortie de trésorerie et son rapport comptable seront créés ensemble.</p>
          </div>
        </div>
        <a href="#" onclick="woErpModalClose('woExpenseModal');return false;" class="wo-iconbtn">&times;</a>
      </div>
      <form method="post" action="{Text::url('plugin/wifi_otantik_erp/expense')}" enctype="multipart/form-data" id="woExpenseForm" style="margin-top:16px">
        <div class="wo-erp-form-grid">
          <p class="span2"><label class="wo-label">Intitulé <span class="wo-req">*</span></label>
          <input class="wo-input wo-input-lg" name="expense_title" required placeholder="Ex. Achat routeur MikroTik"></p>
          <p><label class="wo-label">Date de la dépense <span class="wo-req">*</span></label>
          <input class="wo-input" type="date" name="expense_date" value="{$smarty.now|date_format:'%Y-%m-%d'}" max="{$smarty.now|date_format:'%Y-%m-%d'}" required></p>
          <p><label class="wo-label">Montant (FCFA) <span class="wo-req">*</span></label>
          <input class="wo-input" type="number" name="expense_amount" min="1" required></p>
          <p><label class="wo-label">Catégorie</label>
          <select class="wo-select" name="expense_category">
            <option value="équipement">Équipement</option>
            <option value="internet">Internet & télécom</option>
            <option value="électricité">Électricité</option>
            <option value="transport">Transport</option>
            <option value="salaires">Salaires</option>
            <option value="loyer">Loyer</option>
            <option value="maintenance">Maintenance</option>
            <option value="fournisseur">Fournisseur</option>
            <option value="autre">Autre</option>
          </select></p>
          <p><label class="wo-label">Mode de paiement</label>
          <select class="wo-select" name="expense_payment_method">
            <option value="Espèces">Espèces</option>
            <option value="MTN MoMo">MTN MoMo</option>
            <option value="Orange Money">Orange Money</option>
            <option value="Virement">Virement bancaire</option>
            <option value="Carte">Carte</option>
            <option value="Wallet">Wallet / retrait</option>
          </select></p>
          <p><label class="wo-label">Fournisseur</label>
          <input class="wo-input" name="expense_vendor" placeholder="Nom du fournisseur ou bénéficiaire"></p>
          <p class="span2"><label class="wo-label">Note</label>
          <textarea class="wo-input" name="expense_note" rows="3" placeholder="Contexte, référence, détail de l’achat…"></textarea></p>
          <div class="span2">
            <label class="wo-label">Pièces justificatives</label>
            <label class="wo-dropzone" id="woExpenseDrop">
              <input type="file" name="expense_files[]" id="woExpenseFiles" accept=".pdf,.jpg,.jpeg,.png,.webp,.gif" multiple>
              <span class="wo-dropzone-art"><i class="fa fa-cloud-upload"></i></span>
              <strong>Déposez vos factures ou reçus ici</strong>
              <small>ou cliquez pour parcourir · PDF, JPG, PNG, WEBP · 8 Mo par fichier</small>
              <em><i class="fa fa-camera"></i> Vous pouvez sélectionner plusieurs images</em>
            </label>
            <div class="wo-upload-preview" id="woExpensePreview"></div>
          </div>
        </div>
        <div class="wo-expense-submit">
          <div><i class="fa fa-link"></i> Une sortie sera liée automatiquement à ce rapport.</div>
          <button class="wo-btn wo-btn-primary" type="submit"><i class="fa fa-check"></i> Créer le rapport de dépense</button>
        </div>
      </form>
    </div>
  </div>

  <div class="wo-modal" id="woRecurringModal" onclick="if(event.target===this) woErpModalClose('woRecurringModal')">
    <div class="wo-modal-card" style="width:min(520px,100%)">
      <div class="wo-modal-h">
        <h2 style="margin:0">Charge récurrente mensuelle</h2>
        <a href="#" onclick="woErpModalClose('woRecurringModal');return false;" class="wo-iconbtn">&times;</a>
      </div>
      <form method="post" action="{Text::url('plugin/wifi_otantik_erp/recurring')}" style="margin-top:16px">
        <div class="wo-erp-form-grid">
          <p class="span2"><label class="wo-label">Libellé</label>
          <input class="wo-input" name="rec_title" required placeholder="Ex. Loyer"></p>
          <p><label class="wo-label">Montant</label>
          <input class="wo-input" type="number" name="rec_amount" min="1" required></p>
          <p><label class="wo-label">Jour (1–28)</label>
          <input class="wo-input" type="number" name="rec_day" min="1" max="28" value="1"></p>
          <p class="span2"><label class="wo-label">Catégorie</label>
          <input class="wo-input" name="rec_category" placeholder="fixe"></p>
        </div>
        <div style="text-align:right;margin-top:12px">
          <button class="wo-btn wo-btn-primary" type="submit">Activer la récurrence</button>
        </div>
      </form>
    </div>
  </div>

  <div class="wo-erp-card wo-erp-s6">
    <b style="font-size:16px;margin-bottom:12px;display:block">Dépenses</b>
    {if $expenses|@count == 0}
      <div class="wo-empty"><i class="fa fa-shopping-cart"></i>Aucune dépense</div>
    {else}
    <table class="wo-erp-table">
      <thead><tr><th>Rapport / Date</th><th>Intitulé</th><th>Montant</th><th>Pièce</th><th></th></tr></thead>
      <tbody>
        {foreach $expenses as $e}
        {assign var=eid value=$e.id}
        <tr>
          <td><a href="{Text::url('plugin/wifi_otantik_erp/report/')}{$e.id}"><b>{$e.number|escape}</b></a><br><small class="wo-muted">{if $e.expense_date}{$e.expense_date|escape}{else}{$e.paid_at|escape}{/if}</small></td>
          <td>
            <b>{$e.title|escape}</b>
            {if $e.vendor}<br><small class="wo-muted">{$e.vendor|escape}</small>{/if}
            {if $e.note}<br><small class="wo-muted">{$e.note|escape|truncate:80}</small>{/if}
          </td>
          <td><span class="wo-erp-flow-amt out">−{$e.amount|string_format:"%.0f"} F</span></td>
          <td>
            {if isset($expense_files[$eid]) && $expense_files[$eid]|@count > 0}
              {foreach $expense_files[$eid] as $f}
                <a href="{Text::url('plugin/wifi_otantik_erp/file/')}{$f.id}" target="_blank">{$f.filename|escape}</a><br>
              {/foreach}
            {/if}
            <form method="post" action="{Text::url('plugin/wifi_otantik_erp/expense_file/')}{$e.id}" enctype="multipart/form-data" style="margin-top:6px">
              <input type="file" name="expense_files[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp,.gif" style="font-size:11px;max-width:150px" onchange="this.form.submit()">
            </form>
          </td>
          <td style="white-space:nowrap">
            <a class="wo-btn wo-btn-ghost" style="padding:6px 10px" href="{Text::url('plugin/wifi_otantik_erp/edit_expense/')}{$e.id}">Modifier</a>
            <form method="post" action="{Text::url('plugin/wifi_otantik_erp/delete_expense/')}{$e.id}" style="display:inline" onsubmit="return confirm('Supprimer définitivement {$e.number|escape:'javascript'} ?');">
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

  <div class="wo-erp-card wo-erp-s6">
    <b style="font-size:16px;margin-bottom:12px;display:block">Charges récurrentes</b>
    {if $recurring|@count == 0}
      <p class="wo-muted" style="font-size:13px">Aucune charge fixe. Elles créent automatiquement une sortie chaque mois.</p>
    {else}
    <table class="wo-erp-table">
      <thead><tr><th>Libellé</th><th>Montant</th><th>Jour</th><th>État</th><th></th></tr></thead>
      <tbody>
        {foreach $recurring as $r}
        <tr>
          <td><b>{$r.title|escape}</b></td>
          <td>{$r.amount|string_format:"%.0f"} F</td>
          <td>{$r.day_of_month}</td>
          <td>{if $r.active}<span class="wo-chip ok">Active</span>{else}<span class="wo-chip no">Désactivée</span>{/if}
            {if $r.last_run}<br><small class="wo-muted">Dernier : {$r.last_run|escape}</small>{/if}
          </td>
          <td>
            <a class="wo-btn wo-btn-ghost" style="padding:6px 10px" href="{Text::url('plugin/wifi_otantik_erp/recurring_toggle/')}{$r.id}">{if $r.active}Désactiver{else}Activer{/if}</a>
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
  var input=document.getElementById('woExpenseFiles');
  var drop=document.getElementById('woExpenseDrop');
  var preview=document.getElementById('woExpensePreview');
  if(!input||!drop||!preview) return;
  function size(n){ return n>1048576?(n/1048576).toFixed(1)+' Mo':Math.max(1,Math.round(n/1024))+' Ko'; }
  function paint(files){
    preview.innerHTML='';
    Array.prototype.forEach.call(files,function(file){
      var item=document.createElement('div');
      item.className='wo-upload-item';
      var visual=document.createElement('span');
      visual.className='wo-upload-thumb';
      if(file.type.indexOf('image/')===0){
        var img=document.createElement('img');
        img.src=URL.createObjectURL(file);
        img.onload=function(){URL.revokeObjectURL(img.src);};
        visual.appendChild(img);
      }else{
        visual.innerHTML='<i class="fa fa-file-pdf-o"></i>';
      }
      var text=document.createElement('span');
      text.innerHTML='<b></b><small></small>';
      text.querySelector('b').textContent=file.name;
      text.querySelector('small').textContent=size(file.size);
      item.appendChild(visual); item.appendChild(text); preview.appendChild(item);
    });
  }
  input.addEventListener('change',function(){paint(this.files);});
  ['dragenter','dragover'].forEach(function(name){drop.addEventListener(name,function(e){e.preventDefault();drop.classList.add('is-drag');});});
  ['dragleave','drop'].forEach(function(name){drop.addEventListener(name,function(e){e.preventDefault();drop.classList.remove('is-drag');});});
  drop.addEventListener('drop',function(e){
    if(e.dataTransfer&&e.dataTransfer.files.length){
      try{input.files=e.dataTransfer.files;}catch(ignore){}
      paint(e.dataTransfer.files);
    }
  });
})();
</script>
{/literal}
<script src="{$app_url}/ui/ui_custom/otantik-erp.js?v=20260820erp4"></script>
{include file="otantik_shell_foot.tpl"}
