{if isset($pager) && $pager.show}
<nav class="wo-pager" data-wo-server="1" aria-label="Pagination">
  <span class="wo-pager-meta">{$pager.from}–{$pager.to} sur {$pager.total}</span>
  <div class="wo-pager-nav">
    {if $pager.page > 1}
      <a href="{$pager.url}{$pager.sep}{$pager.param}={$pager.prev}">Préc.</a>
    {else}
      <span class="is-off">Préc.</span>
    {/if}
    {foreach $pager.nums as $n}
      {if $n == 0}
        <span class="wo-pager-gap">…</span>
      {elseif $n == $pager.page}
        <a class="on" href="{$pager.url}{$pager.sep}{$pager.param}={$n}" aria-current="page">{$n}</a>
      {else}
        <a href="{$pager.url}{$pager.sep}{$pager.param}={$n}">{$n}</a>
      {/if}
    {/foreach}
    {if $pager.page < $pager.pages}
      <a href="{$pager.url}{$pager.sep}{$pager.param}={$pager.next}">Suiv.</a>
    {else}
      <span class="is-off">Suiv.</span>
    {/if}
  </div>
</nav>
{/if}
