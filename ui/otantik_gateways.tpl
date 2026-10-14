{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Paiements</h1>
    <p>Identifiants Mobile Money de la plateforme. Les exploitants et les clients ne voient jamais ces détails.</p>
  </div>
</div>
<div class="wo-grid wo-2" style="margin-bottom:16px">
  <div class="wo-card wo-kpi">
    <div>
      <span>Paiement actif</span>
      <h3>{$pg_label}</h3>
      <small>Encaissements hotspot, Connectik, revendeurs et retraits</small>
    </div>
    <div class="wo-ico {if $pg_health.ok}green{else}orange{/if}"><i class="fa fa-credit-card"></i></div>
  </div>
  <div class="wo-card wo-kpi">
    <div>
      <span>Test {if $pg_health.ok}OK{else}à vérifier{/if}</span>
      <h3 style="font-size:18px;line-height:1.35">{$pg_health.detail|escape}</h3>
      <small>Les identifiants ne sont jamais affichés en clair</small>
    </div>
    <div class="wo-ico {if $pg_health.ok}teal{else}orange{/if}"><i class="fa {if $pg_health.ok}fa-check{else}fa-warning{/if}"></i></div>
  </div>
</div>
<form class="wo-card" method="post">
  <input type="hidden" name="save_pg" value="1">
  <h3 style="margin-top:0">Mode</h3>
  <p style="color:var(--wo-muted)">Un seul mode à la fois. Le premier est le défaut. Le second envoie la demande directement sur le téléphone.</p>
  <div class="wo-row" style="margin-bottom:16px">
    <label class="wo-card" style="margin:0;cursor:pointer;border:2px solid {if $pg_active == 'mesomb'}#f05018{else}transparent{/if}">
      <input type="radio" name="otantik_pg" value="mesomb" {if $pg_active == 'mesomb'}checked{/if}>
      <b>Mode A</b>
      <small style="display:block;color:var(--wo-muted)">Collecte MTN / Orange</small>
    </label>
    <label class="wo-card" style="margin:0;cursor:pointer;border:2px solid {if $pg_active == 'fapshi'}#f05018{else}transparent{/if}">
      <input type="radio" name="otantik_pg" value="fapshi" {if $pg_active == 'fapshi'}checked{/if}>
      <b>Mode B</b>
      <small style="display:block;color:var(--wo-muted)">Demande directe sur le téléphone</small>
    </label>
  </div>

  <h3>Mode A — identifiants</h3>
  <p><label class="wo-label">URL de notification</label>
    <input class="wo-input" value="{$pg_webhook|escape}" readonly onclick="this.select()"></p>
  <div class="wo-row">
    <p><label class="wo-label">Clé application</label>
      <input class="wo-input" name="mesomb_application_key" value="{$mesomb_application_key|escape}" autocomplete="off"></p>
    <p><label class="wo-label">Clé d’accès</label>
      <input class="wo-input" name="mesomb_access_key" value="{$mesomb_access_key|escape}" autocomplete="off"></p>
  </div>
  <div class="wo-row">
    <p><label class="wo-label">Clé secrète</label>
      <input class="wo-input" type="password" name="mesomb_secret_key" value="{$mesomb_secret_mask|escape}" placeholder="Laisser vide pour conserver" autocomplete="new-password"></p>
    <p><label class="wo-label">Adresse du service</label>
      <input class="wo-input" name="mesomb_api_base" value="{$mesomb_api_base|escape}" autocomplete="off"></p>
  </div>
  <p><label class="wo-label">Frais de virement (%)</label>
    <input class="wo-input" type="number" name="mesomb_withdraw_fee" min="0" max="20" step="0.01" value="{$mesomb_withdraw_fee|escape}">
    <small style="color:var(--wo-muted)">Pourcentage prélevé en plus du montant reçu par l’exploitant. Le plafond de retrait en tient compte automatiquement.</small></p>

  <h3>Mode B — encaissement</h3>
  <div class="wo-row">
    <p><label class="wo-label">Identifiant</label>
      <input class="wo-input" name="fapshi_api_user" value="{$fapshi_api_user|escape}" autocomplete="off"></p>
    <p><label class="wo-label">Clé</label>
      <input class="wo-input" type="password" name="fapshi_api_key" value="{$fapshi_api_key_mask|escape}" placeholder="Laisser vide pour conserver" autocomplete="new-password"></p>
  </div>
  <div class="wo-row">
    <p><label class="wo-label">Environnement</label>
      <select class="wo-select" name="fapshi_env">
        <option value="live" {if $fapshi_env == 'live'}selected{/if}>Production</option>
        <option value="sandbox" {if $fapshi_env == 'sandbox'}selected{/if}>Test</option>
        <option value="auto" {if $fapshi_env == 'auto'}selected{/if}>Auto</option>
      </select></p>
    <p><label class="wo-label">Secret de notification (optionnel)</label>
      <input class="wo-input" type="password" name="fapshi_webhook_secret" value="{$fapshi_webhook_secret_mask|escape}" placeholder="Laisser vide pour conserver" autocomplete="new-password"></p>
  </div>

  <h3>Mode B — retraits</h3>
  <p style="color:var(--wo-muted);font-size:13px">Laissez vide pour réutiliser les identifiants d’encaissement. Sinon, renseignez un second jeu de clés dédié aux virements.</p>
  <div class="wo-row">
    <p><label class="wo-label">Identifiant virements</label>
      <input class="wo-input" name="fapshi_payout_api_user" value="{$fapshi_payout_api_user|escape}" autocomplete="off"></p>
    <p><label class="wo-label">Clé virements</label>
      <input class="wo-input" type="password" name="fapshi_payout_api_key" value="{$fapshi_payout_api_key_mask|escape}" placeholder="Laisser vide pour conserver" autocomplete="new-password"></p>
  </div>
  <h3>WhatsApp (via WhatsChatAds)</h3>
  <p><label class="wo-label" style="display:flex;align-items:center;gap:8px;font-weight:normal">
    <input type="checkbox" name="otantik_wa_enabled" value="1"{if $wa_enabled} checked{/if}> Activer l'envoi de messages WhatsApp
  </label></p>
  <p style="color:var(--wo-muted);font-size:13px">{if !$wa_enabled}Désactivé : aucun message WhatsApp ne sera envoyé (tickets, relances, rappels, accusés de réception, boîte de réception), même si le jeton est configuré.{elseif $wa_ready}Prêt : tickets, relances, rappels et accusé de réception partent via WhatsChatAds.{else}À configurer : collez le jeton API WhatsChatAds ci-dessous. Les modèles (<code>wo_*</code>) doivent déjà être créés et actifs dans WhatsChatAds/WhatsApp Manager.{/if}</p>
  <p><label class="wo-label">URL du webhook (si WhatsChatAds propose des webhooks entrants)</label>
    <input class="wo-input" value="{$wa_hook|escape}" readonly onclick="this.select()"></p>
  <div class="wo-row">
    <p><label class="wo-label">Identifiant du numéro (optionnel, non utilisé par WhatsChatAds)</label>
      <input class="wo-input" name="otantik_wa_phone_id" value="{$otantik_wa_phone_id|escape}" autocomplete="off"></p>
    <p><label class="wo-label">Jeton de vérification (optionnel)</label>
      <input class="wo-input" name="otantik_wa_verify" value="{$otantik_wa_verify|escape}" autocomplete="off"></p>
  </div>
  <p><label class="wo-label">Jeton API WhatsChatAds (Bearer token)</label>
    <input class="wo-input" type="password" name="otantik_wa_token" value="{$otantik_wa_token_mask|escape}" placeholder="Laisser vide pour conserver" autocomplete="new-password"></p>
  {if $wa_catalog}
  <p style="color:var(--wo-muted);font-size:13px">Modèles à créer (nom exact, français) : <code>{$wa_catalog|escape}</code></p>
  {/if}
  <p><a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_wa_logs')}">Ouvrir le journal WhatsApp</a></p>
  <button class="wo-btn wo-btn-primary" type="submit">Enregistrer</button>
</form>
<form class="wo-card" method="post" style="margin-top:16px">
  <input type="hidden" name="wa_test" value="1">
  <h3 style="margin-top:0">Tester un envoi</h3>
  <p style="color:var(--wo-muted);font-size:13px">Envoie le modèle <code>wo_hello</code> (français) vers un numéro camerounais. Le modèle doit être <b>Active</b> dans WhatsApp Manager. L’envoi apparaît ensuite dans le <a href="{Text::url('plugin/wifi_otantik_wa_logs')}">journal WhatsApp</a>.</p>
  <p><label class="wo-label">Numéro de test</label>
    <input class="wo-input" name="wa_test_phone" placeholder="6XX XX XX XX" autocomplete="off"></p>
  {if $wa_last_error}
  <p style="color:#b45309;font-size:13px">Dernier échec{if $wa_last_at} ({$wa_last_at|escape}){/if} : {$wa_last_error|escape}</p>
  {elseif $wa_ready && $wa_last_at}
  <p style="color:var(--wo-muted);font-size:13px">Dernier envoi OK le {$wa_last_at|escape}.</p>
  {/if}
  <button class="wo-btn wo-btn-primary" type="submit"{if !$wa_ready} disabled{/if}>Envoyer wo_hello</button>
</form>
{include file="otantik_shell_foot.tpl"}
