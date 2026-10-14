<!DOCTYPE HTML>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">
<title>Mon code — {$company|escape}</title>
<link rel="icon" href="{$app_url}/ui/ui_custom/images/favicon.png" type="image/png">
<link rel="stylesheet" href="{$kit_url}/styles/bootstrap.css">
<link rel="stylesheet" href="{$kit_url}/fonts/bootstrap-icons.css">
<link rel="stylesheet" href="{$kit_url}/styles/style.css?v=wo-hs8">
<meta id="theme-check" name="theme-color" content="#FFFFFF">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<style id="wo-hs-theme">{if isset($hs_theme_css)}{$hs_theme_css nofilter}{/if}</style>
{literal}
<style>
.wo-hs-logo{height:32px;width:auto;max-width:88px;object-fit:contain;margin:0 8px 0 0;border-radius:6px}
.header-bar .header-title{display:flex;align-items:center}
.wo-pay-op{display:none;align-items:center;gap:8px;margin:-4px 0 12px;font-size:13px;font-weight:700}
.wo-pay-op.on{display:flex}
.wo-pay-op .dot{width:8px;height:8px;border-radius:50%}
.wo-pay-op.mtn{color:#854d0e}
.wo-pay-op.mtn .dot{background:#facc15}
.wo-pay-op.om{color:#9a3412}
.wo-pay-op.om .dot{background:var(--wo-hs-accent,#f05018)}
.wo-pay-op.bad{color:#b91c1c}
.wo-pay-op.bad .dot{background:#ef4444}
.wo-phone-err{display:none;color:#b91c1c;font-size:12px;font-weight:700;margin:-6px 0 10px}
.wo-ticket-card{text-align:center;padding:8px 0 4px}
.wo-ticket-k{font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b}
.wo-ticket-v{margin:12px 0 10px;font-size:28px;font-weight:800;letter-spacing:.14em;color:var(--wo-hs-accent,#f05018);word-break:break-all}
.wo-ticket-meta{margin:0;color:#64748b;font-size:13px}
.wo-empty{text-align:center;padding:8px 0}
.wo-empty p{color:#64748b;margin:0 0 14px}
.wo-nav-svg{width:18px;height:18px;display:block}
#footer-bar a:not(.btn) svg.wo-nav-svg{width:18px;height:18px;margin:0 auto 2px}
.wo-lookup-toggle{border:0;background:none;padding:0;color:var(--wo-hs-navy,#002858);font-weight:700;font-size:13px;cursor:pointer}
.wo-ticket-actions{display:grid;gap:10px;margin:16px 0 4px}
.wo-ticket-actions .btn{margin:0}
.wo-btn-copy{background:#fff;border:1.5px solid var(--wo-hs-accent,#f05018);color:var(--wo-hs-accent,#f05018)}
.wo-ticket-v{cursor:pointer}
</style>
{/literal}
</head>
<body class="theme-light">
<div id="page">
  <div class="header-bar header-fixed header-app header-bar-detached">
    <a href="{$hs_home}" class="color-theme" aria-label="Retour"><i class="bi bi-chevron-left font-18"></i></a>
    {if $kit_logo}<img class="wo-hs-logo" src="{$kit_logo|escape}" alt="">{/if}
    <a href="{$hs_home}" class="header-title color-theme">{$kit_title|default:$company|escape}</a>
  </div>
  <div id="footer-bar" class="footer-bar footer-bar-detached">
    <a href="{$hs_home}"><i class="bi bi-house-fill font-16"></i><span>Home</span></a>
    <a href="{$hs_ticket}"><i class="bi bi-heart-fill font-15"></i><span>Tarifs</span></a>
    <a href="{$recover_url}" class="active-nav"><svg class="wo-nav-svg" viewBox="0 0 16 16" aria-hidden="true"><path fill="currentColor" d="M0 4.5A1.5 1.5 0 0 1 1.5 3h13A1.5 1.5 0 0 1 16 4.5V6a.5.5 0 0 1-.5.5 1.5 1.5 0 1 0 0 3 .5.5 0 0 1 .5.5v1.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 11.5V10a.5.5 0 0 1 .5-.5 1.5 1.5 0 0 0 0-3A.5.5 0 0 1 0 6V4.5Z"/></svg><span>Mon code</span></a>
  </div>
  <div class="page-content header-clear-medium">
    <div class="card card-style">
      <div class="content">
        <h3>Mon code</h3>
        {if $credit_notify}<div class="alert wo-login-err" role="alert"><strong>Attention</strong><span class="wo-err-txt">{$credit_notify|escape}</span></div>{/if}
        {if $ticket_ok}
        <div class="wo-ticket-card">
          <div class="wo-ticket-k">{if $plan_kind == 'PPPOE'}Identifiants PPPoE{else}Votre code ticket{/if}</div>
          {if $plan_kind == 'PPPOE'}
          <p class="wo-ticket-v" id="woCodeVal" data-copy="{$creds.username|escape}">{$creds.username|escape}</p>
          <p class="wo-ticket-meta">Mot de passe : <b id="woPassVal" data-copy="{$creds.password|escape}">{$creds.password|escape}</b></p>
          {if $plan_name}<p class="wo-ticket-meta">Forfait {$plan_name|escape}</p>{/if}
          <div class="wo-ticket-actions">
            <button type="button" class="btn btn-full rounded-xs font-700 w-100 btn-s wo-btn-copy" data-copy-btn="woCodeVal">Copier l’identifiant</button>
            <button type="button" class="btn btn-full rounded-xs font-700 w-100 btn-s wo-btn-copy" data-copy-btn="woPassVal">Copier le mot de passe</button>
          </div>
          {else}
          <p class="wo-ticket-v" id="woCodeVal" data-copy="{$creds.username|escape}">{$creds.username|escape}</p>
          <p class="wo-ticket-meta">{if $plan_name}Forfait {$plan_name|escape}.{/if}</p>
          <div class="wo-ticket-actions">
            <button type="button" class="btn btn-full rounded-xs font-700 w-100 btn-s wo-btn-copy" data-copy-btn="woCodeVal">Copier le code</button>
            <a class="btn btn-full bg-highlight rounded-xs text-uppercase font-700 w-100 btn-s" href="{$hs_login_url|escape}">Se connecter pour profiter d’Internet</a>
          </div>
          {/if}
        </div>
        {if $hs_credit > 0}
        <p class="wo-ticket-meta" style="text-align:center">Crédit : <b>{$hs_credit|string_format:"%.0f"} F</b></p>
        {if $can_credit}
        <form method="post">
          <input type="hidden" name="phone" value="{$phone|escape}">
          <button class="btn btn-full bg-highlight rounded-xs text-uppercase font-700 w-100 btn-s mt-3" type="submit" name="use_credit" value="1">Me connecter avec mon crédit</button>
        </form>
        {/if}
        {/if}
        <p class="text-center mt-3 mb-0"><button type="button" class="wo-lookup-toggle" id="woLookupOpen">Autre numéro</button></p>
        {else}
        <div class="wo-empty">
          <p>{if $ticket_expired || $not_found}Vous n’avez pas de code valide. Achetez un forfait.{else}Entrez le numéro utilisé pour payer, ou achetez un forfait.{/if}</p>
          <a class="btn btn-full bg-highlight rounded-xs text-uppercase font-700 w-100 btn-s" href="{$buy_url}">Acheter un forfait</a>
        </div>
        {/if}
        <form method="post" id="woRec" class="mt-4{if $ticket_ok} d-none{/if}">
          <div class="form-custom form-label form-icon mb-3">
            <i class="bi bi-phone font-14"></i>
            <input class="form-control rounded-xs" name="phone" id="woPhone" value="{$phone|escape}" placeholder="6XX XXX XXX" required inputmode="tel">
            <label class="color-theme">Numéro utilisé pour payer</label>
          </div>
          <div class="wo-pay-op" id="woOp"><span class="dot"></span><span class="txt"></span></div>
          <p class="wo-phone-err" id="woPhoneErr"></p>
          <button class="btn btn-full bg-highlight rounded-xs text-uppercase font-700 w-100 btn-s" type="submit">Afficher mon code</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="{$kit_url}/scripts/bootstrap.min.js"></script>
<script src="{$kit_url}/scripts/custom.js?v=wo-hs8"></script>
<script src="{$app_url}/ui/ui_custom/wo-cm-phone.js?v=wo-hs8"></script>
{literal}
<script>
(function(){
  if (window.woCmPhone) woCmPhone.bind(document.getElementById('woPhone'), document.getElementById('woOp'), document.getElementById('woPhoneErr'));
  var open=document.getElementById('woLookupOpen');
  var form=document.getElementById('woRec');
  if(open&&form){
    open.addEventListener('click', function(){
      form.classList.remove('d-none');
      var i=document.getElementById('woPhone');
      if(i) i.focus();
    });
  }
  function copyText(t, btn){
    t=String(t||'');
    if(!t) return;
    function ok(){
      if(!btn) return;
      var old=btn.getAttribute('data-label')||btn.textContent;
      btn.setAttribute('data-label', old);
      btn.textContent='Copié';
      setTimeout(function(){ btn.textContent=old; }, 1600);
    }
    if(navigator.clipboard && navigator.clipboard.writeText){
      navigator.clipboard.writeText(t).then(ok).catch(function(){
        var a=document.createElement('textarea');
        a.value=t; document.body.appendChild(a); a.select();
        try{ document.execCommand('copy'); ok(); }catch(e){}
        document.body.removeChild(a);
      });
      return;
    }
    var a=document.createElement('textarea');
    a.value=t; document.body.appendChild(a); a.select();
    try{ document.execCommand('copy'); ok(); }catch(e){}
    document.body.removeChild(a);
  }
  document.querySelectorAll('[data-copy-btn]').forEach(function(btn){
    btn.addEventListener('click', function(){
      var el=document.getElementById(btn.getAttribute('data-copy-btn'));
      copyText(el?el.getAttribute('data-copy')||el.textContent:'', btn);
    });
  });
  var code=document.getElementById('woCodeVal');
  if(code){
    code.addEventListener('click', function(){
      var btn=document.querySelector('[data-copy-btn="woCodeVal"]');
      copyText(code.getAttribute('data-copy')||code.textContent, btn);
    });
  }
})();
</script>
{/literal}
</body>
</html>
