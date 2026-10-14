<label class="wo-find">
  <i class="fa fa-search" aria-hidden="true"></i>
  <input class="wo-find-input" data-wo-q type="search" placeholder="{$placeholder|default:'Rechercher…'}" autocomplete="off" spellcheck="false"{if $find_id} id="{$find_id}"{/if}{if $find_name} name="{$find_name}" value="{$find_value|escape}"{/if}>
  <button type="button" class="wo-find-x" hidden aria-label="Effacer">&times;</button>
</label>
