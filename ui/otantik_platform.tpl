{include file="otantik_shell_head.tpl"}
<div class="wo-head"><div><h1>Plateforme</h1><p>Commission, profit réel et clients Wifi-Otantik.</p></div></div>
<div class="wo-grid wo-4">
  <div class="wo-card wo-kpi"><div><span>Clients</span><h3>{$tenant_count}</h3></div><div class="wo-ico blue"><i class="fa fa-users"></i></div></div>
  <div class="wo-card wo-kpi"><div><span>CA du jour</span><h3>{$sales_today|string_format:"%.0f"}</h3></div><div class="wo-ico orange"><i class="fa fa-money"></i></div></div>
  <div class="wo-card wo-kpi"><div><span>Profit du jour</span><h3>{$profit_today|string_format:"%.0f"}</h3><small>Commission encaissée</small></div><div class="wo-ico green"><i class="fa fa-line-chart"></i></div></div>
  <div class="wo-card wo-kpi"><div><span>Profit total</span><h3>{$profit|string_format:"%.0f"}</h3><small>sur {$sales_all|string_format:"%.0f"} FCFA de CA</small></div><div class="wo-ico teal"><i class="fa fa-percent"></i></div></div>
</div>
<div class="wo-grid wo-2" style="margin-top:16px">
  <form class="wo-card" method="post">
    <input type="hidden" name="save_platform" value="1">
    <h3 style="margin-top:0">Commission plateforme</h3>
    <p style="color:var(--wo-muted)">Ce pourcentage est retiré à chaque vente. Le client ne voit et ne retire que le reste.</p>
    <p><label class="wo-label">Pourcentage prélevé (%)</label>
      <input class="wo-input" type="number" name="commission" min="0" max="90" step="0.5" value="{$commission}" required></p>
    <p>Exemple actuel : 1000 FCFA vendus → plateforme {$example_fee|string_format:"%.0f"} FCFA · client {$example_net|string_format:"%.0f"} FCFA.</p>
    <h3>Google Sign-In</h3>
    <p style="color:var(--wo-muted);font-size:13px">ID client OAuth (type « Application Web ») depuis Google Cloud. Origines autorisées : https://business.otantik.cm. Les codes OTP partent de noreply@otantik.cm.</p>
    <p><label class="wo-label">Google Client ID</label>
      <input class="wo-input" name="google_client_id" value="{$google_client_id|escape}" placeholder="xxxxx.apps.googleusercontent.com"></p>
    <button class="wo-btn wo-btn-primary" type="submit">Enregistrer</button>
  </form>
  <div class="wo-card">
    <span>Soldes nets chez les clients</span>
    <h2>{$wallet_sum|string_format:"%.0f"} FCFA</h2>
    <p style="color:var(--wo-muted)">Déjà après commission. Routeurs : {$router_count}.</p>
  </div>
</div>
<div class="wo-card" style="margin-top:16px">
  <b>Derniers clients</b>
  <div class="wo-filters" data-wo-filter>
    {include file="otantik_find.tpl" placeholder="Rechercher un client, un tél…"}
    <span class="wo-filters-meta" data-wo-count></span>
  </div>
  <table class="wo-table"><thead><tr><th>User</th><th>Tél</th><th>Solde net</th><th>% appliqué</th><th>Statut</th></tr></thead>
  <tbody>{foreach $tenants as $t}<tr data-wo-row data-wo-hay="{$t.user_id} {$t.phone|escape} {$t.status|escape}"><td>{$t.user_id}</td><td>{$t.phone}</td><td>{$t.wallet}</td><td>{if $t.commission_rate > 0}{$t.commission_rate}{else}{$commission}{/if}%</td><td>{$t.status}</td></tr>{/foreach}</tbody></table>
  <div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucun client ne correspond</div>
</div>
{include file="otantik_shell_foot.tpl"}
