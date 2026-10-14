{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Supprimer {$user.username|escape}</h1>
    <p>Cette action est définitive et irréversible.</p>
  </div>
  <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_tenants')}">Retour aux clients</a>
</div>
<div class="wo-zone-new">
  <div class="wo-card" style="max-width:600px;border:1px solid #fecaca">
    <p style="color:#b91c1c;font-weight:700;margin-top:0"><i class="fa fa-warning"></i> Suppression irréversible</p>
    <p class="wo-muted" style="font-size:14px">Supprimer <b>{$user.username|escape}</b>{if $user.fullname} ({$user.fullname|escape}){/if} effacera définitivement, pour ce client uniquement :</p>
    <ul style="font-size:14px;line-height:1.9;margin:0 0 16px">
      <li><b>{$impact.routers}</b> zone(s) Wifi / routeur(s)</li>
      <li><b>{$impact.customers}</b> abonné(s) (PPPoE / Hotspot)</li>
      <li><b>{$impact.paid_count}</b> vente(s) payée(s), soit <b>{$impact.sales_total|string_format:"%.0f"} FCFA</b> d’historique financier</li>
      <li><b>{$impact.staff}</b> compte(s) du personnel (Gérant, Vendeur, etc.)</li>
      <li><b>{$impact.resellers}</b> revendeur(s)</li>
      <li><b>{$impact.erp_docs}</b> devis/facture(s)</li>
      <li>Le solde à retirer, l’historique de retraits, la trésorerie ERP, les dépenses, les logs et tous les fichiers uploadés (logo, audio, KYC, justificatifs)</li>
    </ul>
    <p style="font-size:13px;color:var(--wo-muted)">Aucune sauvegarde n’est conservée après cette action. Si ce client a juste besoin d’être suspendu, utilisez plutôt le statut « Inactif » depuis la liste des clients.</p>
    <form method="post" onsubmit="return confirm('Dernière confirmation : supprimer définitivement {$user.username|escape:'javascript'} et toutes ses données ?');">
      <input type="hidden" name="confirm_delete" value="1">
      <p><label class="wo-label">Tapez <code>{$user.username|escape}</code> pour confirmer</label>
        <input class="wo-input" name="confirm_username" autocomplete="off" required placeholder="{$user.username|escape}"></p>
      <div style="text-align:right;margin-top:12px">
        <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_tenants')}">Annuler</a>
        <button class="wo-btn wo-btn-danger" type="submit">Supprimer définitivement</button>
      </div>
    </form>
  </div>
</div>
{include file="otantik_shell_foot.tpl"}
