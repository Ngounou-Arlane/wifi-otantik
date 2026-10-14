<!DOCTYPE html>
<html lang="fr" class="wo-auth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Aide — Wifi-Otantik</title>
  <link rel="icon" href="{$app_url}/ui/ui_custom/images/favicon.png" type="image/png">
  <link rel="stylesheet" href="{$app_url}/ui/ui_custom/otantik-auth.css?v=20260819c">
</head>
<body class="wo-auth">
<div class="wo-auth-shell">
  <section class="wo-auth-form">
    <a class="wo-auth-brand" href="{$url_login}">
      <img src="{$app_url}/ui/ui_custom/images/logo.png" alt="Wifi-Otantik"> Wifi-Otantik
    </a>
    <h1>Besoin<br>d’aide ?</h1>
    <p class="wo-lead">Connexion espace pro, mot de passe, ou code Wi‑Fi client.</p>
    <div class="wo-auth-alert ok">
      <b>Espace client / admin</b><br>
      Connectez-vous avec votre <b>téléphone</b> ou votre <b>email</b> et le mot de passe. Pas de code email à la connexion.
    </div>
    <p><a class="wo-auth-btn" href="{$url_login}">Se connecter</a></p>
    <p style="margin-top:14px"><a class="wo-auth-btn" href="{$url_forgot}" style="background:#002858">J’ai oublié mon mot de passe</a></p>
    <p class="wo-auth-foot">
      Pas encore de compte ? <a href="{$url_signup}">Créer un compte</a><br>
      Client Wi‑Fi, code perdu ? <a href="https://{$_c['otantik_host_portal']|default:'portal.otantik.cm'}/recuperer">Retrouver mon code</a>
    </p>
  </section>
  <aside class="wo-auth-art" aria-hidden="true"><div class="wo-auth-art-card"></div></aside>
</div>
</body>
</html>
