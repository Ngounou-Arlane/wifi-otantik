{include file="otantik_shell_head.tpl"}
<div class="wo-head">
  <div>
    <h1>Abonnés</h1>
    <p>{if $filter_label}{$filter_label}{else}Par zone, hotspot ou PPPoE.{/if}</p>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    {if $filter_label}<a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_customers')}">Tous les abonnés</a>{/if}
    {include file="otantik_export.tpl" export_csv=$export_csv export_pdf=$export_pdf}
    <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_map')}"><i class="fa fa-map-marker"></i> Carte</a>
    <a class="wo-btn wo-btn-primary" href="{Text::url('plugin/wifi_otantik_tickets')}"><i class="fa fa-plus"></i> Créer un ticket / PPPoE</a>
  </div>
</div>
<div class="wo-card">
{if $rows|@count == 0 && $filter_q == '' && $filter_st == '' && $filter_ty == ''}
  <div class="wo-empty"><i class="fa fa-users"></i>{if $filter_label}Aucun inscrit sur cette période{else}Aucun abonné pour le moment{/if}</div>
{else}
<form method="get" class="wo-filters" data-wo-filter>
  {include file="otantik_get_keep.tpl"}
  {include file="otantik_find.tpl" placeholder="Rechercher un identifiant, une zone…" find_name="q" find_value=$filter_q}
  <select class="wo-select" name="st" data-wo-st onchange="this.form.submit()">
    <option value="">Tous les statuts</option>
    <option value="on"{if $filter_st == 'on'} selected{/if}>Ticket actif</option>
    <option value="off"{if $filter_st == 'off'} selected{/if}>Expiré</option>
    <option value="none"{if $filter_st == 'none'} selected{/if}>Aucun ticket</option>
  </select>
  <select class="wo-select" name="ty" data-wo-ty onchange="this.form.submit()">
    <option value="">Tous les types</option>
    <option value="Hotspot"{if $filter_ty == 'Hotspot'} selected{/if}>Hotspot</option>
    <option value="PPPOE"{if $filter_ty == 'PPPOE'} selected{/if}>PPPoE</option>
  </select>
  <span class="wo-filters-meta" data-wo-count></span>
</form>
{if $rows|@count == 0}
  <div class="wo-empty"><i class="fa fa-search"></i>Aucun résultat</div>
{else}
<table class="wo-table">
  <thead><tr><th>Identifiant</th><th>Zone</th><th>Téléphone</th><th>Inscrit</th><th>Service</th><th>Ticket</th><th>Expiration</th><th>Recharge</th><th></th></tr></thead>
  <tbody>
  {foreach $rows as $c}
    {assign var=cid value=$c.id}
    {assign var=ring value=$rings[$cid]}
    {assign var=st value='none'}
    {if !$ring.none && !$ring.expired}{assign var=st value='on'}{elseif !$ring.none}{assign var=st value='off'}{/if}
    <tr data-wo-row data-wo-status="{$st}" data-wo-type="{$c.service_type|default:'Hotspot'}" data-wo-hay="{$c.username|escape} {$c.phonenumber|escape} {$zones[$cid]|default:''|escape} {$ring.ticket|escape}">
      <td><b>{$c.username}</b></td>
      <td>{$zones[$cid]|default:'—'}</td>
      <td>{$c.phonenumber}</td>
      <td><small>{$c.created_at|default:'—'}</small></td>
      <td>{if $c.service_type == 'PPPOE'}<span class="wo-chip wait">PPPoE</span>{else}<span class="wo-chip ok">Hotspot</span>{/if}</td>
      <td>{if $ring.none}<span class="wo-muted">Aucun ticket</span>{else}<b>{$ring.ticket|default:'—'}</b>{/if}</td>
      <td>{include file="otantik_expiry_ring.tpl" ring=$ring}</td>
      <td>
        {if $c.service_type == 'PPPOE'}
          <a class="wo-btn wo-btn-primary" href="{Text::url('plugin/wifi_otantik_customers/recharge/')}{$c.id}">Recharger</a>
          <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_customers/pin/')}{$c.id}">Position</a>
        {/if}
        {if isset($share[$cid]) && $share[$cid]}
          <button type="button" class="wo-btn wo-btn-ghost" data-url="{$share[$cid]|escape}" onclick="woCopy(this.getAttribute('data-url'))">Copier</button>
          <a class="wo-btn wo-btn-primary" target="_blank" href="https://wa.me/?text={$share[$cid]|escape:'url'}">WhatsApp</a>
        {/if}
        <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_customers/wa/')}{$c.id}">Modèle</a>
      </td>
      <td>
        <a href="{Text::url('plugin/wifi_otantik_customers/kick/')}{$c.id}">Déconnecter</a>
        {if !$ring.none}
          <br><a href="{Text::url('plugin/wifi_otantik_customers/edit_recharge/')}{$c.id}">Modifier dates</a>
        {/if}
        <br><form method="post" action="{Text::url('plugin/wifi_otantik_customers/delete/')}{$c.id}" style="display:inline" onsubmit="return confirm('Supprimer définitivement {$c.username|escape:'javascript'} ? Cette action est irréversible.');">
          <input type="hidden" name="confirm_delete" value="1">
          <button type="submit" style="background:none;border:none;padding:0;color:#b91c1c;text-decoration:underline;cursor:pointer;font:inherit">Supprimer</button>
        </form>
      </td>
    </tr>
  {/foreach}
  </tbody>
</table>
<div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucun résultat</div>
{include file="otantik_pager.tpl"}
{/if}
{/if}
</div>
{include file="otantik_shell_foot.tpl"}
