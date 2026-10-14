{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Modifier le tarif</h1>
    <p>{$p.name_plan} · {$p.routers} · {if $kind == 'PPPOE'}PPPoE{else}Hotspot{/if}</p>
  </div>
  <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_plans')}">Retour</a>
</div>

<form class="wo-plan-card {if $kind == 'PPPOE'}is-pppoe{else}is-featured{/if} wo-plan-create" method="post" action="{Text::url('plugin/wifi_otantik_plans/edit/')}{$p.id}" onsubmit="return woPlanReady(this)">
  <input type="hidden" name="plan_type" value="{$kind}">
  <div class="wo-plan-card-h">
    <div>
      <i class="fa {if $kind == 'PPPOE'}fa-plug{else}fa-wifi{/if} wo-plan-gem"></i>
      <strong>{if $kind == 'PPPOE'}PPPoE{else}Hotspot{/if}</strong>
      <small>{$p.routers}</small>
    </div>
    <div class="wo-plan-price">
      <input type="number" name="price" min="0" value="{$p.price}" required>
      <em>F</em>
    </div>
  </div>
  <input class="wo-plan-name" name="name_plan" value="{$p.name_plan|escape}" required>
  <ul class="wo-plan-feats">
    <li class="ok"><i class="fa fa-clock-o"></i> Durée
      <span class="wo-plan-inline">
        <input type="number" name="validity" min="1" value="{$p.validity}" required>
        <select name="validity_unit">
          <option value="Mins" {if $p.validity_unit == 'Mins'}selected{/if}>Min</option>
          <option value="Hrs" {if $p.validity_unit == 'Hrs'}selected{/if}>Heures</option>
          <option value="Days" {if $p.validity_unit == 'Days'}selected{/if}>Jours</option>
        </select>
      </span>
    </li>
    <li class="ok"><i class="fa fa-arrow-down"></i> Descendant
      <span class="wo-plan-inline">
        <input type="number" name="rate_down" min="1" value="{if $p.bw_down > 0}{$p.bw_down}{else}1{/if}" required> <em>Mbps</em>
      </span>
    </li>
    <li class="ok"><i class="fa fa-arrow-up"></i> Montant
      <span class="wo-plan-inline">
        <input type="number" name="rate_up" min="1" value="{if $p.bw_up > 0}{$p.bw_up}{else}1{/if}" required> <em>Mbps</em>
      </span>
    </li>
    <li class="ok"><i class="fa fa-database"></i> Volume
      <span class="wo-plan-inline">
        <input type="number" name="data_limit" min="0" value="{$p.data_limit|default:0}" required>
        <select name="data_unit">
          <option value="MB" {if $p.data_unit != 'GB'}selected{/if}>Mo</option>
          <option value="GB" {if $p.data_unit == 'GB'}selected{/if}>Go</option>
        </select>
      </span>
    </li>
    {if $kind == 'Hotspot'}
    <li class="ok"><i class="fa fa-users"></i> Sessions
      <span class="wo-plan-inline">
        <input type="number" name="shared_users" min="1" value="{if $p.shared_users > 0}{$p.shared_users}{else}1{/if}" required>
      </span>
    </li>
    {else}
    <input type="hidden" name="shared_users" value="1">
    {/if}
    <li class="ok"><i class="fa fa-bolt"></i> Burst
      <span class="wo-plan-inline">
        <select name="burst_preset" class="wo-plan-burst">
          <option value="none" {if $p.burst_preset == 'none'}selected{/if}>Aucun</option>
          <option value="leger" {if $p.burst_preset == 'leger'}selected{/if}>Léger 1,5× / 8s</option>
          <option value="standard" {if $p.burst_preset == 'standard'}selected{/if}>Standard 2× / 8s</option>
          <option value="fort" {if $p.burst_preset == 'fort'}selected{/if}>Fort 3× / 10s</option>
        </select>
      </span>
    </li>
  </ul>
  <p class="wo-plan-hint">Volume 0 = illimité. Les changements sont renvoyés au MikroTik.</p>
  <button class="wo-plan-cta" type="submit">Enregistrer les modifications</button>
</form>
{literal}
<script>
window.woPlanReady = function(f){
  var b=f.querySelector('[type=submit]');
  if(b.disabled) return false;
  b.disabled=true;
  return true;
};
</script>
{/literal}
{include file="otantik_shell_foot.tpl"}
