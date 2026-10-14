{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Gestion de profil</h1>
    <p>Photo, téléphone MoMo, identité et sécurité du compte.</p>
  </div>
</div>
<div class="wo-profile">
  <div class="wo-card" style="text-align:center">
    <form method="post" enctype="multipart/form-data">
      <label class="wo-ava lg wo-ava-edit" title="Changer la photo">
        <img src="{$otantik_avatar}" alt="">
        <span class="wo-online" title="En ligne"></span>
        <span class="wo-ava-cam"><i class="fa fa-camera"></i></span>
        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" onchange="this.form.submit()">
      </label>
    </form>
    <h3 style="margin:0">{$_admin.fullname|default:$_admin.username}</h3>
    <p class="wo-phone-line">
      {if $phone_raw}
        <a href="tel:+237{$phone_raw}">{$phone_fmt}</a>
      {else}
        <span style="color:var(--wo-muted)">Téléphone non renseigné</span>
      {/if}
    </p>
    <p><i class="fa fa-map-marker wo-pin"></i> {$tenant.city|default:''} {$tenant.country|default:'Cameroun'}</p>
    <span class="wo-btn wo-btn-ghost">{if $otantik_is_platform}SuperAdmin{else}Administrateur{/if}</span>
    <p style="margin-top:14px;font-size:12px;color:var(--wo-muted)">Solde {$tenant.wallet|string_format:"%.0f"} FCFA · commission {$otantik_commission}%</p>
    <p style="margin-top:8px"><span class="wo-chip {$kyc_chip}">KYC · {$kyc_label}</span></p>
    <p style="margin-top:8px"><a class="wo-btn wo-btn-primary" href="{Text::url('plugin/wifi_otantik_profile')}&tab=edit">Mettre à jour</a></p>
  </div>
  <div class="wo-card">
    <div class="wo-tabs">
      <a class="{if $tab == 'overview'}on{/if}" href="{Text::url('plugin/wifi_otantik_profile')}&tab=overview">Aperçu</a>
      <a class="{if $tab == 'edit'}on{/if}" href="{Text::url('plugin/wifi_otantik_profile')}&tab=edit">Édition</a>
      <a class="{if $tab == 'kyc'}on{/if}" href="{Text::url('plugin/wifi_otantik_profile')}&tab=kyc">Identité (KYC)</a>
      <a class="{if $tab == 'security'}on{/if}" href="{Text::url('plugin/wifi_otantik_profile')}&tab=security">Sécurité</a>
      <a class="{if $tab == '2fa'}on{/if}" href="{Text::url('plugin/wifi_otantik_profile')}&tab=2fa">2FA</a>
    </div>
    {if $tab == 'edit'}
      <h3>Modifier le profil</h3>
      {if $email_verify}
      <p>Un code a été envoyé à <b>{$email_pending_hint}</b>. Saisissez-le pour confirmer la nouvelle adresse.</p>
      <form method="post">
        <p><label class="wo-label">Code à 6 chiffres</label>
          <input class="wo-input" name="email_code" inputmode="numeric" maxlength="8" required autofocus placeholder="000000"></p>
        <button class="wo-btn wo-btn-primary" type="submit">Confirmer l’email</button>
      </form>
      {else}
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="save_profile" value="1">
        <p><label class="wo-label">Photo de profil</label>
          <input class="wo-input" type="file" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif"></p>
        <div class="wo-row">
          <p><label class="wo-label">Prénom(s)</label><input class="wo-input" name="firstname" value="{$firstname}" required></p>
          <p><label class="wo-label">Nom de famille</label><input class="wo-input" name="lastname" value="{$lastname}"></p>
        </div>
        <p><label class="wo-label">Email (codes de sécurité et retraits)</label>
          <input class="wo-input" type="email" name="email" value="{$_admin.email}" required placeholder="vous@gmail.com" title="Exemple : vous@gmail.com" oninvalid="this.setCustomValidity('Indiquez un email du type vous@gmail.com')" oninput="this.setCustomValidity('')"></p>
        <p><label class="wo-label">Téléphone (retraits MoMo et contact)</label>
          <input class="wo-input" name="phone" value="{$tenant.phone|default:$_admin.phone}" required inputmode="tel" placeholder="6XX XX XX XX"></p>
        <div class="wo-row">
          <p><label class="wo-label">Ville</label><input class="wo-input" name="city" value="{$tenant.city}"></p>
          <p><label class="wo-label">Zone géographique</label><input class="wo-input" name="country" value="{$tenant.country|default:'Cameroun'}"></p>
        </div>
        <p><label class="wo-label">Adresse</label><input class="wo-input" name="address" value="{$tenant.address}"></p>
        <p><label class="wo-label">Numéro WhatsApp pour les notifications (alertes zone hors ligne, etc.)</label>
          <input class="wo-input" name="wa_notify_phone" value="{$tenant.wa_notify_phone}" inputmode="tel" placeholder="6XX XX XX XX (laisser vide pour utiliser le téléphone ci-dessus)"></p>
        <p style="color:var(--wo-muted);font-size:13px">N'utilisez pas le numéro WhatsApp Business de la plateforme lui-même : Meta refuse qu'un numéro API s'envoie un message à lui-même.</p>
        <button class="wo-btn wo-btn-primary" type="submit">Enregistrer</button>
      </form>
      {/if}
      {if $withdraw_email_owner}
      <hr style="margin:24px 0;border:none;border-top:1px solid var(--wo-border)">
      <h3>Email de retrait</h3>
      <p style="color:var(--wo-muted);font-size:13px">Le code de confirmation d’un retrait part toujours à cette adresse, même si un membre de l’équipe (Gérant, Comptable…) lance le retrait. Laissez vide pour utiliser l’email de la personne qui retire.</p>
      <form method="post" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">
        <input type="hidden" name="save_withdraw_email" value="1">
        <label style="flex:1;min-width:220px">
          <span class="wo-label">Email de confirmation des retraits</span>
          <input class="wo-input" type="email" name="withdraw_email" value="{$withdraw_email|escape}" placeholder="ex. proprietaire@exemple.com">
        </label>
        <button class="wo-btn wo-btn-primary" type="submit">Enregistrer</button>
      </form>
      {/if}
    {elseif $tab == 'kyc'}
      <h3>Vérification d’identité</h3>
      <p>Statut : <span class="wo-chip {$kyc_chip}">{$kyc_label}</span>
        {if $tenant.kyc_submitted_at}<span style="color:var(--wo-muted);font-size:12px"> · envoyé le {$tenant.kyc_submitted_at}</span>{/if}
      </p>
      {if $tenant.kyc_status == 'rejected' && $tenant.kyc_note}
        <p class="wo-kyc-note">Motif du refus : {$tenant.kyc_note|escape}</p>
      {/if}
      {if $kyc_locked}
        <p>Votre identité est validée. Les documents ne peuvent plus être modifiés.</p>
        <div class="wo-kyc-docs">
          {foreach $kyc_docs as $doc}
            <div class="wo-kyc-doc">
              <label>{$doc.label}</label>
              {if $doc.has}<a href="{$doc.url}" target="_blank"><img src="{$doc.url}" alt="{$doc.label}"></a>{else}<span class="wo-muted">—</span>{/if}
            </div>
          {/foreach}
        </div>
        <p><label class="wo-label">Pièce</label> {$tenant.kyc_id_type|upper} · {$tenant.kyc_id_number}</p>
      {else}
        <p style="color:var(--wo-muted);font-size:13px">Envoyez des photos nettes (JPG, PNG ou WEBP, 5 Mo max). CNI : recto + verso + selfie. Passeport / NUI : page d’identité + selfie.</p>
        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="save_kyc" value="1">
          <p><label class="wo-label">Type de pièce</label>
            <select class="wo-select" name="kyc_id_type">
              <option value="cni"{if $tenant.kyc_id_type == 'cni' || $tenant.kyc_id_type == ''} selected{/if}>CNI</option>
              <option value="passport"{if $tenant.kyc_id_type == 'passport'} selected{/if}>Passeport</option>
              <option value="nui"{if $tenant.kyc_id_type == 'nui'} selected{/if}>NUI / RCCM</option>
            </select></p>
          <p><label class="wo-label">Numéro</label><input class="wo-input" name="kyc_id_number" value="{$tenant.kyc_id_number}" required></p>
          <div class="wo-kyc-docs">
            {foreach $kyc_docs as $doc}
              <label class="wo-kyc-doc">
                <span>{$doc.label}</span>
                {if $doc.has}<img src="{$doc.url}" alt="{$doc.label}">{else}<em>Aucun fichier</em>{/if}
                <input type="file" name="{$doc.field}" accept="image/jpeg,image/png,image/webp,image/gif">
              </label>
            {/foreach}
          </div>
          <p style="margin-top:16px"><button class="wo-btn wo-btn-primary" type="submit">Envoyer le dossier</button></p>
        </form>
      {/if}
    {elseif $tab == 'security'}
      <h3>Sécurité du compte</h3>
      <form method="post">
        <input type="hidden" name="save_password" value="1">
        <p><label class="wo-label">Mot de passe actuel</label><input class="wo-input" type="password" name="current_password" required></p>
        <p><label class="wo-label">Nouveau mot de passe</label><input class="wo-input" type="password" name="new_password" minlength="6" required></p>
        <p><label class="wo-label">Confirmation</label><input class="wo-input" type="password" name="new_password2" minlength="6" required></p>
        <button class="wo-btn wo-btn-primary" type="submit">Changer le mot de passe</button>
      </form>
    {elseif $tab == '2fa'}
      <h3>Double authentification</h3>
      <p>L’inscription et les retraits envoient un code à l’email du profil. La connexion se fait avec le téléphone ou l’email, plus le mot de passe. Google Sign-In est aussi disponible.</p>
    {else}
      <div class="wo-contact-card">
        <small>Téléphone de contact / retraits MoMo</small>
        <strong>{if $phone_raw}<a href="tel:+237{$phone_raw}">{$phone_fmt}</a>{else}Non renseigné — ajoutez-le dans Édition{/if}</strong>
        <span>{$_admin.email|default:'Email non renseigné'}</span>
      </div>
      <h3><i class="fa fa-info-circle" style="color:var(--wo-blue)"></i> Détails personnels</h3>
      <div class="wo-dl">
        <div><dt>Nom de famille</dt><dd>{$lastname|default:'—'}</dd></div>
        <div><dt>Prénom(s)</dt><dd>{$firstname|default:'—'}</dd></div>
        <div><dt>Identifiant</dt><dd>{$_admin.username}</dd></div>
        <div><dt>Téléphone</dt><dd>{$phone_fmt}</dd></div>
        <div><dt>Adresse email</dt><dd>{$_admin.email|default:'—'}</dd></div>
        <div><dt>Ville</dt><dd>{$tenant.city|default:'—'}</dd></div>
        <div><dt>Adresse</dt><dd>{$tenant.address|default:'—'}</dd></div>
        <div><dt>Zone géographique</dt><dd>{$tenant.country|default:'Cameroun'}</dd></div>
        <div><dt>Rôle</dt><dd>{$_admin.user_type}</dd></div>
        <div><dt>KYC</dt><dd><span class="wo-chip {$kyc_chip}">{$kyc_label}</span> <a href="{Text::url('plugin/wifi_otantik_profile')}&tab=kyc">Gérer</a></dd></div>
        {if $withdraw_email_owner}
        <div><dt>Email de retrait</dt><dd>{if $withdraw_email}{$withdraw_email|escape}{else}<span class="wo-muted">Non défini — email du retirant utilisé</span>{/if} <a href="{Text::url('plugin/wifi_otantik_profile')}&tab=edit">Modifier</a></dd></div>
        {/if}
        <div><dt>Compte créé</dt><dd>{$_admin.creationdate|default:'—'}</dd></div>
      </div>
    {/if}
  </div>
</div>
{include file="otantik_shell_foot.tpl"}
