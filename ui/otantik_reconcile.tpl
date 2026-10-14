{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Réconciliation</h1>
    <p>Vérifie que chaque client qui a payé a bien reçu un ticket - en base de données et sur le routeur. Les ventes payées il y a moins d’une heure sont reprovisionnées automatiquement toutes les 5 minutes ; les plus anciennes restent à votre décision ci-dessous.</p>
  </div>
</div>

<div class="wo-grid wo-2" style="margin-bottom:16px">
  <div class="wo-card wo-kpi"><div><span>Ventes payées sans ticket créé</span><h3>{$gaps|@count}</h3><small>14 derniers jours</small></div><div class="wo-ico {if $gaps|@count > 0}orange{else}green{/if}"><i class="fa fa-exclamation-triangle"></i></div></div>
  <div class="wo-card wo-kpi"><div><span>Montant concerné</span><h3>{$gap_total|string_format:"%.0f"} F</h3></div><div class="wo-ico blue"><i class="fa fa-money"></i></div></div>
</div>

<div class="wo-card" style="margin-bottom:16px">
  <b style="font-size:16px;margin-bottom:4px;display:block">Ventes payées, jamais provisionnées</b>
  <p class="wo-muted" style="font-size:13px;margin:0 0 12px">Le paiement a été reçu et encaissé, mais aucun ticket n’a été créé - ni en base, ni sur le routeur. C’est exactement ce qui est arrivé le 3 octobre : le client paie, mais son appareil n’obtient jamais l’accès. « Reprovisionner » rejoue la création du ticket et le pousse sur le routeur maintenant.</p>
  {if $gaps|@count == 0}
    <div class="wo-empty"><i class="fa fa-check-circle"></i>Aucun écart - tous les paiements récents ont un ticket</div>
  {else}
  <div class="wo-table-wrap">
  <table class="wo-table">
    <thead><tr><th>Payé le</th><th>Client</th><th>Montant</th><th>Tarif</th><th>Zone</th><th>Paiement</th><th></th></tr></thead>
    <tbody>
    {foreach $gaps as $g}
      <tr>
        <td><small>{$g.paid_date|escape}</small></td>
        <td><b>{$g.username|escape}</b></td>
        <td>{$g.price|string_format:"%.0f"} F</td>
        <td>{$g.plan_name|escape}</td>
        <td>{$g.routers|escape}</td>
        <td>{$g.payment_method|escape}</td>
        <td>
          <form method="post" action="{Text::url('plugin/wifi_otantik_reconcile/reprovision/')}{$g.id}" onsubmit="return confirm('Créer et pousser ce ticket pour {$g.username|escape:'javascript'} maintenant ?');">
            <input type="hidden" name="confirm" value="1">
            <button class="wo-btn wo-btn-primary" style="padding:6px 12px" type="submit">Reprovisionner</button>
          </form>
        </td>
      </tr>
    {/foreach}
    </tbody>
  </table>
  </div>
  {/if}
</div>

<div class="wo-card">
  <b style="font-size:16px;margin-bottom:4px;display:block">Vérifier directement sur le routeur</b>
  <p class="wo-muted" style="font-size:13px;margin:0 0 12px">Compare les tickets actifs en base à ce qui existe réellement sur chaque routeur - utile si un routeur a été réinitialisé ou si sa mémoire locale a été perdue. Nécessite une connexion en direct, peut prendre quelques secondes.</p>
  {if $routers|@count == 0}
    <div class="wo-empty"><i class="fa fa-wifi"></i>Aucune Wifi Zone</div>
  {else}
  {foreach $routers as $r}
  <div class="wo-recon-router" data-rid="{$r.id}" style="border-top:1px solid var(--wo-border);padding:12px 0">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap">
      <b>{$r.name|escape}</b>
      <button type="button" class="wo-btn wo-btn-ghost" style="padding:6px 12px" onclick="woReconCheck({$r.id})">Vérifier maintenant</button>
    </div>
    <div class="wo-recon-result" id="woReconResult{$r.id}" style="margin-top:8px;font-size:13px"></div>
  </div>
  {/foreach}
  {/if}
</div>

<div class="wo-card" style="margin-top:16px">
  <b style="font-size:16px;margin-bottom:4px;display:block">Dernières exécutions automatiques</b>
  <p class="wo-muted" style="font-size:13px;margin:0 0 12px">Journal des tentatives faites par le cron (toutes les 5 minutes, uniquement pour les ventes payées il y a moins d’une heure).</p>
  {if $logs|@count == 0}
    <div class="wo-empty"><i class="fa fa-clock-o"></i>Aucune tentative automatique pour le moment</div>
  {else}
  <div class="wo-table-wrap">
  <table class="wo-table">
    <thead><tr><th>Quand</th><th>Client</th><th>Résultat</th><th>Détail</th></tr></thead>
    <tbody>
    {foreach $logs as $l}
      <tr>
        <td><small>{$l.created_at|escape}</small></td>
        <td>{$l.username|escape}</td>
        <td>{if $l.ok}<span class="wo-chip ok">Réussi</span>{else}<span class="wo-chip no">Échec</span>{/if}</td>
        <td><small class="wo-muted">{$l.message|escape}</small></td>
      </tr>
    {/foreach}
    </tbody>
  </table>
  </div>
  {/if}
</div>

{literal}
<script>
function woReconCheck(rid){
  var box = document.getElementById('woReconResult' + rid);
  box.innerHTML = '<span class="wo-muted"><i class="fa fa-spinner fa-spin"></i> Vérification en cours…</span>';
  var ctrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
  var to = ctrl ? setTimeout(function(){ ctrl.abort(); }, 15000) : null;
  fetch('{/literal}{Text::url('plugin/wifi_otantik_reconcile/check_router/')}{literal}' + rid, {credentials:'same-origin', signal: ctrl ? ctrl.signal : undefined})
    .then(function(r){ return r.json(); })
    .then(function(d){
      if (to) clearTimeout(to);
      if (!d.ok) {
        box.innerHTML = '<span style="color:#b91c1c">' + (d.error || 'Routeur injoignable') + '</span>';
        return;
      }
      if (!d.missing || !d.missing.length) {
        box.innerHTML = '<span style="color:#15803d"><i class="fa fa-check-circle"></i> ' + d.checked + ' ticket(s) actif(s) vérifié(s) - tous présents sur le routeur</span>';
        return;
      }
      var html = '<div style="color:#b91c1c;margin-bottom:6px"><i class="fa fa-exclamation-triangle"></i> ' + d.missing.length + ' ticket(s) actif(s) en base mais absent(s) du routeur (' + d.checked + ' vérifié(s)) :</div><ul style="margin:0;padding-left:18px">';
      d.missing.forEach(function(m){
        html += '<li>' + m.username + ' · ' + m.plan + ' · expire ' + m.expiration + ' (' + m.kind + ')</li>';
      });
      html += '</ul>';
      box.innerHTML = html;
    })
    .catch(function(){
      box.innerHTML = '<span style="color:#b91c1c">Délai dépassé - le routeur n’a pas répondu</span>';
    });
}
</script>
{/literal}
{include file="otantik_shell_foot.tpl"}
