{if isset($ring)}
<div class="wo-ring wo-ring-{$ring.tone}{if $ring.none} is-empty{/if}" title="{$ring.label|escape}">
  <svg viewBox="0 0 44 44" aria-hidden="true">
    <circle class="wo-ring-track" cx="22" cy="22" r="18" fill="none" stroke-width="4"></circle>
    <circle class="wo-ring-val" cx="22" cy="22" r="18" fill="none" stroke-width="4" stroke-linecap="round" stroke-dasharray="113.1" stroke-dashoffset="{$ring.offset}" transform="rotate(-90 22 22)"></circle>
  </svg>
  <span>{$ring.label}</span>
</div>
{/if}
