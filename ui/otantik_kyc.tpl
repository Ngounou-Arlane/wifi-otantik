{include file="otantik_shell_head.tpl"}
<div class="wo-head"><div><h1>Conformité KYC</h1><p>Identité du compte pour les retraits MoMo.</p></div></div>
<div class="wo-card" style="max-width:640px">
  <p>Statut actuel : <span class="wo-chip {if $tenant.kyc_status == 'verified'}ok{elseif $tenant.kyc_status == 'pending'}wait{else}no{/if}">{$tenant.kyc_status|default:'none'}</span></p>
  <form method="post">
    <p><label class="wo-label">Pièce</label>
      <select class="wo-select" name="kyc_id_type">
        <option value="cni">CNI</option>
        <option value="passport">Passeport</option>
        <option value="nui">NUI / RCCM</option>
      </select></p>
    <p><label class="wo-label">Numéro</label><input class="wo-input" name="kyc_id_number" value="{$tenant.kyc_id_number}" required></p>
    <button class="wo-btn wo-btn-primary" type="submit">Envoyer le dossier</button>
  </form>
</div>
{include file="otantik_shell_foot.tpl"}
