{include file="otantik_shell_head.tpl"}
<div class="wo-ref">
  <section class="wo-ref-hero">
    <div class="wo-ref-orbits" aria-hidden="true">
      <span></span><span></span><span></span>
    </div>
    <div class="wo-ref-hero-copy">
      <span class="wo-ref-badge"><i class="fa fa-star"></i> Ambassadeur Wifi-Otantik</span>
      <h1>{$first_name}, votre lien ouvre des Wifi Zone.</h1>
      <p>Partagez Wifi-Otantik comme un secret de métier : un MikroTik, des tickets, <b>{$keep_pct|string_format:"%.0f"}&nbsp;%</b> de chaque vente sur le portefeuille.</p>
      <div class="wo-ref-hero-stats">
        <div>
          <strong>{$keep_pct|string_format:"%.0f"}%</strong>
          <span>gardés à chaque ticket</span>
        </div>
        <div>
          <strong>{$count}</strong>
          <span>{if $count <= 1}compte parrainé{else}comptes parrainés{/if}</span>
        </div>
        <div>
          <strong>{$wallet|string_format:"%.0f"}</strong>
          <span>FCFA déjà encaissés</span>
        </div>
      </div>
      <div class="wo-ref-cta">
        <button type="button" class="wo-btn wo-btn-primary wo-ref-copy" data-copy="{$link|escape}" onclick="woCopy(this.getAttribute('data-copy'))"><i class="fa fa-link"></i> Copier mon lien</button>
        <a class="wo-ref-soc wa" target="_blank" rel="noopener" href="{$wa_share|escape}"><i class="fa fa-whatsapp"></i> WhatsApp</a>
        <a class="wo-ref-soc tg" target="_blank" rel="noopener" href="{$tg_share|escape}"><i class="fa fa-paper-plane"></i> Telegram</a>
        <a class="wo-ref-soc fb" target="_blank" rel="noopener" href="{$fb_share|escape}"><i class="fa fa-facebook"></i> Facebook</a>
      </div>
    </div>
    <aside class="wo-ref-code">
      <small>Votre code</small>
      <b>{$code}</b>
      <input class="wo-input" readonly onclick="this.select()" value="{$link|escape}">
      <button type="button" class="wo-btn wo-btn-ghost" data-copy="{$share_text|escape}" onclick="woCopy(this.getAttribute('data-copy'))">Copier le message prêt</button>
    </aside>
  </section>

  <section class="wo-ref-offers">
    <h2>L’offre à raconter</h2>
    <p class="wo-ref-lead">Trois cartes, les vrais chiffres de votre tarif. Envoyez-les telles quelles.</p>
    <div class="wo-ref-cards">
      {foreach $pitch_cards as $card}
      <article class="wo-ref-card" style="--wo-ref-i:{$card@index}">
        <span>{$card.kicker}</span>
        <h3>{$card.title}</h3>
        <p>{$card.body}</p>
        <button type="button" class="wo-btn wo-btn-ghost" data-copy="{$card.copy|escape}" onclick="woCopy(this.getAttribute('data-copy'))"><i class="fa fa-share-alt"></i> Copier pour envoyer</button>
      </article>
      {/foreach}
    </div>
  </section>

  <section class="wo-ref-how">
    <h2>Comment ça marche</h2>
    <ol class="wo-ref-steps">
      <li style="--wo-ref-i:0">
        <i>1</i>
        <div>
          <strong>Vous envoyez le lien</strong>
          <span>WhatsApp, Telegram ou Facebook — votre code <b>{$code}</b> voyage avec.</span>
        </div>
      </li>
      <li style="--wo-ref-i:1">
        <i>2</i>
        <div>
          <strong>Ils créent leur espace</strong>
          <span>Inscription sur business.otantik.cm, MikroTik branché, tarifs hotspot ou PPPoE.</span>
        </div>
      </li>
      <li style="--wo-ref-i:2">
        <i>3</i>
        <div>
          <strong>Ils vendent, ils encaissent</strong>
          <span>{$keep_pct|string_format:"%.0f"}&nbsp;% de chaque ticket sur leur portefeuille. Vous restez leur parrain.</span>
        </div>
      </li>
    </ol>
  </section>

  <section class="wo-ref-proof">
    <div class="wo-ref-proof-h">
      <h2>Votre réseau</h2>
      {if $kyc_status == 'verified'}
      <span class="wo-chip {$kyc_chip}">Identité {$kyc_label|lower}</span>
      {else}
      <a class="wo-chip wait" href="{Text::url('plugin/wifi_otantik_profile')}&tab=kyc">KYC · {$kyc_label}</a>
      {/if}
    </div>
    {if $filleuls|@count == 0 && $filter_q == ''}
    <p class="wo-ref-empty">Personne n’a encore ouvert de compte avec votre lien. Un message WhatsApp suffit souvent.</p>
    {else}
    <form method="get" class="wo-filters" data-wo-filter>
      {include file="otantik_get_keep.tpl"}
      {include file="otantik_find.tpl" placeholder="Rechercher un filleul…" find_name="q" find_value=$filter_q}
      <span class="wo-filters-meta" data-wo-count></span>
    </form>
    {if $filleuls|@count == 0}
    <div class="wo-empty"><i class="fa fa-search"></i>Aucun filleul ne correspond</div>
    {else}
    <ul class="wo-ref-list">
      {foreach $filleuls as $f}
      <li data-wo-row data-wo-hay="{$f.name|escape}">
        <span class="wo-ref-ava">{$f.initial|escape}</span>
        <div>
          <b>{$f.name|escape}</b>
          <small>{if $f.when}Inscrit le {$f.when}{else}Inscrit avec votre code{/if}</small>
        </div>
      </li>
      {/foreach}
    </ul>
    <div class="wo-empty" data-wo-empty style="display:none"><i class="fa fa-search"></i>Aucun filleul ne correspond</div>
    {include file="otantik_pager.tpl"}
    {/if}
    {/if}
    <p class="wo-ref-note">Les inscriptions via votre lien sont enregistrées à votre nom. Le tarif plateforme actuel est de {$fee_pct|string_format:"%.0f"}&nbsp;% — sur 1&nbsp;000 FCFA, {$example_net|string_format:"%.0f"} FCFA restent sur le portefeuille. Une commission ambassadeur sur les ventes de vos filleuls n’est pas encore versée.</p>
  </section>
</div>
{include file="otantik_shell_foot.tpl"}
