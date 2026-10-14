<!DOCTYPE HTML>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">
<title>{if isset($plan_kind) && $plan_kind == 'PPPOE'}Votre ligne PPPoE{else}Paiement réussi{/if} — {$company|escape}</title>
<link rel="icon" href="{$app_url}/ui/ui_custom/images/favicon.png" type="image/png">
<link rel="stylesheet" href="{$kit_url}/styles/bootstrap.css">
<link rel="stylesheet" href="{$kit_url}/fonts/bootstrap-icons.css">
<link rel="stylesheet" href="{$kit_url}/styles/style.css?v=wo-hs11">
<meta id="theme-check" name="theme-color" content="#FFFFFF">
<style id="wo-hs-theme">{if isset($hs_theme_css)}{$hs_theme_css nofilter}{/if}</style>
<style>
{literal}
#preloader{display:none!important}
.wo-hs-logo{height:32px;width:auto;max-width:88px;object-fit:contain;margin:0 8px 0 0;border-radius:6px}
.header-bar .header-title{display:flex;align-items:center}
.wo-voucher-card{text-align:center}
.wo-voucher-mark{width:56px;height:56px;border-radius:50%;background:#dcfce7;color:#166534;display:grid;place-items:center;margin:0 auto 12px;font-size:24px}
.theme-dark .wo-voucher-mark{background:#052e1b;color:#4ade80}
.wo-code-row{display:flex;align-items:center;justify-content:center;gap:10px;margin:6px 0 14px}
.wo-code-row .code{font-size:26px;font-weight:800;letter-spacing:2px;color:var(--wo-hs-accent,#f05018)}
.wo-copy-btn{display:inline-flex;align-items:center;gap:4px;border:1px solid var(--wo-hs-navy,#002858);background:transparent;color:var(--wo-hs-navy,#002858);font-size:11px;font-weight:700;padding:3px 10px;border-radius:999px;cursor:pointer}
.wo-copy-btn:active{opacity:.7}
.theme-dark .wo-copy-btn{border-color:#f8fafc;color:#f8fafc}
{/literal}
</style>
</head>
<body class="theme-light">
<div id="page">
  <div class="header-bar header-fixed header-app header-bar-detached">
    {if $kit_logo}<img class="wo-hs-logo" src="{$kit_logo|escape}" alt="">{/if}
    <a href="{$hs_home}" class="header-title color-theme">{$kit_title|default:$company}</a>
    <a href="#" class="show-on-theme-light default-link" data-toggle-theme><i class="bi bi-moon-fill font-13"></i></a>
    <a href="#" class="show-on-theme-dark default-link" data-toggle-theme><i class="bi bi-lightbulb-fill color-yellow-dark font-13"></i></a>
  </div>
  <div class="page-content header-clear-medium">
    <div class="card card-style">
      <div class="content wo-voucher-card">
        <div class="wo-voucher-mark"><i class="bi bi-check-lg"></i></div>
        <h3>Paiement réussi</h3>
        <p class="mb-3">Forfait <b>{$plan.name_plan}</b> · {if isset($plan_kind) && $plan_kind == 'PPPOE'}PPPoE{else}Hotspot{/if}</p>
        <p class="mb-1 font-13 opacity-70">{if isset($plan_kind) && $plan_kind == 'PPPOE'}Identifiant{else}Votre code Wi‑Fi — notez-le{/if}</p>
        <div class="wo-code-row"><div class="code" id="woCodeUser">{$wifi_user}</div><button type="button" class="wo-copy-btn" data-copy="#woCodeUser">Copier</button></div>
        {if isset($plan_kind) && $plan_kind == 'PPPOE'}
        <p class="mb-1 font-13 opacity-70">Mot de passe</p>
        <div class="wo-code-row"><div class="code" id="woCodePass">{$wifi_pass}</div><button type="button" class="wo-copy-btn" data-copy="#woCodePass">Copier</button></div>
        {/if}
        {if isset($push_error)}<p class="text-danger font-13">Le routeur n'a pas reçu le compte : {$push_error}. Le code reste valable.</p>{/if}
        {if isset($plan_kind) && $plan_kind == 'PPPOE'}
        <p class="font-13 opacity-70">Sur votre routeur / ONU, créez une connexion PPPoE avec ces identifiants, service <b>pppoe-otantik</b>.</p>
        {else}
        <p class="font-13 opacity-70 mb-2">Paiement confirmé. Le statut en ligne s’ouvre dans <b id="woHsSec">8</b> s…</p>
        {if isset($plan_left) && $plan_left}<p class="font-13 mb-1">Temps restant estimé : <b>{$plan_left|escape}</b></p>{/if}
        {if isset($plan_expires) && $plan_expires}<p class="font-13 opacity-70 mb-2">Expire le {$plan_expires|escape}</p>{/if}
        <a id="woHsGo" href="{$status_url|default:'#'}" hidden></a>
        {/if}
        {if $hs_credit > 0}
        <p class="font-13 mt-3">Crédit : <b>{$hs_credit|string_format:"%.0f"} F</b></p>
        {/if}
      </div>
    </div>
  </div>
</div>
<script src="{$kit_url}/scripts/bootstrap.min.js"></script>
<script src="{$kit_url}/scripts/custom.js?v=wo-hs11"></script>
<script>
try{
  localStorage.setItem('wo_hs_code', '{$wifi_user|escape:"javascript"}');
  {if $ticket_phone}localStorage.setItem('wo_hs_phone', '{$ticket_phone|escape:"javascript"}');{/if}
  document.cookie='wo_hs_code='+encodeURIComponent('{$wifi_user|escape:"javascript"}')+';Path=/;Max-Age=604800;SameSite=Lax';
}catch(e){}
document.addEventListener('click', function(ev){
  var btn = ev.target.closest ? ev.target.closest('[data-copy]') : null;
  if(!btn) return;
  var src = document.querySelector(btn.getAttribute('data-copy'));
  if(!src) return;
  var text = src.textContent;
  function done(ok){
    var old = btn.textContent;
    btn.textContent = ok ? 'Copié !' : 'Erreur';
    btn.disabled = true;
    setTimeout(function(){ btn.textContent = old; btn.disabled = false; }, 1500);
  }
  if(navigator.clipboard && window.isSecureContext){
    navigator.clipboard.writeText(text).then(function(){ done(true); }, function(){ done(false); });
    return;
  }
  try{
    var ta = document.createElement('textarea');
    ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
    document.body.appendChild(ta); ta.focus(); ta.select();
    var ok = document.execCommand('copy');
    document.body.removeChild(ta);
    done(ok);
  }catch(e){ done(false); }
});
{if !isset($plan_kind) || $plan_kind != 'PPPOE'}
(function(){
  var n=8, el=document.getElementById('woHsSec'), a=document.getElementById('woHsGo');
  if(!a) return;
  var href=a.getAttribute('href')||'';
  if(!href || href==='#') return;
  var t=setInterval(function(){
    n--;
    if(el) el.textContent=String(n);
    if(n<=0){ clearInterval(t); location.href=href; }
  },1000);
})();
{/if}
</script>
</body>
</html>
