<!DOCTYPE HTML>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">
<title>{if $channel == 'PPPOE'}Connectik{else}{$kit_title|default:$router.name}{/if} — {$company}</title>
<link rel="icon" href="{$app_url}/ui/ui_custom/images/favicon.png" type="image/png">
<link rel="stylesheet" href="{$kit_url}/styles/bootstrap.css">
<link rel="stylesheet" href="{$kit_url}/fonts/bootstrap-icons.css">
<link rel="stylesheet" href="{$kit_url}/styles/style.css?v=wo-hs10">
<meta id="theme-check" name="theme-color" content="#FFFFFF">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<style id="wo-hs-theme">{if isset($hs_theme_css)}{$hs_theme_css nofilter}{/if}</style>
{literal}
<style>
.wo-hs-logo{height:32px;width:auto;max-width:88px;object-fit:contain;margin:0 8px 0 0;border-radius:6px}
.header-bar .header-title{display:flex;align-items:center}
.wo-pay-plan{display:block;border:1.5px solid #e2e8f0;border-radius:14px;padding:12px 14px;margin:0 0 10px;cursor:pointer;color:var(--wo-hs-navy,#002858)}
.wo-pay-plan input{accent-color:var(--wo-hs-accent,#f05018);margin-right:8px}
.wo-pay-plan:has(input:checked),.wo-pay-plan.on{border-color:var(--wo-hs-accent,#f05018);background:var(--wo-hs-wash,#fff8f5)}
.wo-pay-alert{background:#fff6e5;color:#92400e;border:1px solid #facc15;border-radius:12px;padding:10px 12px;margin-bottom:14px}
.wo-pay-sel{background:linear-gradient(160deg,var(--wo-hs-navy,#002858),var(--wo-hs-navy-dark,#001e42) 55%,var(--wo-hs-navy-mix,#3a1a10));color:#fff;border-radius:16px;padding:16px;margin-bottom:14px}
.wo-pay-sel small{display:block;opacity:.75;font-weight:700}
.wo-pay-sel strong{display:block;font-size:26px;line-height:1.1}
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
.wo-nav-svg{width:18px;height:18px;display:block}
#footer-bar a:not(.btn) svg.wo-nav-svg{width:18px;height:18px;margin:0 auto 2px}
.wo-ref-line{margin:10px 0 0;text-align:left}
.wo-ref-line button{border:0;background:none;padding:0;color:var(--wo-hs-navy,#002858);font-size:13px;font-weight:700;cursor:pointer}
.wo-ref-box{display:none;margin-top:8px}
.wo-ref-box.on{display:block}
.wo-pts{display:none;margin:8px 0 4px;padding:10px 12px;border-radius:12px;background:var(--wo-hs-wash,#fff8f5);border:1px solid #fed7c2;color:var(--wo-hs-navy,#002858);font-size:13px}
.wo-pts.on{display:block}
.wo-pts label{display:flex;align-items:flex-start;gap:8px;margin:8px 0 0;font-weight:600;cursor:pointer}
.wo-more{margin:8px 0 0;font-size:12px}
.wo-more button{border:0;background:none;padding:0;color:var(--wo-hs-accent,#f05018);font-weight:700;cursor:pointer}
.wo-ref-modal{display:none;position:fixed;inset:0;background:rgba(0,40,88,.45);z-index:80;align-items:flex-end;justify-content:center;padding:16px}
.wo-ref-modal.on{display:flex}
.wo-ref-modal .box{background:#fff;border-radius:16px 16px 0 0;padding:18px 16px 24px;max-height:80vh;overflow:auto;width:100%;max-width:480px;color:var(--wo-hs-navy,#002858)}
.wo-ref-modal h4{margin:0 0 8px}
.wo-ref-modal p,.wo-ref-modal pre{white-space:pre-wrap;font-family:inherit;font-size:13px;line-height:1.45;margin:0 0 10px}
.wo-pay-hint{background:#fff8f5;border:1px solid #fed7c2;border-radius:12px;padding:10px 12px;margin:4px 0 0;font-size:13px;line-height:1.45;color:#002858}
.wo-pay-hint code{font-weight:800;font-size:14px}
.wo-pay-scrim{position:fixed;top:0;left:0;right:0;bottom:0;width:100%;height:100%;height:-webkit-fill-available;height:100dvh;min-height:100vh;z-index:2147483000;background:rgba(0,20,48,.62);display:none;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;padding:18px;padding-bottom:max(18px,env(safe-area-inset-bottom));-webkit-transform:translate3d(0,0,0);transform:translate3d(0,0,0);-webkit-overflow-scrolling:touch;backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px)}
.wo-pay-scrim[hidden]{display:none!important}
.wo-pay-scrim.is-on{display:-webkit-box!important;display:-webkit-flex!important;display:flex!important}
.wo-pay-wait{width:min(420px,100%);background:#fff;border-radius:22px;padding:26px 20px 22px;text-align:center;color:#002858;box-shadow:0 20px 50px rgba(0,40,88,.28);-webkit-transform:translateZ(0);transform:translateZ(0)}
.wo-pay-wait .wo-spin-wrap{position:relative;width:56px;height:56px;margin:0 auto 16px}
.wo-pay-wait .spin,.wo-pay-wait .spin2{position:absolute;inset:0;border-radius:50%;border:3px solid #fed7c2;border-top-color:#f05018;-webkit-animation:woSpin .75s linear infinite;animation:woSpin .75s linear infinite}
.wo-pay-wait .spin2{inset:8px;border-width:2px;border-color:#e2e8f0;border-top-color:#002858;-webkit-animation-duration:1.1s;animation-duration:1.1s;-webkit-animation-direction:reverse;animation-direction:reverse}
.wo-pay-wait .wo-dots{display:flex;justify-content:center;gap:6px;margin:10px 0 4px}
.wo-pay-wait .wo-dots i{width:6px;height:6px;border-radius:50%;background:#fed7c2;display:block;-webkit-animation:woDot 1.2s ease-in-out infinite;animation:woDot 1.2s ease-in-out infinite}
.wo-pay-wait .wo-dots i:nth-child(2){-webkit-animation-delay:.15s;animation-delay:.15s}
.wo-pay-wait .wo-dots i:nth-child(3){-webkit-animation-delay:.3s;animation-delay:.3s}
@-webkit-keyframes woSpin{to{-webkit-transform:rotate(360deg);transform:rotate(360deg)}}
@keyframes woSpin{to{-webkit-transform:rotate(360deg);transform:rotate(360deg)}}
@-webkit-keyframes woDot{0%,80%,100%{opacity:.35;transform:scale(.85)}40%{opacity:1;transform:scale(1.15)}}
@keyframes woDot{0%,80%,100%{opacity:.35;transform:scale(.85)}40%{opacity:1;transform:scale(1.15)}}
.wo-btn-loading{color:transparent!important;position:relative;pointer-events:none}
.wo-btn-loading:after{content:"";position:absolute;left:0;right:0;top:0;bottom:0;margin:auto;width:16px;height:16px;border:2px solid rgba(255,255,255,.5);border-top-color:#fff;border-radius:50%;-webkit-animation:woSpin .6s linear infinite;animation:woSpin .6s linear infinite}
.wo-pay-wait h2{margin:0 0 8px;font-size:20px;font-weight:800}
.wo-pay-wait p{margin:0 0 10px;color:#475569;font-size:14px;line-height:1.45}
.wo-ussd{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:14px 0 4px}
.wo-ussd div{border:1.5px solid #e2e8f0;border-radius:14px;padding:12px 10px;transition:border-color .2s,background .2s,box-shadow .2s}
.wo-ussd div.on{border-color:#f05018;background:#fff8f5;box-shadow:0 0 0 3px rgba(240,80,24,.12)}
.wo-ussd b{display:block;font-size:12px;color:#64748b;margin-bottom:4px}
.wo-ussd code{font-size:18px;font-weight:800;color:#002858}
#woPhone,#woPayPhone{font-size:16px!important}
</style>
{/literal}
</head>
<body class="theme-light">
<div id="page">
  <div class="header-bar header-fixed header-app header-bar-detached">
    {if $kit_logo}<img class="wo-hs-logo" src="{$kit_logo|escape}" alt="">{/if}
    <a href="{if $channel == 'PPPOE'}{$connectik_home}{else}{$hs_home}{/if}" class="header-title color-theme">{if $channel == 'PPPOE'}Connectik · {$router.name}{else}{$kit_title|default:$router.name}{/if}</a>
    <a href="#" class="show-on-theme-light default-link" data-toggle-theme><i class="bi bi-moon-fill font-13"></i></a>
    <a href="#" class="show-on-theme-dark default-link" data-toggle-theme><i class="bi bi-lightbulb-fill color-yellow-dark font-13"></i></a>
  </div>
  <div id="footer-bar" class="footer-bar footer-bar-detached">
    {if $channel == 'PPPOE'}
      <a href="{$connectik_home}"><i class="bi bi-house-fill font-16"></i><span>Zones</span></a>
      <a href="#" class="active-nav"><i class="bi bi-heart-fill font-15"></i><span>Connectik</span></a>
    {else}
      <a href="{$hs_home}"><i class="bi bi-house-fill font-16"></i><span>Home</span></a>
      <a href="{$hs_ticket}" class="active-nav"><i class="bi bi-heart-fill font-15"></i><span>Tarifs</span></a>
      <a href="{$recover_url}"><svg class="wo-nav-svg" viewBox="0 0 16 16" aria-hidden="true"><path fill="currentColor" d="M0 4.5A1.5 1.5 0 0 1 1.5 3h13A1.5 1.5 0 0 1 16 4.5V6a.5.5 0 0 1-.5.5 1.5 1.5 0 1 0 0 3 .5.5 0 0 1 .5.5v1.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 11.5V10a.5.5 0 0 1 .5-.5 1.5 1.5 0 0 0 0-3A.5.5 0 0 1 0 6V4.5Z"/></svg><span>Mon code</span></a>
    {/if}
  </div>
  <div class="page-content header-clear-medium">
    <div class="card card-style">
      <div class="content">
        {if $channel == 'PPPOE'}
          <h3>Recharge PPPoE</h3>
          <p class="mb-3">{if $pref_user}Ligne <b>{$pref_user|escape}</b>. Choisissez un forfait.{else}Saisissez votre identifiant de ligne, puis payez. Le mot de passe ne change pas.{/if}</p>
        {else}
          <h3>Payer par Mobile Money</h3>
          <p class="mb-3">Entrez votre numéro. Votre code s’affiche après le paiement.</p>
        {/if}
        {if isset($notify)}<div class="wo-pay-alert">{$notify}</div>{/if}
        <form method="post" action="{$pay_action}" id="woPay" class="demo-animation m-0">
          {if $channel != 'PPPOE' && isset($hs_mac) && $hs_mac}<input type="hidden" name="mac" value="{$hs_mac|escape}">{/if}
          {if $channel == 'PPPOE'}
            {if $pppoe_plans|@count == 0}
              <p>Aucun forfait PPPoE pour cette zone.</p>
            {else}
              {foreach $pppoe_plans as $p}
              <label class="wo-pay-plan"><input type="radio" name="plan_id" value="{$p.id}" {if $pref_plan == $p.id || ($pref_plan == 0 && $p@first)}checked{/if} required>
                <b>{$p.name_plan}</b> — {$p.price} FCFA
                <small>({$p.validity} {$p.validity_unit})</small>
              </label>
              {/foreach}
              <div class="form-custom form-label form-icon mb-3">
                <i class="bi bi-person-circle font-14"></i>
                <input class="form-control rounded-xs" name="pppoe_username" value="{$pref_user|escape}" placeholder="Identifiant PPPoE" required autocomplete="username">
              </div>
            {/if}
          {else}
            {if $sel_plan}
              <input type="hidden" name="plan_id" value="{$sel_plan.id}" data-price="{$sel_plan.price}">
              <div class="wo-pay-sel">
                <small>{$sel_plan.validity} {$sel_plan.validity_unit}{if $sel_plan.data_limit > 0} · {$sel_plan.data_limit} {$sel_plan.data_unit}{/if}</small>
                <strong>{$sel_plan.price} Fcfa</strong>
                <span>{$sel_plan.name_plan}</span>
              </div>
            {elseif $hotspot_plans|@count == 0}
              <p>Aucun forfait hotspot pour le moment.</p>
            {else}
              {foreach $hotspot_plans as $p}
              <label class="wo-pay-plan"><input type="radio" name="plan_id" value="{$p.id}" data-price="{$p.price}" {if $pref_plan == $p.id || ($pref_plan == 0 && $p@first)}checked{/if} required>
                <b>{$p.name_plan}</b> — {$p.price} FCFA
                <small>({$p.validity} {$p.validity_unit}{if $p.data_limit > 0} · {$p.data_limit} {$p.data_unit}{/if})</small>
              </label>
              {/foreach}
            {/if}
          {/if}
          <div class="form-custom form-label form-icon mb-3">
            <i class="bi bi-phone font-14"></i>
            <input class="form-control rounded-xs" name="phone" id="woPhone" value="{$pref_phone|escape}" placeholder="6XX XXX XXX" required inputmode="tel">
            <label class="color-theme">Téléphone</label>
          </div>
          <div class="wo-pay-op" id="woOp"><span class="dot"></span><span class="txt"></span></div>
          <p class="wo-phone-err" id="woPhoneErr"></p>
          {if $ref_on}
          <div class="wo-pts" id="woPts">
            <div id="woPtsTxt"></div>
            <label id="woPtsPay"><input type="checkbox" name="use_points" id="woUsePts" value="1"> Payer ce forfait avec mes points</label>
          </div>
          <p class="wo-ref-line"><button type="button" id="woRefOpen">Affiliation</button>
            <span class="wo-more"> · <button type="button" id="woRefMore">En savoir plus</button></span></p>
          <div class="wo-ref-box{if $pref_ref} on{/if}" id="woRefBox">
            <div class="form-custom form-label form-icon mb-3">
              <i class="bi bi-phone font-14"></i>
              <input class="form-control rounded-xs" name="ref_phone" id="woRefCode" value="{$pref_ref|escape}" placeholder="Numéro de l’ami" maxlength="16" inputmode="tel" autocomplete="off">
              <label class="color-theme">Numéro parrainé</label>
            </div>
            <p class="font-12 opacity-70 mb-0">Ce numéro recevra {$ref_points|default:50} points après votre paiement.</p>
          </div>
          {/if}
          <button type="submit" class="btn btn-full bg-highlight rounded-xs text-uppercase font-700 w-100 btn-s mt-4">Payer</button>
        </form>
        {if $channel != 'PPPOE'}
        <p class="text-center mt-3 mb-0"><a href="{$recover_url}">J’ai déjà payé — retrouver mon code</a></p>
        {/if}
      </div>
    </div>
  </div>
</div>
{if $ref_on}
<div class="wo-ref-modal" id="woRefModal">
  <div class="box">
    <h4>Affiliation et points</h4>
    <p>{$ref_help|escape}</p>
    <h4>Règles</h4>
    <p>{$ref_rules|escape}</p>
    <p class="font-12 opacity-70">{$ref_points|default:50} points offerts par affiliation · {$ref_bundle_pts|default:100} points pour un forfait (1 point = 1 FCFA).</p>
    <button type="button" class="btn btn-full bg-highlight rounded-xs w-100" id="woRefClose">Fermer</button>
  </div>
</div>
{/if}
<div class="wo-pay-scrim" id="woPayWait" hidden>
  <div class="wo-pay-wait">
    <div class="wo-spin-wrap"><div class="spin"></div><div class="spin2"></div></div>
    <h2>Validez sur votre téléphone</h2>
    <p>Une demande Mobile Money a été envoyée. Entrez votre code secret.</p>
    <div class="wo-dots" aria-hidden="true"><i></i><i></i><i></i></div>
    <div class="wo-ussd">
      <div id="woBoxMtn"><b>MTN MoMo</b><code>*126#</code></div>
      <div id="woBoxOm"><b>Orange Money</b><code>#150*50#</code></div>
    </div>
    <p>Pas de pop-up ? Composez le code USSD ci-dessus, puis confirmez. Cette page se mettra à jour toute seule.</p>
  </div>
</div>
<script src="{$kit_url}/scripts/bootstrap.min.js"></script>
<script src="{$kit_url}/scripts/custom.js?v=wo-hs10"></script>
<script src="{$app_url}/ui/ui_custom/wo-cm-phone.js?v=wo-hs10"></script>
{literal}
<script>
(function(){
  var f=document.getElementById('woPay');
  var el=document.getElementById('woPhone');
  var op=document.getElementById('woOp');
  var err=document.getElementById('woPhoneErr');
  if(window.woCmPhone) woCmPhone.bind(el, op, err);
  var open=document.getElementById('woRefOpen');
  var box=document.getElementById('woRefBox');
  if(open&&box){
    open.addEventListener('click', function(){
      box.classList.toggle('on');
      var i=document.getElementById('woRefCode');
      if(box.classList.contains('on')&&i) i.focus();
    });
  }
  var more=document.getElementById('woRefMore');
  var modal=document.getElementById('woRefModal');
  var close=document.getElementById('woRefClose');
  if(more&&modal){ more.addEventListener('click', function(){ modal.classList.add('on'); }); }
  if(close&&modal){ close.addEventListener('click', function(){ modal.classList.remove('on'); }); }
  if(modal){ modal.addEventListener('click', function(ev){ if(ev.target===modal) modal.classList.remove('on'); }); }
  var ptsBox=document.getElementById('woPts');
  var ptsTxt=document.getElementById('woPtsTxt');
  var ptsPay=document.getElementById('woPtsPay');
  var ptsUrl={/literal}'{$points_url|escape:"javascript"}'{literal};
  var bundleNeed={/literal}{$ref_bundle_pts|default:100}{literal};
  var lastPts=0;
  var t=null;
  function planPrice(){
    var sel=document.querySelector('input[name=plan_id]:checked')||document.querySelector('input[name=plan_id]');
    var n=sel?parseInt(sel.getAttribute('data-price')||'0',10):0;
    if(!(n>0)) n=bundleNeed;
    return n;
  }
  function renderPts(){
    if(!ptsBox) return;
    var need=planPrice();
    if(lastPts<1){
      ptsBox.classList.remove('on');
      if(ptsPay) ptsPay.style.display='none';
      var c=document.getElementById('woUsePts'); if(c) c.checked=false;
      return;
    }
    ptsBox.classList.add('on');
    var can=lastPts>=need;
    ptsTxt.textContent=can
      ? ('Vous avez '+lastPts+' point'+(lastPts>1?'s':'')+' — suffisant pour ce forfait ('+need+' F).')
      : ('Vous avez '+lastPts+' point'+(lastPts>1?'s':'')+'. Encore '+(need-lastPts)+' pour payer ce forfait ('+need+' F).');
    if(ptsPay) ptsPay.style.display=can?'flex':'none';
    if(!can){ var c=document.getElementById('woUsePts'); if(c) c.checked=false; }
  }
  function lookup(){
    if(!el||!ptsBox) return;
    var p=window.woCmPhone?woCmPhone.digits(el.value):String(el.value||'').replace(/\D+/g,'');
    if(p.length<9){ lastPts=0; renderPts(); return; }
    fetch(ptsUrl+(ptsUrl.indexOf('?')>=0?'&':'?')+'phone='+encodeURIComponent(p),{credentials:'same-origin'})
      .then(function(r){ return r.json(); })
      .then(function(d){
        lastPts=(d&&d.ok)?(parseInt(d.points,10)||0):0;
        renderPts();
      }).catch(function(){});
  }
  if(el){
    el.addEventListener('input', function(){
      clearTimeout(t);
      t=setTimeout(lookup, 400);
    });
    lookup();
  }
  document.querySelectorAll('input[name=plan_id]').forEach(function(r){
    r.addEventListener('change', renderPts);
  });
  function ussd(op){
    var orange=op==='ORANGE';
    var m=document.getElementById('woBoxMtn');
    var o=document.getElementById('woBoxOm');
    if(m) m.classList.toggle('on', !orange);
    if(o) o.classList.toggle('on', orange);
  }
  if(el){
    el.addEventListener('input', function(){
      var d=window.woCmPhone?woCmPhone.detect(el.value):'';
      if(d) ussd(d);
    });
  }
  if(!f) return;
  function showWait(scrim){
    if(!scrim) return;
    scrim.hidden=false;
    scrim.removeAttribute('hidden');
    scrim.classList.add('is-on');
    try{ document.documentElement.appendChild(scrim); }catch(e0){}
    try{ void scrim.offsetWidth; }catch(e){}
  }
  function afterPaint(fn){
    var run=function(){ setTimeout(fn, 160); };
    if(window.requestAnimationFrame) requestAnimationFrame(function(){ requestAnimationFrame(run); });
    else run();
  }
  f.addEventListener('submit', function(event){
    if(el && window.woCmPhone && !woCmPhone.valid(el.value)){
      event.preventDefault();
      return false;
    }
    if(f.getAttribute('data-off')){ event.preventDefault(); return false; }
    event.preventDefault();
    f.setAttribute('data-off','1');
    if(el && el.blur) el.blur();
    try{ if(document.activeElement && document.activeElement.blur) document.activeElement.blur(); window.scrollTo(0,0); }catch(e){}
    var b=f.querySelector('[type=submit]');
    if(b) b.classList.add('wo-btn-loading');
    var scrim=document.getElementById('woPayWait');
    var pts=document.getElementById('woUsePts');
    if(scrim && !(pts&&pts.checked)){
      var d=el&&window.woCmPhone?woCmPhone.detect(el.value):'';
      if(d) ussd(d);
      showWait(scrim);
    }
    afterPaint(function(){
      if(b) b.disabled=true;
      HTMLFormElement.prototype.submit.call(f);
    });
  });
  window.addEventListener('pageshow', function(){
    f.removeAttribute('data-off');
    var b2=f.querySelector('[type=submit]');
    if(b2){ b2.disabled=false; b2.classList.remove('wo-btn-loading'); }
    var sc2=document.getElementById('woPayWait');
    if(sc2){ sc2.hidden=true; sc2.setAttribute('hidden','hidden'); sc2.classList.remove('is-on'); }
  });
})();
</script>
{/literal}
</body>
</html>
