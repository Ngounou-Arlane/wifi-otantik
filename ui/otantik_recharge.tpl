{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Recharger · {$cust.username|escape}</h1>
    <p>Zone {$router.name|escape}. Le mot de passe de la ligne ne change pas.</p>
  </div>
  <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_customers')}">Retour</a>
</div>
<div class="wo-card" style="max-width:560px">
  {if $plans|@count == 0}
    <p>Aucun forfait PPPoE sur cette zone. Créez-en un dans Mes tarifs.</p>
    <a class="wo-btn wo-btn-primary" href="{Text::url('plugin/wifi_otantik_plans')}">Mes tarifs</a>
  {else}
  <form method="post">
    <input type="hidden" name="recharge_pppoe" value="1">
    <p>
      <label class="wo-label">Forfait</label>
      <select class="wo-select" name="plan_id" required>
        {foreach $plans as $p}
        <option value="{$p.id}">{$p.name_plan|escape} — {$p.price} FCFA ({$p.validity} {$p.validity_unit})</option>
        {/foreach}
      </select>
    </p>
    <p class="wo-paid-toggle">
      <input type="hidden" name="paid" value="0">
      <label><input type="checkbox" name="paid" value="1" checked> Déjà payé — ajoute le montant au chiffre d’affaires</label>
    </p>
    <div style="text-align:right;margin-top:12px">
      <button class="wo-btn wo-btn-primary" type="submit">Recharger la ligne</button>
    </div>
  </form>
  {/if}
</div>
{include file="otantik_shell_foot.tpl"}
