{include file="otantik_shell_head.tpl"}
{if !$print_mode}
<div class="wo-head no-print">
  <div>
    <h1>Reçu</h1>
    <p>{$receipt.number|escape} · {$receipt.date|escape}</p>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    {if $sales_tabs}
    <div class="wo-seg">
      <a href="{Text::url('plugin/wifi_otantik_tickets')}">Tickets</a>
      <a href="{Text::url('plugin/wifi_otantik_sales')}">Ventes</a>
      <a class="on" href="{Text::url('plugin/wifi_otantik_receipts')}">Reçus</a>
    </div>
    {/if}
    <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_receipts')}"><i class="fa fa-arrow-left"></i> Liste</a>
    {if $otantik_is_platform}
    <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_receipts/edit/')}{$receipt.id}"><i class="fa fa-pencil"></i> Modifier</a>
    <form method="post" action="{Text::url('plugin/wifi_otantik_receipts/delete/')}{$receipt.id}" style="display:inline" onsubmit="return confirm('Supprimer définitivement ce reçu et sa vente ? Le solde sera ajusté en conséquence. Cette action est irréversible.');">
      <input type="hidden" name="confirm_delete" value="1">
      <button type="submit" class="wo-btn wo-btn-danger"><i class="fa fa-trash"></i> Supprimer</button>
    </form>
    {/if}
    <a class="wo-btn wo-btn-primary" href="{Text::url('plugin/wifi_otantik_receipts/print/')}{$receipt.id}" target="_blank"><i class="fa fa-print"></i> Imprimer</a>
  </div>
</div>
{/if}

<div class="wo-rx-stage{if $print_mode} wo-rx-print{/if}">
  <div class="wo-rx-themes no-print">
    <button type="button" class="on" data-rx-theme="ticket">Coloré</button>
    <button type="button" data-rx-theme="glass">Glass</button>
    <button type="button" data-rx-theme="neon">Neon</button>
  </div>
  <div class="wo-rx-ticket wo-rx-theme-ticket is-{$receipt.tone|escape}{if $print_mode} wo-rx-print-color{/if}" id="woReceipt">
    <div class="wo-rx-ticket-shine"></div>
    <header class="wo-rx-ticket-h">
      <div class="wo-rx-brand">
        <img src="{$app_url}/ui/ui_custom/images/logo.png" alt="">
        <div>
          <b>{$receipt_company|escape}</b>
          <small>Reçu de paiement · {$receipt.method|escape}</small>
        </div>
      </div>
      <div class="wo-rx-stamp">PAYÉ</div>
    </header>

    <div class="wo-rx-ticket-body">
      <div class="wo-rx-row-top">
        <div>
          <span class="wo-rx-k">N° de reçu</span>
          <strong class="wo-rx-v mono">{$receipt.number|escape}</strong>
        </div>
        <div class="wo-rx-right">
          <span class="wo-rx-k">Date</span>
          <strong class="wo-rx-v">{$receipt.date|escape}</strong>
        </div>
      </div>

      <div class="wo-rx-amount-block">
        <span>Montant encaissé</span>
        <b>{$receipt.amount|string_format:"%.0f"} <i>FCFA</i></b>
      </div>

      <div class="wo-rx-grid2">
        <div class="wo-rx-cell">
          <span class="wo-rx-k">Client</span>
          <strong>{$receipt.phone|escape}</strong>
        </div>
        <div class="wo-rx-cell">
          <span class="wo-rx-k">Moyen</span>
          <strong>{$receipt.method|escape}</strong>
        </div>
        <div class="wo-rx-cell">
          <span class="wo-rx-k">Wifi Zone</span>
          <strong>{$receipt.zone|escape}</strong>
        </div>
        <div class="wo-rx-cell">
          <span class="wo-rx-k">Type</span>
          <strong>{$receipt.kind|escape}</strong>
        </div>
      </div>

      <div class="wo-rx-line">
        <div>
          <span class="wo-rx-k">Forfait</span>
          <strong>{$receipt.plan|escape}</strong>
        </div>
        <div class="wo-rx-line-price">{$receipt.amount|string_format:"%.0f"} F</div>
      </div>

      {if $receipt.code}
      <div class="wo-rx-codebox">
        <span class="wo-rx-k">{if $receipt.kind == 'PPPoE'}Identifiants{else}Code Wi‑Fi{/if}</span>
        <div class="wo-rx-code">{$receipt.code|escape}</div>
        {if $receipt.kind == 'PPPOE' && $receipt.pass}
        <div class="wo-rx-pass">Mot de passe · <b>{$receipt.pass|escape}</b></div>
        {/if}
      </div>
      {/if}

      {if $receipt.gateway_trx}
      <p class="wo-rx-ref">Réf. transaction · {$receipt.gateway_trx|escape}</p>
      {/if}
    </div>

    <div class="wo-rx-perforation" aria-hidden="true"></div>

    <footer class="wo-rx-ticket-f">
      <div>
        <small>Merci pour votre confiance</small>
        <b>Conservez ce reçu comme preuve d’achat</b>
      </div>
      <div class="wo-rx-qr">
        <img src="{$receipt_qr_url|escape:'html'}" width="88" height="88" alt="QR code du reçu">
        <small>Scanner pour vérifier</small>
      </div>
    </footer>
  </div>

  {if $print_mode}
  <div class="wo-rx-print-bar no-print">
    <p class="wo-rx-print-hint">Pour un PDF coloré : cochez <b>Graphiques d’arrière-plan</b> dans la fenêtre d’impression.</p>
    <button type="button" class="wo-btn wo-btn-primary" onclick="window.print()"><i class="fa fa-print"></i> Imprimer / PDF</button>
    <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_receipts/view/')}{$receipt.id}">Retour</a>
  </div>
  <script>
  window.addEventListener('load', function(){
    var img = document.querySelector('.wo-rx-qr img');
    var go = function(){ setTimeout(function(){ window.print(); }, 250); };
    if (img && !img.complete) {
      img.addEventListener('load', go);
      img.addEventListener('error', go);
      setTimeout(go, 2000);
    } else {
      go();
    }
  });
  </script>
  {else}
  <script>
  (function(){
    var root = document.getElementById('woReceipt');
    var btns = document.querySelectorAll('[data-rx-theme]');
    if(!root || !btns.length) return;
    btns.forEach(function(b){
      b.addEventListener('click', function(){
        btns.forEach(function(x){ x.classList.remove('on'); });
        b.classList.add('on');
        root.classList.remove('wo-rx-theme-ticket','wo-rx-theme-glass','wo-rx-theme-neon');
        root.classList.add('wo-rx-theme-' + b.getAttribute('data-rx-theme'));
      });
    });
  })();
  </script>
  {/if}
</div>
{include file="otantik_shell_foot.tpl"}
