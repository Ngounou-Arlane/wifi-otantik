{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>{if $row}Modifier le revendeur{else}Nouveau revendeur{/if}</h1>
    <p>{if $row}Zones, commission et droits.{else}Un crédit de {$seed} FCFA est versé à la création.{/if}</p>
  </div>
  <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_resellers')}">Retour</a>
</div>
<form class="wo-card" method="post">
  <input type="hidden" name="save_reseller" value="1">
  <input type="hidden" name="perm_hotspot" value="0">
  <input type="hidden" name="perm_pppoe" value="0">
  <input type="hidden" name="perm_recharge" value="0">
  <div class="wo-row">
    <p><label class="wo-label">Nom <span class="wo-req">*</span></label>
      <input class="wo-input" name="name" value="{if $row}{$row.name|escape}{/if}" required></p>
    <p><label class="wo-label">Téléphone <span class="wo-req">*</span></label>
      <input class="wo-input" name="phone" value="{if $row}{$row.phone|escape}{/if}" required placeholder="6XX XXX XXX"></p>
  </div>
  <div class="wo-row">
    <p><label class="wo-label">Email</label>
      <input class="wo-input" type="email" name="email" value="{if $row}{$row.email|escape}{/if}"></p>
    <p><label class="wo-label">Commission (%) <span class="wo-req">*</span></label>
      <input class="wo-input" type="number" name="commission" min="0" max="90" step="0.5" value="{if $row}{$row.commission}{else}10{/if}" required>
      <small class="wo-muted">Ticket 1 000 F à 10 % → revendeur 100 F, vous 900 F.</small></p>
  </div>
  <p><label class="wo-label">Mot de passe {if !$row}<span class="wo-req">*</span>{/if}</label>
    <input class="wo-input" type="password" name="password" {if !$row}required{/if} minlength="6" autocomplete="new-password" placeholder="{if $row}Laisser vide pour ne pas changer{else}6 caractères min.{/if}"></p>
  {if $row}
  <p><label class="wo-label">Statut</label>
    <select class="wo-select" name="status">
      <option value="active" {if $row.status == 'active'}selected{/if}>Actif</option>
      <option value="disabled" {if $row.status == 'disabled'}selected{/if}>Désactivé</option>
    </select></p>
  {/if}
  <h3>Wifi Zones autorisées</h3>
  {if $routers|@count == 0}
    <p class="wo-muted">Créez d’abord une Wifi Zone.</p>
  {elseif $auto_zone}
    <input type="hidden" name="zone_ids[]" value="{$solo_zone.id}">
    <p>Zone unique : <b>{$solo_zone.name|escape}</b> — attribuée automatiquement.</p>
  {else}
    {foreach $routers as $z}
      <label class="wo-switch" style="margin:6px 0"><input type="checkbox" name="zone_ids[]" value="{$z.id}"{foreach $sel_zones as $sid}{if $sid == $z.id} checked{/if}{/foreach}> {$z.name|escape}</label>
    {/foreach}
  {/if}
  <h3>Droits</h3>
  <label class="wo-switch" style="margin:6px 0"><input type="checkbox" name="perm_hotspot" value="1" {if $perm.sell_hotspot}checked{/if}> Vendre des tickets hotspot</label>
  <label class="wo-switch" style="margin:6px 0"><input type="checkbox" name="perm_pppoe" value="1" {if $perm.sell_pppoe}checked{/if}> Vendre des lignes PPPoE</label>
  <label class="wo-switch" style="margin:6px 0"><input type="checkbox" name="perm_recharge" value="1" {if $perm.recharge}checked{/if}> Recharger sa caisse (Mobile Money)</label>
  <div style="text-align:right;margin-top:16px">
    <button class="wo-btn wo-btn-primary" type="submit">Enregistrer</button>
  </div>
</form>
{include file="otantik_shell_foot.tpl"}
