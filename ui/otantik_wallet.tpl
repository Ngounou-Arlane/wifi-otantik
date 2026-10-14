{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Retraits</h1>
    <p>Retirez vers MTN MoMo ou Orange Money. Les deux réseaux sont séparés : un numéro MTN ne peut pas être servi depuis Orange, et inversement. Vous recevez le montant demandé.</p>
  </div>
  <div class="wo-head-actions">
    {include file="otantik_export.tpl" export_csv=$export_csv export_pdf=$export_pdf}
    <a class="wo-btn wo-btn-primary" href="#" onclick="document.getElementById('woWd').classList.add('on');return false;"><i class="fa fa-plus"></i> <span>Retirer</span></a>
  </div>
</div>
<div class="wo-kpi-rail wo-wallet-rail" aria-label="Soldes">
  <div class="wo-card wo-kpi">
    <div>
      <span>Solde à retirer</span>
      <h3>{$tenant.wallet|string_format:"%.0f"} FCFA</h3>
      <small>Après commission. Un frais de virement peut réduire le maximum selon le réseau du numéro.</small>
    </div>
    <div class="wo-ico blue"><i class="fa fa-briefcase"></i></div>
  </div>
  <div class="wo-card wo-kpi">
    <div>
      <span>Total retiré</span>
      <div class="wo-seg wo-seg-sm">
        <a class="{if $wd_tab == 'week'}on{/if}" href="{Text::url('plugin/wifi_otantik_wallet')}&wd=week">Semaine</a>
        <a class="{if $wd_tab == 'year'}on{/if}" href="{Text::url('plugin/wifi_otantik_wallet')}&wd=year">Année</a>
      </div>
      <h3>{$wd_range_amt|string_format:"%.0f"} FCFA</h3>
      <small>{if $wd_tab == 'year'}Depuis le 1er janvier{else}7 derniers jours{/if} · {$wd_range_count} virement{if $wd_range_count > 1}s{/if}</small>
    </div>
    <div class="wo-ico orange"><i class="fa fa-arrow-circle-up"></i></div>
  </div>
  <div class="wo-card wo-kpi">
    <div>
      <span>En attente</span>
      <h3>{$wd_pending_amt|string_format:"%.0f"} FCFA</h3>
      <small>{if $wd_pending_count > 0}{$wd_pending_count} virement{if $wd_pending_count > 1}s{/if} en cours{else}Aucun virement en cours{/if}</small>
    </div>
    <div class="wo-ico yellow"><i class="fa fa-clock-o"></i></div>
  </div>
</div>
<div class="wo-card" style="margin-top:16px">
  <b>Email de retrait</b>
  {if $withdraw_email_owner}
  <p class="wo-muted" style="margin:4px 0 12px;font-size:13px">Le code de confirmation part toujours à cette adresse, même si un membre de l’équipe lance le retrait. Laissez vide pour utiliser l’email de la personne qui retire.</p>
  <form method="post" action="{Text::url('plugin/wifi_otantik_wallet')}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">
    <input type="hidden" name="save_withdraw_email" value="1">
    <label style="flex:1;min-width:220px">
      <span class="wo-label">Email de confirmation</span>
      <input class="wo-input" type="email" name="withdraw_email" value="{$withdraw_email|escape}" placeholder="ex. proprietaire@exemple.com">
    </label>
    <button class="wo-btn wo-btn-primary" type="submit">Enregistrer</button>
  </form>
  {else}
  <p class="wo-muted" style="margin:4px 0 0;font-size:13px">{if $withdraw_email}Les codes de retrait sont envoyés à <b>{$withdraw_email|escape}</b>, configuré par le propriétaire.{else}Aucun email de retrait dédié - le code part sur votre propre email de profil.{/if}</p>
  {/if}
</div>
<div class="wo-card" style="margin-top:16px">
  <b>Historique</b>
  {if $withdrawals|@count == 0 && $filter_q == ''}
    <div class="wo-empty"><i class="fa fa-briefcase"></i>Aucun retrait pour le moment</div>
  {else}
  <form method="get" class="wo-filters" data-wo-filter>
    {include file="otantik_get_keep.tpl"}
    {include file="otantik_find.tpl" placeholder="Rechercher un n°, un montant, un statut…" find_name="q" find_value=$filter_q}
    <span class="wo-filters-meta" data-wo-count></span>
  </form>
  {if $withdrawals|@count == 0}
    <div class="wo-empty"><i class="fa fa-search"></i>Aucun retrait ne correspond</div>
  {else}
  <div class="wo-table-wrap">
  <table class="wo-table wo-table-wallet">
    <thead><tr><th>Date</th><th>Opérateur</th><th>Téléphone</th><th>Motif</th><th>Montant</th><th>Statut</th></tr></thead>
    <tbody>
    {foreach $withdrawals as $w}
      <tr data-wo-row data-wo-hay="{$w.service|escape} {$w.receiver|escape} {$w.amount} {$w.status|escape} {$w.reason|escape}">
        <td data-label="Date">{$w.created_at}</td>
        <td data-label="Opérateur">{$w.service}</td>
        <td data-label="Téléphone">{$w.receiver}</td>
        <td data-label="Motif">{if $w.reason}{$w.reason|escape}{else}<span class="wo-muted">—</span>{/if}</td>
        <td data-label="Montant"><b>{$w.amount} FCFA</b></td>
        <td data-label="Statut"><span class="wo-chip {if $w.status == 'SUCCESS'}ok{elseif $w.status == 'FAILED'}no{else}wait{/if}">{if $w.status == 'SUCCESS'}Réussi{elseif $w.status == 'FAILED'}Échoué{else}En cours{/if}</span></td>
      </tr>
    {/foreach}
    </tbody>
  </table>
  </div>
  <div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucun retrait ne correspond</div>
  {include file="otantik_pager.tpl"}
  {/if}
  {/if}
</div>
<div class="wo-modal {if $confirm_wd}on{/if}" id="woWd">
  <div class="wo-modal-card" style="width:min(480px,100%)">
    <div class="wo-modal-h"><h2 style="margin:0">{if $confirm_wd}Code de confirmation{else}Retrait immédiat{/if}</h2><a href="{Text::url('plugin/wifi_otantik_wallet')}" class="wo-iconbtn">&times;</a></div>
    {if $confirm_wd}
    <p>Un code a été envoyé à <b>{$wd_email_hint}</b> pour valider {$wd_amount} FCFA.</p>
    <form method="post">
      <p><label class="wo-label">Code à 6 chiffres</label><input class="wo-input" name="withdraw_code" inputmode="numeric" maxlength="6" required autofocus></p>
      <div style="text-align:right"><button class="wo-btn wo-btn-primary" type="submit">Confirmer le virement</button></div>
    </form>
    {else}
    <form method="post" id="woWdForm">
      {if $notify_email == ''}
        <p style="color:#b91c1c">Ajoutez un email dans <a href="{Text::url('plugin/wifi_otantik_profile')}&tab=edit">Profil</a> : le code de sécurité y sera envoyé.</p>
      {/if}
      <p><label class="wo-label">Montant reçu (min 500)</label><input class="wo-input" type="number" name="withdraw_amount" id="woWdAmt" min="500" max="{$tenant.wallet|string_format:'%.0f'}" required></p>
      <p><label class="wo-label">Motif du retrait <span class="wo-req">*</span></label><input class="wo-input" name="withdraw_reason" placeholder="Ex. Achat équipement, salaire, remboursement…" required maxlength="255"></p>
      <p><label class="wo-label">Numéro Mobile Money</label><input class="wo-input" name="withdraw_phone" id="woWdPhone" value="{$tenant.phone}" required></p>
      <p id="woWdOp" style="display:none;font-size:13px;font-weight:700;margin-top:-6px"></p>
      <p id="woWdFee" style="font-size:12px;color:var(--wo-muted);margin-top:-4px">Vous recevez le montant saisi. Le réseau du numéro (MTN ou Orange) détermine le maximum.</p>
      <p style="font-size:12px;color:var(--wo-muted)">Un code sera envoyé à {$wd_email_hint|default:'votre email'}.</p>
      <div style="text-align:right"><button class="wo-btn wo-btn-primary" type="submit" {if $notify_email == ''}disabled{/if}>Envoyer le code</button></div>
    </form>
    {/if}
  </div>
</div>
{if $open_withdraw && !$confirm_wd}<script>document.addEventListener('DOMContentLoaded', function(){ document.getElementById('woWd').classList.add('on'); });</script>{/if}
{literal}
<script>
(function(){
  function detect(phone){
    var p=String(phone||'').replace(/\D+/g,'');
    if(p.indexOf('237')===0 && p.length>9) p=p.slice(3);
    if(p.charAt(0)==='0' && p.length===10) p=p.slice(1);
    if(/^(69|65[5-9]|64|68[6-9])/.test(p)) return 'ORANGE';
    if(/^(67|65[0-4]|68[0-3])/.test(p)) return 'MTN';
    return '';
  }
  var el=document.getElementById('woWdPhone');
  var op=document.getElementById('woWdOp');
  var amt=document.getElementById('woWdAmt');
  var form=document.getElementById('woWdForm') || (el && el.form);
  var feeEl=document.getElementById('woWdFee');
  function n(v){ return parseInt(v,10)||0; }
  function paint(){
    if(!op||!el) return;
    var p=String(el.value||'').replace(/\D+/g,'');
    var s=detect(el.value);
    var v=amt?n(amt.value):0;
    if(feeEl && v>=500 && s){
      feeEl.textContent='Vous recevez '+v+' F vers '+(s==='ORANGE'?'Orange Money':'MTN MoMo')+'.';
    }
    if(p.length<3){ op.style.display='none'; return; }
    op.style.display='block';
    if(s==='MTN'){ op.style.color='#854d0e'; op.textContent='MTN MoMo'; }
    else if(s==='ORANGE'){ op.style.color='#9a3412'; op.textContent='Orange Money'; }
    else { op.style.color='#b91c1c'; op.textContent='Numéro MTN ou Orange requis'; }
  }
  if(el){ el.addEventListener('input', paint); paint(); }
  if(amt){ amt.addEventListener('input', paint); }
  if(form){
    form.addEventListener('submit', function(e){
      if(el && !detect(el.value)){ e.preventDefault(); paint(); return; }
    });
  }
})();
</script>
{/literal}
{include file="otantik_shell_foot.tpl"}
