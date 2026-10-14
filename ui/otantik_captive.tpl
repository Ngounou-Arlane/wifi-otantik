{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Portail captif</h1>
    <p>Une page type otantikhub : connexion ticket + tarifs hotspot. Le PPPoE est sur Connectik, pas ici.</p>
  </div>
</div>
{if $routers|@count == 0}
  <div class="wo-card wo-empty">
    <div>Créez d’abord une Wifi Zone, puis revenez personnaliser l’écran Wi‑Fi.</div>
    <p><a class="wo-btn wo-btn-primary" href="{Text::url('plugin/wifi_otantik_routers/new')}">Nouvelle Wifi Zone</a></p>
  </div>
{else}
<div class="wo-card" style="margin-bottom:16px">
  <label class="wo-label">Wifi Zone</label>
  <select class="wo-select" onchange="location.href=this.value">
    {foreach $routers as $r}
      <option value="{Text::url('plugin/wifi_otantik_captive/')}{$r.id}" {if $router && $router.id == $r.id}selected{/if}>{$r.name}</option>
    {/foreach}
  </select>
</div>
{if $router}
<div class="wo-grid wo-2">
  <form class="wo-card" method="post" enctype="multipart/form-data" id="woCaptiveForm">
    <input type="hidden" name="id" value="{$router.id}">
    <h3 style="margin-top:0">Ce que voient vos clients</h3>
    <p><label class="wo-label">Nom sur l’écran Wi‑Fi</label>
      <input class="wo-input" name="portal_title" value="{$cfg.title|escape}" placeholder="{$router.name|escape}">
      <small style="color:var(--wo-muted)">Par défaut : le nom de la zone (« {$router.name} »).</small></p>
    <div class="wo-logo-up">
      <div class="wo-logo-frame">
        <img id="woLogoPrev" src="{if $logo_url}{$logo_url|escape}{/if}" alt="Logo actuel"{if !$logo_url} style="display:none"{/if}>
        {if !$logo_url}<span id="woLogoEmpty" class="wo-logo-empty">Aucun logo</span>{/if}
      </div>
      <div>
        <label class="wo-label">Logo du portail</label>
        <input class="wo-input" type="file" name="portal_logo" accept="image/jpeg,image/png,image/webp" onchange="var i=document.getElementById('woLogoPrev'),e=document.getElementById('woLogoEmpty');if(!this.files[0])return;i.src=URL.createObjectURL(this.files[0]);i.style.display='block';if(e)e.style.display='none';">
        <small style="color:var(--wo-muted)">JPG, PNG ou WEBP — 2 Mo max. Affiché à côté du nom Wi‑Fi. Après enregistrement, renvoyez le portail au MikroTik.</small>
        {if $logo_url}
        <label class="wo-switch" style="margin-top:8px"><input type="checkbox" name="clear_logo" value="1"> Retirer le logo</label>
        {/if}
      </div>
    </div>
    <p><label class="wo-label">Phrase d’accueil</label>
      <textarea class="wo-input" name="portal_welcome" rows="3">{$cfg.welcome|escape}</textarea></p>
    <p><label class="wo-label">Texte du bouton d’achat</label>
      <input class="wo-input" name="portal_buy_label" value="{$cfg.buy|escape}"></p>

    <h3>Audio d’accueil</h3>
    <p style="color:var(--wo-muted);font-size:13px;margin-top:0">Un bouton « 🔊 Écouter » explique en français comment se connecter et payer. Utile pour les clients peu habitués au portail Wi‑Fi.</p>
    <p><label class="wo-label">Mode</label>
      <select class="wo-select" name="portal_audio_mode">
        <option value="off" {if $cfg.audio_mode == 'off'}selected{/if}>Désactivé</option>
        <option value="tts" {if $cfg.audio_mode == 'tts'}selected{/if}>Voix du téléphone (gratuit, lit le texte ci-dessous)</option>
        <option value="upload" {if $cfg.audio_mode == 'upload'}selected{/if}>Fichier MP3 enregistré</option>
      </select></p>
    <p><label class="wo-label">Texte lu par la voix du téléphone</label>
      <textarea class="wo-input" name="portal_audio_text" rows="6">{$cfg.audio_text|escape}</textarea>
      <small style="color:var(--wo-muted)">Utilisé seulement en mode « Voix du téléphone ». 1500 caractères max.</small></p>
    <div class="wo-logo-up">
      <div>
        <label class="wo-label">Fichier MP3</label>
        <input class="wo-input" type="file" name="portal_audio" accept="audio/mpeg,.mp3" onchange="var a=document.getElementById('woAudioPrev');if(!this.files[0])return;a.src=URL.createObjectURL(this.files[0]);a.hidden=false;">
        <small style="color:var(--wo-muted)">MP3 — 1,5 Mo max (≈60-90 secondes). Utilisé seulement en mode « Fichier MP3 ». Après enregistrement, renvoyez le portail au MikroTik.</small>
        {if $router.portal_audio_mode == 'upload' && !$audio_has_file}
        <p style="color:#b91c1c;font-size:13px">Aucun MP3 envoyé pour l’instant : le bouton restera masqué sur le portail tant que vous n’en ajoutez pas un.</p>
        {/if}
        <audio id="woAudioPrev" controls style="display:block;margin-top:8px" {if !$audio_url}hidden{/if} src="{$audio_url|escape}"></audio>
        {if $audio_has_file}
        <label class="wo-switch" style="margin-top:8px"><input type="checkbox" name="clear_audio" value="1"> Retirer le fichier audio</label>
        {/if}
      </div>
    </div>

    <h3>Couleurs</h3>
    <p style="color:var(--wo-muted);font-size:13px;margin-top:0">Par défaut : orange <b>#f05018</b> et bleu <b>#002858</b> de Wifi-Otantik. Changez-les pour cette zone, puis renvoyez le portail au MikroTik.</p>
    <div class="wo-colors">
      <label class="wo-color-pick">
        <span>Accent (boutons, liens)</span>
        <input type="color" name="portal_color" id="woPortalAccent" value="{$cfg.color|escape}">
        <code id="woPortalAccentHex">{$cfg.color|escape}</code>
      </label>
      <label class="wo-color-pick">
        <span>Texte et en-tête</span>
        <input type="color" name="portal_navy" id="woPortalNavy" value="{$cfg.navy|escape}">
        <code id="woPortalNavyHex">{$cfg.navy|escape}</code>
      </label>
    </div>
    <button type="button" class="wo-color-preset{if $cfg.is_default_color} is-on{/if}" id="woBrandColors">
      <span class="wo-swatch" style="background:#f05018"></span>
      <span class="wo-swatch" style="background:#002858"></span>
      Couleurs officielles Wifi-Otantik
    </button>
    {if !$cfg.is_default_color}
    <label class="wo-switch" style="margin:10px 0 14px"><input type="checkbox" name="portal_reset_colors" value="1"> Revenir aux couleurs officielles</label>
    {/if}

    <h3>Image des forfaits</h3>
    <p style="color:var(--wo-muted);font-size:13px;margin-top:0">Fond des cartes tarif. Par défaut : fibre optique Wifi-Otantik.</p>
    <div class="wo-logo-up">
      <div class="wo-media-frame">
        <img id="woBundlePrev" src="{if $bundle_url}{$bundle_url|escape}{/if}" alt="Fond forfait"{if !$bundle_url} style="display:none"{/if}>
      </div>
      <div>
        <label class="wo-label">Image de fond</label>
        <input class="wo-input" type="file" name="portal_bundle" accept="image/jpeg,image/png,image/webp" onchange="var i=document.getElementById('woBundlePrev');if(!this.files[0])return;i.src=URL.createObjectURL(this.files[0]);i.style.display='block';">
        <small style="color:var(--wo-muted)">JPG, PNG ou WEBP — 3 Mo max.</small>
        {if $bundle_custom}
        <label class="wo-switch" style="margin-top:8px"><input type="checkbox" name="clear_bundle" value="1"> Revenir à l’image fibre par défaut</label>
        {/if}
      </div>
    </div>

    <h3>Slider</h3>
    <p style="color:var(--wo-muted);font-size:13px;margin-top:0">Trois bandeaux en haut de l’écran Wi‑Fi. Hauteur réduite, overlay bleu Wifi-Otantik. Sans image perso, la fibre est utilisée.</p>
    {foreach $cfg.slides as $s}
    {assign var=n value=$s@iteration}
    <div class="wo-slide-edit">
      <div class="wo-media-frame sm">
        <img id="woSlidePrev{$n}" src="{$slide_urls[$n]|escape}" alt="Slide {$n}">
      </div>
      <div>
        <label class="wo-label">Slide {$n} — titre</label>
        <input class="wo-input" name="slide_title_{$n}" value="{$s.title|escape}">
        <label class="wo-label">Texte</label>
        <input class="wo-input" name="slide_text_{$n}" value="{$s.text|escape}">
        <input class="wo-input" type="file" name="slide_img_{$n}" accept="image/jpeg,image/png,image/webp" onchange="var i=document.getElementById('woSlidePrev{$n}');if(!this.files[0])return;i.src=URL.createObjectURL(this.files[0]);">
        {if $slide_custom[$n]}
        <label class="wo-switch" style="margin-top:8px"><input type="checkbox" name="clear_slide_{$n}" value="1"> Image fibre par défaut</label>
        {/if}
      </div>
    </div>
    {/foreach}

    <h3>Message d’aide</h3>
    <p><label class="wo-label">Petit titre</label>
      <input class="wo-input" name="portal_help_kicker" value="{$cfg.help.kicker|escape}"></p>
    <p><label class="wo-label">Titre</label>
      <input class="wo-input" name="portal_help_title" value="{$cfg.help.title|escape}"></p>
    <p><label class="wo-label">Texte</label>
      <textarea class="wo-input" name="portal_help_text" rows="3">{$cfg.help.text|escape}</textarea></p>
    <p><label class="wo-label">Bouton</label>
      <input class="wo-input" name="portal_help_btn" value="{$cfg.help.btn|escape}"></p>

    <h3>WhatsApp flottant</h3>
    <p style="color:var(--wo-muted);font-size:13px;margin-top:0">Bouton vert en bas à droite. Il utilise le numéro WhatsApp de la section Contacts.</p>
    <input type="hidden" name="portal_wa_on" value="0">
    <label class="wo-switch"><input type="checkbox" name="portal_wa_on" value="1" {if $cfg.wa_on}checked{/if}> Afficher le bouton WhatsApp</label>
    <p><label class="wo-label">Message prérempli</label>
      <input class="wo-input" name="portal_wa_text" value="{$cfg.wa_text|escape}" placeholder="Bonjour, j’ai besoin d’aide pour le Wi-Fi…"></p>

    <h3>Page contact</h3>
    <p><label class="wo-label">Phrase d’intro</label>
      <input class="wo-input" name="portal_contact_intro" value="{$cfg.contact_intro|escape}" placeholder="Un souci ? Écrivez-nous."></p>
    <p><label class="wo-label">Site web</label>
      <input class="wo-input" name="portal_web" value="{$cfg.web|escape}" placeholder="https://…"></p>
    <p><label class="wo-label">Facebook</label>
      <input class="wo-input" name="portal_facebook" value="{$cfg.facebook|escape}" placeholder="https://facebook.com/…"></p>

    <h3>Bloc services</h3>
    <input type="hidden" name="portal_services_on" value="0">
    <label class="wo-switch" style="margin-bottom:12px"><input type="checkbox" name="portal_services_on" value="1" {if $cfg.services_on}checked{/if}> Afficher « Nos services » dans le menu</label>
    {foreach $cfg.services as $sv}
    {assign var=n value=$sv@iteration}
    <p><label class="wo-label">Service {$n}</label>
      <input class="wo-input" name="svc_title_{$n}" value="{$sv.title|escape}" placeholder="Titre">
      <textarea class="wo-input" name="svc_text_{$n}" rows="2" placeholder="Description">{$sv.text|escape}</textarea></p>
    {/foreach}

    <h3>Codes ticket hotspot</h3>
    <p style="color:var(--wo-muted);font-size:13px;margin-top:0">Après un paiement réussi, le client reçoit <b>un mot français simple</b> (ex. <code>lune</code>, <code>cafe</code>). C’est son code Wi‑Fi : il le saisit sur le portail captif. Au rachat, le même code est réutilisé. Si un mot est déjà pris, un second mot est ajouté (<code>lune-ciel</code>).</p>

    <h3>Affiliation et crédit (points)</h3>
    <p style="color:var(--wo-muted);font-size:13px;margin-top:0">Pendant le paiement, le client peut indiquer le numéro d’un autre utilisateur. Ce numéro reçoit des points. L’option « Payer ce forfait avec mes points » n’apparaît que lorsque le solde atteint le seuil ci-dessous <b>et</b> le prix du forfait choisi (1 point = 1 FCFA).</p>
    <input type="hidden" name="portal_ref_on" value="0">
    <label class="wo-switch" style="margin-bottom:12px"><input type="checkbox" name="portal_ref_on" value="1" {if $cfg.ref_on}checked{/if}> Activer l’affiliation et le paiement par points</label>
    <p><label class="wo-label">Points offerts à l’ami parrainé</label>
      <input class="wo-input" type="number" name="portal_ref_points" min="1" max="10000" value="{$cfg.ref_points|default:50}"></p>
    <p><label class="wo-label">Seuil minimum pour payer avec les points</label>
      <input class="wo-input" type="number" name="portal_ref_bundle_pts" min="1" max="100000" value="{$cfg.ref_bundle_pts|default:100}">
      <small style="color:var(--wo-muted)">Le client voit l’option de paiement par points seulement s’il a au moins ce nombre de points, et assez pour le forfait choisi.</small></p>
    <p><label class="wo-label">Texte « En savoir plus »</label>
      <textarea class="wo-input" name="portal_ref_help" rows="5" placeholder="Expliquez le parrainage et les points…">{$cfg.ref_help|escape}</textarea></p>
    <p><label class="wo-label">Règles d’utilisation des points</label>
      <textarea class="wo-input" name="portal_ref_rules" rows="5" placeholder="Les règles affichées au client…">{$cfg.ref_rules|escape}</textarea></p>

    <h3>Contacts</h3>
    <p><label class="wo-label">Téléphone</label>
      <input class="wo-input" name="portal_phone" value="{$router.portal_phone|escape}" placeholder="{$router.assistance_contact|escape}"></p>
    <p><label class="wo-label">WhatsApp</label>
      <input class="wo-input" name="portal_whatsapp" value="{$router.portal_whatsapp|escape}" placeholder="Si vide, le téléphone est utilisé"></p>
    <p><label class="wo-label">Email</label>
      <input class="wo-input" type="email" name="portal_email" value="{$router.portal_email|escape}"></p>

    <h3>Messages Telegram</h3>
    <p style="color:var(--wo-muted);font-size:13px;margin-top:0">Les clés restent sur le serveur. Un Chat ID personnel (chiffres, via @userinfobot) alimente aussi le live chat Telegram de la page Chat. Un ID de groupe (commençant par -) ne sert qu’aux notifications du formulaire.</p>
    <p><label class="wo-label">Token du bot</label>
      <input class="wo-input" name="tg_bot_token" type="password" autocomplete="off" placeholder="{if $cfg.tg}Token enregistré — laisser vide pour garder{else}Ex. 123456:ABC… depuis @BotFather{/if}"></p>
    <p><label class="wo-label">Chat ID</label>
      <input class="wo-input" name="tg_chat_id" value="{$router.tg_chat_id|escape}" placeholder="Ex. 123456789 — @userinfobot"></p>
    <label class="wo-switch" style="margin:8px 0 14px"><input type="checkbox" name="clear_tg" value="1"> Retirer le bot Telegram</label>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <button class="wo-btn wo-btn-primary" type="submit" name="save" value="1">Enregistrer</button>
      <button class="wo-btn wo-btn-ghost" type="submit" name="save_push" value="1" id="woPushMikrotik">Enregistrer et envoyer au MikroTik</button>
      <button class="wo-link-btn" type="submit" name="test_tg" value="1">Tester</button>
    </div>
  </form>
  <div>
    <div class="wo-phone">
      <iframe title="Aperçu portail captif" src="{$preview_url|escape}" loading="eager"></iframe>
    </div>
    <p style="color:var(--wo-muted);font-size:12px;text-align:center;margin-top:10px">Aperçu. Après « envoyer au MikroTik », les téléphones voient cette page sur le Wi‑Fi.</p>
  </div>
</div>
{/if}
{/if}
<div class="wo-xfer" id="woXfer" hidden>
  <div class="wo-xfer-card" role="dialog" aria-modal="true" aria-labelledby="woXferTitle">
    <div class="wo-xfer-top">
      <div>
        <p class="wo-xfer-kicker">Transfert vers le routeur</p>
        <h2 id="woXferTitle">Envoi du portail captif</h2>
        <p class="wo-xfer-sub" id="woXferSub">{if $router}{$router.name|escape}{/if} · dossier hotspot/</p>
      </div>
      <span class="wo-xfer-pct" id="woXferPct">0%</span>
    </div>
    <div class="wo-xfer-stage">
      <div class="wo-xfer-node">
        <div class="wo-xfer-folder" aria-hidden="true">
          <svg viewBox="0 0 64 52" width="56" height="46"><path d="M6 10h20l4 6h28a6 6 0 0 1 6 6v22a6 6 0 0 1-6 6H6a6 6 0 0 1-6-6V16a6 6 0 0 1 6-6z" fill="#f05018"/><path d="M0 22h64v22a6 6 0 0 1-6 6H6a6 6 0 0 1-6-6V22z" fill="#d44510"/></svg>
        </div>
        <small>Wifi-Otantik</small>
      </div>
      <div class="wo-xfer-path" aria-hidden="true">
        <i></i><i></i><i></i><i></i>
        <span class="wo-xfer-chip" id="woXferChip">login.html</span>
      </div>
      <div class="wo-xfer-node">
        <div class="wo-xfer-router" aria-hidden="true">
          <svg viewBox="0 0 80 56" width="72" height="50">
            <rect x="4" y="18" width="72" height="34" rx="10" fill="#002858"/>
            <rect x="12" y="26" width="18" height="6" rx="3" fill="#f05018"/>
            <circle cx="58" cy="29" r="4" fill="#10b981"/>
            <circle cx="68" cy="29" r="4" fill="#64748b"/>
            <path d="M28 12c8-10 24-10 32 0" stroke="#f05018" stroke-width="3" fill="none" stroke-linecap="round"/>
            <path d="M34 16c5-6 15-6 20 0" stroke="#f05018" stroke-width="3" fill="none" stroke-linecap="round"/>
          </svg>
        </div>
        <small>MikroTik</small>
      </div>
    </div>
    <div class="wo-xfer-bar"><b id="woXferBar"></b></div>
    <p class="wo-xfer-now" id="woXferNow">Préparation de l’envoi…</p>
    <ul class="wo-xfer-files" id="woXferFiles"></ul>
    <p class="wo-xfer-foot" id="woXferFoot" hidden>
      <button type="button" class="wo-btn wo-btn-primary" id="woXferClose">Continuer</button>
    </p>
  </div>
</div>
<script>
(function(){
  var a=document.getElementById('woPortalAccent');
  var n=document.getElementById('woPortalNavy');
  var ah=document.getElementById('woPortalAccentHex');
  var nh=document.getElementById('woPortalNavyHex');
  function hex(el,lab){ if(el&&lab) lab.textContent=el.value; }
  if(a){ a.addEventListener('input', function(){ hex(a,ah); }); }
  if(n){ n.addEventListener('input', function(){ hex(n,nh); }); }
  var b=document.getElementById('woBrandColors');
  if(b){
    b.addEventListener('click', function(){
      if(a){ a.value='#f05018'; hex(a,ah); }
      if(n){ n.value='#002858'; hex(n,nh); }
      b.classList.add('is-on');
    });
  }
})();
</script>
<script>
var woPushFiles = {if $push_files_json}{$push_files_json}{else}[]{/if};
{literal}
(function(){
  var form = document.getElementById('woCaptiveForm');
  var btn = document.getElementById('woPushMikrotik');
  var box = document.getElementById('woXfer');
  if (!form || !btn || !box) return;
  var files = Array.isArray(woPushFiles) ? woPushFiles : [];
  var list = document.getElementById('woXferFiles');
  var bar = document.getElementById('woXferBar');
  var pct = document.getElementById('woXferPct');
  var now = document.getElementById('woXferNow');
  var chip = document.getElementById('woXferChip');
  var foot = document.getElementById('woXferFoot');
  var close = document.getElementById('woXferClose');
  var timer = null;
  var i = 0;
  var done = false;
  function ext(name){
    var e = String(name||'').split('.').pop().toLowerCase();
    if (e === 'html') return 'html';
    if (e === 'js') return 'js';
    if (e === 'png' || e === 'jpg' || e === 'jpeg' || e === 'webp') return 'img';
    return 'file';
  }
  function paint(idx, mode){
    var n = files.length || 1;
    var p = Math.min(99, Math.round((idx / n) * 100));
    if (mode === 'ok') p = 100;
    if (bar) bar.style.width = p + '%';
    if (pct) pct.textContent = p + '%';
    var name = files[Math.min(idx, n - 1)] || '';
    if (chip) chip.textContent = name || 'fichiers';
    if (now && mode !== 'ok' && mode !== 'err') now.textContent = name ? ('Envoi de ' + name + '…') : 'Connexion au MikroTik…';
    [].slice.call(list.querySelectorAll('li')).forEach(function(li, k){
      li.className = k < idx ? 'is-done' : (k === idx ? 'is-run' : '');
    });
  }
  function build(){
    list.innerHTML = '';
    files.forEach(function(f){
      var li = document.createElement('li');
      li.innerHTML = '<i class="wo-xfer-ico '+ext(f)+'"></i><span>'+f+'</span><em></em>';
      list.appendChild(li);
    });
  }
  function finish(ok, msg){
    done = true;
    if (timer) clearInterval(timer);
    paint(files.length, ok ? 'ok' : 'err');
    box.classList.toggle('is-ok', ok);
    box.classList.toggle('is-err', !ok);
    if (now) now.textContent = msg || (ok ? 'Portail installé sur le MikroTik.' : 'Échec de l’envoi.');
    if (pct) pct.textContent = ok ? '100%' : '!';
    if (foot) foot.hidden = false;
    btn.disabled = false;
  }
  function start(ev){
    ev.preventDefault();
    if (btn.disabled) return;
    btn.disabled = true;
    done = false;
    i = 0;
    build();
    box.hidden = false;
    box.classList.remove('is-ok','is-err');
    if (foot) foot.hidden = true;
    paint(0);
    timer = setInterval(function(){
      if (done) return;
      if (i < files.length - 1) i++;
      paint(i);
    }, 420);
    var fd = new FormData(form);
    fd.set('save_push', '1');
    fd.set('ajax', '1');
    var ctrl = typeof AbortSignal !== 'undefined' && AbortSignal.timeout ? AbortSignal.timeout(180000) : undefined;
    fetch(form.action || location.href, {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
      signal: ctrl
    }).then(function(r){
      return r.json().then(function(d){ return {ok: r.ok && d && d.ok, message: (d && d.message) || ''}; }).catch(function(){
        return {ok: false, message: 'Réponse inattendue du serveur.'};
      });
    }).then(function(d){
      finish(!!d.ok, d.message);
    }).catch(function(){
      finish(false, 'Réseau coupé pendant l’envoi. Vérifiez le tunnel, puis réessayez.');
    });
  }
  btn.addEventListener('click', start);
  if (close) close.addEventListener('click', function(){ location.reload(); });
})();
{/literal}
</script>
{include file="otantik_shell_foot.tpl"}
