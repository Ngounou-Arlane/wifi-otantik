{include file="otantik_shell_head.tpl"}
<div class="wo-head"><div><h1>Clients</h1><p>Comptes, soldes, réduction et vérification d’identité.</p></div></div>
<div class="wo-grid wo-4" style="margin-bottom:16px">
  <div class="wo-card wo-kpi"><div><span>Clients</span><h3>{$tenant_count}</h3></div><div class="wo-ico blue"><i class="fa fa-users"></i></div></div>
  <div class="wo-card wo-kpi"><div><span>KYC à vérifier</span><h3>{$kyc_pending}</h3></div><div class="wo-ico teal"><i class="fa fa-id-card"></i></div></div>
  <div class="wo-card wo-kpi"><div><span>Profit du jour</span><h3>{$profit_today|string_format:"%.0f"}</h3></div><div class="wo-ico green"><i class="fa fa-line-chart"></i></div></div>
  <div class="wo-card wo-kpi"><div><span>Profit total</span><h3>{$profit|string_format:"%.0f"}</h3><small>Soldes {$wallet_sum|string_format:"%.0f"} FCFA</small></div><div class="wo-ico teal"><i class="fa fa-percent"></i></div></div>
</div>
<form class="wo-card" method="post" style="margin-bottom:16px">
  <input type="hidden" name="save_platform" value="1">
  <h3 style="margin-top:0">Réglages</h3>
  <div class="wo-row">
    <p><label class="wo-label">Réduction %</label>
      <input class="wo-input" type="number" name="commission" min="0" max="90" step="0.5" value="{$commission}" required></p>
    <p><label class="wo-label">Google Client ID</label>
      <input class="wo-input" name="google_client_id" value="{$google_client_id|escape}" placeholder="xxxxx.apps.googleusercontent.com"></p>
  </div>
  <h3>Envoi des codes par email</h3>
  <p style="color:var(--wo-muted);font-size:13px">{if $mail_ready}Prêt : les codes de retrait, d’inscription et de mot de passe partent par email.{else}À configurer : sans ces réglages, les codes ne partent pas.{/if}</p>
  <p><label class="wo-label">Compte d’envoi</label>
    <select class="wo-select" name="mail_provider" id="woMailProv">
      <option value="gmail" {if $mail_provider == 'gmail'}selected{/if}>Gmail</option>
      <option value="other" {if $mail_provider == 'other'}selected{/if}>Autre messagerie</option>
    </select></p>
  <div id="woMailGmail">
    <p style="color:var(--wo-muted);font-size:13px">Oui, un Gmail suffit. Pas le mot de passe habituel : créez un <b>mot de passe d’application</b> (16 caractères) dans le compte Google (validation en 2 étapes obligatoire), puis collez-le ici.</p>
    <div class="wo-row">
      <p><label class="wo-label">Adresse Gmail</label>
        <input class="wo-input" type="email" name="mail_from" id="woMailFrom" value="{$mail_from|escape}" placeholder="vous@gmail.com"></p>
      <p><label class="wo-label">Nom affiché</label>
        <input class="wo-input" name="mail_from_name" value="{$mail_from_name|escape}" placeholder="Wifi-Otantik"></p>
    </div>
    <p><label class="wo-label">Mot de passe d’application Gmail</label>
      <input class="wo-input" type="password" name="smtp_pass" value="" placeholder="{if $smtp_pass_mask}Laisser vide pour conserver{else}16 caractères, pas le mot de passe du compte{/if}" autocomplete="new-password"></p>
  </div>
  <div id="woMailOther" style="display:none">
    <div class="wo-row">
      <p><label class="wo-label">Adresse d’expédition</label>
        <input class="wo-input" type="email" name="mail_from_other" value="{$mail_from|escape}" placeholder="noreply@votre-domaine.com"></p>
      <p><label class="wo-label">Nom affiché</label>
        <input class="wo-input" name="mail_from_name_other" value="{$mail_from_name|escape}" placeholder="Wifi-Otantik"></p>
    </div>
    <div class="wo-row">
      <p><label class="wo-label">Serveur</label>
        <input class="wo-input" name="smtp_host" value="{$smtp_host|escape}" placeholder="smtp.votre-domaine.com"></p>
      <p><label class="wo-label">Port</label>
        <input class="wo-input" type="number" name="smtp_port" min="1" max="65535" value="{$smtp_port|escape}"></p>
    </div>
    <div class="wo-row">
      <p><label class="wo-label">Identifiant</label>
        <input class="wo-input" name="smtp_user" value="{$smtp_user|escape}" autocomplete="off"></p>
      <p><label class="wo-label">Mot de passe</label>
        <input class="wo-input" type="password" name="smtp_pass_other" value="" placeholder="Laisser vide pour conserver" autocomplete="new-password"></p>
    </div>
    <div class="wo-row">
      <p><label class="wo-label">Sécurité</label>
        <select class="wo-select" name="smtp_ssltls">
          <option value="tls" {if $smtp_ssltls == 'tls'}selected{/if}>STARTTLS (souvent 587)</option>
          <option value="ssl" {if $smtp_ssltls == 'ssl'}selected{/if}>SSL (souvent 465)</option>
          <option value="none" {if $smtp_ssltls == 'none'}selected{/if}>Aucune</option>
        </select></p>
      <p><label class="wo-label">Réponse (optionnel)</label>
        <input class="wo-input" type="email" name="mail_reply_to" value="{$mail_reply_to|escape}" placeholder="contact@votre-domaine.com"></p>
    </div>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px">
    <button class="wo-btn wo-btn-primary" type="submit">Enregistrer</button>
    <button class="wo-btn wo-btn-ghost" type="submit" name="mail_test" value="1">Enregistrer et tester</button>
  </div>
{literal}
<script>
(function(){
  var sel=document.getElementById('woMailProv');
  var g=document.getElementById('woMailGmail');
  var o=document.getElementById('woMailOther');
  function freeze(box, off){
    if(!box) return;
    var list=box.querySelectorAll('input,select,textarea');
    for(var i=0;i<list.length;i++) list[i].disabled=!!off;
  }
  function paint(){
    var gmail=sel && sel.value==='gmail';
    if(g) g.style.display=gmail?'block':'none';
    if(o) o.style.display=gmail?'none':'block';
    freeze(g, !gmail);
    freeze(o, gmail);
  }
  if(sel){ sel.addEventListener('change', paint); paint(); }
})();
</script>
{/literal}
</form>
<div class="wo-card">
<form method="get" class="wo-filters" data-wo-filter>
  {include file="otantik_get_keep.tpl"}
  {include file="otantik_find.tpl" placeholder="Rechercher un client, un tél…" find_name="q" find_value=$filter_q}
  <select class="wo-select" name="st" data-wo-st onchange="this.form.submit()">
    <option value="">Tous les statuts</option>
    <option value="Active"{if $filter_st == 'Active'} selected{/if}>Actif</option>
    <option value="Inactive"{if $filter_st == 'Inactive'} selected{/if}>Inactif</option>
  </select>
  <select class="wo-select" name="ty" data-wo-ty onchange="this.form.submit()">
    <option value="">Tous les KYC</option>
    <option value="pending"{if $filter_ty == 'pending'} selected{/if}>KYC à vérifier</option>
    <option value="verified"{if $filter_ty == 'verified'} selected{/if}>KYC validé</option>
    <option value="rejected"{if $filter_ty == 'rejected'} selected{/if}>KYC refusé</option>
    <option value="none"{if $filter_ty == 'none'} selected{/if}>Sans KYC</option>
  </select>
  <span class="wo-filters-meta" data-wo-count></span>
