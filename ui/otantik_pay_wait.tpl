<!DOCTYPE HTML>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">
<title>Paiement en cours — {$company|escape}</title>
<link rel="icon" href="{$app_url}/ui/ui_custom/images/favicon.png" type="image/png">
<link rel="stylesheet" href="{$kit_url}/styles/bootstrap.css">
<link rel="stylesheet" href="{$kit_url}/fonts/bootstrap-icons.css">
<link rel="stylesheet" href="{$kit_url}/styles/style.css?v=wo-hs10">
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<meta id="theme-check" name="theme-color" content="#FFFFFF">
<style id="wo-hs-theme">{if isset($hs_theme_css)}{$hs_theme_css nofilter}{/if}</style>
<style>
.wo-hs-logo{height:32px;width:auto;max-width:88px;object-fit:contain;margin:0 8px 0 0;border-radius:6px}
.header-bar .header-title{display:flex;align-items:center}
.wo-wait-card{text-align:center}
.wo-wait-card .wo-spin-wrap{position:relative;width:56px;height:56px;margin:4px auto 18px}
.wo-wait-card .spin,.wo-wait-card .spin2{position:absolute;inset:0;border-radius:50%;border:3px solid #fed7c2;border-top-color:var(--wo-hs-accent,#f05018);-webkit-animation:woSpin .75s linear infinite;animation:woSpin .75s linear infinite;-webkit-transform:translateZ(0);transform:translateZ(0)}
.wo-wait-card .spin2{inset:8px;border-width:2px;border-color:#e2e8f0;border-top-color:#002858;-webkit-animation-duration:1.1s;animation-duration:1.1s;-webkit-animation-direction:reverse;animation-direction:reverse}
.wo-wait-card .wo-dots{display:flex;justify-content:center;gap:6px;margin:8px 0 12px}
.wo-wait-card .wo-dots i{width:6px;height:6px;border-radius:50%;background:#fed7c2;display:block;-webkit-animation:woDot 1.2s ease-in-out infinite;animation:woDot 1.2s ease-in-out infinite}
.wo-wait-card .wo-dots i:nth-child(2){-webkit-animation-delay:.15s;animation-delay:.15s}
.wo-wait-card .wo-dots i:nth-child(3){-webkit-animation-delay:.3s;animation-delay:.3s}
@-webkit-keyframes woSpin{to{-webkit-transform:rotate(360deg);transform:rotate(360deg)}}
@keyframes woSpin{to{-webkit-transform:rotate(360deg);transform:rotate(360deg)}}
@-webkit-keyframes woDot{0%,80%,100%{opacity:.35;transform:scale(.85)}40%{opacity:1;transform:scale(1.15)}}
@keyframes woDot{0%,80%,100%{opacity:.35;transform:scale(.85)}40%{opacity:1;transform:scale(1.15)}}
.wo-ussd{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:16px 0 8px}
.wo-ussd div{border:1.5px solid #e2e8f0;border-radius:14px;padding:10px 8px;transition:border-color .2s,background .2s,box-shadow .2s}
.wo-ussd div.on{border-color:var(--wo-hs-accent,#f05018);background:var(--wo-hs-wash,#fff8f5);box-shadow:0 0 0 3px rgba(240,80,24,.12)}
.wo-ussd b{display:block;font-size:12px;color:#64748b}
.wo-ussd code{font-size:20px;font-weight:800;color:var(--wo-hs-navy,#002858)}
.theme-dark .wo-ussd div{border-color:#333}
.theme-dark .wo-ussd code{color:#f8fafc}
#woWaitSt{transition:opacity .2s}
</style>
</head>
<body class="theme-light">
<div id="preloader"><div class="spinner-border color-highlight" role="status"></div></div>
<div id="page">
  <div class="header-bar header-fixed header-app header-bar-detached">
    {if $kit_logo}<img class="wo-hs-logo" src="{$kit_logo|escape}" alt="">{/if}
    <a href="{$hs_home}" class="header-title color-theme">{$kit_title|default:$company}</a>
    <a href="#" class="show-on-theme-light default-link" data-toggle-theme><i class="bi bi-moon-fill font-13"></i></a>
    <a href="#" class="show-on-theme-dark default-link" data-toggle-theme><i class="bi bi-lightbulb-fill color-yellow-dark font-13"></i></a>
  </div>
  <div class="page-content header-clear-medium">
    <div class="card card-style">
      <div class="content wo-wait-card">
        <div class="wo-spin-wrap"><div class="spin"></div><div class="spin2"></div></div>
        <h3>Validez sur votre téléphone</h3>
        <p class="mb-3">Une demande a été envoyée au <b>{$pay_wait_phone|escape}</b>. Confirmez avec votre code secret.</p>
        <div class="wo-dots" aria-hidden="true"><i></i><i></i><i></i></div>
        <div class="wo-ussd">
          <div{if $pay_ussd_op != 'ORANGE'} class="on"{/if}><b>MTN MoMo</b><code>*126#</code></div>
          <div{if $pay_ussd_op == 'ORANGE'} class="on"{/if}><b>Orange Money</b><code>#150*50#</code></div>
        </div>
        <p class="opacity-70 font-13 mb-3">Pas de message ? Composez <b>{$pay_ussd_code|escape}</b> ({$pay_ussd_label|escape}), puis confirmez.</p>
        <p id="woWaitSt" class="mb-0 font-13 font-700">En attente de confirmation…</p>
        <a href="{$pay_back}" class="default-link d-block mt-3">Annuler et revenir</a>
      </div>
    </div>
  </div>
</div>
<script src="{$kit_url}/scripts/bootstrap.min.js"></script>
<script src="{$kit_url}/scripts/custom.js?v=wo-hs10"></script>
<script>
window.addEventListener('pageshow', function(ev){
  // Safari's back-forward cache restores a full in-memory snapshot of this
  // page - including a polling loop that already stopped - regardless of
  // the Cache-Control headers sent for the original request. Force a real
  // reload whenever that happens so it isn't stuck showing a dead page.
  if (ev.persisted) { location.reload(); }
});
(function(){
  var u = '{$pay_stat_url|escape:"javascript"}';
  var fail = '{$pay_fail|escape:"javascript"}';
  var st = document.getElementById('woWaitSt');
  var tries = 0;
  var msgs = ['En attente de confirmation…', 'Toujours en attente… vérifiez votre téléphone', 'Patientez, la confirmation peut prendre un instant'];
  function tick(){
    fetch(u, {credentials:'same-origin'}).then(function(r){ return r.json(); }).then(function(d){
      if (d.status === 'paid') { location.replace(location.href.split('#')[0]); return; }
      if (d.status === 'fail') { location.replace(fail || location.href); return; }
      tries++;
      if (st && tries > 1) { st.style.opacity = 0; setTimeout(function(){ st.textContent = msgs[Math.min(tries - 1, msgs.length - 1)]; st.style.opacity = 1; }, 200); }
      setTimeout(tick, 2000);
    }).catch(function(){ setTimeout(tick, 3000); });
  }
  tick();
})();
</script>
</body>
</html>
