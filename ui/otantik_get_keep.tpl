{if isset($pager) && $pager.route}<input type="hidden" name="_route" value="{$pager.route|escape}">{/if}
{if isset($pager.keep)}
{foreach $pager.keep as $hk => $hv}
{if $hv !== ''}<input type="hidden" name="{$hk|escape}" value="{$hv|escape}">{/if}
{/foreach}
{/if}