</form>
<table class="wo-table">
<thead><tr><th>User</th><th>Nom</th><th>Tél</th><th>Solde</th><th>%</th><th>Parrain</th><th>ERP</th><th>WhatsApp</th><th>KYC</th><th>Statut</th><th></th></tr></thead>
<tbody>
{foreach $rows as $t}
{assign var=uid value=$t.user_id}
<tr data-wo-row data-wo-status="{$t.status|default:'active'}" data-wo-type="{$t.kyc_status|default:'none'}" data-wo-hay="{if isset($users[$uid])}{$users[$uid].username|escape} {$users[$uid].fullname|escape}{/if} {$t.phone|escape}">
<td>{if isset($users[$t.user_id])}{$users[$t.user_id].username}{else}#{$t.user_id}{/if}</td>
<td>{if isset($users[$t.user_id])}{$users[$t.user_id].fullname}{/if}</td>
<td>{$t.phone}</td>
<td>{$t.wallet} FCFA</td>
<td>
  <form method="post" style="display:flex;gap:6px;align-items:center">
    <input type="hidden" name="tenant_id" value="{$t.id}">
    <input class="wo-input" style="width:88px;padding:8px" type="number" name="commission_rate" min="0" max="90" step="0.5" value="{$t.commission_rate}">
    <button class="wo-btn wo-btn-ghost" type="submit">OK</button>
  </form>
</td>
<td>
  <form method="post" style="display:flex;gap:6px;align-items:center">
    <input type="hidden" name="tenant_id" value="{$t.id}">
    <input class="wo-input" style="width:110px;padding:8px" name="referred_by" value="{$t.referred_by|escape}" placeholder="identifiant">
    <button class="wo-btn wo-btn-ghost" type="submit">OK</button>
  </form>
</td>
<td>
  <form method="post" action="{Text::url('plugin/wifi_otantik_erp/enable')}" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
    <input type="hidden" name="tenant_id" value="{$t.id}">
    <label style="font-size:12px;display:flex;align-items:center;gap:4px"><input type="checkbox" name="erp_enabled" value="1" {if $t.erp_enabled}checked{/if}> Pro</label>
    <input class="wo-input" type="date" name="erp_until" value="{if $t.erp_until}{$t.erp_until|substr:0:10}{/if}" style="width:130px;padding:6px;font-size:12px" title="Expiration (optionnel)">
    <button class="wo-btn wo-btn-ghost" type="submit" style="padding:6px 10px">OK</button>
  </form>
</td>
<td>
  <form method="post" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
    <input type="hidden" name="tenant_id" value="{$t.id}">
    <input type="hidden" name="wa_enabled_toggle" value="1">
    <label style="font-size:12px;display:flex;align-items:center;gap:4px"><input type="checkbox" name="wa_enabled" value="1" {if $t.wa_enabled}checked{/if}> Actif</label>
    <input class="wo-input" type="date" name="wa_until" value="{if $t.wa_until}{$t.wa_until|substr:0:10}{/if}" style="width:130px;padding:6px;font-size:12px" title="Expiration (optionnel)">
    <button class="wo-btn wo-btn-ghost" type="submit" style="padding:6px 10px">OK</button>
  </form>
</td>
<td>
  <a href="{Text::url('plugin/wifi_otantik_tenants/kyc/')}{$t.id}">
    <span class="wo-chip {$kyc_chips[$t.id]}">{$kyc_labels[$t.id]}</span>
  </a>
</td>
<td>{$t.status}</td>
<td><a href="{Text::url('plugin/wifi_otantik_tenants/delete/')}{$t.id}" style="color:#b91c1c;text-decoration:underline;font-size:13px">Supprimer</a></td>
</tr>
{/foreach}
</tbody></table>
<div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucun résultat</div>
{include file="otantik_pager.tpl"}
</div>
{include file="otantik_shell_foot.tpl"}
