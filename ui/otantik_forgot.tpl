<!DOCTYPE html>
<html lang="fr" class="wo-auth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mot de passe oublié — Wifi-Otantik</title>
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
    <h1>Nouveau<br>mot de passe</h1>
    <p class="wo-lead">Entrez le code envoyé à {$otp_hint} puis choisissez un mot de passe</p>
    {else}
    <h1>J’ai oublié<br>mon mot de passe</h1>
    <p class="wo-lead">Indiquez le téléphone ou l’email du compte. Un code partira par email.</p>
    {/if}

    {if isset($notify)}
      <div class="wo-auth-alert {if $notify_t == 's'}ok{else}err{/if}">{$notify}</div>
    {/if}

    {if $otp_step}
    <form method="post">
      <div class="wo-field">
        <label class="wo-lbl" for="reset_code">Code de vérification</label>
        <input class="wo-input wo-otp" id="reset_code" type="text" name="reset_code" required inputmode="numeric" autocomplete="one-time-code" maxlength="8" placeholder="000000" autofocus>
      </div>
      <div class="wo-field">
        <label class="wo-lbl" for="new_password">Nouveau mot de passe</label>
        <input class="wo-input" id="new_password" type="password" name="new_password" minlength="6" required autocomplete="new-password">
      </div>
      <div class="wo-field">
        <label class="wo-lbl" for="new_password2">Confirmation</label>
        <input class="wo-input" id="new_password2" type="password" name="new_password2" minlength="6" required autocomplete="new-password">
      </div>
      <button class="wo-auth-btn" type="submit">Enregistrer</button>
    </form>
    {else}
    <form method="post">
      <div class="wo-field">
        <label class="wo-lbl" for="login">Téléphone ou email</label>
        <input class="wo-input" id="login" type="text" name="login" required autocomplete="username" placeholder="6XX XXX XXX ou vous@email.com" autofocus>
      </div>
      <button class="wo-auth-btn" type="submit">Recevoir le code</button>
    </form>
    {/if}
    <p class="wo-auth-foot"><a href="{$url_login}">Retour à la connexion</a> · <a href="{$url_help}">Besoin d’aide ?</a></p>
  </section>
  <aside class="wo-auth-art" aria-hidden="true"><div class="wo-auth-art-card"></div></aside>
</div>
</body>
</html>
