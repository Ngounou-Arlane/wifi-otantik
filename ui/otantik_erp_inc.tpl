<link rel="stylesheet" href="{$app_url}/ui/ui_custom/otantik-erp.css?v=20260820erp4">
<div class="wo-erp-orbs" aria-hidden="true"><span></span><span></span><span></span></div>
<div class="wo-erp wo-erp-alive">
<nav class="wo-erp-nav wo-erp-reveal">
  <a class="{if $erp_nav == 'home'}on{/if}" href="{Text::url('plugin/wifi_otantik_erp')}"><i class="fa fa-dashboard"></i> Vue d’ensemble</a>
  <a class="{if $erp_nav == 'flow'}on{/if}" href="{Text::url('plugin/wifi_otantik_erp/flow')}"><i class="fa fa-exchange"></i> Trésorerie</a>
  <a class="{if $erp_nav == 'expenses'}on{/if}" href="{Text::url('plugin/wifi_otantik_erp/expenses')}"><i class="fa fa-shopping-cart"></i> Dépenses</a>
  <a class="{if $erp_nav == 'documents'}on{/if}" href="{Text::url('plugin/wifi_otantik_erp/documents')}"><i class="fa fa-file-text-o"></i> Devis &amp; factures</a>
  <a class="{if $erp_nav == 'assets'}on{/if}" href="{Text::url('plugin/wifi_otantik_erp/assets')}"><i class="fa fa-cubes"></i> Actifs</a>
  <a class="wo-btn wo-btn-primary" style="margin-left:auto" href="{Text::url('plugin/wifi_otantik_erp')}#woIncomeForm"><i class="fa fa-plus"></i> Autre revenu</a>
  <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_erp/sync')}"><i class="fa fa-refresh"></i> Synchroniser</a>
</nav>
