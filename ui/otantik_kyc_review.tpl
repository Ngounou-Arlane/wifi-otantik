{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Vérification KYC</h1>
    <p>{if $user}{$user.fullname|default:$user.username}{else}Client #{$row.id}{/if} · {$phone_fmt}</p>
  </div>
  <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_tenants')}">Retour aux clients</a>
</div>
<div class="wo-card" style="margin-bottom:16px">
  <p>Statut : <span class="wo-chip {$kyc_chip}">{$kyc_label}</span>
    {if $row.kyc_submitted_at}<span style="color:var(--wo-muted);font-size:12px"> · envoyé le {$row.kyc_submitted_at}</span>{/if}
  </p>
  <div class="wo-dl">
    <div><dt>Identifiant</dt><dd>{if $user}{$user.username}{else}#{$row.user_id}{/if}</dd></div>
    <div><dt>Nom</dt><dd>{if $user}{$user.fullname}{else}—{/if}</dd></div>
    <div><dt>Email</dt><dd>{if $user}{$user.email|default:'—'}{else}—{/if}</dd></div>
    <div><dt>Téléphone</dt><dd>{$phone_fmt}</dd></div>
    <div><dt>Pièce</dt><dd>{$row.kyc_id_type|upper|default:'—'} · {$row.kyc_id_number|default:'—'}</dd></div>
    {if $row.kyc_note}<div><dt>Note</dt><dd>{$row.kyc_note|escape}</dd></div>{/if}
  </div>
</div>
<div class="wo-card" style="margin-bottom:16px">
  <h3 style="margin-top:0">Documents</h3>
  <div class="wo-kyc-docs">
    {foreach $kyc_docs as $doc}
      <div class="wo-kyc-doc">
        <label>{$doc.label}</label>
        {if $doc.has}
          <a href="{$doc.url}" target="_blank"><img src="{$doc.url}" alt="{$doc.label}"></a>
        {else}
          <em>Document manquant</em>
        {/if}
      </div>
    {/foreach}
  </div>
</div>
{if $row.kyc_status != 'verified'}
<div class="wo-card">
  <h3 style="margin-top:0">Décision</h3>
  <form method="post">
    <input type="hidden" name="kyc_tenant_id" value="{$row.id}">
    <p><label class="wo-label">Note (obligatoire en cas de refus)</label>
      <input class="wo-input" name="kyc_note" value="{$row.kyc_note}" placeholder="Pièce illisible, selfie non conforme…"></p>
    <p style="display:flex;gap:10px;flex-wrap:wrap">
      <button class="wo-btn wo-btn-primary" type="submit" name="kyc_action" value="verified">Valider l’identité</button>
      <button class="wo-btn wo-btn-danger wo-btn-danger-solid" type="submit" name="kyc_action" value="rejected">Refuser</button>
    </p>
  </form>
</div>
{/if}
{include file="otantik_shell_foot.tpl"}
