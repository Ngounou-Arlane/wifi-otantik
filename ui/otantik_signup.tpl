<!DOCTYPE html>
<html lang="fr" class="wo-auth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inscription — {$company|default:'Wifi-Otantik'}</title>
  <link rel="icon" href="{$app_url}/ui/ui_custom/images/favicon.png" type="image/png">
  <link rel="stylesheet" href="{$app_url}/ui/ui/fonts/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="{$app_url}/ui/ui_custom/otantik-auth.css?v=20260819c">
</head>
<body class="wo-auth">
<div class="wo-auth-shell">
  <section class="wo-auth-form">
    <a class="wo-auth-brand" href="{$url_login}">
      <img src="{$app_url}/ui/ui_custom/images/logo.png" alt="Wifi-Otantik"> Wifi-Otantik
    </a>
    {if $otp_step}
    <h1>Confirmez<br>votre email</h1>
    <p class="wo-lead">Un code à 6 chiffres a été envoyé à {$otp_hint}</p>
    {else}
    <h1>Créer votre<br>espace Wifi</h1>
    <p class="wo-lead">Un compte pour connecter votre MikroTik et vendre du Wi‑Fi</p>
    {/if}

    {if isset($notify)}
      <div class="wo-auth-alert {if $notify_t == 's'}ok{else}err{/if}">{$notify}</div>
    {/if}

    {if $otp_step}
    <form method="post">
      {if $ref}<input type="hidden" name="ref" value="{$ref|escape}">{/if}
      <div class="wo-field">
        <label class="wo-lbl" for="signup_code">Code de vérification</label>
        <input class="wo-input wo-otp" id="signup_code" type="text" name="signup_code" required inputmode="numeric" autocomplete="one-time-code" maxlength="8" placeholder="000000" autofocus>
      </div>
      <button class="wo-auth-btn" type="submit">Créer mon compte</button>
    </form>
    <p class="wo-auth-foot"><a href="{$url_signup|default:'/inscription'}">Revenir à l’inscription</a></p>
    {else}
    <form id="woSignupForm" method="post">
      {if $ref}<input type="hidden" name="ref" value="{$ref|escape}">{/if}
      {if $ref_valid}
      <p class="wo-auth-ref"><i class="fa fa-user-plus"></i> Parrainé par <b>{$ref|escape}</b></p>
      {/if}
      <div class="wo-field">
        <label class="wo-lbl" for="fullname">Nom complet</label>
        <input class="wo-input" id="fullname" name="fullname" autocomplete="name" placeholder="Votre nom" required>
      </div>
      <div class="wo-field">
        <label class="wo-lbl" for="phone">Téléphone</label>
        <input class="wo-input" id="phone" name="phone" required inputmode="tel" placeholder="6XX XXX XXX">
      </div>
      <div class="wo-field">
        <label class="wo-lbl" for="email">Email</label>
        <input class="wo-input" id="email" type="email" name="email" required autocomplete="email" placeholder="vous@gmail.com" title="Exemple : vous@gmail.com" oninvalid="this.setCustomValidity('Indiquez un email du type vous@gmail.com')" oninput="this.setCustomValidity('');this.value=this.value.replace(/\s+/g,'')">
      </div>
      <div class="wo-field">
        <label class="wo-lbl" for="password">Mot de passe</label>
        <div class="wo-passwrap">
          <input class="wo-input" id="password" type="password" name="password" minlength="6" required autocomplete="new-password" placeholder="6 caractères minimum">
          <button class="wo-eye" type="button" id="woEye" aria-label="Afficher le mot de passe"><i class="fa fa-eye-slash"></i></button>
        </div>
      </div>
      <div class="wo-auth-actions">
        <button class="wo-auth-btn" type="submit">Recevoir le code</button>
        {include file="otantik_google_btn.tpl"}
      </div>
    </form>
    <p class="wo-auth-foot">Déjà client ? <a href="{$url_login}">Se connecter</a><br>
      <a href="{$url_help|default:'/aide'}">Besoin d’aide ?</a></p>
    {/if}
  </section>

  <aside class="wo-auth-art" aria-hidden="true">
    <div class="wo-auth-art-card">
      <img src="{$app_url}/ui/ui_custom/images/auth-signup.png?v=20260819c" alt="">
    </div>
  </aside>
</div>
{literal}
<script>
(function(){
  var b=document.getElementById('woEye');
  var i=document.getElementById('password');
  if(!b||!i) return;
  b.addEventListener('click', function(){
    var show=i.type==='password';
    i.type=show?'text':'password';
    b.innerHTML=show?'<i class="fa fa-eye"></i>':'<i class="fa fa-eye-slash"></i>';
  });
})();
</script>
{/literal}
</body>
</html>
